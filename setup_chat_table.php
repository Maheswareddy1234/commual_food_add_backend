<?php
/**
 * Setup Chat Tables
 * Run once to create chat_messages table
 */

header("Content-Type: application/json");
require_once "db.php";

// Create chat_messages table
$sql = "CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    sender_type ENUM('customer', 'chef') NOT NULL,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order (order_id),
    INDEX idx_sender (sender_type, sender_id),
    INDEX idx_created (created_at)
)";

if ($conn->query($sql)) {
    echo json_encode([
        "success" => true,
        "message" => "Chat table created successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $conn->error
    ]);
}
?>
