<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['id']);
}

// Get username of logged in user
function getUserName() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : '';
}

// Get user role
function getUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : '';
}
?>
