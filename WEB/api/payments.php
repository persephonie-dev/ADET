<?php
// 
//  api/payments.php
//  Requires login.
//
//  POST /api/payments.php?action=pay
//      Records a payment for a booking.
//      Body: booking_id, payment_method_id, amount_paid,
//            transaction_reference
//
//  GET  /api/payments.php?action=methods
//      Returns all available payment methods.
// 

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action !== 'methods') {
    require_login();
}

switch ($action) {
    case 'pay':     handle_pay($pdo);     break;
    case 'methods': handle_methods($pdo); break;
    default:
        json_response(['error' => 'Unknown action.'], 400);
}

// 
//  PAY
//  Records a payment and updates booking status to Confirmed.
//  In production you would integrate a real payment gateway
//  (e.g. PayMongo for GCash / cards) and use the gateway's
//  webhook to confirm — never trust the client to self-confirm.
// 
function handle_pay(PDO $pdo): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['error' => 'POST required.'], 405);
    }

    $body            = json_decode(file_get_contents('php://input'), true);
    $bookingId       = (int)($body['booking_id']           ?? 0);
    $methodId        = (int)($body['payment_method_id']    ?? 0);
    $amountPaid      = (float)($body['amount_paid']        ?? 0);
    $transactionRef  = trim($body['transaction_reference'] ?? '');

    if (!$bookingId || !$methodId || $amountPaid <= 0 || !$transactionRef) {
        json_response(['error' => 'booking_id, payment_method_id, amount_paid, and transaction_reference are required.'], 422);
    }

    // --- Confirm the booking belongs to this user ---
    $stmt = $pdo->prepare("
        SELECT b.booking_id, b.total_amount, b.user_id, bs.status_name
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
    if ($booking['status_name'] !== 'Pending') {
        json_response(['error' => "Booking is already '{$booking['status_name']}' and cannot be paid again."], 409);
    }

    // --- Check for duplicate transaction reference ---
    $stmt = $pdo->prepare("SELECT payment_id FROM payments WHERE transaction_reference = ?");
    $stmt->execute([$transactionRef]);
    if ($stmt->fetch()) {
        json_response(['error' => 'Duplicate transaction reference.'], 409);
    }

    // --- Determine payment status ---
    // For demo purposes we mark everything Completed.
    // A real gateway integration would set this via webhook.
    $completedId = get_payment_status_id($pdo, 'Completed');
    $pendingId   = get_payment_status_id($pdo, 'Pending');
    $statusId    = $completedId; // swap to $pendingId when using a gateway

    $confirmedBookingStatusId = get_booking_status_id($pdo, 'Confirmed');

    $pdo->beginTransaction();
    try {
        // Insert the payment record.
        $stmt = $pdo->prepare("
            INSERT INTO payments
                (booking_id, amount_paid, payment_method_id,
                 payment_status_id, transaction_reference)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $bookingId, $amountPaid, $methodId, $statusId, $transactionRef
        ]);

        // Update the booking status to Confirmed.
        $stmt = $pdo->prepare("
            UPDATE bookings SET booking_status_id = ? WHERE booking_id = ?
        ");
        $stmt->execute([$confirmedBookingStatusId, $bookingId]);

        $pdo->commit();
        json_response(['success' => true, 'payment_id' => (int)$pdo->lastInsertId()]);

    } catch (Exception $e) {
        $pdo->rollBack();
        json_response(['error' => 'Payment recording failed.', 'detail' => $e->getMessage()], 500);
    }
}

// 
//  PAYMENT METHODS
//  Returns all rows from the payment_methods lookup table.
// 
function handle_methods(PDO $pdo): void
{
    $stmt = $pdo->query("SELECT * FROM payment_methods ORDER BY method_name");
    json_response($stmt->fetchAll());
}

// --- Helpers to get status IDs from lookup tables ---

function get_payment_status_id(PDO $pdo, string $name): int
{
    $stmt = $pdo->prepare("SELECT payment_status_id FROM payment_status WHERE status_name = ?");
    $stmt->execute([$name]);
    return (int)$stmt->fetchColumn();
}

function get_booking_status_id(PDO $pdo, string $name): int
{
    $stmt = $pdo->prepare("SELECT booking_status_id FROM booking_status WHERE status_name = ?");
    $stmt->execute([$name]);
    return (int)$stmt->fetchColumn();
}