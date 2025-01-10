<?php
session_start();

// Include database connection
include_once 'database.php';

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

// Check if search query is submitted
if (isset($_GET['search_query'])) {
    $search_query = $_GET['search_query'];
    // Adjust the SQL query based on your database schema
    $sql = "SELECT * FROM rooms WHERE room_name LIKE '%$search_query%'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Display filtered results
        echo "<div class='box-container'>";
        while ($row = $result->fetch_assoc()) {
            echo "<div class='box'>";
            echo "<img decoding='async' src='" . $row['image'] . "' alt=''>";
            echo "<div class='content'>";
            echo "<h3><i class='fas fa-map-marker-alt'></i> " . $row['room_name'] . "</h3>";
            echo "<p>" . $row['description'] . "</p>";
            echo "<a href='#' class='btn'>SEE MORE</a>";
            echo "</div>";
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "No results found.";
    }
}
?>
