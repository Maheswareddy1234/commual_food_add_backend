<?php
/**
 * Toggle Favorite API
 * Add or remove a dish from customer's favorites
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
        throw new Exception("Database connection failed");
    }
    
    // Ensure the favorites table exists
    $createTable = "CREATE TABLE IF NOT EXISTS favorites (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL,
        dish_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_favorite (customer_id, dish_id)
    )";
    $conn->query($createTable);
    
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    $customer_id = isset($input['customer_id']) ? intval($input['customer_id']) : 0;
    $dish_id = isset($input['dish_id']) ? intval($input['dish_id']) : 0;
    
    if ($customer_id <= 0) {
        throw new Exception("Valid customer_id is required");
    }
    
    if ($dish_id <= 0) {
        throw new Exception("Valid dish_id is required");
    }
    
    // Check if already favorited
    $checkQuery = "SELECT id FROM favorites WHERE customer_id = ? AND dish_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ii", $customer_id, $dish_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        // Already favorited - remove it
        $deleteQuery = "DELETE FROM favorites WHERE customer_id = ? AND dish_id = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bind_param("ii", $customer_id, $dish_id);
        $deleteStmt->execute();
        
        echo json_encode([
            "success" => true,
            "is_favorite" => false,
            "message" => "Removed from favorites"
        ]);
        
        $deleteStmt->close();
    } else {
        // Not favorited - add it
        $insertQuery = "INSERT INTO favorites (customer_id, dish_id) VALUES (?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("ii", $customer_id, $dish_id);
        $insertStmt->execute();
        
        echo json_encode([
            "success" => true,
            "is_favorite" => true,
            "message" => "Added to favorites"
        ]);
        
        $insertStmt->close();
    }
    
    $checkStmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>
