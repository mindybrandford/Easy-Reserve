<?php
session_start();
include 'database.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    // Redirect unauthorized users
    header("Location: login.php");
    exit();
}

// Get user ID from the URL parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirect if user ID is not provided
    header("Location: admin_panel.php");
    exit();
}

$user_id = $_GET['id'];

// Fetch user details from the database
$sql_user = "SELECT * FROM users WHERE user_id='$user_id'";
$result_user = $conn->query($sql_user);

if ($result_user->num_rows == 0) {
    // Redirect if user ID is invalid
    header("Location: admin_panel.php");
    exit();
}

$user = $result_user->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['new_password'])) {
        $new_password = $_POST['new_password'];
        
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update the password in the database
        $sql = "UPDATE users SET password='$hashed_password' WHERE user_id='$user_id'";
        
        if ($conn->query($sql) === TRUE) {
            echo "Password updated successfully";
        } else {
            echo "Error updating password: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password for <?php echo $user['username']; ?></title>
</head>
<body>
    <h2>Change Password for <?php echo $user['username']; ?></h2>
    <form method="post">
        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password" required><br><br>
        <input type="submit" value="Change Password">
    </form>
</body>
</html>
