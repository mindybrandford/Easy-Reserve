<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "easy_reserve";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Get email and new password from form submission
    $email = $_POST['email'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Check if new password and confirm password match
    if ($newPassword !== $confirmPassword) {
        echo "Passwords do not match";
        exit();
    }

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update the password in the database
    $sql = "UPDATE users SET password_hash = ? WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $hashedPassword, $email);
    $stmt->execute();

    // Redirect to a page indicating that the password has been reset successfully
    header('Location:login.php');
    exit();

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>
