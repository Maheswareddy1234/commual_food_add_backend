<?php
header("Content-Type: application/json");
require_once "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(["success"=>false,"message"=>"Email and password required"]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, name, password FROM chefs WHERE email=?"
);
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success"=>false,"message"=>"Invalid email"]);
    exit;
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    echo json_encode(["success"=>false,"message"=>"Invalid password"]);
    exit;
}

echo json_encode([
    "success" => true,
    "message" => "Login successful",
    "user_id" => $user['id'],
    "user_name" => $user['name']
]);
