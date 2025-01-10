<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add a small delay for loading screen (2 seconds)
    sleep(2);

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "easy_reserve";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Prepare and bind parameters
    $sql = "SELECT user_id, username, fullname, password_hash, role, email FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);

    // Bind parameters
    $stmt->bind_param("s", $_POST['username']);

    // Execute query
    $stmt->execute();

    // Get result
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Valid username found, fetch the user data
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($_POST['password'], $user['password_hash'])) {
            // Password is correct
            $_SESSION['username'] = $_POST['username'];
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];

            header('Location: /try2/index.php');
            exit();
        } else {
            // Invalid password
            header('Location: /try2/login.php?error=invalid');
            exit();
        }
    } else {
        // Invalid username
        header('Location: /try2/login.php?error=invalid');
        exit();
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>
