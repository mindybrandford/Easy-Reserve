<?php

// Function to check if the user is logged in
function isLoggedIn() {
    return isset($_SESSION['username']);
}

// Function to check if the user is logged out
function isLoggedOut() {
    return !isset($_SESSION['username']);
}

// Function to get the logged-in user's name
function getUserName() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : '';
}

// Check if the user is a teacher
function isLecturer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'lecturer';
}

?>
