<?php
/**
 * FCM Helper - Reusable functions for sending Firebase notifications
 * Include this file in other PHP scripts to send notifications
 */

define('FCM_PROJECT_ID', 'food-30b73');

/**
 * Get access token using service account
 */
function getFCMAccessToken() {
    $serviceAccountFile = __DIR__ . '/firebase-service-account.json';
    
    if (!file_exists($serviceAccountFile)) {
        error_log("FCM: Service account file not found");
        return null;
    }
    
    $serviceAccount = json_decode(file_get_contents($serviceAccountFile), true);
    if (!$serviceAccount) {
        error_log("FCM: Invalid service account JSON");
        return null;
    }
    
    // Create JWT
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
        error_log("FCM: Invalid private key");
        return null;
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
        error_log("FCM: Token exchange failed - $response");
        return null;
    }
    
    $tokenData = json_decode($response, true);
    return $tokenData['access_token'] ?? null;
}

/**
 * Send FCM notification to a specific token
 */
function sendFCMNotification($fcmToken, $title, $body, $data = []) {
    if (empty($fcmToken)) {
        error_log("FCM: Empty token provided");
        return false;
    }
    
    $accessToken = getFCMAccessToken();
    if (!$accessToken) {
        return false;
    }
    
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
                    'sound' => 'default'
                ]
            ],
            'data' => array_merge(['title' => $title, 'message' => $body], $data)
        ]
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/' . FCM_PROJECT_ID . '/messages:send');
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
        error_log("FCM: Notification sent successfully to token " . substr($fcmToken, 0, 20) . "...");
        return true;
    } else {
        error_log("FCM: Failed to send notification - HTTP $httpCode - $response");
        return false;
    }
}

/**
 * Send notification to a chef by chef_id
 */
function notifyChef($conn, $chefId, $title, $body, $data = []) {
    $stmt = $conn->prepare("SELECT fcm_token FROM chefs WHERE id = ?");
    $stmt->bind_param("i", $chefId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $fcmToken = $row['fcm_token'];
        if (!empty($fcmToken)) {
            $data['type'] = $data['type'] ?? 'new_order';
            return sendFCMNotification($fcmToken, $title, $body, $data);
        }
    }
    return false;
}

/**
 * Send notification to a customer by customer_id
 */
function notifyCustomer($conn, $customerId, $title, $body, $data = []) {
    $stmt = $conn->prepare("SELECT fcm_token FROM customers WHERE id = ?");
    $stmt->bind_param("i", $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $fcmToken = $row['fcm_token'];
        if (!empty($fcmToken)) {
            $data['type'] = $data['type'] ?? 'order_update';
            return sendFCMNotification($fcmToken, $title, $body, $data);
        }
    }
    return false;
}
?>
