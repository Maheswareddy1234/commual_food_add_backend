<?php
/**
 * Test FCM Setup - Debug Endpoint
 * Run this to verify FCM is configured correctly
 */

header('Content-Type: application/json');
require_once 'db.php';

$results = [];

// 1. Check if fcm_token column exists
$chefCheck = $conn->query("SHOW COLUMNS FROM chefs LIKE 'fcm_token'");
$results['chefs_fcm_column'] = $chefCheck->num_rows > 0 ? 'EXISTS' : 'MISSING';

$customerCheck = $conn->query("SHOW COLUMNS FROM customers LIKE 'fcm_token'");
$results['customers_fcm_column'] = $customerCheck->num_rows > 0 ? 'EXISTS' : 'MISSING';

// 2. Check if any tokens are saved
$chefTokens = $conn->query("SELECT id, name, fcm_token FROM chefs WHERE fcm_token IS NOT NULL AND fcm_token != ''");
$results['chefs_with_tokens'] = $chefTokens->num_rows;

if ($chefTokens->num_rows > 0) {
    $results['chef_tokens_list'] = [];
    while ($row = $chefTokens->fetch_assoc()) {
        $results['chef_tokens_list'][] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'token_preview' => substr($row['fcm_token'], 0, 30) . '...'
        ];
    }
}

$customerTokens = $conn->query("SELECT id, name, fcm_token FROM customers WHERE fcm_token IS NOT NULL AND fcm_token != ''");
$results['customers_with_tokens'] = $customerTokens->num_rows;

if ($customerTokens->num_rows > 0) {
    $results['customer_tokens_list'] = [];
    while ($row = $customerTokens->fetch_assoc()) {
        $results['customer_tokens_list'][] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'token_preview' => substr($row['fcm_token'], 0, 30) . '...'
        ];
    }
}

// 3. Check if service account file exists
$serviceAccountFile = __DIR__ . '/firebase-service-account.json';
$results['service_account_file'] = file_exists($serviceAccountFile) ? 'EXISTS' : 'MISSING';

if (file_exists($serviceAccountFile)) {
    $serviceAccount = json_decode(file_get_contents($serviceAccountFile), true);
    if ($serviceAccount) {
        $results['project_id'] = $serviceAccount['project_id'] ?? 'NOT FOUND';
        $results['client_email'] = $serviceAccount['client_email'] ?? 'NOT FOUND';
    } else {
        $results['service_account_error'] = 'Invalid JSON in service account file';
    }
}

// Output results
echo json_encode([
    'success' => true,
    'message' => 'FCM Debug Info',
    'data' => $results
], JSON_PRETTY_PRINT);

$conn->close();
?>
