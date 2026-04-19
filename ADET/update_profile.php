<?php
header('Content-Type: application/json');
require_once 'db_connection.php';


$user_id = $_POST['user_id'] ?? '';
$new_street = $_POST['street'] ?? '';
$new_city = $_POST['city'] ?? '';
$new_phone = $_POST['phone_number'] ?? '';


if (empty($user_id)) 
{
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "User ID is required to update profile."]);
    exit;
}

try 
{
   
    $check = $pdo->prepare("SELECT User_ID FROM users WHERE User_ID = ?");
    $check->execute([$user_id]);
    
    if (!$check->fetch()) 
        {
        http_response_code(404); // Not Found
        echo json_encode(["status" => "error", "message" => "User not found."]);
        exit;
    }

   
    $sql = "UPDATE users SET Street = ?, City = ?, Phone_Number = ? WHERE User_ID = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$new_street, $new_city, $new_phone, $user_id]);

    echo json_encode([
        "status" => "success", 
        "message" => "Profile updated successfully for User ID: " . $user_id
    ]);

} catch (PDOException $e) 
{
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Update failed: " . $e->getMessage()]);
}