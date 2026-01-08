<?php
// CRITICAL: Start output buffering to catch all output
ob_start();

// Set headers immediately
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    ob_end_clean();
    exit;
}

// Register shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_end_clean();
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Fatal Error: " . $error['message'] . " in " . $error['file'] . " line " . $error['line']
        ]);
    }
});

// Custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "PHP Error [$errno]: $errstr in $errfile on line $errline"
    ]);
    exit;
});

// Custom exception handler
set_exception_handler(function($e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Exception: " . $e->getMessage()
    ]);
    exit;
});

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Database connection
$conn = new mysqli("localhost", "root", "", "food-app");
if ($conn->connect_error) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "DB Connection failed: " . $conn->connect_error]);
    exit;
}

// Create cart table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    dish_id INT NOT NULL,
    quantity INT DEFAULT 1,
    portion_size VARCHAR(50) DEFAULT 'Regular',
    spice_level VARCHAR(50) DEFAULT 'Medium',
    oil_level VARCHAR(50) DEFAULT 'Medium',
    salt_level VARCHAR(50) DEFAULT 'Normal',
    instructions TEXT,
    customization TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Add customization column if it doesn't exist (for existing tables)
$result = $conn->query("SHOW COLUMNS FROM cart LIKE 'customization'");
if ($result && $result->num_rows == 0) {
    $conn->query("ALTER TABLE cart ADD COLUMN customization TEXT AFTER instructions");
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Invalid JSON input"]);
    exit;
}

$customer_id = (int)($data["customer_id"] ?? 0);
$dish_id     = (int)($data["dish_id"] ?? 0);
$quantity    = (int)($data["quantity"] ?? 1);

if ($customer_id <= 0) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Invalid customer ID: $customer_id"]);
    exit;
}

if ($dish_id <= 0) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Invalid dish ID: $dish_id"]);
    exit;
}

$portion_size = $conn->real_escape_string($data["portion_size"] ?? $data["portion"] ?? "Regular");
$spice_level  = $conn->real_escape_string($data["spice_level"] ?? "Medium");
$oil_level    = $conn->real_escape_string($data["oil_level"] ?? "Medium");
$salt_level   = $conn->real_escape_string($data["salt_level"] ?? "Normal");
$instructions = $conn->real_escape_string($data["instructions"] ?? "");

// Create customization JSON
$customization = json_encode([
    "portion_size" => $portion_size,
    "spice_level" => $spice_level,
    "oil_level" => $oil_level,
    "salt_level" => $salt_level,
    "instructions" => $instructions
]);
$customization = $conn->real_escape_string($customization);

$sql = "INSERT INTO cart 
    (customer_id, dish_id, quantity, portion_size, spice_level, oil_level, salt_level, instructions, customization)
    VALUES
    ($customer_id, $dish_id, $quantity, '$portion_size', '$spice_level', '$oil_level', '$salt_level', '$instructions', '$customization')";

ob_end_clean();
if ($conn->query($sql)) {
    echo json_encode([
        "success" => true,
        "message" => "Item added to cart",
        "cart_id" => $conn->insert_id
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Insert failed: " . $conn->error
    ]);
}

$conn->close();
