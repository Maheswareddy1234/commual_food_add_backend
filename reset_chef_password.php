<?php
// Suppress PHP errors from being output (they break JSON)
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once "db.php";

// Check database connection
if (!$conn || $conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (empty($data["email"]) || empty($data["password"])) {
    echo json_encode([
        "success" => false,
        "message" => "Email and password are required"
    ]);
    exit;
}

$email = $conn->real_escape_string($data["email"]);
$password = $data["password"];

// Validate password length
if (strlen($password) < 6) {
    echo json_encode([
        "success" => false,
        "message" => "Password must be at least 6 characters"
    ]);
    exit;
}

// Check if email exists in chefs table
$checkSql = "SELECT id FROM chefs WHERE email = '$email' LIMIT 1";
$checkResult = $conn->query($checkSql);

if (!$checkResult || $checkResult->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Email not found"
    ]);
    exit;
}

// Hash the new password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Update the password
$updateSql = "UPDATE chefs SET password = '$hashedPassword' WHERE email = '$email'";
$updateResult = $conn->query($updateSql);

if ($updateResult) {
    echo json_encode([
        "success" => true,
        "message" => "Password reset successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to reset password. Please try again."
    ]);
}

$conn->close();
?>
