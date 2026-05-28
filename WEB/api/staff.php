<?php
// 
//  api/staff.php
//  Staff-facing API. Requires role_id 3 (Staff) OR 1 (Admin).
//
//  GET  ?action=dashboard      — arrivals, departures, room counts
//  GET  ?action=bookings       — all bookings with optional filters
//  POST ?action=update_booking — change a booking status
//                                (Check In / Check Out only)
//  GET  ?action=rooms          — room list for housekeeping view
//  POST ?action=update_room    — change room status
//  GET  ?action=messages       — contact messages
//  POST ?action=update_message — mark message read/replied
//  GET  ?action=guests         — guest lookup by name or email
// 

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

//Auth: Staff (role 3) OR Admin (role 1) may call this file ──
if (empty($_SESSION['user_id'])) {
    json_response(['error' => 'Not authenticated.'], 401);
}
$userRole = (int)($_SESSION['role_id'] ?? 0);
if ($userRole !== 1 && $userRole !== 3) {
    json_response(['error' => 'Access denied. Staff or Admin role required.'], 403);
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'dashboard': handle_dashboard($pdo); break;
    case 'bookings': handle_bookings($pdo); break;
    case 'arrivals': handle_arrivals($pdo); break;  
    case 'departures': handle_departures($pdo); break; 
    case 'search_bookings': handle_search_bookings($pdo); break;
    case 'update_booking': handle_update_booking($pdo); break;
    case 'rooms':          handle_rooms($pdo);          break;
    case 'update_room':    handle_update_room($pdo);    break;
    case 'messages':       handle_messages($pdo);       break;
    case 'update_message': handle_update_message($pdo); break;
    case 'guests':         handle_guests($pdo);         break;
    default:
        json_response(['error' => 'Unknown action.'], 400);
}

// 
//  DASHBOARD
//  Returns all data the staff_dashboard.php page needs:
//    - today's arrivals (Confirmed bookings checking in today)
//    - today's departures (Checked In bookings due out today)
//    - stat counts
//    - room status breakdown
// 
function handle_dashboard(PDO $pdo): void
{
    //Today's arrivals — Confirmed bookings with check_in = today ──
    $stmt = $pdo->query("
        SELECT
            b.booking_id, b.check_in_date, b.check_out_date,
            b.adults_count, b.children_count, b.special_requests,
            bs.status_name AS booking_status,
            CONCAT(u.first_name, ' ', u.last_name) AS guest_name,
            r.room_number,
            rt.type_name AS room_type
        FROM bookings b
        JOIN booking_status bs  ON b.booking_status_id = bs.booking_status_id
        LEFT JOIN users u       ON b.user_id           = u.user_id
        JOIN booking_rooms br   ON br.booking_id       = b.booking_id
        JOIN rooms r            ON br.room_id          = r.room_id
        JOIN room_types rt      ON r.room_type_id      = rt.room_type_id
        WHERE b.check_in_date = CURDATE()
          AND bs.status_name  = 'Confirmed'
        ORDER BY b.booking_id ASC
    ");
    $arrivals = $stmt->fetchAll();

    //Today's departures — Checked In bookings with check_out = today ──
    $stmt = $pdo->query("
        SELECT
            b.booking_id, b.check_in_date, b.check_out_date,
            b.total_amount,
            bs.status_name AS booking_status,
            CONCAT(u.first_name, ' ', u.last_name) AS guest_name,
            r.room_number,
            rt.type_name AS room_type
        FROM bookings b
        JOIN booking_status bs  ON b.booking_status_id = bs.booking_status_id
        LEFT JOIN users u       ON b.user_id           = u.user_id
        JOIN booking_rooms br   ON br.booking_id       = b.booking_id
        JOIN rooms r            ON br.room_id          = r.room_id
        JOIN room_types rt      ON r.room_type_id      = rt.room_type_id
        WHERE b.check_out_date = CURDATE()
          AND bs.status_name   = 'Checked In'
        ORDER BY b.booking_id ASC
    ");
    $departures = $stmt->fetchAll();

    //Room status counts ──
    $roomCounts = $pdo->query("
        SELECT rs.status_name, COUNT(*) AS cnt
        FROM rooms r
        JOIN room_status rs ON r.room_status_id = rs.room_status_id
        GROUP BY rs.status_name
    ")->fetchAll();

    $roomMap = [];
    foreach ($roomCounts as $row) {
        $roomMap[$row['status_name']] = (int)$row['cnt'];
    }

    //Scalar counts ──
    $checkedInCount = (int)$pdo->query("
        SELECT COUNT(*) FROM bookings b
        JOIN booking_status bs ON b.booking_status_id = bs.booking_status_id
        WHERE bs.status_name = 'Checked In'
    ")->fetchColumn();

    $newMessages = (int)$pdo->query(
        "SELECT COUNT(*) FROM contact_messages WHERE message_status = 'New'"
    )->fetchColumn();

    json_response([
        'today_arrivals'      => count($arrivals),
        'today_departures'    => count($departures),
        'currently_checked_in'=> $checkedInCount,
        'available_rooms'     => $roomMap['Available']    ?? 0,
        'new_messages'        => $newMessages,
        'arrivals'            => $arrivals,
        'departures'          => $departures,
        'rooms_available'     => $roomMap['Available']    ?? 0,
        'rooms_occupied'      => $roomMap['Occupied']     ?? 0,
        'rooms_maintenance'   => $roomMap['Maintenance']  ?? 0,
        'rooms_reserved'      => $roomMap['Reserved']     ?? 0,
    ]);
}

// 
//  BOOKINGS
//  Returns bookings filtered by status and/or search term.
//  Used by staff/checkin.php and staff/bookings.php.
// 
function handle_bookings(PDO $pdo): void
{
    $statusFilter = $_GET['status'] ?? '';
    $search       = trim($_GET['search'] ?? '');

    $sql = "
        SELECT
            b.booking_id, b.check_in_date, b.check_out_date,
            b.adults_count, b.children_count, b.total_amount,
            b.booking_date, b.special_requests,
            bs.status_name AS booking_status,
            CONCAT(u.first_name, ' ', u.last_name) AS guest_name,
            u.email AS guest_email, u.phone_number AS guest_phone,
            r.room_number,
            rt.type_name AS room_type
        FROM bookings b
        JOIN booking_status bs  ON b.booking_status_id = bs.booking_status_id
        LEFT JOIN users u       ON b.user_id           = u.user_id
        JOIN booking_rooms br   ON br.booking_id       = b.booking_id
        JOIN rooms r            ON br.room_id          = r.room_id
        JOIN room_types rt      ON r.room_type_id      = rt.room_type_id
        WHERE 1=1
    ";
    $params = [];

    if ($statusFilter) {
        $sql     .= ' AND bs.status_name = ?';
        $params[] = $statusFilter;
    }
    if ($search) {
        $sql     .= " AND (CONCAT(u.first_name,' ',u.last_name) LIKE ? OR u.email LIKE ? OR r.room_number LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $sql .= ' ORDER BY b.check_in_date ASC, b.booking_id ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    json_response($stmt->fetchAll());
}

// 
//  UPDATE BOOKING STATUS
//  Staff can only move bookings to Checked In or Checked Out.
//  The room status is updated automatically to match.
//  Body: booking_id, status
// 
function handle_update_booking(PDO $pdo): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['error' => 'POST required.'], 405);
    }

    $body      = json_decode(file_get_contents('php://input'), true);
    $bookingId = (int)($body['booking_id'] ?? 0);
    $status    = $body['status'] ?? '';

    // Staff are limited to these two status transitions.
    // Admin actions (Confirmed, Cancelled, etc.) go through api/admin.php.
    $allowed = ['Checked In', 'Checked Out'];
    if (!$bookingId || !in_array($status, $allowed)) {
        json_response(['error' => "Status must be 'Checked In' or 'Checked Out'."], 422);
    }

    // Look up the booking_status_id
    $stmt = $pdo->prepare("SELECT booking_status_id FROM booking_status WHERE status_name = ?");
    $stmt->execute([$status]);
    $newStatusId = (int)$stmt->fetchColumn();
    if (!$newStatusId) {
        json_response(['error' => 'Booking status not found in lookup table.'], 500);
    }

    // Verify the booking exists
    $stmt = $pdo->prepare("SELECT booking_id FROM bookings WHERE booking_id = ?");
    $stmt->execute([$bookingId]);
    if (!$stmt->fetch()) {
        json_response(['error' => 'Booking not found.'], 404);
    }

    $pdo->beginTransaction();
    try {
        // Update booking status
        $pdo->prepare("UPDATE bookings SET booking_status_id = ? WHERE booking_id = ?")
            ->execute([$newStatusId, $bookingId]);

        // Sync the physical room status
        if ($status === 'Checked In') {
            // Room becomes Occupied when guest arrives
            $pdo->prepare("
                UPDATE rooms r
                JOIN booking_rooms br ON br.room_id = r.room_id
                JOIN room_status rs   ON rs.status_name = 'Occupied'
                SET r.room_status_id = rs.room_status_id
                WHERE br.booking_id = ?
            ")->execute([$bookingId]);
        }
        if ($status === 'Checked Out') {
            // Room returns to Available when guest leaves
            $pdo->prepare("
                UPDATE rooms r
                JOIN booking_rooms br ON br.room_id = r.room_id
                JOIN room_status rs   ON rs.status_name = 'Available'
                SET r.room_status_id = rs.room_status_id
                WHERE br.booking_id = ?
            ")->execute([$bookingId]);
        }

        $pdo->commit();
        json_response(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        json_response(['error' => 'Update failed: ' . $e->getMessage()], 500);
    }
}

// 
//  ROOMS — for housekeeping overview
// 
function handle_rooms(PDO $pdo): void
{
    $stmt = $pdo->query("
        SELECT
            r.room_id, r.room_number, r.floor_number,
            r.capacity, r.price_per_night, r.description,
            rt.type_name, rt.bed_type,
            rs.status_name AS status
        FROM rooms r
        JOIN room_types rt  ON r.room_type_id   = rt.room_type_id
        JOIN room_status rs ON r.room_status_id  = rs.room_status_id
        ORDER BY r.room_number ASC
    ");
    json_response($stmt->fetchAll());
}

// 
//  UPDATE ROOM STATUS — housekeeping
//  Body: room_id, status
// 
function handle_update_room(PDO $pdo): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['error' => 'POST required.'], 405);
    }

    $body   = json_decode(file_get_contents('php://input'), true);
    $roomId = (int)($body['room_id'] ?? 0);
    $status = $body['status'] ?? '';

    $allowed = ['Available', 'Occupied', 'Maintenance', 'Reserved'];
    if (!$roomId || !in_array($status, $allowed)) {
        json_response(['error' => 'Valid room_id and status are required.'], 422);
    }

    $stmt = $pdo->prepare("SELECT room_status_id FROM room_status WHERE status_name = ?");
    $stmt->execute([$status]);
    $statusId = (int)$stmt->fetchColumn();

    $pdo->prepare("UPDATE rooms SET room_status_id = ? WHERE room_id = ?")
        ->execute([$statusId, $roomId]);

    json_response(['success' => true]);
}

// 
//  MESSAGES — staff can read and reply to contact messages
// 
function handle_messages(PDO $pdo): void
{
    $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
    json_response($stmt->fetchAll());
}

// 
//  UPDATE MESSAGE STATUS
//  Body: message_id, status ('New'|'Read'|'Replied')
// 
function handle_update_message(PDO $pdo): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['error' => 'POST required.'], 405);
    }

    $body      = json_decode(file_get_contents('php://input'), true);
    $messageId = (int)($body['message_id'] ?? 0);
    $status    = $body['status'] ?? '';

    if (!$messageId || !in_array($status, ['New', 'Read', 'Replied'])) {
        json_response(['error' => 'Valid message_id and status are required.'], 422);
    }

    $pdo->prepare("UPDATE contact_messages SET message_status = ? WHERE message_id = ?")
        ->execute([$status, $messageId]);

    json_response(['success' => true]);
}

// 
//  GUESTS — lookup by name, email, or phone for front-desk use
// 
function handle_guests(PDO $pdo): void
{
    $search = trim($_GET['search'] ?? '');
    if (strlen($search) < 2) {
        json_response(['error' => 'Search term must be at least 2 characters.'], 422);
    }

    $stmt = $pdo->prepare("
        SELECT
            u.user_id,
            CONCAT(u.first_name, ' ', u.last_name) AS full_name,
            u.email, u.phone_number, u.city, u.user_status,
            COUNT(b.booking_id) AS total_bookings
        FROM users u
        LEFT JOIN bookings b ON b.user_id = u.user_id
        WHERE u.role_id = 2
          AND (
              CONCAT(u.first_name, ' ', u.last_name) LIKE ?
              OR u.email          LIKE ?
              OR u.phone_number   LIKE ?
          )
        GROUP BY u.user_id
        ORDER BY u.last_name, u.first_name
        LIMIT 30
    ");
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
    json_response($stmt->fetchAll());
}

// 
//  ARRIVALS — Dedicated endpoint for checkin.php
// 
function handle_arrivals(PDO $pdo): void
{
    $stmt = $pdo->query("
        SELECT
            b.booking_id, b.check_in_date, b.check_out_date,
            b.adults_count, b.children_count, b.special_requests,
            bs.status_name AS booking_status,
            CONCAT(u.first_name, ' ', u.last_name) AS guest_name,
            u.email AS guest_email,
            r.room_number,
            rt.type_name AS room_type
        FROM bookings b
        JOIN booking_status bs  ON b.booking_status_id = bs.booking_status_id
        LEFT JOIN users u       ON b.user_id           = u.user_id
        JOIN booking_rooms br   ON br.booking_id       = b.booking_id
        JOIN rooms r            ON br.room_id          = r.room_id
        JOIN room_types rt      ON r.room_type_id      = rt.room_type_id
        WHERE b.check_in_date = CURDATE()
          AND bs.status_name  = 'Confirmed'
        ORDER BY b.booking_id ASC
    ");
    json_response($stmt->fetchAll());
}

// 
//  DEPARTURES — Dedicated endpoint for checkin.php
// 
function handle_departures(PDO $pdo): void
{
    $stmt = $pdo->query("
        SELECT
            b.booking_id, b.check_in_date, b.check_out_date,
            b.total_amount, b.payment_status,
            bs.status_name AS booking_status,
            CONCAT(u.first_name, ' ', u.last_name) AS guest_name,
            r.room_number,
            rt.type_name AS room_type
        FROM bookings b
        JOIN booking_status bs  ON b.booking_status_id = bs.booking_status_id
        LEFT JOIN users u       ON b.user_id           = u.user_id
        JOIN booking_rooms br   ON br.booking_id       = b.booking_id
        JOIN rooms r            ON br.room_id          = r.room_id
        JOIN room_types rt      ON r.room_type_id      = rt.room_type_id
        WHERE b.check_out_date = CURDATE()
          AND bs.status_name   = 'Checked In'
        ORDER BY b.booking_id ASC
    ");
    json_response($stmt->fetchAll());
}

function handle_search_bookings(PDO $pdo): void
{
    $q = trim($_GET['q'] ?? '');
    
    $sql = "
        SELECT
            b.booking_id, b.check_in_date, b.check_out_date,
            b.adults_count, b.children_count, b.total_amount,
            b.booking_date, b.special_requests,
            bs.status_name AS booking_status,
            CONCAT(u.first_name, ' ', u.last_name) AS guest_name,
            u.email AS guest_email,
            r.room_number,
            rt.type_name AS room_type
        FROM bookings b
        JOIN booking_status bs  ON b.booking_status_id = bs.booking_status_id
        LEFT JOIN users u       ON b.user_id           = u.user_id
        JOIN booking_rooms br   ON br.booking_id       = b.booking_id
        JOIN rooms r            ON br.room_id          = r.room_id
        JOIN room_types rt      ON r.room_type_id      = rt.room_type_id
        WHERE (
            CONCAT(u.first_name,' ',u.last_name) LIKE ? 
            OR u.email LIKE ? 
            OR r.room_number LIKE ? 
            OR b.booking_id = ?
        )
        ORDER BY b.check_in_date ASC, b.booking_id ASC";

    $stmt = $pdo->prepare($sql);
    $bookingIdQuery = is_numeric($q) ? (int)$q : 0;
    $stmt->execute(["%$q%", "%$q%", "%$q%", $bookingIdQuery]);
    
    json_response($stmt->fetchAll());
}