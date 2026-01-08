<?php
header("Content-Type: application/json");
require_once "db.php";

$chef_id = 1; // later from login/session

$sql = "
SELECT
    (SELECT SUM(total_amount) FROM orders 
     WHERE chef_id=? AND status='DELIVERED' AND DATE(created_at)=CURDATE()) AS today_earnings,

    (SELECT COUNT(*) FROM orders 
     WHERE chef_id=? AND DATE(created_at)=CURDATE()) AS orders_today,

    (SELECT SUM(total_amount) FROM orders 
     WHERE chef_id=? AND status='DELIVERED'
     AND YEARWEEK(created_at)=YEARWEEK(CURDATE())) AS weekly_revenue,

    (SELECT COUNT(*) FROM menu_items 
     WHERE chef_id=? AND is_active=1) AS active_menu_items,

    (SELECT ROUND(AVG(rating),1) FROM chef_reviews 
     WHERE chef_id=?) AS rating
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiii",$chef_id,$chef_id,$chef_id,$chef_id,$chef_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

$orders = $conn->query("
    SELECT customer_name,items,total_amount,status,created_at
    FROM orders
    WHERE chef_id=$chef_id
    ORDER BY created_at DESC
    LIMIT 3
")->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    "dashboard"=>$data,
    "recent_orders"=>$orders
]);
