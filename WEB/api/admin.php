<?php

//  action requires admin role 1
//
//  GET  ?action=dashboard       — summary counts + recent bookings
//  GET  ?action=bookings        — all bookings with filters
//  POST ?action=update_booking  — change booking status
//  GET  ?action=rooms           — all rooms
//  GET  ?action=room_types      — room type catalogue
//  POST ?action=add_room        — create a new room
//  POST ?action=edit_room       — update an existing room (all fields)
//  POST ?action=delete_room     — delete a room (blocks if has bookings)
//  POST ?action=update_room     — quick status-only update
//  GET  ?action=users           — all users with booking counts
//  POST ?action=update_user     — change user status
//  POST ?action=delete_user     — delete a user (blocks if has bookings)
//  GET  ?action=reviews         — reviews (with ?status= filter)
//  POST ?action=update_review   — approve / reject review
//  GET  ?action=messages        — contact messages
//  POST ?action=update_message  — mark message read/replied
//  GET  ?action=promos          — all promotions
//  POST ?action=save_promo      — create or update promo
//  POST ?action=delete_promo    — delete a promotion
//  GET  ?action=staff           — all staff records
//  GET  ?action=staff_roles     — staff role catalogue
//  POST ?action=add_staff       — add a staff member
//  POST ?action=edit_staff      — update a staff member
//  POST ?action=delete_staff    — remove a staff member


require_once __DIR__ . '/config.php';


header('Content-Type: application/json');

require_admin();

$action = $_GET['action'] ?? '';

switch ($action) 
{
  
    case 'dashboard': handle_dashboard($pdo); break;
    case 'bookings': handle_bookings($pdo); break;
    case 'update_booking': handle_update_booking($pdo); break;

    // --- Rooms ---
    case 'rooms': handle_rooms($pdo); break;
    case 'room_types': handle_room_types($pdo); break;
    case 'add_room': handle_add_room($pdo);  break;
    case 'edit_room': handle_edit_room($pdo);  break;
    case 'delete_room': handle_delete_room($pdo); break;
    case 'update_room': handle_update_room($pdo); break;

    // --- Users ---
    case 'users': handle_users($pdo); break;
    case 'update_user': handle_update_user($pdo); break;
    case 'delete_user': handle_delete_user($pdo); break;

    // --- Reviews & Messages ---
    case 'reviews': handle_reviews($pdo); break;
    case 'update_review': handle_update_review($pdo); break;
    case 'messages': handle_messages($pdo); break;
    case 'update_message':handle_update_message($pdo); break;

    // --- Promotions ---
    case 'promos': handle_promos($pdo); break;
    case 'save_promo': handle_save_promo($pdo); break;
    case 'delete_promo': handle_delete_promo($pdo); break;

    // --- Staff ---
    case 'staff': handle_staff($pdo);  break;
    case 'staff_roles': handle_staff_roles($pdo); break;
    case 'add_staff': handle_add_staff($pdo); break;
    case 'edit_staff': handle_edit_staff($pdo); break;
    case 'delete_staff': handle_delete_staff($pdo); break;

    default:
        json_response(['error' => 'Unknown action.'], 400);
}

function handle_dashboard(PDO $pdo): void
{
    $stats = [];

    $stats['bookings_today'] = (int)$pdo->query(
        "SELECT COUNT(*) FROM bookings WHERE DATE(booking_date) = CURDATE()"
    )->fetchColumn();

    $stats['revenue_this_month'] = (float)$pdo->query("
        SELECT COALESCE(SUM(p.amount_paid), 0)
        FROM payments p
        JOIN payment_status ps ON p.payment_status_id = ps.payment_status_id
        WHERE ps.status_name = 'Completed'
          AND MONTH(p.created_at) = MONTH(CURDATE())
          AND YEAR(p.created_at)  = YEAR(CURDATE())
    ")->fetchColumn();

    $stats['currently_checked_in'] = (int)$pdo->query("
        SELECT COUNT(*) FROM bookings b
        JOIN booking_status bs ON b.booking_status_id = bs.booking_status_id
        WHERE bs.status_name = 'Checked In'
    ")->fetchColumn();

    $stats['available_rooms'] = (int)$pdo->query("
        SELECT COUNT(*) FROM rooms r
        JOIN room_status rs ON r.room_status_id = rs.room_status_id
        WHERE rs.status_name = 'Available'
    ")->fetchColumn();

    $stats['pending_reviews']  = (int)$pdo->query(
        "SELECT COUNT(*) FROM reviews WHERE review_status = 'Pending'"
    )->fetchColumn();

    $stats['new_messages'] = (int)$pdo->query(
        "SELECT COUNT(*) FROM contact_messages WHERE message_status = 'New'"
    )->fetchColumn();

    $stats['pending_bookings'] = (int)$pdo->query("
        SELECT COUNT(*) FROM bookings b
        JOIN booking_status bs ON b.booking_status_id = bs.booking_status_id
        WHERE bs.status_name = 'Pending'
    ")->fetchColumn();

    $stmt = $pdo->query("
        SELECT
            b.booking_id, b.check_in_date, b.check_out_date, b.total_amount, b.booking_date,
            bs.status_name AS booking_status,
            CONCAT(u.first_name, ' ', u.last_name) AS guest_name,
            r.room_number,
            rt.type_name AS room_type
        FROM bookings b
        JOIN booking_status bs ON b.booking_status_id = bs.booking_status_id
        LEFT JOIN users u      ON b.user_id           = u.user_id
        JOIN booking_rooms br  ON br.booking_id       = b.booking_id
        JOIN rooms r           ON br.room_id          = r.room_id
        JOIN room_types rt     ON r.room_type_id      = rt.room_type_id
        ORDER BY b.booking_date DESC
        LIMIT 10
    ");
    $stats['recent_bookings'] = $stmt->fetchAll();

    json_response($stats);
}


function handle_bookings(PDO $pdo): void
{
    $statusFilter = $_GET['status'] ?? '';
    $search       = trim($_GET['search'] ?? '');

    $sql = "
        SELECT
            b.booking_id, b.check_in_date, b.check_out_date, b.adults_count,
            b.total_amount, b.booking_date, b.special_requests,
            bs.status_name AS booking_status,
            CONCAT(u.first_name, ' ', u.last_name) AS guest_name,
            u.email AS guest_email,
            r.room_number,
            rt.type_name AS room_type
        FROM bookings b
        JOIN booking_status bs ON b.booking_status_id = bs.booking_status_id
        LEFT JOIN users u      ON b.user_id           = u.user_id
        JOIN booking_rooms br  ON br.booking_id       = b.booking_id
        JOIN rooms r           ON br.room_id          = r.room_id
        JOIN room_types rt     ON r.room_type_id      = rt.room_type_id
        WHERE 1=1
    ";
    $params = [];
    if ($statusFilter) { $sql .= " AND bs.status_name = ?"; $params[] = $statusFilter; }
    if ($search)        { $sql .= " AND (CONCAT(u.first_name,' ',u.last_name) LIKE ? OR u.email LIKE ? OR r.room_number LIKE ?)";
                          $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }
    $sql .= " ORDER BY b.booking_date DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    json_response($stmt->fetchAll());
}


function handle_update_booking(PDO $pdo): void
{
    $body      = json_decode(file_get_contents('php://input'), true);
    $bookingId = (int)($body['booking_id'] ?? 0);
    $status    = $body['status'] ?? '';

    $allowed = ['Pending','Confirmed','Checked In','Checked Out','Cancelled','No Show'];
    if (!$bookingId || !in_array($status, $allowed)) {
        json_response(['error' => 'Valid booking_id and status are required.'], 422);
    }

    $stmt = $pdo->prepare("SELECT booking_status_id FROM booking_status WHERE status_name = ?");
    $stmt->execute([$status]);
    $statusId = (int)$stmt->fetchColumn();

    $pdo->prepare("UPDATE bookings SET booking_status_id = ? WHERE booking_id = ?")->execute([$statusId, $bookingId]);

    if ($status === 'Checked In') 
        {
        $pdo->prepare("
            UPDATE rooms r
            JOIN booking_rooms br ON br.room_id = r.room_id
            JOIN room_status rs   ON rs.status_name = 'Occupied'
            SET r.room_status_id = rs.room_status_id
            WHERE br.booking_id = ?
        ")->execute([$bookingId]);
    }
 
    if ($status === 'Checked Out') 
        {
        $pdo->prepare("
            UPDATE rooms r
            JOIN booking_rooms br ON br.room_id = r.room_id
            JOIN room_status rs   ON rs.status_name = 'Available'
            SET r.room_status_id = rs.room_status_id
            WHERE br.booking_id = ?
        ")->execute([$bookingId]);
    }

    json_response(['success' => true]);
}


function handle_rooms(PDO $pdo): void
{
    $stmt = $pdo->query("
        SELECT
            r.room_id, r.room_number, r.floor_number, r.price_per_night,
            r.capacity, r.description,
            r.room_type_id,
            rt.type_name, rt.bed_type,
            rs.status_name AS status,
            r.created_at
        FROM rooms r
        JOIN room_types rt  ON r.room_type_id   = rt.room_type_id
        JOIN room_status rs ON r.room_status_id = rs.room_status_id
        ORDER BY r.room_number ASC
    ");
    json_response($stmt->fetchAll());
}


function handle_room_types(PDO $pdo): void
{
    $stmt = $pdo->query("SELECT room_type_id, type_name, base_price, description, max_capacity, bed_type FROM room_types ORDER BY type_name");
    json_response($stmt->fetchAll());
}


function handle_add_room(PDO $pdo): void
{
    $body           = json_decode(file_get_contents('php://input'), true);
    $room_number    = trim($body['room_number']    ?? '');
    $room_type_id   = (int)($body['room_type_id']  ?? 0);
    $floor_number   = $body['floor_number']   !== null && $body['floor_number'] !== '' ? (int)$body['floor_number']  : null;
    $capacity       = $body['capacity']       !== null && $body['capacity']       !== '' ? (int)$body['capacity']      : null;
    $price          = (float)($body['price_per_night'] ?? 0);
    $description    = trim($body['description'] ?? '') ?: null;
    $statusName     = $body['status'] ?? 'Available';

    if (!$room_number || !$room_type_id || $price < 0) {
        json_response(['error' => 'Room number, type, and price are required.'], 422);
    }


    $chk = $pdo->prepare("SELECT room_id FROM rooms WHERE room_number = ?");
    $chk->execute([$room_number]);
    if ($chk->fetchColumn()) {
        json_response(['error' => "Room number '$room_number' already exists."], 409);
    }

    $stmt = $pdo->prepare("SELECT room_status_id FROM room_status WHERE status_name = ?");
    $stmt->execute([$statusName]);
    $statusId = (int)$stmt->fetchColumn();

    $pdo->prepare("
        INSERT INTO rooms (room_type_id, room_status_id, room_number, floor_number, capacity, price_per_night, description)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ")->execute([$room_type_id, $statusId, $room_number, $floor_number, $capacity, $price, $description]);

   
    json_response([
        'success'      => true, 
        'room_id'      => (int)$pdo->lastInsertId(),
        'room_type_id' => $room_type_id
    ]);
}


function handle_edit_room(PDO $pdo): void
{
    $body           = json_decode(file_get_contents('php://input'), true);
    $roomId         = (int)($body['room_id']       ?? 0);
    $room_number    = trim($body['room_number']    ?? '');
    $room_type_id   = (int)($body['room_type_id']  ?? 0);
    $floor_number   = $body['floor_number']   !== null && $body['floor_number'] !== '' ? (int)$body['floor_number']  : null;
    $capacity       = $body['capacity']       !== null && $body['capacity']       !== '' ? (int)$body['capacity']      : null;
    $price          = (float)($body['price_per_night'] ?? 0);
    $description    = trim($body['description'] ?? '') ?: null;
    $statusName     = $body['status'] ?? 'Available';

    if (!$roomId || !$room_number || !$room_type_id || $price < 0) {
        json_response(['error' => 'room_id, room_number, type, and price are required.'], 422);
    }

    
    $chk = $pdo->prepare("SELECT room_id FROM rooms WHERE room_number = ? AND room_id != ?");
    $chk->execute([$room_number, $roomId]);
    if ($chk->fetchColumn()) {
        json_response(['error' => "Room number '$room_number' already exists."], 409);
    }

    $stmt = $pdo->prepare("SELECT room_status_id FROM room_status WHERE status_name = ?");
    $stmt->execute([$statusName]);
    $statusId = (int)$stmt->fetchColumn();

    $pdo->prepare("
        UPDATE rooms
        SET room_type_id = ?, room_status_id = ?, room_number = ?,
            floor_number = ?, capacity = ?, price_per_night = ?, description = ?
        WHERE room_id = ?
    ")->execute([$room_type_id, $statusId, $room_number, $floor_number, $capacity, $price, $description, $roomId]);

    json_response(['success' => true]);
}

function handle_delete_room(PDO $pdo): void
{
    $body   = json_decode(file_get_contents('php://input'), true);
    $roomId = (int)($body['room_id'] ?? 0);
    if (!$roomId) { json_response(['error' => 'room_id required.'], 422); }


    $chk = $pdo->prepare("SELECT COUNT(*) FROM booking_rooms WHERE room_id = ?");
    $chk->execute([$roomId]);
    if ((int)$chk->fetchColumn() > 0) {
        json_response(['error' => 'Cannot delete room — it has existing bookings.'], 409);
    }

    $pdo->prepare("DELETE FROM rooms WHERE room_id = ?")->execute([$roomId]);
    json_response(['success' => true]);
}

function handle_update_room(PDO $pdo): void
{
    $body    = json_decode(file_get_contents('php://input'), true);
    $roomId  = (int)($body['room_id'] ?? 0);
    $status  = $body['status'] ?? '';

    $allowed = ['Available','Occupied','Maintenance','Reserved'];
    if (!$roomId || !in_array($status, $allowed)) {
        json_response(['error' => 'Valid room_id and status are required.'], 422);
    }

    $stmt = $pdo->prepare("SELECT room_status_id FROM room_status WHERE status_name = ?");
    $stmt->execute([$status]);
    $statusId = (int)$stmt->fetchColumn();

    $pdo->prepare("UPDATE rooms SET room_status_id = ? WHERE room_id = ?")->execute([$statusId, $roomId]);
    json_response(['success' => true]);
}


function handle_users(PDO $pdo): void
{
    $stmt = $pdo->query("
        SELECT
            u.user_id, u.first_name, u.last_name, u.email,
            u.phone_number, u.user_status,
            DATE_FORMAT(u.created_at, '%Y-%m-%d %H:%i:%s') AS created_at,
            ro.role_name,
            COUNT(b.booking_id) AS total_bookings
        FROM users u
        JOIN roles ro        ON u.role_id  = ro.role_id
        LEFT JOIN bookings b ON b.user_id  = u.user_id
        GROUP BY u.user_id
        ORDER BY u.created_at DESC
    ");
    json_response($stmt->fetchAll());
}

function handle_update_user(PDO $pdo): void
{
    $body   = json_decode(file_get_contents('php://input'), true);
    $userId = (int)($body['user_id'] ?? 0);
    $status = $body['status'] ?? '';

    if (!$userId || !in_array($status, ['Active','Suspended','Inactive'])) {
        json_response(['error' => 'Valid user_id and status are required.'], 422);
    }

    $pdo->prepare("UPDATE users SET user_status = ? WHERE user_id = ?")->execute([$status, $userId]);
    json_response(['success' => true]);
}

function handle_delete_user(PDO $pdo): void
{
    $body   = json_decode(file_get_contents('php://input'), true);
    $userId = (int)($body['user_id'] ?? 0);
    if (!$userId) { json_response(['error' => 'user_id required.'], 422); }

   
    $chk = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
    $chk->execute([$userId]);
    if ((int)$chk->fetchColumn() > 0) {
        json_response(['error' => 'Cannot delete user — they have existing bookings.'], 409);
    }

    $pdo->prepare("DELETE FROM users WHERE user_id = ?")->execute([$userId]);
    json_response(['success' => true]);
}


function handle_reviews(PDO $pdo): void
{
    $status = $_GET['status'] ?? 'Pending';
    $stmt   = $pdo->prepare("
        SELECT
            rv.review_id, rv.rating, rv.title, rv.comment,
            rv.review_date, rv.review_status,
            CONCAT(u.first_name, ' ', u.last_name) AS guest_name,
            rt.type_name AS room_type
        FROM reviews rv
        JOIN users u       ON rv.user_id    = u.user_id
        JOIN bookings b    ON rv.booking_id = b.booking_id
        JOIN booking_rooms br ON br.booking_id = b.booking_id
        JOIN rooms r       ON br.room_id    = r.room_id
        JOIN room_types rt ON r.room_type_id = rt.room_type_id
        WHERE rv.review_status = ?
        ORDER BY rv.review_date DESC
    ");
    $stmt->execute([$status]);
    json_response($stmt->fetchAll());
}


function handle_update_review(PDO $pdo): void
{
    $body     = json_decode(file_get_contents('php://input'), true);
    $reviewId = (int)($body['review_id'] ?? 0);
    $status   = $body['status'] ?? '';

    if (!$reviewId || !in_array($status, ['Approved','Rejected'])) {
        json_response(['error' => 'Valid review_id and status (Approved|Rejected) are required.'], 422);
    }

    $pdo->prepare("UPDATE reviews SET review_status = ? WHERE review_id = ?")->execute([$status, $reviewId]);
    json_response(['success' => true]);
}


function handle_messages(PDO $pdo): void
{
    $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
    json_response($stmt->fetchAll());
}


function handle_update_message(PDO $pdo): void
{
    $body      = json_decode(file_get_contents('php://input'), true);
    $messageId = (int)($body['message_id'] ?? 0);
    $status    = $body['status'] ?? '';

    if (!$messageId || !in_array($status, ['New','Read','Replied'])) {
        json_response(['error' => 'Valid message_id and status are required.'], 422);
    }

    $pdo->prepare("UPDATE contact_messages SET message_status = ? WHERE message_id = ?")->execute([$status, $messageId]);
    json_response(['success' => true]);
}


function handle_promos(PDO $pdo): void
{
    $stmt = $pdo->query("SELECT * FROM promotions ORDER BY start_date DESC");
    json_response($stmt->fetchAll());
}


function handle_save_promo(PDO $pdo): void
{
    $body   = json_decode(file_get_contents('php://input'), true);
    $id     = (int)($body['promotion_id']   ?? 0);
    $code   = trim($body['promo_code']      ?? '');
    $name   = trim($body['promo_name']      ?? '');
    $type   = $body['discount_type']        ?? '';
    $value  = (float)($body['discount_value'] ?? 0);
    $start  = $body['start_date'] ?? '';
    $end    = $body['end_date']   ?? '';
    $active = isset($body['is_active']) ? (int)$body['is_active'] : 1;

    if (!$code || !$name || !in_array($type, ['Percentage','Fixed']) || !$start || !$end) {
        json_response(['error' => 'All promo fields are required.'], 422);
    }

    if ($id > 0) {
        $pdo->prepare("
            UPDATE promotions
            SET promo_code=?, promo_name=?, discount_type=?,
                discount_value=?, start_date=?, end_date=?, is_active=?
            WHERE promotion_id=?
        ")->execute([$code, $name, $type, $value, $start, $end, $active, $id]);
    } else {
        $pdo->prepare("
            INSERT INTO promotions (promo_code, promo_name, discount_type, discount_value, start_date, end_date, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ")->execute([$code, $name, $type, $value, $start, $end, $active]);
    }
    json_response(['success' => true]);
}


function handle_delete_promo(PDO $pdo): void
{
    $body = json_decode(file_get_contents('php://input'), true);
    $id   = (int)($body['promotion_id'] ?? 0);
    if (!$id) { json_response(['error' => 'promotion_id required.'], 422); }
    $pdo->prepare("DELETE FROM promotions WHERE promotion_id = ?")->execute([$id]);
    json_response(['success' => true]);
}


function handle_staff(PDO $pdo): void
{
    $stmt = $pdo->query("
        SELECT s.*, sr.role_name
        FROM staff s
        JOIN staff_roles sr ON s.staff_role_id = sr.staff_role_id
        ORDER BY s.last_name, s.first_name
    ");
    json_response($stmt->fetchAll());
}


function handle_staff_roles(PDO $pdo): void
{
    $stmt = $pdo->query("SELECT staff_role_id, role_name, description FROM staff_roles ORDER BY role_name");
    json_response($stmt->fetchAll());
}


function handle_add_staff(PDO $pdo): void
{
    $body          = json_decode(file_get_contents('php://input'), true);
    $first_name    = trim($body['first_name']    ?? '');
    $last_name     = trim($body['last_name']     ?? '');
    $email         = trim($body['email']         ?? '');
    $phone         = trim($body['phone_number']  ?? '');
    $role_id       = (int)($body['staff_role_id'] ?? 0);
    $hire_date     = $body['hire_date'] ?: null;
    $staff_status  = $body['staff_status'] ?? 'Active';

    if (!$first_name || !$last_name || !$email || !$phone) {
        json_response(['error' => 'Name, email, and phone are required.'], 422);
    }

    $pdo->prepare("
        INSERT INTO staff (staff_role_id, first_name, last_name, email, phone_number, hire_date, staff_status)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ")->execute([$role_id, $first_name, $last_name, $email, $phone, $hire_date, $staff_status]);

    json_response(['success' => true, 'staff_id' => (int)$pdo->lastInsertId()]);
}


function handle_edit_staff(PDO $pdo): void
{
    $body          = json_decode(file_get_contents('php://input'), true);
    $staffId       = (int)($body['staff_id']     ?? 0);
    $first_name    = trim($body['first_name']    ?? '');
    $last_name     = trim($body['last_name']     ?? '');
    $email         = trim($body['email']         ?? '');
    $phone         = trim($body['phone_number']  ?? '');
    $role_id       = (int)($body['staff_role_id'] ?? 0);
    $hire_date     = $body['hire_date'] ?: null;
    $staff_status  = $body['staff_status'] ?? 'Active';

    if (!$staffId || !$first_name || !$last_name || !$email || !$phone) {
        json_response(['error' => 'staff_id, name, email, and phone are required.'], 422);
    }

    $pdo->prepare("
        UPDATE staff
        SET staff_role_id=?, first_name=?, last_name=?, email=?, phone_number=?, hire_date=?, staff_status=?
        WHERE staff_id=?
    ")->execute([$role_id, $first_name, $last_name, $email, $phone, $hire_date, $staff_status, $staffId]);

    json_response(['success' => true]);
}


function handle_delete_staff(PDO $pdo): void
{
    $body    = json_decode(file_get_contents('php://input'), true);
    $staffId = (int)($body['staff_id'] ?? 0);
    if (!$staffId) { json_response(['error' => 'staff_id required.'], 422); }
    $pdo->prepare("DELETE FROM staff WHERE staff_id = ?")->execute([$staffId]);
    json_response(['success' => true]);
}