<?php
/**
 * Setup Food Categories Table
 * Run this once to create the food_categories table and insert default data
 */

header("Content-Type: application/json");
require_once 'db.php';

try {
    // Create food_categories table
    $sql = "CREATE TABLE IF NOT EXISTS food_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        icon VARCHAR(50) NOT NULL,
        color VARCHAR(20) NOT NULL,
        description TEXT,
        display_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($sql)) {
        throw new Exception("Failed to create table: " . $conn->error);
    }
    
    // Check if categories already exist
    $check = $conn->query("SELECT COUNT(*) as cnt FROM food_categories");
    $count = $check->fetch_assoc()['cnt'];
    
    if ($count == 0) {
        // Insert default categories
        $categories = [
            ['Veg', '🥗', '#E8F5E9', 'Vegetarian dishes - pure veg food without any meat', 1],
            ['Non-Veg', '🍗', '#FFEBEE', 'Non-vegetarian dishes including chicken, mutton, fish', 2],
            ['Diabetic', '🥬', '#E3F2FD', 'Diabetic-friendly meals with low sugar and controlled carbs', 3],
            ['Soft Diet', '🥣', '#FFF8E1', 'Soft and easy-to-digest meals for recovery and elderly', 4],
            ['Low Salt', '🧂', '#F3E5F5', 'Low sodium meals for heart health and blood pressure control', 5],
            ['High Protein', '💪', '#FFF3E0', 'High protein meals for fitness and muscle building', 6]
        ];
        
        $stmt = $conn->prepare("INSERT INTO food_categories (name, icon, color, description, display_order) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($categories as $cat) {
            $stmt->bind_param("ssssi", $cat[0], $cat[1], $cat[2], $cat[3], $cat[4]);
            $stmt->execute();
        }
        
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Food categories table created and populated with ' . count($categories) . ' categories'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Food categories table already exists with ' . $count . ' categories'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
