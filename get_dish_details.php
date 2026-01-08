<?php
// IMPORTANT: No closing ?> tag at end to prevent whitespace issues

// Suppress all display errors - they break JSON
error_reporting(0);
ini_set('display_errors', 0);

// Set JSON header first
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

// Simple response function
function respond($success, $message, $data = null) {
    $response = ["success" => $success, "message" => $message];
    if ($data !== null) {
        $response["dish"] = $data;
    }
    echo json_encode($response);
    exit;
}

// Connect to database
$conn = @new mysqli("localhost", "root", "", "food-app");
if ($conn->connect_error) {
    respond(false, "DB connection failed");
}

/**
 * Get detailed information about a specific dish
 * Usage: GET get_dish_details.php?dish_id=1
 */

$dish_id = isset($_GET["dish_id"]) ? (int) $_GET["dish_id"] : 0;

if ($dish_id <= 0) {
    respond(false, "Invalid dish ID");
}

// Get dish details with chef info
$sql = "SELECT 
            d.id,
            d.chef_id,
            d.dish_name,
            d.description,
            d.price,
            d.preparation_time,
            d.category,
            d.food_type,
            d.health_tags,
            d.image,
            d.is_available,
            d.created_at,
            COALESCE(u.full_name, 'Home Chef') as chef_name
        FROM dishes d
        LEFT JOIN users u ON d.chef_id = u.id
        WHERE d.id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    respond(false, "Query preparation failed");
}

$stmt->bind_param("i", $dish_id);
if (!$stmt->execute()) {
    $stmt->close();
    respond(false, "Query execution failed");
}

$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // Parse health tags
    $healthTags = !empty($row["health_tags"]) ? 
        array_map('trim', explode(",", $row["health_tags"])) : [];
    
    // Generate ingredients based on dish name (since we don't have a separate ingredients table)
    $ingredients = generateIngredients($row["dish_name"], $row["category"]);
    
    // Generate nutrition info (placeholder values based on category)
    $nutrition = generateNutrition($row["category"], $row["health_tags"]);
    
    // Generate rating (placeholder - can be updated when ratings table is implemented)
    $rating = 4.5 + (rand(0, 5) / 10);
    $reviewCount = rand(50, 150);
    
    $dish = [
        "id" => (int) $row["id"],
        "chef_id" => (int) $row["chef_id"],
        "chef_name" => $row["chef_name"],
        "dish_name" => $row["dish_name"],
        "description" => $row["description"] ?? "Delicious homemade dish prepared with fresh ingredients.",
        "price" => (float) $row["price"],
        "preparation_time" => (int) $row["preparation_time"],
        "category" => $row["category"],
        "food_type" => $row["food_type"],
        "health_tags" => $healthTags,
        "image" => $row["image"],
        "is_available" => (bool) $row["is_available"],
        "rating" => round($rating, 1),
        "review_count" => $reviewCount,
        "calories" => $nutrition["calories"],
        "protein" => $nutrition["protein"],
        "carbs" => $nutrition["carbs"],
        "fat" => $nutrition["fat"],
        "fiber" => $nutrition["fiber"],
        "ingredients" => $ingredients
    ];
    
    $stmt->close();
    $conn->close();
    respond(true, "Dish found", $dish);
} else {
    $stmt->close();
    $conn->close();
    respond(false, "Dish not found");
}

/**
 * Generate ingredients based on dish name and category
 */
function generateIngredients($dishName, $category) {
    $dishName = strtolower($dishName);
    
    if (strpos($dishName, "paneer") !== false) {
        return ["Paneer", "Onion", "Tomato", "Spices", "Cream", "Ginger-Garlic"];
    } else if (strpos($dishName, "palak") !== false || strpos($dishName, "spinach") !== false) {
        return ["Spinach", "Paneer", "Onion", "Tomato", "Spices", "Low-fat cream"];
    } else if (strpos($dishName, "dal") !== false || strpos($dishName, "lentil") !== false) {
        return ["Lentils", "Onion", "Tomato", "Ghee", "Spices", "Cilantro"];
    } else if (strpos($dishName, "biryani") !== false) {
        return ["Basmati Rice", "Spices", "Saffron", "Ghee", "Yogurt", "Mint"];
    } else if (strpos($dishName, "rice") !== false || strpos($dishName, "pulao") !== false) {
        return ["Basmati Rice", "Vegetables", "Spices", "Ghee", "Cashews"];
    } else if (strpos($dishName, "curry") !== false) {
        return ["Vegetables", "Coconut Milk", "Spices", "Onion", "Tomato", "Curry Leaves"];
    } else if (strpos($dishName, "chicken") !== false) {
        return ["Chicken", "Onion", "Tomato", "Spices", "Yogurt", "Ginger-Garlic"];
    } else if (strpos($dishName, "roti") !== false || strpos($dishName, "chapati") !== false) {
        return ["Whole Wheat Flour", "Water", "Salt", "Ghee"];
    } else if (strpos($dishName, "paratha") !== false) {
        return ["Whole Wheat Flour", "Ghee", "Salt", "Spices"];
    } else if (strpos($dishName, "dosa") !== false) {
        return ["Rice", "Urad Dal", "Fenugreek", "Salt", "Oil"];
    } else if (strpos($dishName, "idli") !== false) {
        return ["Rice", "Urad Dal", "Salt"];
    } else if (strpos($dishName, "sambar") !== false) {
        return ["Toor Dal", "Vegetables", "Tamarind", "Sambar Powder", "Mustard Seeds"];
    } else {
        return ["Fresh Ingredients", "Spices", "Herbs", "Organic Produce"];
    }
}

/**
 * Generate nutrition info based on category and health tags
 */
function generateNutrition($category, $healthTags) {
    $healthTags = strtolower($healthTags ?? "");
    
    // Default values
    $nutrition = [
        "calories" => 200,
        "protein" => 10,
        "carbs" => 25,
        "fat" => 8,
        "fiber" => 4
    ];
    
    // Adjust based on health tags
    if (strpos($healthTags, "high protein") !== false) {
        $nutrition["protein"] = rand(18, 25);
        $nutrition["calories"] = rand(220, 280);
    }
    
    if (strpos($healthTags, "low fat") !== false) {
        $nutrition["fat"] = rand(3, 6);
        $nutrition["calories"] = rand(150, 200);
    }
    
    if (strpos($healthTags, "low salt") !== false || strpos($healthTags, "diabetic") !== false) {
        $nutrition["carbs"] = rand(12, 20);
    }
    
    if (strpos($healthTags, "high fiber") !== false) {
        $nutrition["fiber"] = rand(6, 10);
    }
    
    // Random variation
    $nutrition["protein"] = $nutrition["protein"] + rand(-2, 2);
    $nutrition["carbs"] = $nutrition["carbs"] + rand(-3, 3);
    $nutrition["fat"] = max(2, $nutrition["fat"] + rand(-2, 2));
    $nutrition["fiber"] = max(2, $nutrition["fiber"] + rand(-1, 1));
    $nutrition["calories"] = $nutrition["calories"] + rand(-20, 20);
    
    return $nutrition;
}
