<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "easy_reserve";

// Create connection with error handling
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to ensure proper handling of special characters
    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception("Error setting charset: " . $conn->error);
    }
    
} catch (Exception $e) {
    error_log("Database Error: " . $e->getMessage());
    die("Database connection error. Please try again later.");
}
?>