<?php
/**
 * Get Customer's Chat Conversations
 * Returns list of chefs who the customer has chatted with
 */
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once "db.php";

try {
    // Check database connection
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
    
    $customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;
    
    if ($customer_id == 0) {
        echo json_encode([
            "success" => false,
            "message" => "customer_id is required"
        ]);
        exit;
    }
    
    // Simpler query - get orders for this customer
    $sql = "SELECT DISTINCT 
        o.id as order_id,
        o.order_number,
        ch.id as chef_id,
        ch.name as chef_name
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN dishes d ON oi.dish_id = d.id
    JOIN chefs ch ON d.chef_id = ch.id
    WHERE o.customer_id = ?
    ORDER BY o.created_at DESC
    LIMIT 50";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        // Return empty if query fails
        echo json_encode([
            "success" => true,
            "count" => 0,
            "total_unread" => 0,
            "conversations" => []
        ]);
        exit;
    }
    
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $conversations = [];
    $totalUnread = 0;
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orderId = (int)$row['order_id'];
            
            // Get last message for this order
            $msgQuery = "SELECT message, created_at FROM chat_messages WHERE order_id = ? ORDER BY created_at DESC LIMIT 1";
            $msgStmt = $conn->prepare($msgQuery);
            $lastMessage = "";
            $lastTime = "";
            $timeAgo = "";
            
            if ($msgStmt) {
                $msgStmt->bind_param("i", $orderId);
                $msgStmt->execute();
                $msgResult = $msgStmt->get_result();
                if ($msgRow = $msgResult->fetch_assoc()) {
                    $lastMessage = $msgRow['message'];
                    $lastTime = $msgRow['created_at'];
                    $timeAgo = formatTimeAgo(strtotime($lastTime));
                }
                $msgStmt->close();
            }
            
            // Get unread count (messages from chef that customer hasn't read)
            $unreadQuery = "SELECT COUNT(*) as cnt FROM chat_messages WHERE order_id = ? AND sender_type = 'chef' AND is_read = 0";
            $unreadStmt = $conn->prepare($unreadQuery);
            $unreadCount = 0;
            
            if ($unreadStmt) {
                $unreadStmt->bind_param("i", $orderId);
                $unreadStmt->execute();
                $unreadResult = $unreadStmt->get_result();
                if ($unreadRow = $unreadResult->fetch_assoc()) {
                    $unreadCount = (int)$unreadRow['cnt'];
                }
                $unreadStmt->close();
            }
            
            $totalUnread += $unreadCount;
            
            // Get dish name from first order item
            $dishQuery = "SELECT d.name as dish_name, d.image as dish_image FROM order_items oi 
                          JOIN dishes d ON oi.dish_id = d.id WHERE oi.order_id = ? LIMIT 1";
            $dishStmt = $conn->prepare($dishQuery);
            $dishName = "";
            $dishImage = "";
            
            if ($dishStmt) {
                $dishStmt->bind_param("i", $orderId);
                $dishStmt->execute();
                $dishResult = $dishStmt->get_result();
                if ($dishRow = $dishResult->fetch_assoc()) {
                    $dishName = $dishRow['dish_name'];
                    $dishImage = $dishRow['dish_image'];
                }
                $dishStmt->close();
            }
            
            $conversations[] = [
                "order_id" => $orderId,
                "order_number" => $row['order_number'],
                "chef_id" => (int)$row['chef_id'],
                "chef_name" => $row['chef_name'],
                "chef_image" => "",
                "dish_name" => $dishName,
                "dish_image" => $dishImage,
                "last_message" => $lastMessage,
                "last_message_time" => $lastTime,
                "time_ago" => $timeAgo,
                "unread_count" => $unreadCount
            ];
        }
    }
    
    $stmt->close();
    
    echo json_encode([
        "success" => true,
        "count" => count($conversations),
        "total_unread" => $totalUnread,
        "conversations" => $conversations
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}

function formatTimeAgo($timestamp) {
    if (!$timestamp) return "";
    
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return "Just now";
    } else if ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . " min ago";
    } else if ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . "h ago";
    } else if ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . "d ago";
    } else {
        return date("M d", $timestamp);
    }
}
?>
