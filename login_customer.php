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

// Check database connection - handle both null and error cases
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
if (
    empty($data["email"]) ||
    empty($data["password"])
) {
    echo json_encode([
        "success" => false,
        "message" => "Email and password are required"
    ]);
    exit;
}

$email = $conn->real_escape_string($data["email"]);
$password = $data["password"];

// Fetch customer with id and name
$sql = "SELECT id, name, password 
        FROM customers 
        WHERE email = '$email' 
        LIMIT 1";

$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password"
    ]);
    exit;
}

$user = $result->fetch_assoc();

// Verify password
if (!password_verify($password, $user["password"])) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password"
    ]);
    exit;
}

// Login success - return user_id and user_name
// IMPORTANT: user_id must be an integer, not null
$userId = (int)$user["id"];
$userName = $user["name"];

echo json_encode([
    "success" => true,
    "message" => "Login successful",
    "user_id" => $userId,
    "user_name" => $userName
]);
?>
