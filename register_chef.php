<?php
header("Content-Type: application/json");
require_once "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$phone = $data['phone'] ?? '';
$password = $data['password'] ?? '';
$confirm_password = $data['confirm_password'] ?? '';
$latitude = isset($data['latitude']) ? (float)$data['latitude'] : null;
$longitude = isset($data['longitude']) ? (float)$data['longitude'] : null;

if (
    empty($name) || empty($email) || empty($phone) ||
    empty($password) || empty($confirm_password)
) {
    echo json_encode(["success"=>false,"message"=>"All fields required"]);
    exit;
}

if ($password !== $confirm_password) {
    echo json_encode(["success"=>false,"message"=>"Passwords do not match"]);
    exit;
}

$check = $conn->prepare("SELECT id FROM chefs WHERE email=?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(["success"=>false,"message"=>"Email already registered"]);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

// Insert with optional location
if ($latitude !== null && $longitude !== null) {
    $stmt = $conn->prepare(
        "INSERT INTO chefs (name,email,phone,password,latitude,longitude) VALUES (?,?,?,?,?,?)"
    );
    $stmt->bind_param("ssssdd", $name, $email, $phone, $hash, $latitude, $longitude);
} else {
    $stmt = $conn->prepare(
        "INSERT INTO chefs (name,email,phone,password) VALUES (?,?,?,?)"
    );
    $stmt->bind_param("ssss", $name, $email, $phone, $hash);
}

if ($stmt->execute()) {
    $chef_id = $conn->insert_id;
    echo json_encode([
        "success"=>true,
        "message"=>"Chef registered successfully",
        "chef_id"=>$chef_id,
        "has_location" => ($latitude !== null && $longitude !== null)
    ]);
} else {
    echo json_encode([
        "success"=>false,
        "message"=>"Registration failed: " . $conn->error
    ]);
}
?>
