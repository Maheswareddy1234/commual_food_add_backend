<?php
// Dish Image Upload API
// Handles multipart image upload for dish photos

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Response function
function respond($success, $message, $filename = null) {
    $response = ["success" => $success, "message" => $message];
    if ($filename) {
        $response["filename"] = $filename;
    }
    echo json_encode($response);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $errorCode = isset($_FILES['image']) ? $_FILES['image']['error'] : 'No file';
    respond(false, "No image uploaded or upload error: $errorCode");
}

$file = $_FILES['image'];

// Validate file type
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    respond(false, "Invalid file type: $mimeType. Allowed: jpg, png, gif, webp");
}

// Validate file size (max 5MB)
$maxSize = 5 * 1024 * 1024; // 5MB
if ($file['size'] > $maxSize) {
    respond(false, "File too large. Maximum size is 5MB");
}

// Create uploads directory if it doesn't exist
$uploadsDir = __DIR__ . '/uploads/';
if (!is_dir($uploadsDir)) {
    if (!mkdir($uploadsDir, 0755, true)) {
        respond(false, "Failed to create uploads directory");
    }
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
if (empty($extension)) {
    // Determine extension from mime type
    $extMap = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    ];
    $extension = $extMap[$mimeType] ?? 'jpg';
}

$filename = 'dish_' . time() . '_' . uniqid() . '.' . $extension;
$targetPath = $uploadsDir . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    // Construct full URL for the uploaded image
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $baseUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/uploads/";
    $fullUrl = $baseUrl . $filename;
    
    $response = [
        "success" => true,
        "message" => "Image uploaded successfully",
        "filename" => $filename,
        "url" => $fullUrl
    ];
    echo json_encode($response);
    exit;
} else {
    respond(false, "Failed to save image");
}
