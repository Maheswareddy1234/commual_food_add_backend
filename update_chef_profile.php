<?php
/**
 * Update Chef Profile API
 * Handles profile photo upload and profile data update
 */

// Catch ALL errors to prevent 500 with no output
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo json_encode([
        "success" => false,
        "message" => "PHP Error: $errstr in $errfile on line $errline"
    ]);
    exit;
});

set_exception_handler(function($e) {
    echo json_encode([
        "success" => false,
        "message" => "Exception: " . $e->getMessage()
    ]);
    exit;
});

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once "db.php";

// Check database connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "POST method required"]);
    exit;
}

$chef_id = isset($_POST['chef_id']) ? (int)$_POST['chef_id'] : 0;
$about = isset($_POST['about']) ? mysqli_real_escape_string($conn, $_POST['about']) : '';
$specialties = isset($_POST['specialties']) ? mysqli_real_escape_string($conn, $_POST['specialties']) : '';
$start_time = isset($_POST['start_time']) ? mysqli_real_escape_string($conn, $_POST['start_time']) : '';
$end_time = isset($_POST['end_time']) ? mysqli_real_escape_string($conn, $_POST['end_time']) : '';

if ($chef_id == 0) {
    echo json_encode(["success" => false, "message" => "chef_id is required"]);
    exit;
}

// Verify chef exists
$chefCheck = $conn->query("SELECT id FROM chefs WHERE id = $chef_id");
if (!$chefCheck || $chefCheck->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Chef not found with id: $chef_id"]);
    exit;
}

// Create chef_profiles table if not exists
$createTable = $conn->query("CREATE TABLE IF NOT EXISTS chef_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chef_id INT NOT NULL UNIQUE,
    about TEXT,
    specialties TEXT,
    availability_start VARCHAR(20),
    availability_end VARCHAR(20),
    image VARCHAR(255),
    cuisine VARCHAR(100) DEFAULT 'Home Chef',
    rating DECIMAL(2,1) DEFAULT 0,
    reviews INT DEFAULT 0,
    experience VARCHAR(50) DEFAULT '1+ years',
    orders INT DEFAULT 0,
    tags TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Add missing columns if they don't exist (for existing tables)
$conn->query("ALTER TABLE chef_profiles ADD COLUMN IF NOT EXISTS specialties TEXT");
$conn->query("ALTER TABLE chef_profiles ADD COLUMN IF NOT EXISTS availability_start VARCHAR(20)");
$conn->query("ALTER TABLE chef_profiles ADD COLUMN IF NOT EXISTS availability_end VARCHAR(20)");
$conn->query("ALTER TABLE chef_profiles ADD COLUMN IF NOT EXISTS tags TEXT");
$conn->query("ALTER TABLE chef_profiles ADD COLUMN IF NOT EXISTS about TEXT");;

// Handle image upload
$imagePath = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/chef_profiles/';
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($fileExt, $allowedExts)) {
        echo json_encode(["success" => false, "message" => "Invalid file type"]);
        exit;
    }
    
    $fileName = 'chef_' . $chef_id . '_' . time() . '.' . $fileExt;
    $targetPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        $imagePath = $targetPath;
    }
}

// Check if profile exists
$checkResult = $conn->query("SELECT id FROM chef_profiles WHERE chef_id = $chef_id");
$profileExists = $checkResult && $checkResult->num_rows > 0;

$success = false;

if ($profileExists) {
    // Update existing profile
    if ($imagePath) {
        $sql = "UPDATE chef_profiles SET about = '$about', specialties = '$specialties', availability_start = '$start_time', availability_end = '$end_time', image = '$imagePath', tags = '$specialties' WHERE chef_id = $chef_id";
    } else {
        $sql = "UPDATE chef_profiles SET about = '$about', specialties = '$specialties', availability_start = '$start_time', availability_end = '$end_time', tags = '$specialties' WHERE chef_id = $chef_id";
    }
    $success = $conn->query($sql);
} else {
    // Insert new profile
    if ($imagePath) {
        $sql = "INSERT INTO chef_profiles (chef_id, about, specialties, availability_start, availability_end, image, tags) VALUES ($chef_id, '$about', '$specialties', '$start_time', '$end_time', '$imagePath', '$specialties')";
    } else {
        $sql = "INSERT INTO chef_profiles (chef_id, about, specialties, availability_start, availability_end, tags) VALUES ($chef_id, '$about', '$specialties', '$start_time', '$end_time', '$specialties')";
    }
    $success = $conn->query($sql);
}

if ($success) {
    echo json_encode([
        "success" => true,
        "message" => "Profile updated successfully",
        "image" => $imagePath
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to update profile: " . $conn->error
    ]);
}

$conn->close();
?>
