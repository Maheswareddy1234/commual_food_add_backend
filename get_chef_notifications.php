<?php
/**
 * Get Chef Notifications API
 * Returns notifications for a specific chef from the database
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Include database connection
require_once 'db.php';

// Get chef_id from request
$chef_id = isset($_GET['chef_id']) ? intval($_GET['chef_id']) : 0;

if ($chef_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid chef ID'
    ]);
    exit;
}

try {
    // Check if notifications table exists, if not create it
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

    // Fetch notifications for the chef
    $stmt = $conn->prepare("
        SELECT 
            n.id,
            n.type,
            n.title,
            n.message,
            n.order_id,
            n.is_read,
            n.created_at
        FROM chef_notifications n
        WHERE n.chef_id = :chef_id
        ORDER BY n.created_at DESC
        LIMIT 50
    ");
    $stmt->bindParam(':chef_id', $chef_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Count unread notifications
    $countStmt = $conn->prepare("
        SELECT COUNT(*) as unread_count 
        FROM chef_notifications 
        WHERE chef_id = :chef_id AND is_read = 0
    ");
    $countStmt->bindParam(':chef_id', $chef_id, PDO::PARAM_INT);
    $countStmt->execute();
    $unreadCount = $countStmt->fetch(PDO::FETCH_ASSOC)['unread_count'];

    // Format notifications
    $formattedNotifications = [];
    foreach ($notifications as $notification) {
        $formattedNotifications[] = [
            'id' => (int)$notification['id'],
            'type' => $notification['type'],
            'title' => $notification['title'],
            'message' => $notification['message'],
            'order_id' => $notification['order_id'] ? (int)$notification['order_id'] : null,
            'is_read' => (bool)$notification['is_read'],
            'created_at' => $notification['created_at'],
            'time_ago' => getTimeAgo($notification['created_at'])
        ];
    }

    echo json_encode([
        'success' => true,
        'unread_count' => (int)$unreadCount,
        'notifications' => $formattedNotifications
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

/**
 * Convert timestamp to human-readable time ago format
 */
function getTimeAgo($timestamp) {
    $time = strtotime($timestamp);
    $diff = time() - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M d, Y', $time);
    }
}
?>
