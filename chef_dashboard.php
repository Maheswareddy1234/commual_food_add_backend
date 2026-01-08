<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

$conn = new mysqli("localhost", "root", "", "food-app");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB Connection failed"]);
    exit;
}

// Get chef_id from query parameter
if (!isset($_GET["chef_id"]) || empty($_GET["chef_id"])) {
    echo json_encode(["success" => false, "message" => "chef_id is required"]);
    exit;
}

$chef_id = (int)$_GET["chef_id"];

// Get chef name
$chef_result = $conn->query("SELECT name FROM chefs WHERE id = $chef_id");
if ($chef_result && $chef_result->num_rows > 0) {
    $chef_name = $chef_result->fetch_assoc()["name"];
} else {
    $chef_name = "Chef";
}

// Get today's date
$today = date('Y-m-d');

// Today's earnings (from orders linked via order_items to dishes owned by this chef)
$today_earnings = 0;
$earnings_sql = "SELECT COALESCE(SUM(o.total), 0) as earnings 
                 FROM orders o 
                 INNER JOIN order_items oi ON o.id = oi.order_id
                 INNER JOIN dishes d ON oi.dish_id = d.id
                 WHERE d.chef_id = $chef_id 
                 AND DATE(o.created_at) = '$today'
                 AND o.status = 'delivered'";
$result = $conn->query($earnings_sql);
if ($result) {
    $today_earnings = (int)$result->fetch_assoc()['earnings'];
}

// Today's orders count
$orders_today = 0;
$orders_sql = "SELECT COUNT(DISTINCT o.id) as count 
               FROM orders o 
               INNER JOIN order_items oi ON o.id = oi.order_id
               INNER JOIN dishes d ON oi.dish_id = d.id
               WHERE d.chef_id = $chef_id 
               AND DATE(o.created_at) = '$today'";
$result = $conn->query($orders_sql);
if ($result) {
    $orders_today = (int)$result->fetch_assoc()['count'];
}

// Weekly revenue
$weekly_revenue = 0;
$week_start = date('Y-m-d', strtotime('monday this week'));
$week_end = date('Y-m-d', strtotime('sunday this week'));
$weekly_sql = "SELECT COALESCE(SUM(o.total), 0) as revenue 
               FROM orders o 
               INNER JOIN order_items oi ON o.id = oi.order_id
               INNER JOIN dishes d ON oi.dish_id = d.id
               WHERE d.chef_id = $chef_id 
               AND DATE(o.created_at) BETWEEN '$week_start' AND '$week_end'
               AND o.status = 'delivered'";
$result = $conn->query($weekly_sql);
if ($result) {
    $weekly_revenue = (int)$result->fetch_assoc()['revenue'];
}

// Active menu items
$menu_items = 0;
$menu_sql = "SELECT COUNT(*) as count FROM dishes WHERE chef_id = $chef_id AND is_available = 1";
$result = $conn->query($menu_sql);
if ($result) {
    $menu_items = (int)$result->fetch_assoc()['count'];
}

// Rating (average from reviews or default)
$rating = 4.5;
$rating_sql = "SELECT AVG(rating) as avg_rating FROM chef_reviews WHERE chef_id = $chef_id";
$result = $conn->query($rating_sql);
if ($result && $row = $result->fetch_assoc()) {
    if ($row['avg_rating'] !== null) {
        $rating = round((float)$row['avg_rating'], 1);
    }
}

// Recent orders
$recent_orders = [];
$recent_sql = "SELECT DISTINCT o.id, o.order_number, o.total, o.status, o.created_at, o.delivery_date, o.delivery_time,
               c.name as customer_name
               FROM orders o 
               INNER JOIN order_items oi ON o.id = oi.order_id
               INNER JOIN dishes d ON oi.dish_id = d.id
               LEFT JOIN customers c ON o.customer_id = c.id
               WHERE d.chef_id = $chef_id 
               ORDER BY o.created_at DESC
               LIMIT 5";
$result = $conn->query($recent_sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Get items for this order
        $order_id = $row['id'];
        $items_sql = "SELECT dish_name, quantity FROM order_items WHERE order_id = $order_id";
        $items_result = $conn->query($items_sql);
        $items_arr = [];
        if ($items_result) {
            while ($item = $items_result->fetch_assoc()) {
                $items_arr[] = $item['dish_name'] . " x" . $item['quantity'];
            }
        }
        
        $recent_orders[] = [
            "id" => (int)$row['id'],
            "customer_name" => $row['customer_name'] ?? "Customer",
            "items" => implode(", ", $items_arr),
            "total_amount" => (int)$row['total'],
            "status" => strtoupper($row['status']),
            "order_time" => date('h:i A', strtotime($row['created_at'])),
            "order_date" => date('Y-m-d', strtotime($row['created_at']))
        ];
    }
}

echo json_encode([
    "success" => true,
    "chef_name" => $chef_name,
    "today_earnings" => $today_earnings,
    "orders_today" => $orders_today,
    "orders_diff" => "+0 from yesterday",
    "weekly_revenue" => $weekly_revenue,
    "revenue_diff" => "+0% this week",
    "menu_items" => $menu_items,
    "out_of_stock" => 0,
    "rating" => $rating,
    "total_reviews" => 0,
    "recent_orders" => $recent_orders
]);

$conn->close();
?>
