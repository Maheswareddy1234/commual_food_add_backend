<?php
/**
 * Update Customer Profile API
 * Updates customer name, email, phone, and date of birth
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

// Catch ALL errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo json_encode([
        "success" => false,
        "message" => "PHP Error: $errstr in $errfile on line $errline"
    ]);
    exit;
});

require_once "db.php";

// Check connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

// Get POST data
$customer_id = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$dob = isset($_POST['dob']) ? trim($_POST['dob']) : '';

// Validate required fields
if ($customer_id == 0) {
    echo json_encode(["success" => false, "message" => "Customer ID is required"]);
    exit;
}

if (empty($name)) {
    echo json_encode(["success" => false, "message" => "Name is required"]);
    exit;
}

// Add dob column if not exists
$conn->query("ALTER TABLE customers ADD COLUMN IF NOT EXISTS dob DATE NULL");
$conn->query("ALTER TABLE customers ADD COLUMN IF NOT EXISTS phone VARCHAR(20) NULL");

// Escape values
$name = $conn->real_escape_string($name);
$email = $conn->real_escape_string($email);
$phone = $conn->real_escape_string($phone);

// Handle DOB format (convert DD/MM/YYYY to YYYY-MM-DD)
$dobDb = null;
if (!empty($dob)) {
    $parts = explode('/', $dob);
    if (count($parts) == 3) {
        $dobDb = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
    } else {
        $dobDb = $dob; // Try as-is if already in correct format
    }
}

// Update customer
$sql = "UPDATE customers SET 
        name = '$name',
        email = '$email',
        phone = '$phone'" .
        ($dobDb ? ", dob = '$dobDb'" : "") .
        " WHERE id = $customer_id";

if ($conn->query($sql)) {
    echo json_encode([
        "success" => true,
        "message" => "Profile updated successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to update profile: " . $conn->error
    ]);
}

$conn->close();
?>
