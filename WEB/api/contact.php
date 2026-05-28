<?php
// 
//  api/contact.php
//
//  POST /api/contact.php  — submit a contact message (public)
// 

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'POST required.'], 405);
}

$body    = json_decode(file_get_contents('php://input'), true);
$name    = trim($body['name']    ?? '');
$email   = trim($body['email']   ?? '');
$subject = trim($body['subject'] ?? '');
$message = trim($body['message'] ?? '');

if (!$email || !$subject || !$message) {
    json_response(['error' => 'email, subject, and message are required.'], 422);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['error' => 'Invalid email address.'], 422);
}

$stmt = $pdo->prepare("
    INSERT INTO contact_messages (name, email, subject, message)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$name ?: null, $email, $subject, $message]);

json_response(['success' => true], 201);