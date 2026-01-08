<?php
header("Content-Type: application/json");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "db.php";


// ===== READ MULTIPART DATA =====
$full_name = $_POST['full_name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$location = $_POST['location'] ?? '';
$kitchen_address = $_POST['kitchen_address'] ?? '';
$cuisines = $_POST['cuisines'] ?? '';
$experience = $_POST['experience'] ?? '';
$latitude = isset($_POST['latitude']) ? (float)$_POST['latitude'] : null;
$longitude = isset($_POST['longitude']) ? (float)$_POST['longitude'] : null;

// ===== VALIDATION =====
if (
    empty($full_name) || empty($email) || empty($phone)
) {
    echo json_encode([
        "success" => false,
        "message" => "Name, email and phone are required"
    ]);
    exit;
}

// Validate location
if ($latitude === null || $longitude === null || $latitude == 0 || $longitude == 0) {
    echo json_encode([
        "success" => false,
        "message" => "Kitchen location is required. Please select your location on the map."
    ]);
    exit;
}

// ===== UPLOAD DIR =====
$uploadDir = __DIR__ . "/uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ===== ID PROOF =====
$id_proof_path = "";
if (isset($_FILES['id_proof'])) {
    $idName = time() . "_id_" . basename($_FILES['id_proof']['name']);
    move_uploaded_file($_FILES['id_proof']['tmp_name'], $uploadDir . $idName);
    $id_proof_path = $idName;
}

// ===== KITCHEN IMAGE =====
$kitchen_image_path = "";
if (isset($_FILES['kitchen_image'])) {
    $kitchenName = time() . "_kitchen_" . basename($_FILES['kitchen_image']['name']);
    move_uploaded_file($_FILES['kitchen_image']['tmp_name'], $uploadDir . $kitchenName);
    $kitchen_image_path = $kitchenName;
}

// ===== INSERT INTO home_chefs =====
$sql = "INSERT INTO home_chefs
(full_name, email, phone, location, kitchen_address, cuisines, experience, id_proof, kitchen_image, latitude, longitude)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssssssissdd",
    $full_name,
    $email,
    $phone,
    $location,
    $kitchen_address,
    $cuisines,
    $experience,
    $id_proof_path,
    $kitchen_image_path,
    $latitude,
    $longitude
);

if ($stmt->execute()) {
    $home_chef_id = $conn->insert_id;
    
    // Also update the chefs table if there's a matching chef by email
    $updateChef = $conn->prepare("UPDATE chefs SET latitude = ?, longitude = ? WHERE email = ?");
    $updateChef->bind_param("dds", $latitude, $longitude, $email);
    $updateChef->execute();
    
    echo json_encode([
        "success" => true,
        "message" => "Application submitted successfully! Your kitchen location has been saved.",
        "home_chef_id" => $home_chef_id,
        "location" => [
            "latitude" => $latitude,
            "longitude" => $longitude,
            "address" => $kitchen_address
        ]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $conn->error
    ]);
}
