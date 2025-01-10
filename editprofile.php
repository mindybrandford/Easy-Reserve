<?php
session_start();

// Include database connection
include_once 'database.php';

if ($conn->connect_error) {
    die("Connection failed: " . mysqli_connect_error());
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

// Retrieve current user's information from the database
if (isLoggedIn()) {
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $currentFullName = $row['fullname'];
        $currentUsername = $row['username'];
    } else {
        echo "Error retrieving user information";

    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["submit"])) {
        // Retrieve form data
        $newFullName = $_POST["fullname"];
        $newUsername = $_POST["username"];
        $newPassword = $_POST["password"];
        $confirmPassword = $_POST["confirm_password"];

        // Initialize variables for message handling
        $message = "";
        $success = false;
        $hasChanges = false;

        // Prepare SQL statement
        $sql = "UPDATE users SET ";
        $params = array();

        // Add fullname if changed
        if (!empty($newFullName)) {
            $sql .= "fullname = ?, ";
            $params[] = $newFullName;
            $hasChanges = true;
        }

        // Add username if changed
        if (!empty($newUsername)) {
            $sql .= "username = ?, ";
            $params[] = $newUsername;
            $hasChanges = true;
        }

        // Handle password change
        if (!empty($newPassword) || !empty($confirmPassword)) {
            if ($newPassword !== $confirmPassword) {
                $message = "Passwords do not match!";
                $success = false;
            } elseif (!empty($newPassword)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $sql .= "password = ?, ";
                $params[] = $hashedPassword;
                $hasChanges = true;
            }
        }

        // Only proceed with update if there are changes and no password mismatch
        if ($hasChanges && empty($message)) {
            // Remove trailing comma and space
            $sql = rtrim($sql, ", ");

            // Add WHERE clause
            $sql .= " WHERE username = ?";
            $params[] = $_SESSION['username'];

            // Prepare and execute statement
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param(str_repeat("s", count($params)), ...$params);
                if ($stmt->execute()) {
                    // Update session if username changed
                    if (!empty($newUsername)) {
                        $_SESSION['username'] = $newUsername;
                    }
                    $message = "Profile updated successfully!";
                    $success = true;
                } else {
                    $message = "Error updating profile: " . $conn->error;
                    $success = false;
                }
            } else {
                $message = "Error preparing statement: " . $conn->error;
                $success = false;
            }
        } elseif (!$hasChanges) {
            $message = "No changes made.";
            $success = true;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<?php include 'header.php'; ?>
<style>
    * {
        font-family: 'Montserrat', 'Roboto', sans-serif;
    }

    body {
        background: #f5f5f5;
        min-height: 100vh;
        padding-top: 100px;
    }

    .profile-container {
        width`: 100%;
        margin: 100px auto;
        padding: 30px;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        position: relative;
        z-index: 1;
    }

    .logo-container {
        text-align: center;
        margin-bottom: 20px;
    }

    .logo-container img {
        max-width: 150px;
        height: auto;
        margin-bottom: 15px;
    }

    .profile-container h1 {
        color: #198754;
        text-align: center;
        margin-bottom: 30px;
        font-size: 2.2em;
        position: relative;
    }

    .profile-form {
        display: flex;
        flex-direction: column;
        gap: 25px;
        width: 100%;
    }

    .form-group {
        position: relative;
        margin-bottom: 20px;
        width: 100%;
    }

    .profile-form label {
        color: #0e463f;
        font-weight: 600;
        font-size: 1.1em;
        margin-bottom: 8px;
        display: block;
        transition: color 0.3s ease;
    }

    .profile-form input {
        width: 100%;
        padding: 12px 20px;
        border: 2px solid #e1bee7;
        border-radius: 15px;
        font-size: 1em;
        transition: all 0.3s ease;
        background: white;
        display: block;
        margin-top: 5px;
    }

    .profile-form input:focus {
        outline: none;
        border-color: #0e463f;
        box-shadow: 0 0 15px rgba(14, 70, 63, 0.2);
    }

    .profile-form button {
        background-color: #0e463f;
        color: white;
        padding: 12px 25px;
        border: none;
        border-radius: 15px;
        cursor: pointer;
        font-size: 16px;
        transition: background-color 0.3s ease;
    }

    .profile-form button:hover {
        background-color: #0a332e;
    }

    .home {
        text-align: center;
        margin-top: 30px;
    }

    .home a {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: #333;
        text-decoration: none;
        padding: 12px 25px;
        border-radius: 15px;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.8);
    }

    .home a:hover {
        background: #f5f5f5;
        transform: scale(1.05);
    }

    .home i {
        font-size: 1.2em;
        margin-right: 5px;
    }

    .alert {
        padding: 15px;
        margin-bottom: 25px;
        border-radius: 15px;
        text-align: center;
        animation: slideIn 0.5s ease;
    }

    @keyframes slideIn {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .alert-success {
        background: linear-gradient(45deg, #a5d6a7, #81c784);
        color: #1b5e20;
        border: none;
    }

    .alert-error {
        background: linear-gradient(45deg, #ef9a9a, #e57373);
        color: #b71c1c;
        border: none;
    }

    .loading {
        display: none;
        text-align: center;
        margin: 20px 0;
    }

    .loading span {
        display: inline-block;
        width: 10px;
        height: 10px;
        margin: 0 3px;
        background: #4CAF50;
        border-radius: 50%;
        animation: bounce 0.6s infinite alternate;
    }

    .loading span:nth-child(2) { animation-delay: 0.2s; }
    .loading span:nth-child(3) { animation-delay: 0.4s; }

    @keyframes bounce {
        to { transform: translateY(-10px); }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.profile-form');
    const loading = document.createElement('div');
    loading.className = 'loading';
    loading.innerHTML = '<span></span><span></span><span></span>';
    form.appendChild(loading);

    // Show loading animation on form submit
    form.addEventListener('submit', function(e) {
        const button = this.querySelector('button');
        button.style.display = 'none';
        loading.style.display = 'block';
    });
});
</script>

<div class="profile-container">
    <div class="logo-container">
        <img src="https://elearn.salcc.edu.lc/pluginfile.php/1/theme_academi/logo/1729725012/salcc_black.png" alt="SALCC Logo">
    </div>
    <h1>Edit Profile</h1>
    
    <?php if (isset($message)): ?>
        <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form class="profile-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="form-group">
            <label for="fullname">✏️ Your Name</label>
            <input type="text" id="fullname" name="fullname" placeholder="What should we call you?" 
                value="<?php echo isset($currentFullName) ? htmlspecialchars($currentFullName) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="username">👤 Username</label>
            <input type="text" id="username" name="username" placeholder="Pick a cool username!" 
                value="<?php echo isset($currentUsername) ? htmlspecialchars($currentUsername) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="password">🔐 New Password</label>
            <input type="password" id="password" name="password" placeholder="Enter new password">
        </div>

        <div class="form-group">
            <label for="confirm_password">🔐 Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your new password">
        </div>

        <button type="submit" name="submit">✨ Save Changes ✨</button>
    </form>

    <div class="home">
        <a href="/try2/index.php">
            <i class="fas fa-home"></i>
            Home
        </a>
    </div>
</div>

<?php include 'footer.php'; ?>
</html>