<?php
session_start();

// Include database connection
include 'database.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to check if the user is logged in
function isLoggedIn()
{
    return isset($_SESSION['username']);
}

// Function to get the logged-in user's name
function getUserName()
{
    return isset($_SESSION['username']) ? $_SESSION['username'] : '';
}

// Array of table names
$tables = array("lft_rooms", "bus_rooms", "bed_rooms", "var_rooms", "dtem_rooms", "otw_rooms");

// Iterate through each table
foreach($tables as $table) {
    // Prepare and execute the SQL query to update records
    $update_sql = "UPDATE $table SET status = 1, user_id = NULL WHERE user_id IS NOT NULL";
    if ($conn->query($update_sql) === TRUE) {
        echo "Records updated successfully for table: $table<br>";
    } else {
        echo "Error updating records for table: $table - " . $conn->error . "<br>";
    }
}

// If no errors occurred during update, display success message
if (!$update_error) {
    echo "All records updated successfully<br>";
}

// Close the database connection
$conn->close();
?>
