<?php
/**
 * Create Chef Notification API
 * Used to create notifications for chefs (called internally when orders are placed, etc.)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once 'db.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$chef_id = isset($input['chef_id']) ? intval($input['chef_id']) : 0;
$type = isset($input['type']) ? $input['type'] : '';
$title = isset($input['title']) ? $input['title'] : '';
$message = isset($input['message']) ? $input['message'] : '';
$order_id = isset($input['order_id']) ? intval($input['order_id']) : null;

if ($chef_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid chef ID'
    ]);
    exit;
}

if (empty($type) || empty($title) || empty($message)) {
    echo json_encode([
        'success' => false,
        'message' => 'Type, title, and message are required'
    ]);
    exit;
}

try {
    // Ensure the notifications table exists
    $createTable = "CREATE TABLE IF NOT EXISTS chef_notifications (
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
    )";
    $conn->exec($createTable);

    // Insert the notification
    $stmt = $conn->prepare("
        INSERT INTO chef_notifications (chef_id, type, title, message, order_id, is_read, created_at)
        VALUES (:chef_id, :type, :title, :message, :order_id, 0, NOW())
    ");
    
    $stmt->bindParam(':chef_id', $chef_id, PDO::PARAM_INT);
    $stmt->bindParam(':type', $type, PDO::PARAM_STR);
    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
    $stmt->bindParam(':message', $message, PDO::PARAM_STR);
    $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    
    $stmt->execute();
    
    $notificationId = $conn->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Notification created successfully',
        'notification_id' => (int)$notificationId
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
