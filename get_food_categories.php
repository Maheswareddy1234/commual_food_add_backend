<?php
/**
 * Get Food Categories API
 * Returns all active food categories from the database
 * Auto-creates and seeds the table if it doesn't exist
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

require_once 'db.php';

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

try {
    // Check if table exists, create if not
    $tableCheck = $conn->query("SHOW TABLES LIKE 'food_categories'");
    if ($tableCheck->num_rows == 0) {
        // Create the table
        $createSql = "CREATE TABLE food_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            icon VARCHAR(50) NOT NULL,
            color VARCHAR(20) NOT NULL,
            description TEXT,
            display_order INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if (!$conn->query($createSql)) {
            throw new Exception("Failed to create table: " . $conn->error);
        }
        
        // Insert default categories
        $categories = [
            ['Vegetarian', '🥗', '#DCFCE7', 'Fresh vegetable-based meals', 1],
            ['Non-Vegetarian', '🍗', '#FFE2E2', 'Protein-rich chicken & meat dishes', 2],
            ['Diabetic Friendly', '🥬', '#DBEAFE', 'Low sugar, controlled carbs', 3],
            ['Soft Diet', '🥣', '#FEF9C2', 'Easy to chew and digest', 4],
            ['Low Sodium', '🧂', '#F3E8FF', 'Heart-healthy, reduced salt', 5],
            ['High Protein', '💪', '#FFEDD4', 'Muscle building & recovery', 6]
        ];
        
        $stmt = $conn->prepare("INSERT INTO food_categories (name, icon, color, description, display_order) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($categories as $cat) {
            $stmt->bind_param("ssssi", $cat[0], $cat[1], $cat[2], $cat[3], $cat[4]);
            $stmt->execute();
        }
        $stmt->close();
    }
    
    // Query to get all active categories
    $sql = "SELECT id, name, icon, color, description 
            FROM food_categories 
            WHERE is_active = 1 
            ORDER BY display_order ASC, name ASC";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'icon' => $row['icon'],
            'color' => $row['color'],
            'description' => $row['description']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'categories' => $categories,
        'count' => count($categories)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching categories: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
