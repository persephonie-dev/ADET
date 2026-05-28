<?php
// ============================================================
//  api/bookings.php
//  Requires login for all actions except price preview.
//
//  POST /api/bookings.php?action=create
//      Creates a new booking + booking_rooms record.
//      Body: room_id, check_in_date, check_out_date,
//            adults_count, children_count, special_requests,
//            promo_code (optional)
//
//  GET  /api/bookings.php?action=my
//      Returns all bookings for the logged-in user.
//
//  GET  /api/bookings.php?action=detail&booking_id=N
//      Returns full detail for one booking (user must own it).
//
//  POST /api/bookings.php?action=cancel&booking_id=N
//      Cancels a booking if still in Pending or Confirmed state.
//
//  GET  /api/bookings.php?action=preview
//      Returns price breakdown without creating a booking.
//      Params: room_id, check_in_date, check_out_date, promo_code
// ============================================================

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// Price preview is public; all other actions need a login.
if ($action !== 'preview') {
    require_login();
}

switch ($action) {
    case 'create':  handle_create($pdo);  break;
    case 'my':      handle_my($pdo);      break;
    case 'detail':  handle_detail($pdo);  break;
    case 'cancel':  handle_cancel($pdo);  break;    
    case 'preview': handle_preview($pdo); break;
    default:
        json_response(['error' => 'Unknown action.'], 400);
}

// ============================================================
//  CREATE BOOKING
//  Wraps everything in a transaction so if any step fails,
//  nothing is saved to the database.
// ============================================================
function handle_create(PDO $pdo): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['error' => 'POST required.'], 405);
    }

    $body       = json_decode(file_get_contents('php://input'), true);
    $roomId     = (int)($body['room_id']       ?? 0);
    $checkIn    = $body['check_in_date']        ?? '';
    $checkOut   = $body['check_out_date']       ?? '';
    $adults     = (int)($body['adults_count']   ?? 1);
    $children   = (int)($body['children_count'] ?? 0);
    $special    = $body['special_requests']     ?? '';
    $promoCode  = trim($body['promo_code']      ?? '');

    // --- Validate required fields ---
    if (!$roomId || !$checkIn || !$checkOut || $adults < 1) {
        json_response(['error' => 'room_id, check_in_date, check_out_date, and adults_count are required.'], 422);
    }
    if ($checkOut <= $checkIn) {
        json_response(['error' => 'check_out must be after check_in.'], 422);
    }

    // --- Calculate number of nights ---
    $nights = (int)((strtotime($checkOut) - strtotime($checkIn)) / 86400);
    if ($nights < 1) {
        json_response(['error' => 'Minimum stay is 1 night.'], 422);
    }

    // --- Fetch the room and confirm it exists ---
    $stmt = $pdo->prepare("
        SELECT r.room_id, r.price_per_night, r.room_number,
               r.capacity, rs.status_name -- Updated to r.capacity
        FROM rooms r
        JOIN room_types rt  ON r.room_type_id   = rt.room_type_id
        JOIN room_status rs ON r.room_status_id = rs.room_status_id
        WHERE r.room_id = ?
    ");
    $stmt->execute([$roomId]);
    $room = $stmt->fetch();

    if (!$room) {
        json_response(['error' => 'Room not found.'], 404);
    }
    if ($room['status_name'] !== 'Available') {
        json_response(['error' => 'Room is not available for booking.'], 409);
    }
    // Updated to check against capacity
    if (($adults + $children) > (int)$room['capacity']) {
        json_response(['error' => 'Guest count exceeds room capacity of ' . $room['capacity'] . '.'], 422);
    }

    // --- Double-check availability (re-run the overlap query) ---
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM booking_rooms br
        JOIN bookings b        ON br.booking_id       = b.booking_id
        JOIN booking_status bs ON b.booking_status_id = bs.booking_status_id
        WHERE br.room_id = ?
          AND bs.status_name NOT IN ('Cancelled', 'No Show')
          AND br.check_in_date  < ?
          AND br.check_out_date > ?
    ");
    $stmt->execute([$roomId, $checkOut, $checkIn]);
    if ((int)$stmt->fetchColumn() > 0) {
        json_response(['error' => 'Room is already booked for those dates.'], 409);
    }

    // --- Calculate pricing ---
    $pricePerNight = (float)$room['price_per_night'];
    $subtotal      = $pricePerNight * $nights;
    $discount      = 0;
    $promoId       = null;

    // --- Apply promo code if provided ---
    if ($promoCode) {
        $promo = validate_promo($pdo, $promoCode);
        if ($promo) {
            $promoId  = $promo['promotion_id'];
            $discount = calculate_discount($subtotal, $promo);
        } else {
            json_response(['error' => 'Promo code is invalid or expired.'], 422);
        }
    }

    $totalAmount = round($subtotal - $discount, 2);

    // --- booking_status_id 1 = Pending ---
    $pendingStatusId = get_status_id($pdo, 'booking_status', 'booking_status_id', 'Pending');

    // --- Wrap in a transaction ---
    $pdo->beginTransaction();
    try {
        // Insert the booking record.
        $stmt = $pdo->prepare("
            INSERT INTO bookings
                (user_id, booking_status_id, check_in_date, check_out_date,
                 adults_count, children_count, total_amount, special_requests)
            VALUES
                (:user_id, :status_id, :check_in, :check_out,
                 :adults, :children, :total, :special)
        ");
        $stmt->execute([
            ':user_id'   => $_SESSION['user_id'],
            ':status_id' => $pendingStatusId,
            ':check_in'  => $checkIn,
            ':check_out' => $checkOut,
            ':adults'    => $adults,
            ':children'  => $children,
            ':total'     => $totalAmount,
            ':special'   => $special,
        ]);
        $bookingId = (int)$pdo->lastInsertId();

        // Insert the booking_rooms junction record.
        // price_per_night is saved here to lock in the rate at booking time.
        $stmt = $pdo->prepare("
            INSERT INTO booking_rooms
                (booking_id, room_id, check_in_date, check_out_date,
                 price_per_night, nights, guest_count)
            VALUES
                (:booking_id, :room_id, :check_in, :check_out,
                 :price, :nights, :guests)
        ");
        $stmt->execute([
            ':booking_id' => $bookingId,
            ':room_id'    => $roomId,
            ':check_in'   => $checkIn,
            ':check_out'  => $checkOut,
            ':price'      => $pricePerNight,
            ':nights'     => $nights,
            ':guests'     => $adults + $children,
        ]);

        // If a promo was used, link it to this booking.
        if ($promoId) {
            $stmt = $pdo->prepare("
                INSERT INTO booking_promotions (booking_id, promotion_id)
                VALUES (?, ?)
            ");
            $stmt->execute([$bookingId, $promoId]);
        }

        $pdo->commit();

        json_response([
            'success'    => true,
            'booking_id' => $bookingId,
            'total'      => $totalAmount,
            'nights'     => $nights,
        ], 201);

    } catch (Exception $e) {
        $pdo->rollBack();
        json_response(['error' => 'Booking failed. Please try again.', 'detail' => $e->getMessage()], 500);
    }
}

// ============================================================
//  MY BOOKINGS
//  Returns all bookings for the logged-in user, newest first.
// ============================================================
function handle_my(PDO $pdo): void
{
    $stmt = $pdo->prepare("
        SELECT
            b.booking_id,
            b.check_in_date,
            b.check_out_date,
            b.adults_count,
            b.children_count,
            b.total_amount,
            b.booking_date,
            b.special_requests,
            bs.status_name AS booking_status,
            -- Collect room info (single room per booking in our design)
            r.room_number,
            rt.type_name AS room_type,
            rt.bed_type,
            br.nights,
            br.price_per_night,
            -- Payment status if any payment exists
            (
                SELECT ps.status_name
                FROM payments p
                JOIN payment_status ps ON p.payment_status_id = ps.payment_status_id
                WHERE p.booking_id = b.booking_id
                ORDER BY p.created_at DESC
                LIMIT 1
            ) AS payment_status
        FROM bookings b
        JOIN booking_status bs  ON b.booking_status_id  = bs.booking_status_id
        JOIN booking_rooms br   ON br.booking_id        = b.booking_id
        JOIN rooms r            ON br.room_id           = r.room_id
        JOIN room_types rt      ON r.room_type_id       = rt.room_type_id
        WHERE b.user_id = ?
        ORDER BY b.booking_date DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    json_response($stmt->fetchAll());
}

// ============================================================
//  BOOKING DETAIL
//  Returns full info for one booking. User must own it.
// ============================================================
function handle_detail(PDO $pdo): void
{
    $bookingId = (int)($_GET['booking_id'] ?? 0);
    if (!$bookingId) {
        json_response(['error' => 'booking_id is required.'], 422);
    }

    $stmt = $pdo->prepare("
        SELECT
            b.*,
            bs.status_name AS booking_status,
            r.room_number,
            r.floor_number,
            rt.type_name, rt.description AS room_description,
            rt.bed_type, 
            r.capacity AS max_capacity,  -- Fixed: Use individual room capacity, aliased for safety
            br.nights, br.price_per_night, br.guest_count
        FROM bookings b
        JOIN booking_status bs ON b.booking_status_id = bs.booking_status_id
        JOIN booking_rooms br  ON br.booking_id       = b.booking_id
        JOIN rooms r           ON br.room_id          = r.room_id
        JOIN room_types rt     ON r.room_type_id      = rt.room_type_id
        WHERE b.booking_id = ?
    ");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();

    if (!$booking) {
        json_response(['error' => 'Booking not found.'], 404);
    }

    // Guests can only see their own bookings.
    // Admins (role_id 1) can see all bookings.
    if ((int)$_SESSION['role_id'] !== 1
        && (int)$booking['user_id'] !== (int)$_SESSION['user_id']) {
        json_response(['error' => 'Access denied.'], 403);
    }

    // Fetch payments for this booking.
    $stmt = $pdo->prepare("
        SELECT p.amount_paid, p.transaction_reference, p.created_at,
               pm.method_name, ps.status_name AS payment_status
        FROM payments p
        JOIN payment_methods pm ON p.payment_method_id = pm.payment_method_id
        JOIN payment_status ps  ON p.payment_status_id  = ps.payment_status_id
        WHERE p.booking_id = ?
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$bookingId]);
    $booking['payments'] = $stmt->fetchAll();

    // Fetch promo code if one was applied.
    $stmt = $pdo->prepare("
        SELECT pr.promo_code, pr.promo_name, pr.discount_type, pr.discount_value
        FROM booking_promotions bp
        JOIN promotions pr ON bp.promotion_id = pr.promotion_id
        WHERE bp.booking_id = ?
    ");
    $stmt->execute([$bookingId]);
    $booking['promotions'] = $stmt->fetchAll();

    json_response($booking);
}

// ============================================================
//  CANCEL BOOKING
//  Only Pending or Confirmed bookings can be cancelled.
// ============================================================
function handle_cancel(PDO $pdo): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['error' => 'POST required.'], 405);
    }

    $bookingId = (int)($_GET['booking_id'] ?? 0);
    if (!$bookingId) {
        json_response(['error' => 'booking_id is required.'], 422);
    }

    // Confirm ownership.
    $stmt = $pdo->prepare("
        SELECT b.user_id, bs.status_name
        FROM bookings b
        JOIN booking_status bs ON b.booking_status_id = bs.booking_status_id
        WHERE b.booking_id = ?
    ");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();

    if (!$booking) {
        json_response(['error' => 'Booking not found.'], 404);
    }
    if ((int)$_SESSION['role_id'] !== 1
        && (int)$booking['user_id'] !== (int)$_SESSION['user_id']) {
        json_response(['error' => 'Access denied.'], 403);
    }
    if (!in_array($booking['status_name'], ['Pending', 'Confirmed'])) {
        json_response(['error' => "Cannot cancel a booking with status '{$booking['status_name']}'. "], 409);
    }

    $cancelledId = get_status_id($pdo, 'booking_status', 'booking_status_id', 'Cancelled');

    $stmt = $pdo->prepare("
        UPDATE bookings SET booking_status_id = ? WHERE booking_id = ?
    ");
    $stmt->execute([$cancelledId, $bookingId]);

    json_response(['success' => true]);
}

// ============================================================
//  PRICE PREVIEW
//  Returns a cost breakdown without creating a booking.
// ============================================================
function handle_preview(PDO $pdo): void
{
    $roomId    = (int)($_GET['room_id']       ?? 0);
    $checkIn   = $_GET['check_in_date']       ?? '';
    $checkOut  = $_GET['check_out_date']      ?? '';
    $promoCode = trim($_GET['promo_code']     ?? '');

    if (!$roomId || !$checkIn || !$checkOut) {
        json_response(['error' => 'room_id, check_in_date, and check_out_date are required.'], 422);
    }

    $nights = (int)((strtotime($checkOut) - strtotime($checkIn)) / 86400);
    if ($nights < 1) {
        json_response(['error' => 'Invalid date range.'], 422);
    }

    $stmt = $pdo->prepare("SELECT price_per_night FROM rooms WHERE room_id = ?");
    $stmt->execute([$roomId]);
    $room = $stmt->fetch();
    if (!$room) {
        json_response(['error' => 'Room not found.'], 404);
    }

    $pricePerNight = (float)$room['price_per_night'];
    $subtotal      = $pricePerNight * $nights;
    $discount      = 0;
    $promoName     = null;

    if ($promoCode) {
        $promo = validate_promo($pdo, $promoCode);
        if ($promo) {
            $discount  = calculate_discount($subtotal, $promo);
            $promoName = $promo['promo_name'];
        }
    }

    json_response([
        'price_per_night' => $pricePerNight,
        'nights'          => $nights,
        'subtotal'        => $subtotal,
        'discount'        => $discount,
        'promo_name'      => $promoName,
        'total'           => round($subtotal - $discount, 2),
    ]);
}

// ============================================================
//  SHARED HELPERS
// ============================================================

/**
 * Validate a promo code and return the promo row if valid.
 */
function validate_promo(PDO $pdo, string $code): array|false
{
    $stmt = $pdo->prepare("
        SELECT * FROM promotions
        WHERE promo_code = ?
          AND is_active = 1
          AND start_date <= CURDATE()
          AND end_date   >= CURDATE()
    ");
    $stmt->execute([$code]);
    return $stmt->fetch() ?: false;
}

/**
 * Calculate the discount amount given a subtotal and a promo row.
 */
function calculate_discount(float $subtotal, array $promo): float
{
    if ($promo['discount_type'] === 'Percentage') {
        return round($subtotal * ($promo['discount_value'] / 100), 2);
    }
    // Fixed discount — do not discount below zero.
    return min((float)$promo['discount_value'], $subtotal);
}

/**
 * Fetch the integer PK for a status name from any lookup table.
 * E.g. get_status_id($pdo, 'booking_status', 'booking_status_id', 'Pending')
 */
function get_status_id(PDO $pdo, string $table, string $pkCol, string $name): int
{
    $stmt = $pdo->prepare("SELECT $pkCol FROM $table WHERE status_name = ?");
    $stmt->execute([$name]);
    $row = $stmt->fetch();
    if (!$row) {
        throw new RuntimeException("Status '$name' not found in table '$table'.");
    }
    return (int)$row[$pkCol];
}