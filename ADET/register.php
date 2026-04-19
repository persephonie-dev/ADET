<?php
// Set headers so the browser knows we are sending JSON
header('Content-Type: application/json');

require_once 'db_connection.php'; 

$errors = [];

$required_fields = ['username', 'password', 'first_name', 'surname', 'dob', 'street', 'city', 'zip_code', 'phone_number'];

foreach ($required_fields as $field) 
{
    if (empty($_POST[$field])) 
    {
        $errors[] = "The field " . str_replace('_', ' ', $field) . " is required.";
    }
}

// Date check validation
if (!empty($_POST['dob'])) 
{
    $dob = new DateTime($_POST['dob']);
    $today = new DateTime();
    $age = $today->diff($dob)->y;

    if ($dob > $today) 
    {
        $errors[] = "Date of birth cannot be in the future.";
    } elseif ($age < 18) 
    {
        $errors[] = "You must be at least 18 years old to register.";
    }
}

// Stop if initial validation fails
if (!empty($errors)) 
{
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "errors" => $errors,
        "suggestion" => "Please correct the highlighted fields and try again."
    ]);
    exit;
}

// --- Username Check ---
$stmt = $pdo->prepare("SELECT User_ID FROM users WHERE User_name = ?");
$stmt->execute([$_POST['username']]);

if ($stmt->fetch()) 
{
    $errors[] = "The username '" . htmlspecialchars($_POST['username']) . "' is already taken.";
}

// --- Phone Number Check ---

$stmt = $pdo->prepare("SELECT User_ID FROM users WHERE Phone_Number = ?");
$stmt->execute([$_POST['phone_number']]); // Matches HTML name attribute

if ($stmt->fetch()) 
{
    $errors[] = "This phone number is already registered.";
}

// Stop if duplicates found
if (!empty($errors)) 
{
    http_response_code(409);
    echo json_encode([
        "status" => "error",
        "errors" => $errors
    ]);
    exit;
}

// Hash password for security
$hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);


$sql = "INSERT INTO users (User_name, Password, First_name, Middle_name, Sur_name, Dob, Street, City, Zip_code, Phone_Number) 
        VALUES (:username, :password, :f_name, :m_name, :s_name, :dob, :street, :city, :zip, :phone)";

$stmt = $pdo->prepare($sql);

try 
{
    $stmt->execute([
        ':username' => $_POST['username'],
        ':password' => $hashed_password,
        ':f_name'   => $_POST['first_name'],
        ':m_name'   => $_POST['middle_name'],
        ':s_name'   => $_POST['surname'],
        ':dob'      => $_POST['dob'],
        ':street'   => $_POST['street'],
        ':city'     => $_POST['city'],
        ':zip'      => $_POST['zip_code'],
        ':phone'    => $_POST['phone_number'] 
    ]);

    echo json_encode([
        "status" => "success",
        "message" => "Registration complete! "
    ]);
} 
catch (PDOException $e) 
{
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>