<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
require_once "db.php";

// Get chef_id from query parameter
if (!isset($_GET["chef_id"]) || empty($_GET["chef_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "chef_id is required"
    ]);
    exit;
}

$chef_id = (int)$_GET["chef_id"];
$category = isset($_GET["category"]) ? trim($_GET["category"]) : null;

// Build query with optional category filter
$sql = "SELECT 
            d.id,
            d.dish_name,
            d.description,
            d.price,
            d.preparation_time,
            d.category,
            d.food_type,
            d.health_tags,
            d.image,
            d.is_available,
            d.created_at
        FROM dishes d
        WHERE d.chef_id = ?";

$params = [$chef_id];
$types = "i";

// Add category filter if provided
if ($category && $category !== "All") {
    $sql .= " AND d.category = ?";
    $params[] = $category;
    $types .= "s";
}

$sql .= " ORDER BY d.is_available DESC, d.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$dishes = [];
while ($row = $result->fetch_assoc()) {
    $dishes[] = [
        "id" => (int)$row["id"],
        "dish_name" => $row["dish_name"],
        "description" => $row["description"],
        "price" => (float)$row["price"],
        "preparation_time" => (int)$row["preparation_time"],
        "category" => $row["category"],
        "food_type" => $row["food_type"],
        "health_tags" => $row["health_tags"],
        "image" => $row["image"],
        "is_available" => (bool)(int)$row["is_available"]
    ];
}

// Get statistics
$stats_sql = "SELECT 
    COUNT(*) as total_items,
    SUM(CASE WHEN is_available = 1 THEN 1 ELSE 0 END) as available,
    ROUND(AVG(price), 0) as avg_price,
    COUNT(DISTINCT category) as categories
FROM dishes 
WHERE chef_id = ?";

$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("i", $chef_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

// Get unique categories for filter tabs
$cat_sql = "SELECT DISTINCT category FROM dishes WHERE chef_id = ? ORDER BY category";
$cat_stmt = $conn->prepare($cat_sql);
$cat_stmt->bind_param("i", $chef_id);
$cat_stmt->execute();
$cat_result = $cat_stmt->get_result();

$categories = ["All"];
while ($cat = $cat_result->fetch_assoc()) {
    if (!empty($cat["category"])) {
        $categories[] = $cat["category"];
    }
}

echo json_encode([
    "success" => true,
    "dishes" => $dishes,
    "stats" => [
        "total_items" => (int)($stats["total_items"] ?? 0),
        "available" => (int)($stats["available"] ?? 0),
        "avg_price" => (int)($stats["avg_price"] ?? 0),
        "categories" => (int)($stats["categories"] ?? 0)
    ],
    "filter_categories" => $categories
]);

$stmt->close();
$stats_stmt->close();
$cat_stmt->close();
$conn->close();
?>
