<?php
/**
 * Get Chef's Own Profile API
 * GET: chef_id
 */

header("Content-Type: application/json");
require_once "db.php";

$chef_id = isset($_GET['chef_id']) ? (int)$_GET['chef_id'] : 0;

if ($chef_id == 0) {
    echo json_encode([
        "success" => false,
        "message" => "chef_id is required"
    ]);
    exit;
}

// Get chef data
$sql = "SELECT 
    c.id,
    c.name,
    c.email,
    c.phone,
    c.created_at,
    p.image,
    p.about
FROM chefs c
LEFT JOIN chef_profiles p ON c.id = p.chef_id
WHERE c.id = ?
LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $chef_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $chef = $result->fetch_assoc();
    
    // Get total orders count
    $ordersSql = "SELECT COUNT(*) as total FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN dishes d ON oi.dish_id = d.id
        WHERE d.chef_id = ?";
    $ordersStmt = $conn->prepare($ordersSql);
    $ordersStmt->bind_param("i", $chef_id);
    $ordersStmt->execute();
    $ordersResult = $ordersStmt->get_result();
    $totalOrders = 0;
    if ($ordersRow = $ordersResult->fetch_assoc()) {
        $totalOrders = (int)$ordersRow['total'];
    }
    
    // Calculate member since year
    $createdAt = $chef['created_at'] ?? date('Y-m-d');
    $memberSince = date('Y', strtotime($createdAt));
    
    // Get location from chef_profiles or home_chefs
    $location = "";
    $locationSql = "SELECT location FROM home_chefs WHERE email = ? LIMIT 1";
    $locStmt = $conn->prepare($locationSql);
    $locStmt->bind_param("s", $chef['email']);
    $locStmt->execute();
    $locResult = $locStmt->get_result();
    if ($locRow = $locResult->fetch_assoc()) {
        $location = $locRow['location'];
    }
    
    echo json_encode([
        "success" => true,
        "data" => [
            "id" => (int)$chef['id'],
            "name" => $chef['name'],
            "email" => $chef['email'],
            "phone" => $chef['phone'],
            "location" => $location ?: "Location not set",
            "image" => $chef['image'] ?? null,
            "member_since" => $memberSince,
            "total_orders" => $totalOrders,
            "rating" => 4.5,
            "reviews_count" => 0
        ]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Chef not found"
    ]);
}
?>
