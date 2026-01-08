<?php
/**
 * Get Chat Messages API
 * GET: order_id, user_id (optional: last_id for pagination)
 */
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once "db.php";

try {
    if ($conn->connect_error) {
        throw new Exception("Database connection failed");
    }
    
    // Create chat_messages table if not exists
    $conn->query("CREATE TABLE IF NOT EXISTS chat_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        sender_id INT NOT NULL,
        sender_type ENUM('chef', 'customer') NOT NULL,
        receiver_id INT NOT NULL DEFAULT 0,
        message TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_order_id (order_id),
        INDEX idx_sender (sender_id, sender_type)
    )");
    
    $order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
    $last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
    $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
    $user_type = isset($_GET['user_type']) ? $_GET['user_type'] : 'customer';
    
    if ($order_id == 0) {
        echo json_encode([
            "success" => true,
            "count" => 0,
            "messages" => []
        ]);
        exit;
    }
    
    // Get messages for this order
    $sql = "SELECT * FROM chat_messages WHERE order_id = ?";
    $params = [$order_id];
    $types = "i";
    
    // If last_id provided, get only new messages (for polling)
    if ($last_id > 0) {
        $sql .= " AND id > ?";
        $params[] = $last_id;
        $types .= "i";
    }
    
    $sql .= " ORDER BY created_at ASC LIMIT 100";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode([
            "success" => true,
            "count" => 0,
            "messages" => []
        ]);
        exit;
    }
    
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            "id" => (int)$row["id"],
            "order_id" => (int)$row["order_id"],
            "sender_type" => $row["sender_type"],
            "sender_id" => (int)$row["sender_id"],
            "receiver_id" => isset($row["receiver_id"]) ? (int)$row["receiver_id"] : 0,
            "message" => $row["message"],
            "is_read" => (bool)$row["is_read"],
            "created_at" => $row["created_at"],
            "time" => date("h:i A", strtotime($row["created_at"]))
        ];
    }
    
    // Mark messages as read if user_id provided
    if ($user_id > 0 && count($messages) > 0) {
        $updateSql = "UPDATE chat_messages SET is_read = 1 WHERE order_id = ? AND receiver_id = ? AND is_read = 0";
        $updateStmt = $conn->prepare($updateSql);
        if ($updateStmt) {
            $updateStmt->bind_param("ii", $order_id, $user_id);
            $updateStmt->execute();
            $updateStmt->close();
        }
    }
    
    $stmt->close();
    
    echo json_encode([
        "success" => true,
        "count" => count($messages),
        "messages" => $messages
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>
