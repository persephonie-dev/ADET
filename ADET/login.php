<?php
header('Content-Type: application/json');
require_once 'db_connection.php';


$user_input = $_POST['username'] ?? '';
$pass_input = $_POST['password'] ?? '';


if (empty($user_input) || empty($pass_input)) 
{
    http_response_code(400); // Bad Request
    echo json_encode([
        "status" => "error",
        "message" => "Both username and password are required."
    ]);
    exit;
}

try 
{
    
    $stmt = $pdo->prepare("SELECT User_ID, User_name, Password FROM users WHERE User_name = ?");
    $stmt->execute([$user_input]);
    $user = $stmt->fetch();

    //  Logic Validation
    if ($user && password_verify($pass_input, $user['Password'])) {
        // SUCCESS
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Login successful! Welcome back, " . htmlspecialchars($user['User_name']),
            "user_id" => $user['User_ID']
        ]);
    } else 
    {
        // FAILURE (Multi-status: 401 Unauthorized)
        http_response_code(401);
        echo json_encode([
            "status" => "error",
            "message" => "Invalid username or password."
        ]);
    }

} catch (PDOException $e) 
{
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database error occurred."
    ]);
}