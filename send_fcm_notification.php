<?php
/**
 * Send FCM Notification API
 * Uses Firebase Cloud Messaging HTTP v1 API with Service Account
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php';

// Firebase Configuration - UPDATE THESE VALUES
define('FIREBASE_PROJECT_ID', 'food-30b73');
define('FIREBASE_SERVICE_ACCOUNT_FILE', __DIR__ . '/firebase-service-account.json');

/**
 * Get OAuth 2.0 Access Token from Service Account
 */
function getAccessToken() {
    $serviceAccount = json_decode(file_get_contents(FIREBASE_SERVICE_ACCOUNT_FILE), true);
    
    if (!$serviceAccount) {
        throw new Exception('Failed to load service account file');
    }
    
    $now = time();
    $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
    
    $payload = base64_encode(json_encode([
        'iss' => $serviceAccount['client_email'],
        'sub' => $serviceAccount['client_email'],
        'aud' => 'https://oauth2.googleapis.com/token',
        'iat' => $now,
        'exp' => $now + 3600,
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging'
    ]));
    
    $signatureInput = $header . '.' . $payload;
    
    $privateKey = openssl_pkey_get_private($serviceAccount['private_key']);
    if (!$privateKey) {
        throw new Exception('Invalid private key');
    }
    
    openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
    $jwt = $signatureInput . '.' . base64_encode($signature);
    
    // Exchange JWT for access token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception('Failed to get access token: ' . $response);
    }
    
    $tokenData = json_decode($response, true);
    return $tokenData['access_token'];
}

/**
 * Send FCM notification to a specific device
 */
function sendNotification($fcmToken, $title, $body, $data = []) {
    try {
        $accessToken = getAccessToken();
        
        $message = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'sound' => 'default',
                        'click_action' => 'OPEN_ACTIVITY'
                    ]
                ],
                'data' => array_merge(['title' => $title, 'message' => $body], $data)
            ]
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/' . FIREBASE_PROJECT_ID . '/messages:send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'response' => json_decode($response, true)];
        } else {
            return ['success' => false, 'error' => $response, 'http_code' => $httpCode];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Send notification to a user by user_id
 */
function sendNotificationToUser($userId, $userRole, $title, $body, $data = []) {
    global $conn;
    
    if ($userRole === 'chef') {
        $stmt = $conn->prepare("SELECT fcm_token FROM chefs WHERE id = ?");
    } else {
        $stmt = $conn->prepare("SELECT fcm_token FROM customers WHERE id = ?");
    }
    
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $fcmToken = $row['fcm_token'];
        if (!empty($fcmToken)) {
            return sendNotification($fcmToken, $title, $body, $data);
        } else {
            return ['success' => false, 'error' => 'User has no FCM token'];
        }
    }
    
    return ['success' => false, 'error' => 'User not found'];
}

// Handle direct API calls
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit;
    }
    
    $userId = isset($data['user_id']) ? intval($data['user_id']) : 0;
    $userRole = isset($data['user_role']) ? $data['user_role'] : 'customer';
    $title = isset($data['title']) ? $data['title'] : 'Communal Food';
    $body = isset($data['body']) ? $data['body'] : '';
    $extraData = isset($data['data']) ? $data['data'] : [];
    
    if ($userId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        exit;
    }
    
    $result = sendNotificationToUser($userId, $userRole, $title, $body, $extraData);
    echo json_encode($result);
}
?>
