<?php
/**
 * Database Setup Script
 */
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$host = "localhost";
$username = "root";
$password = "";
$database = "food-app";

try {
    $conn = new mysqli($host, $username, $password);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->query("CREATE DATABASE IF NOT EXISTS `$database`");
    $conn->select_db($database);
    
    $results = [];
    
    // Create customers table
    $sql = "CREATE TABLE IF NOT EXISTS customers (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, email VARCHAR(100) UNIQUE NOT NULL, phone VARCHAR(20), password VARCHAR(255) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)";
    $results['customers'] = $conn->query($sql) ? "OK" : $conn->error;
    
    // Create chefs table
    $sql = "CREATE TABLE IF NOT EXISTS chefs (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, email VARCHAR(100) UNIQUE NOT NULL, phone VARCHAR(20), password VARCHAR(255) NOT NULL, specialty VARCHAR(255), location VARCHAR(255), rating DECIMAL(3,2) DEFAULT 0.00, total_orders INT DEFAULT 0, is_verified TINYINT(1) DEFAULT 0, profile_image VARCHAR(255), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)";
    $results['chefs'] = $conn->query($sql) ? "OK" : $conn->error;
    
    // Create dishes table
    $sql = "CREATE TABLE IF NOT EXISTS dishes (id INT AUTO_INCREMENT PRIMARY KEY, chef_id INT NOT NULL, name VARCHAR(100) NOT NULL, description TEXT, price DECIMAL(10,2) NOT NULL, category VARCHAR(50), image_url VARCHAR(255), is_available TINYINT(1) DEFAULT 1, protein DECIMAL(5,2) DEFAULT 0, carbs DECIMAL(5,2) DEFAULT 0, fat DECIMAL(5,2) DEFAULT 0, fiber DECIMAL(5,2) DEFAULT 0, ingredients TEXT, rating DECIMAL(3,2) DEFAULT 0.00, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)";
    $results['dishes'] = $conn->query($sql) ? "OK" : $conn->error;
    
    // Drop old cart table and recreate with correct structure
    $conn->query("DROP TABLE IF EXISTS cart");
    $sql = "CREATE TABLE cart (id INT AUTO_INCREMENT PRIMARY KEY, customer_id INT NOT NULL, dish_id INT NOT NULL, quantity INT DEFAULT 1, portion VARCHAR(50) DEFAULT 'Regular', spice_level VARCHAR(50) DEFAULT 'Medium', oil_level VARCHAR(50) DEFAULT 'Medium', salt_level VARCHAR(50) DEFAULT 'Normal', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)";
    $results['cart'] = $conn->query($sql) ? "OK" : $conn->error;
    
    // Create orders table
    $sql = "CREATE TABLE IF NOT EXISTS orders (id INT AUTO_INCREMENT PRIMARY KEY, customer_id INT NOT NULL, chef_id INT NOT NULL, total_amount DECIMAL(10,2) NOT NULL, status VARCHAR(50) DEFAULT 'pending', delivery_address TEXT, special_instructions TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)";
    $results['orders'] = $conn->query($sql) ? "OK" : $conn->error;
    
    // Create order_items table
    $sql = "CREATE TABLE IF NOT EXISTS order_items (id INT AUTO_INCREMENT PRIMARY KEY, order_id INT NOT NULL, dish_id INT NOT NULL, quantity INT NOT NULL, price DECIMAL(10,2) NOT NULL, portion VARCHAR(50) DEFAULT 'Regular', spice_level VARCHAR(50) DEFAULT 'Medium', oil_level VARCHAR(50) DEFAULT 'Medium', salt_level VARCHAR(50) DEFAULT 'Normal')";
    $results['order_items'] = $conn->query($sql) ? "OK" : $conn->error;
    
    echo json_encode([
        "success" => true,
        "message" => "Database setup completed",
        "results" => $results
    ]);
    
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>
