<?php
// 
//  api/config.php
//  Central database connection using PDO.
//  Include this file at the top of every API endpoint.
//
//  Usage:
//    require_once __DIR__ . '/config.php';
//    // $pdo is now available
// 

// --- Database credentials ---
// Change these to match your local or production environment.
define('DB_HOST', 'localhost');
define('DB_NAME', 'pepperland_hotel');
define('DB_USER', 'root');
define('DB_PASS', '');          // Set your MySQL password here
define('DB_CHARSET', 'utf8mb4');

// --- Site base URL ---
// Used for redirects and absolute links throughout the app.
define('BASE_URL', '/WEB');

// --- Session settings ---
// Start sessions here so every file that includes config.php
// has session access without calling session_start() again.
if (session_status() === PHP_SESSION_NONE) 
{
    session_start();
}

// --- PDO connection ---
try {
    $dsn = 'mysql:host=' . DB_HOST
         . ';dbname=' . DB_NAME
         . ';charset=' . DB_CHARSET;

    $options = [
        // Throw exceptions on errors instead of silent failures.
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        // Return rows as associative arrays by default.
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Disable emulated prepares for true parameterized queries.
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

} catch (PDOException $e) {
    // In production you would log this error instead of displaying it.
    http_response_code(500);
    header('Content-Type: application/json');
    die(json_encode([
        'error'   => 'Database connection failed.',
        'message' => $e->getMessage() // Remove this line in production
    ]));
}

// 
//  Helper: send a JSON response and stop execution.
//
//  @param mixed $data   The payload to encode as JSON.
//  @param int   $status HTTP status code (default 200).
// 
function json_response(mixed $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// 
//  Helper: require the user to be logged in.
//  Redirects to login page if no session is active.
//  Call this at the top of any page that needs authentication.
// 
function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

// 
//  Helper: require the user to have the Admin role (role_id = 1).
//  Redirects to home if the user is not an admin.
// 
function require_admin(): void
{
    require_login();
    if ((int)$_SESSION['role_id'] !== 1) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

// 
//  Helper: sanitize a string for safe HTML output.
//  Always use this when echoing user-supplied data in HTML.
// 
function h(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}