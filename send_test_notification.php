<?php
/**
 * Send Test FCM Notification
 * This sends a test notification to verify FCM is working
 */

header('Content-Type: application/json');
require_once 'db.php';

// Get customer with FCM token
$result = $conn->query("SELECT id, name, fcm_token FROM customers WHERE fcm_token IS NOT NULL AND fcm_token != '' LIMIT 1");

if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'No customer with FCM token found']);
    exit;
}

$customer = $result->fetch_assoc();
$fcmToken = $customer['fcm_token'];

echo "Customer: " . $customer['name'] . " (ID: " . $customer['id'] . ")\n";
echo "Token: " . substr($fcmToken, 0, 50) . "...\n\n";

// Load service account
$serviceAccountFile = __DIR__ . '/firebase-service-account.json';
if (!file_exists($serviceAccountFile)) {
    echo json_encode(['success' => false, 'message' => 'Service account file not found']);
    exit;
}

$serviceAccount = json_decode(file_get_contents($serviceAccountFile), true);
$projectId = $serviceAccount['project_id'];

// Create JWT
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

$now = time();
$header = base64url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
$payload = base64url_encode(json_encode([
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
    echo json_encode(['success' => false, 'message' => 'Invalid private key: ' . openssl_error_string()]);
    exit;
}

openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
$jwt = $signatureInput . '.' . base64url_encode($signature);

echo "JWT created successfully\n";

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

$tokenResponse = curl_exec($ch);
$tokenHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Token exchange HTTP code: $tokenHttpCode\n";

if ($tokenHttpCode !== 200) {
    echo "Token exchange failed: $tokenResponse\n";
    exit;
}

$tokenData = json_decode($tokenResponse, true);
$accessToken = $tokenData['access_token'];
echo "Access token obtained successfully\n\n";

// Send notification
$message = [
    'message' => [
        'token' => $fcmToken,
        'notification' => [
            'title' => '🍽️ Test Notification',
            'body' => 'Hello from Communal Food App! FCM is working!'
        ],
        'android' => [
            'priority' => 'high',
            'notification' => [
                'sound' => 'default'
            ]
        ],
        'data' => [
            'type' => 'test',
            'title' => 'Test Notification',
            'message' => 'FCM is working!'
        ]
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/$projectId/messages:send");
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

echo "FCM Send HTTP code: $httpCode\n";
echo "FCM Response: $response\n";

if ($httpCode >= 200 && $httpCode < 300) {
    echo "\n✅ NOTIFICATION SENT SUCCESSFULLY!\n";
} else {
    echo "\n❌ NOTIFICATION FAILED\n";
}

$conn->close();
?>
