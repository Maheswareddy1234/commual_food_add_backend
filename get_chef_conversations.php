<?php
/**
 * Get Chef's Chat Conversations
 * Returns list of customers who have chatted with this chef
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
    
    $chef_id = isset($_GET['chef_id']) ? (int)$_GET['chef_id'] : 0;
    
    if ($chef_id == 0) {
        echo json_encode([
            "success" => false,
            "message" => "chef_id is required"
        ]);
        exit;
    }
    
    // Simpler query - get orders for this chef that have messages
    $sql = "SELECT DISTINCT 
        o.id as order_id,
        o.order_number,
        o.customer_id,
        c.name as customer_name,
        c.email as customer_email
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN dishes d ON oi.dish_id = d.id
    JOIN customers c ON o.customer_id = c.id
    LEFT JOIN chat_messages cm ON o.id = cm.order_id
    WHERE d.chef_id = ?
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
    
    $stmt->bind_param("i", $chef_id);
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
            
            // Get unread count
            $unreadQuery = "SELECT COUNT(*) as cnt FROM chat_messages WHERE order_id = ? AND sender_type = 'customer' AND is_read = 0";
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
            
            $conversations[] = [
                "order_id" => $orderId,
                "order_number" => $row['order_number'],
                "customer_id" => (int)$row['customer_id'],
                "customer_name" => $row['customer_name'],
                "customer_email" => $row['customer_email'],
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
