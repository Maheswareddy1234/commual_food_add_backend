<?php
header("Content-Type: application/json");
require_once "db.php";

/* Read JSON input */
$data = json_decode(file_get_contents("php://input"), true);

if ($data === null) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON input"
    ]);
    exit;
}

/* Validate required fields */
if (
    !isset($data["user_type"]) || !in_array($data["user_type"], ["customer", "chef"], true) ||
    !isset($data["user_id"]) || trim((string)$data["user_id"]) === ""
) {
    echo json_encode([
        "success" => false,
        "message" => "user_type (customer/chef) and user_id are required"
    ]);
    exit;
}

$user_type = $data["user_type"];
$user_id = (int) $data["user_id"];

/* Select query based on user type */
if ($user_type === "customer") {

    $sql = "SELECT 
                id,
                name,
                email,
                phone,
                created_at
            FROM customers
            WHERE id = '$user_id'
            LIMIT 1";

} else { // chef

    $sql = "SELECT 
                id,
                name,
                email,
                phone,
                created_at
            FROM chefs
            WHERE id = '$user_id'
            LIMIT 1";
}

$result = $conn->query($sql);

if ($result && $result->num_rows === 1) {
    echo json_encode([
        "success" => true,
        "data" => $result->fetch_assoc()
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Profile not found"
    ]);
}
?>
