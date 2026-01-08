<?php
/**
 * Get Nearby Chefs API - Location-based filtering
 * Returns chefs within specified radius (default 10km) of customer location
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
require_once "db.php";

// Get parameters
$customer_lat = isset($_GET['latitude']) ? (float)$_GET['latitude'] : null;
$customer_lng = isset($_GET['longitude']) ? (float)$_GET['longitude'] : null;
$radius = isset($_GET['radius']) ? (float)$_GET['radius'] : 10; // Default 10km
$category = isset($_GET['category']) ? $_GET['category'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : null;

// If no location provided, return all chefs (fallback)
if ($customer_lat === null || $customer_lng === null) {
    // Fallback: return chefs without distance filtering
    $sql = "
    SELECT
        c.id AS chef_id,
        c.name,
        c.latitude,
        c.longitude,
        cp.cuisine,
        cp.rating,
        cp.reviews,
        cp.distance,
        cp.image,
        cp.experience,
        cp.tags AS specialty
    FROM chefs c
    LEFT JOIN chef_profiles cp ON c.id = cp.chef_id
    WHERE 1=1
    ";
    
    if ($category) {
        $category = $conn->real_escape_string($category);
        $sql .= " AND (cp.tags LIKE '%$category%' OR cp.cuisine LIKE '%$category%')";
    }
    
    if ($search) {
        $search = $conn->real_escape_string($search);
        $sql .= " AND (c.name LIKE '%$search%' OR cp.cuisine LIKE '%$search%' OR cp.tags LIKE '%$search%')";
    }
    
    $sql .= " ORDER BY cp.rating DESC LIMIT 20";
    
} else {
    // Use Haversine formula for distance calculation
    // 6371 = Earth's radius in kilometers
    $sql = "
    SELECT
        c.id AS chef_id,
        c.name,
        c.latitude,
        c.longitude,
        cp.cuisine,
        cp.rating,
        cp.reviews,
        cp.image,
        cp.experience,
        cp.tags AS specialty,
        (6371 * acos(
            cos(radians($customer_lat)) * 
            cos(radians(c.latitude)) * 
            cos(radians(c.longitude) - radians($customer_lng)) + 
            sin(radians($customer_lat)) * 
            sin(radians(c.latitude))
        )) AS distance_km
    FROM chefs c
    LEFT JOIN chef_profiles cp ON c.id = cp.chef_id
    WHERE c.latitude IS NOT NULL 
      AND c.longitude IS NOT NULL
    ";
    
    if ($category) {
        $category = $conn->real_escape_string($category);
        $sql .= " AND (cp.tags LIKE '%$category%' OR cp.cuisine LIKE '%$category%')";
    }
    
    if ($search) {
        $search = $conn->real_escape_string($search);
        $sql .= " AND (c.name LIKE '%$search%' OR cp.cuisine LIKE '%$search%' OR cp.tags LIKE '%$search%')";
    }
    
    // Filter by radius and order by distance
    $sql .= " HAVING distance_km <= $radius";
    $sql .= " ORDER BY distance_km ASC LIMIT 20";
}

$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Database error",
        "error" => $conn->error
    ]);
    exit;
}

$chefs = [];

while ($row = $result->fetch_assoc()) {
    // Extract specialty from tags (first tag as specialty)
    $specialty = "";
    if (!empty($row["specialty"])) {
        $tags = explode(",", $row["specialty"]);
        $specialty = trim($tags[0]);
    }
    
    // Calculate price level based on experience
    $experience = (int)($row["experience"] ?? 0);
    $priceLevel = "₹";
    if ($experience >= 7) {
        $priceLevel = "₹₹₹";
    } else if ($experience >= 4) {
        $priceLevel = "₹₹";
    }
    
    // Format distance
    $distance = "0 km";
    if (isset($row["distance_km"])) {
        $dist = (float)$row["distance_km"];
        if ($dist < 1) {
            $distance = round($dist * 1000) . " m";
        } else {
            $distance = round($dist, 1) . " km";
        }
    } else if (!empty($row["distance"])) {
        $distance = $row["distance"];
    }
    
    $chefs[] = [
        "chef_id" => (int)$row["chef_id"],
        "name" => $row["name"],
        "cuisine" => $row["cuisine"] ?? "",
        "specialty" => $specialty,
        "rating" => $row["rating"] ? number_format((float)$row["rating"], 1) : "0.0",
        "reviews" => (int)($row["reviews"] ?? 0),
        "distance" => $distance,
        "distance_km" => isset($row["distance_km"]) ? round((float)$row["distance_km"], 2) : null,
        "image" => $row["image"] ?? "",
        "experience" => $experience,
        "price_level" => $priceLevel,
        "is_favorite" => false,
        "latitude" => $row["latitude"] ? (float)$row["latitude"] : null,
        "longitude" => $row["longitude"] ? (float)$row["longitude"] : null
    ];
}

$count = count($chefs);
$message = $count > 0 ? "$count chefs available near you" : "No chefs found within {$radius}km";

echo json_encode([
    "success" => true,
    "message" => $message,
    "count" => $count,
    "radius_km" => $radius,
    "customer_location" => [
        "latitude" => $customer_lat,
        "longitude" => $customer_lng
    ],
    "chefs" => $chefs
]);
?>
