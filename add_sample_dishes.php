<?php
header("Content-Type: application/json");
require_once "db.php";

/**
 * Helper script to add sample dishes for testing
 * Usage: Call this with ?chef_id=YOUR_CHEF_ID
 * Example: http://localhost/Communal-food-app/add_sample_dishes.php?chef_id=1
 */

// Get chef_id from query parameter
$chef_id = isset($_GET['chef_id']) ? (int)$_GET['chef_id'] : 0;

if ($chef_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Please provide chef_id as query parameter. Example: ?chef_id=1"
    ]);
    exit;
}

// Check if chef exists
$check = $conn->prepare("SELECT id, name FROM chefs WHERE id = ?");
$check->bind_param("i", $chef_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Chef with id $chef_id not found"
    ]);
    exit;
}

$chef = $result->fetch_assoc();

// Sample dishes matching the design
$dishes = [
    [
        "dish_name" => "Palak Paneer",
        "description" => "Fresh spinach cooked with cottage cheese",
        "price" => 180,
        "preparation_time" => 25,
        "category" => "North Indian",
        "food_type" => "Veg",
        "health_tags" => "High Protein,Gluten Free",
        "is_available" => 1
    ],
    [
        "dish_name" => "Dal Tadka",
        "description" => "Yellow lentils tempered with spices",
        "price" => 140,
        "preparation_time" => 30,
        "category" => "North Indian",
        "food_type" => "Veg",
        "health_tags" => "High Protein,Heart Healthy",
        "is_available" => 1
    ],
    [
        "dish_name" => "Chicken Curry",
        "description" => "Tender chicken in rich gravy",
        "price" => 220,
        "preparation_time" => 40,
        "category" => "North Indian",
        "food_type" => "Non-Veg",
        "health_tags" => "High Protein",
        "is_available" => 0  // Unavailable as shown in design
    ],
    [
        "dish_name" => "Mix Veg",
        "description" => "Seasonal vegetables cooked in spices",
        "price" => 160,
        "preparation_time" => 20,
        "category" => "North Indian",
        "food_type" => "Veg",
        "health_tags" => "Heart Healthy,Diabetic Friendly",
        "is_available" => 1
    ]
];

$inserted = 0;
$errors = [];

foreach ($dishes as $dish) {
    // Check if dish already exists for this chef
    $checkDish = $conn->prepare("SELECT id FROM dishes WHERE chef_id = ? AND dish_name = ?");
    $checkDish->bind_param("is", $chef_id, $dish['dish_name']);
    $checkDish->execute();
    $checkDish->store_result();
    
    if ($checkDish->num_rows > 0) {
        $errors[] = "{$dish['dish_name']} already exists";
        continue;
    }
    
    $stmt = $conn->prepare(
        "INSERT INTO dishes (chef_id, dish_name, description, price, preparation_time, category, food_type, health_tags, is_available) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "issdiissi",
        $chef_id,
        $dish['dish_name'],
        $dish['description'],
        $dish['price'],
        $dish['preparation_time'],
        $dish['category'],
        $dish['food_type'],
        $dish['health_tags'],
        $dish['is_available']
    );
    
    if ($stmt->execute()) {
        $inserted++;
    } else {
        $errors[] = "Failed to insert {$dish['dish_name']}: " . $stmt->error;
    }
}

echo json_encode([
    "success" => true,
    "message" => "Added $inserted dishes for chef '{$chef['name']}' (id: $chef_id)",
    "dishes_added" => $inserted,
    "skipped" => $errors
]);

$conn->close();
?>
