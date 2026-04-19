<?php
header('Content-Type: application/json');
require_once 'db_connection.php';


$user_id = $_POST['user_id'] ?? '';


if (empty($user_id)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "User ID is required to delete an account."]);
    exit;
}

try
{
   
    $check = $pdo->prepare("SELECT User_ID FROM users WHERE User_ID = ?");
    $check->execute([$user_id]);
    
    if (!$check->fetch())
         {
        http_response_code(404); // Not Found
        echo json_encode(["status" => "error", "message" => "Cannot delete: User ID $user_id does not exist."]);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM users WHERE User_ID = ?");
    $stmt->execute([$user_id]);

    echo json_encode([
        "status" => "success",
        "message" => "Account for User ID $user_id has been successfully removed."
    ]);

} catch (PDOException $e) 
{
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Delete failed: " . $e->getMessage()]);
}