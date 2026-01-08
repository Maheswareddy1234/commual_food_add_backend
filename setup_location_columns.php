<?php
/**
 * Setup script to add location columns to chefs, customers, and home_chefs tables
 * Run this once to update your database schema
 */

header("Content-Type: application/json");
require_once "db.php";

$results = [];

// Add latitude/longitude to chefs table
$sql1 = "ALTER TABLE chefs ADD COLUMN IF NOT EXISTS latitude DECIMAL(10, 8) NULL";
$sql2 = "ALTER TABLE chefs ADD COLUMN IF NOT EXISTS longitude DECIMAL(11, 8) NULL";

// Add latitude/longitude to customers table  
$sql3 = "ALTER TABLE customers ADD COLUMN IF NOT EXISTS latitude DECIMAL(10, 8) NULL";
$sql4 = "ALTER TABLE customers ADD COLUMN IF NOT EXISTS longitude DECIMAL(11, 8) NULL";

// Add latitude/longitude to home_chefs table
$sql5 = "ALTER TABLE home_chefs ADD COLUMN IF NOT EXISTS latitude DECIMAL(10, 8) NULL";
$sql6 = "ALTER TABLE home_chefs ADD COLUMN IF NOT EXISTS longitude DECIMAL(11, 8) NULL";

// Execute each query
$queries = [
    "chefs_latitude" => $sql1,
    "chefs_longitude" => $sql2,
    "customers_latitude" => $sql3,
    "customers_longitude" => $sql4,
    "home_chefs_latitude" => $sql5,
    "home_chefs_longitude" => $sql6
];

foreach ($queries as $name => $sql) {
    if ($conn->query($sql)) {
        $results[$name] = "success";
    } else {
        // Check if column already exists
        if (strpos($conn->error, "Duplicate column") !== false) {
            $results[$name] = "already exists";
        } else {
            $results[$name] = "error: " . $conn->error;
        }
    }
}

echo json_encode([
    "success" => true,
    "message" => "Location columns setup complete",
    "results" => $results
]);
?>
