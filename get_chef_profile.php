<?php
header("Content-Type: application/json");
include "db.php";

// Get chef_id from URL
$chef_id = isset($_GET['chef_id']) ? (int)$_GET['chef_id'] : 0;

if ($chef_id == 0) {
    echo json_encode([
        "success" => false,
        "message" => "chef_id is required"
    ]);
    exit;
}

// First try to get from chef_profiles (if exists)
$hasProfile = false;
$profileData = null;

$checkProfile = $conn->query("SELECT * FROM chef_profiles WHERE chef_id = $chef_id LIMIT 1");
if ($checkProfile && $checkProfile->num_rows > 0) {
    $profileData = $checkProfile->fetch_assoc();
    $hasProfile = true;
}

// Get basic chef data
$sql = "SELECT * FROM chefs WHERE id = $chef_id LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $chef = $result->fetch_assoc();
    
    // Build response - use profile data if available, otherwise use defaults
    $response = [
        "success" => true,
        "data" => [
            "id" => (int)$chef["id"],
            "name" => $chef["name"],
            "cuisine" => $hasProfile && isset($profileData["cuisine"]) 
                ? $profileData["cuisine"] 
                : "Home Chef",
            "rating" => $hasProfile && isset($profileData["rating"]) 
                ? (float)$profileData["rating"] 
                : 4.5,
            "reviews" => $hasProfile && isset($profileData["reviews"]) 
                ? (int)$profileData["reviews"] 
                : 0,
            "distance" => $hasProfile && isset($profileData["distance"]) 
                ? $profileData["distance"] 
                : "Near you",
            "experience" => $hasProfile && isset($profileData["experience"]) 
                ? (int)$profileData["experience"] 
                : 2,
            "availability" => $hasProfile && isset($profileData["availability"]) 
                ? $profileData["availability"] 
                : "Mon-Sat",
            "orders" => $hasProfile && isset($profileData["orders"]) 
                ? (int)$profileData["orders"] 
                : 0,
            "about" => $hasProfile && isset($profileData["about"]) 
                ? $profileData["about"] 
                : "Passionate home chef preparing delicious and healthy meals with love.",
            "tags" => $hasProfile && isset($profileData["tags"]) && !empty($profileData["tags"])
                ? explode(",", $profileData["tags"]) 
                : ["Home Cooked", "Fresh", "Healthy"],
            "image" => $hasProfile && isset($profileData["image"]) 
                ? $profileData["image"] 
                : (isset($chef["profile_image"]) ? $chef["profile_image"] : "chef_default.jpg")
        ]
    ];
    
    echo json_encode($response);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Chef not found"
    ]);
}
?>
