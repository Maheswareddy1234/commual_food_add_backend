<?php
/**
 * Save Health Preferences API
 * Create or update health preferences for a customer
 */
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db.php';

try {
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Ensure the health_preferences table exists
    $createTable = "CREATE TABLE IF NOT EXISTS health_preferences (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL UNIQUE,
        dietary_type VARCHAR(50),
        allergies TEXT,
        health_goals TEXT,
        calorie_limit INT,
        avoid_ingredients TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->query($createTable);
    
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    $customer_id = isset($input['customer_id']) ? intval($input['customer_id']) : 0;
    $dietary_type = isset($input['dietary_type']) ? $input['dietary_type'] : "";
    $allergies = isset($input['allergies']) ? $input['allergies'] : "[]";
    $health_goals = isset($input['health_goals']) ? $input['health_goals'] : "[]";
    $calorie_limit = isset($input['calorie_limit']) ? intval($input['calorie_limit']) : 0;
    $avoid_ingredients = isset($input['avoid_ingredients']) ? $input['avoid_ingredients'] : "[]";
    
    if ($customer_id <= 0) {
        throw new Exception("Valid customer_id is required");
    }
    
    // Use INSERT ... ON DUPLICATE KEY UPDATE for upsert
    $query = "INSERT INTO health_preferences 
              (customer_id, dietary_type, allergies, health_goals, calorie_limit, avoid_ingredients)
              VALUES (?, ?, ?, ?, ?, ?)
              ON DUPLICATE KEY UPDATE
              dietary_type = VALUES(dietary_type),
              allergies = VALUES(allergies),
              health_goals = VALUES(health_goals),
              calorie_limit = VALUES(calorie_limit),
              avoid_ingredients = VALUES(avoid_ingredients)";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("isssis", $customer_id, $dietary_type, $allergies, $health_goals, $calorie_limit, $avoid_ingredients);
    
    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Health preferences saved successfully"
        ]);
    } else {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>
