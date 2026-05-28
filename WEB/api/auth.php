<?php
// 
//  api/auth.php
//  Handles all authentication actions: register, login, logout.
//
//  Accepted requests:
//    POST /api/auth.php?action=register  – create new guest account
//    POST /api/auth.php?action=login     – authenticate and start session
//    POST /api/auth.php?action=logout    – destroy session
//    GET  /api/auth.php?action=me        – return current session user
// 

require_once __DIR__ . '/config.php';

// Set JSON header for all responses from this file.
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// 
//  Route to the correct action
// 
switch ($action) {
    case 'register': handle_register($pdo); break;
    case 'login':    handle_login($pdo);    break;
    case 'logout':   handle_logout();       break;
    case 'me':       handle_me();           break;
    default:
        json_response(['error' => 'Unknown action.'], 400);
}

// 
//  REGISTER
//  Expects JSON body: first_name, middle_name, last_name,
//                     DOB, street_adr, city, region,
//                     email, password, phone_number (optional)
//
//  FIX: Added middle_name, DOB, street_adr, city, region
//  to validation + INSERT to match schema NOT NULL constraints.
// 
function handle_register(PDO $pdo): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['error' => 'POST required.'], 405);
    }

    $body = json_decode(file_get_contents('php://input'), true);

    // --- Validate required fields (all NOT NULL in schema) ---
    $required = [
        'first_name', 'last_name',
        'DOB', 'street_adr', 'city', 'region',
        'email', 'password'
    ];
    foreach ($required as $field) {
        if (empty($body[$field])) {
            json_response(['error' => "Field '$field' is required."], 422);
        }
    }

    $email = trim(strtolower($body['email']));

    // --- Validate email format ---
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(['error' => 'Invalid email address.'], 422);
    }

    // --- Validate password length ---
    if (strlen($body['password']) < 8) {
        json_response(['error' => 'Password must be at least 8 characters.'], 422);
    }

    // --- Validate DOB format (YYYY-MM-DD) ---
    $dob = trim($body['DOB']);
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
        json_response(['error' => 'DOB must be in YYYY-MM-DD format.'], 422);
    }

    // --- Check for duplicate email ---
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        json_response(['error' => 'An account with that email already exists.'], 409);
    }

    // --- Hash password with bcrypt ---
    $hash = password_hash($body['password'], PASSWORD_BCRYPT);

    // --- role_id 2 = Guest (see seed data in schema) ---
    // FIX: INSERT now includes all NOT NULL columns: middle_name, DOB,
    //      street_adr, city, region — previously missing, causing DB errors.
    $stmt = $pdo->prepare("
        INSERT INTO users
            (role_id, first_name, middle_name, last_name,
             DOB, street_adr, city, region,
             email, password_hash, phone_number)
        VALUES
            (2, :fn, :mn, :ln,
             :dob, :street, :city, :region,
             :email, :hash, :phone)
    ");
    $stmt->execute([
        ':fn'     => trim($body['first_name']),
        ':mn'     => trim($body['middle_name']),
        ':ln'     => trim($body['last_name']),
        ':dob'    => $dob,
        ':street' => trim($body['street_adr']),
        ':city'   => trim($body['city']),
        ':region' => trim($body['region']),
        ':email'  => $email,
        ':hash'   => $hash,
        ':phone'  => $body['phone_number'] ?? null,
    ]);

    $userId = (int)$pdo->lastInsertId();

    // --- Start session immediately after registration ---
    $_SESSION['user_id']    = $userId;
    $_SESSION['role_id']    = 2;
    $_SESSION['first_name'] = trim($body['first_name']);

    json_response(['success' => true, 'user_id' => $userId], 201);
}

// 
//  LOGIN
//  Expects JSON body: email, password
// 
function handle_login(PDO $pdo): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['error' => 'POST required.'], 405);
    }

    $body  = json_decode(file_get_contents('php://input'), true);
    $email = trim(strtolower($body['email'] ?? ''));
    $pass  = $body['password'] ?? '';

    if (!$email || !$pass) {
        json_response(['error' => 'Email and password are required.'], 422);
    }

    // --- Fetch user by email ---
    $stmt = $pdo->prepare("
        SELECT user_id, role_id, first_name, last_name, password_hash, user_status
        FROM users
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // --- Verify password (timing-safe via password_verify) ---
    if (!$user || !password_verify($pass, $user['password_hash'])) {
        // Same error message for both cases to prevent email enumeration.
        json_response(['error' => 'Invalid email or password.'], 401);
    }

    // --- Check account is active ---
    if ($user['user_status'] !== 'Active') {
        json_response(['error' => 'Your account has been deactivated. Please contact support.'], 403);
    }

    // --- Store user info in session ---
    $_SESSION['user_id']    = (int)$user['user_id'];
    $_SESSION['role_id']    = (int)$user['role_id'];
    $_SESSION['first_name'] = $user['first_name'];

    json_response([
        'success'    => true,
        'role_id'    => (int)$user['role_id'],
        'first_name' => $user['first_name'],
    ]);
}

// 
//  LOGOUT
//  Destroys the session and clears the cookie.
// 
function handle_logout(): void
{
    $_SESSION = [];

    // Delete the session cookie from the browser.
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }

    session_destroy();
    json_response(['success' => true]);
}

// 
//  ME
//  Returns the currently logged-in user's basic info.
// 
function handle_me(): void
{
    if (empty($_SESSION['user_id'])) {
        json_response(['logged_in' => false]);
    }
    json_response([
        'logged_in'  => true,
        'user_id'    => $_SESSION['user_id'],
        'role_id'    => $_SESSION['role_id'],
        'first_name' => $_SESSION['first_name'],
    ]);
}