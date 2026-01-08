<?php
/**
 * Save FCM Token API
 * Stores the device FCM token for push notifications
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php';

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit;
    }
    
    $user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
    $user_role = isset($data['user_role']) ? $data['user_role'] : 'customer';
    $fcm_token = isset($data['fcm_token']) ? trim($data['fcm_token']) : '';
    
    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        exit;
    }
    
    if (empty($fcm_token)) {
        echo json_encode(['success' => false, 'message' => 'FCM token is required']);
        exit;
    }
    
    // Check if this is for a customer or chef
    if ($user_role === 'chef') {
        // Update chef's FCM token
        $stmt = $conn->prepare("UPDATE chefs SET fcm_token = ? WHERE id = ?");
        $stmt->bind_param("si", $fcm_token, $user_id);
    } else {
        // Update customer's FCM token
        $stmt = $conn->prepare("UPDATE customers SET fcm_token = ? WHERE id = ?");
        $stmt->bind_param("si", $fcm_token, $user_id);
    }
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'FCM token saved successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save FCM token: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
