<?php
header("Content-Type: application/json");
include "db.php";

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Validate required field
if (!isset($data["chef_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "chef_id is required"
    ]);
    exit;
}

$chef_id = $data["chef_id"];
$cuisine = $data["cuisine"] ?? "";
$rating = $data["rating"] ?? 0;
$reviews = $data["reviews"] ?? 0;
$distance = $data["distance"] ?? "";
$experience = $data["experience"] ?? 0;
$availability = $data["availability"] ?? "";
$orders = $data["orders"] ?? 0;
$about = $data["about"] ?? "";
$tags = $data["tags"] ?? "";
$image = $data["image"] ?? "";

// Check if profile already exists
$checkSql = "SELECT id FROM chef_profiles WHERE chef_id = $chef_id";
$checkResult = $conn->query($checkSql);

if ($checkResult->num_rows > 0) {

    // UPDATE profile
    $sql = "
    UPDATE chef_profiles SET
        cuisine='$cuisine',
        rating='$rating',
        reviews='$reviews',
        distance='$distance',
        experience='$experience',
        availability='$availability',
        orders='$orders',
        about='$about',
        tags='$tags',
        image='$image'
    WHERE chef_id=$chef_id
    ";

    $message = "Chef profile updated successfully";

} else {

    // INSERT profile
    $sql = "
    INSERT INTO chef_profiles
    (chef_id, cuisine, rating, reviews, distance, experience, availability, orders, about, tags, image)
    VALUES
    ('$chef_id','$cuisine','$rating','$reviews','$distance','$experience','$availability','$orders','$about','$tags','$image')
    ";

    $message = "Chef profile created successfully";
}

// Execute
if ($conn->query($sql)) {
    echo json_encode([
        "success" => true,
        "message" => $message
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Database error",
        "error" => $conn->error
    ]);
}
