<?php
/**
 * Mark Chef Notifications as Read API
 * Marks one or all notifications as read for a chef
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
$notification_id = isset($input['notification_id']) ? intval($input['notification_id']) : 0;
$mark_all = isset($input['mark_all']) ? (bool)$input['mark_all'] : false;

if ($chef_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid chef ID'
    ]);
    exit;
}

try {
    if ($mark_all) {
        // Mark all notifications as read for this chef
        $stmt = $conn->prepare("
            UPDATE chef_notifications 
            SET is_read = 1 
            WHERE chef_id = :chef_id AND is_read = 0
        ");
        $stmt->bindParam(':chef_id', $chef_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $affected = $stmt->rowCount();
        
        echo json_encode([
            'success' => true,
            'message' => $affected . ' notification(s) marked as read'
        ]);
    } else if ($notification_id > 0) {
        // Mark specific notification as read
        $stmt = $conn->prepare("
            UPDATE chef_notifications 
            SET is_read = 1 
            WHERE id = :notification_id AND chef_id = :chef_id
        ");
        $stmt->bindParam(':notification_id', $notification_id, PDO::PARAM_INT);
        $stmt->bindParam(':chef_id', $chef_id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Notification not found'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Please provide notification_id or set mark_all to true'
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
