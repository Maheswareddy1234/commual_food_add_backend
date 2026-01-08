<?php
/**
 * Add FCM Token Column Migration
 * Run this script to add fcm_token column to customers and chefs tables
 */

header('Content-Type: application/json');
require_once 'db.php';

try {
    $results = [];
    
    // Add fcm_token to chefs table
    $chefResult = $conn->query("SHOW COLUMNS FROM chefs LIKE 'fcm_token'");
    if ($chefResult->num_rows == 0) {
        $conn->query("ALTER TABLE chefs ADD COLUMN fcm_token VARCHAR(512) NULL");
        $results[] = "Added fcm_token to chefs table";
    } else {
        $results[] = "fcm_token already exists in chefs table";
    }
    
    // Add fcm_token to customers table
    $customerResult = $conn->query("SHOW COLUMNS FROM customers LIKE 'fcm_token'");
    if ($customerResult->num_rows == 0) {
        $conn->query("ALTER TABLE customers ADD COLUMN fcm_token VARCHAR(512) NULL");
        $results[] = "Added fcm_token to customers table";
    } else {
        $results[] = "fcm_token already exists in customers table";
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'FCM token columns migration complete',
        'details' => $results
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Migration failed: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
