<?php
// Start session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Establish database connection
$db = mysqli_connect('localhost', 'root', '', 'easy_reserve');

// Check if the connection was successful
if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if user is logged in and their username is set in session
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Query user's ID from the database based on their username
    $query_user_id = "SELECT user_id FROM users WHERE username = '$username'";
    $result_user_id = mysqli_query($db, $query_user_id);

    if ($result_user_id && mysqli_num_rows($result_user_id) > 0) {
        $row_user_id = mysqli_fetch_assoc($result_user_id);
        $user_id = $row_user_id['user_id'];

        // Query user's role from the database based on their user_id
        $query_role = "SELECT role FROM users WHERE user_id = $user_id";
        $result_role = mysqli_query($db, $query_role);

        if ($result_role && mysqli_num_rows($result_role) > 0) {
            $row_role = mysqli_fetch_assoc($result_role);
            $user_role = $row_role['role'];

            // If user's role is "admin", set session variable
            if ($user_role === "lecturer") {
                $_SESSION['lecturer_role'] = true;
            }
        } else {
            // Log error message to a file
            error_log("Error querying database for user's role: " . mysqli_error($db), 3, "error.log");
        }
    } else {
        // Log error message to a file
        error_log("Error querying database for user's ID or username not found", 3, "error.log");
    }
} else {
    // Log error message to a file or handle the case where username is not set in session
    error_log("User not logged in or session username not set", 3, "error.log");
}

// Close database connection
mysqli_close($db);
?>
