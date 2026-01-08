<?php
// Suppress all errors from being displayed (they break JSON responses)
error_reporting(0);
ini_set('display_errors', 0);

$host = "localhost";
$username = "root";
$password = "";
$database = "food-app";

// Create connection with error handling
$conn = @new mysqli($host, $username, $password, $database);

// Don't die here, let the calling script handle the error
// This prevents HTML error output that breaks JSON parsing
?>
