<?php
/**
 * Send Chat Message API
 * POST: sender_type, sender_id, receiver_id, order_id, message
 */
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once "db.php";

try {
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Create chat_messages table if not exists
    $createResult = $conn->query("CREATE TABLE IF NOT EXISTS chat_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL DEFAULT 0,
        sender_id INT NOT NULL,
        sender_type ENUM('chef', 'customer') NOT NULL,
        receiver_id INT NOT NULL DEFAULT 0,
        message TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_order_id (order_id),
        INDEX idx_sender (sender_id, sender_type)
    )");
    
    if (!$createResult) {
        // Table might already exist with different structure, try to continue
    }
    
    // Get POST data
    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData, true);
    
    if ($data === null) {
        throw new Exception("Invalid JSON data received");
    }
    
    $order_id = isset($data['order_id']) ? (int)$data['order_id'] : 0;
    $sender_type = isset($data['sender_type']) ? $data['sender_type'] : '';
    $sender_id = isset($data['sender_id']) ? (int)$data['sender_id'] : 0;
    $receiver_id = isset($data['receiver_id']) ? (int)$data['receiver_id'] : 0;
    $message = isset($data['message']) ? trim($data['message']) : '';
    
    // Validation - only require sender_id, sender_type, and message
    if (empty($sender_type) || $sender_id == 0 || empty($message)) {
        throw new Exception("sender_type, sender_id, and message are required. Got: sender_type=$sender_type, sender_id=$sender_id, message=" . (empty($message) ? "empty" : "has value"));
    }
    
    if (!in_array($sender_type, ['customer', 'chef'])) {
        throw new Exception("Invalid sender type: $sender_type");
    }
    
    // Insert message
    $stmt = $conn->prepare("INSERT INTO chat_messages (order_id, sender_type, sender_id, receiver_id, message) VALUES (?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("isiis", $order_id, $sender_type, $sender_id, $receiver_id, $message);
    
    if ($stmt->execute()) {
        $message_id = $conn->insert_id;
        
        // Send FCM push notification to recipient
        require_once 'fcm_helper.php';
        
        // Get sender name
        $sender_name = "Someone";
        if ($sender_type === 'customer') {
            $name_result = $conn->query("SELECT name FROM customers WHERE id = $sender_id");
            if ($name_result && $row = $name_result->fetch_assoc()) {
                $sender_name = $row['name'];
            }
            // Notify chef (receiver_id is chef_id)
            if ($receiver_id > 0) {
                notifyChef($conn, $receiver_id, "💬 New Message", "$sender_name: $message", [
                    'type' => 'new_message',
                    'sender_type' => 'customer',
                    'sender_id' => strval($sender_id),
                    'order_id' => strval($order_id)
                ]);
            }
        } else {
            $name_result = $conn->query("SELECT name FROM chefs WHERE id = $sender_id");
            if ($name_result && $row = $name_result->fetch_assoc()) {
                $sender_name = $row['name'];
            }
            // Notify customer (receiver_id is customer_id)
            if ($receiver_id > 0) {
                notifyCustomer($conn, $receiver_id, "💬 New Message", "$sender_name: $message", [
                    'type' => 'new_message',
                    'sender_type' => 'chef',
                    'sender_id' => strval($sender_id),
                    'order_id' => strval($order_id)
                ]);
            }
        }
        
        echo json_encode([
            "success" => true,
            "message" => "Message sent",
            "data" => [
                "id" => $message_id,
                "order_id" => $order_id,
                "sender_type" => $sender_type,
                "sender_id" => $sender_id,
                "receiver_id" => $receiver_id,
                "message" => $message,
                "is_read" => false,
                "created_at" => date("Y-m-d H:i:s"),
                "time" => date("h:i A")
            ]
        ]);
    } else {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>
