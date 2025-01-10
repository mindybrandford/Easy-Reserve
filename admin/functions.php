<?php
require_once __DIR__ . '/../database.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Function to get all users
function getAllUsers() {
    global $conn;
    $sql = "SELECT * FROM users";
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        return false;
    }
    
    $users = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    
    return $users;
}

// Function to delete a user
function deleteUser($userId) {
    global $conn;
    $userId = mysqli_real_escape_string($conn, $userId);
    
    $sql = "DELETE FROM users WHERE id = '$userId'";
    return mysqli_query($conn, $sql);
}

// Function to update a user
function updateUser($userId, $username, $email, $role) {
    global $conn;
    
    $userId = mysqli_real_escape_string($conn, $userId);
    $username = mysqli_real_escape_string($conn, $username);
    $email = mysqli_real_escape_string($conn, $email);
    $role = mysqli_real_escape_string($conn, $role);
    
    $sql = "UPDATE users SET username = '$username', email = '$email', role = '$role' WHERE id = '$userId'";
    return mysqli_query($conn, $sql);
}
?>
