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
            "message" => "Fatal Error: " . $error['message']
        ]);
    }
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

$raw_input = file_get_contents("php://input");
$data = json_decode($raw_input, true);

if (!$data) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Invalid JSON input"]);
    exit;
}

$customer_id = (int)($data["customer_id"] ?? 0);
$subtotal = (float)($data["subtotal"] ?? 0);
$discount = (float)($data["discount"] ?? 0);
$total = (float)($data["total"] ?? 0);
$payment_method = $conn->real_escape_string($data["payment_method"] ?? "cod");
$delivery_date = $conn->real_escape_string($data["delivery_date"] ?? "");
$delivery_time = $conn->real_escape_string($data["delivery_time"] ?? "");

if ($customer_id <= 0) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Invalid customer ID: $customer_id"]);
    exit;
}

// Create tables if they don't exist (DO NOT DROP existing tables!)
$conn->query("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    order_number VARCHAR(50) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    delivery_date VARCHAR(50),
    delivery_time VARCHAR(50),
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    dish_id INT NOT NULL,
    dish_name VARCHAR(100),
    quantity INT DEFAULT 1,
    price DECIMAL(10,2),
    customization TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
)");

// Generate order number
$year = date('Y');
$result = $conn->query("SELECT COUNT(*) as cnt FROM orders WHERE YEAR(created_at) = $year");
if ($result) {
    $order_count = $result->fetch_assoc()['cnt'];
} else {
    $order_count = 0;
}
$order_number = "#ORD-$year-" . str_pad($order_count + 1, 6, '0', STR_PAD_LEFT);

// Insert order with 'pending' status so chef can accept/reject
$sql = "INSERT INTO orders (customer_id, order_number, subtotal, discount, total, payment_method, delivery_date, delivery_time, status) 
        VALUES ($customer_id, '$order_number', $subtotal, $discount, $total, '$payment_method', '$delivery_date', '$delivery_time', 'pending')";

if ($conn->query($sql)) {
    $order_id = $conn->insert_id;
    
    // Move cart items to order_items and collect chef IDs
    $cart_items = $conn->query("SELECT c.*, d.dish_name, d.price, d.chef_id FROM cart c JOIN dishes d ON c.dish_id = d.id WHERE c.customer_id = $customer_id");
    
    $chef_ids = []; // Track unique chef IDs for notifications
    
    if ($cart_items && $cart_items->num_rows > 0) {
        while ($item = $cart_items->fetch_assoc()) {
            $dish_id = (int)$item['dish_id'];
            $dish_name = $conn->real_escape_string($item['dish_name']);
            $quantity = (int)$item['quantity'];
            $price = (float)$item['price'];
            $customization = $conn->real_escape_string($item['customization'] ?? '{}');
            
            // Track chef IDs
            if (isset($item['chef_id'])) {
                $chef_ids[(int)$item['chef_id']] = true;
            }
            
            $conn->query("INSERT INTO order_items (order_id, dish_id, dish_name, quantity, price, customization) 
                         VALUES ($order_id, $dish_id, '$dish_name', $quantity, $price, '$customization')");
        }
    }
    
    // Create notifications for all chefs involved in this order
    if (!empty($chef_ids)) {
        // Create notifications table if it doesn't exist
        $conn->query("CREATE TABLE IF NOT EXISTS chef_notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            chef_id INT NOT NULL,
            type VARCHAR(50) NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            order_id INT DEFAULT NULL,
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_chef_id (chef_id),
            INDEX idx_is_read (is_read),
            INDEX idx_created_at (created_at)
        )");
        
        // Get customer name for notification
        $customer_result = $conn->query("SELECT name FROM customers WHERE id = $customer_id");
        $customer_name = "Customer";
        if ($customer_result && $row = $customer_result->fetch_assoc()) {
            $customer_name = $row['name'];
        }
        
        foreach (array_keys($chef_ids) as $chef_id) {
            $notification_title = "New Order Received!";
            $notification_message = "You have a new order ($order_number) from $customer_name. Tap to view details.";
            $notification_type = "new_order";
            
            $stmt = $conn->prepare("INSERT INTO chef_notifications (chef_id, type, title, message, order_id, is_read, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())");
            $stmt->bind_param("isssi", $chef_id, $notification_type, $notification_title, $notification_message, $order_id);
            $stmt->execute();
            $stmt->close();
            
            // Send FCM push notification
            require_once 'fcm_helper.php';
            notifyChef($conn, $chef_id, "🍽️ $notification_title", $notification_message, [
                'type' => 'new_order',
                'order_id' => strval($order_id),
                'order_number' => $order_number
            ]);
        }
    }
    
    // Clear cart
    $conn->query("DELETE FROM cart WHERE customer_id = $customer_id");
    
    ob_end_clean();
    echo json_encode([
        "success" => true,
        "message" => "Order placed successfully",
        "order_id" => $order_id,
        "order_number" => $order_number
    ]);
} else {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "DB Insert Error: " . $conn->error]);
}

$conn->close();

