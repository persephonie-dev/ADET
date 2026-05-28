<?php
// 
//  api/reviews.php
//
//  GET  /api/reviews.php                — public list of approved reviews
//  POST /api/reviews.php?action=submit  — submit a review (login required)
// 

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':   handle_list($pdo);   break;
    case 'submit': handle_submit($pdo); break;
    default:
        json_response(['error' => 'Unknown action.'], 400);
}

function handle_list(PDO $pdo): void
{
    // Only show reviews that have been approved by an admin.
    $stmt = $pdo->query("
        SELECT
            rv.review_id,
            rv.rating,
            rv.title,
            rv.comment,
            rv.review_date,
            CONCAT(u.first_name, ' ', LEFT(u.last_name, 1), '.') AS guest_name,
            rt.type_name AS room_type
        FROM reviews rv
        JOIN users u      ON rv.user_id   = u.user_id
        JOIN bookings b   ON rv.booking_id = b.booking_id
        JOIN booking_rooms br ON br.booking_id = b.booking_id
        JOIN rooms r      ON br.room_id    = r.room_id
        JOIN room_types rt ON r.room_type_id = rt.room_type_id
        WHERE rv.review_status = 'Approved'
        ORDER BY rv.review_date DESC
        LIMIT 20
    ");
    json_response($stmt->fetchAll());
}

function handle_submit(PDO $pdo): void
{
    require_login();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['error' => 'POST required.'], 405);
    }

    $body      = json_decode(file_get_contents('php://input'), true);
    $bookingId = (int)($body['booking_id'] ?? 0);
    $rating    = (int)($body['rating']     ?? 0);
    $title     = trim($body['title']       ?? '');
    $comment   = trim($body['comment']     ?? '');

    if (!$bookingId || $rating < 1 || $rating > 5) {
        json_response(['error' => 'booking_id and rating (1-5) are required.'], 422);
    }

    // Confirm the booking belongs to this user and is checked out.
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
    if ((int)$booking['user_id'] !== (int)$_SESSION['user_id']) {
        json_response(['error' => 'You can only review your own bookings.'], 403);
    }
    if ($booking['status_name'] !== 'Checked Out') {
        json_response(['error' => 'You can only review after check-out.'], 409);
    }

    // Each booking can only have one review (UNIQUE constraint in schema).
    $stmt = $pdo->prepare("SELECT review_id FROM reviews WHERE booking_id = ?");
    $stmt->execute([$bookingId]);
    if ($stmt->fetch()) {
        json_response(['error' => 'You have already reviewed this stay.'], 409);
    }

    // Status defaults to 'Pending' — admin must approve before it shows publicly.
    $stmt = $pdo->prepare("
        INSERT INTO reviews (booking_id, user_id, rating, title, comment)
        VALUES (:bid, :uid, :rating, :title, :comment)
    ");
    $stmt->execute([
        ':bid'     => $bookingId,
        ':uid'     => $_SESSION['user_id'],
        ':rating'  => $rating,
        ':title'   => $title ?: null,
        ':comment' => $comment ?: null,
    ]);

    json_response(['success' => true], 201);
}