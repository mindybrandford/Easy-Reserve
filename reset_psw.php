<?php
session_start();
include('database.php');

if (isset($_POST["reset"])) {
    $psw = $_POST["password"];
    $confirm_psw = $_POST["confirm_password"];
    $email = $_POST["email"];

    // Basic password validation
    if ($psw !== $confirm_psw) {
        echo "<script>alert('Passwords do not match.');</script>";
        exit();
    }

    $hash = password_hash($psw, PASSWORD_DEFAULT);
    $sql = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    $query = mysqli_num_rows($sql);

    if ($query > 0) {
        $row = mysqli_fetch_assoc($sql);
        $username = $row['username']; // Get username for email
        mysqli_query($conn, "UPDATE users SET password_hash='$hash' WHERE email='$email'");

        // Send confirmation email
        require "Mail/phpmailer/PHPMailerAutoload.php";
        $mail = new PHPMailer;

        // SMTP settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';

        $mail->Username = 'salcceasyreserve@gmail.com';
        $mail->Password = 'gimp abqn slnj vjhi';
        
        $mail->setFrom('salcceasyreserve@gmail.com', 'SALCC Easy Reserve');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = "Password Reset Successful";

        // Compose email content
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
            <!-- Header with SALCC Logo -->
            <div style='text-align: center; margin-bottom: 20px; padding: 20px; background-color: #f8f9fa; border-radius: 5px;'>
                <img src='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ4gsx6i7zEklHgqbY68CkEVwPrRVb5WP-Mgw&s' alt='SALCC Logo' style='max-width: 200px; height: auto;'>
            </div>

            <!-- Password Reset Confirmation Title -->
            <div style='text-align: center; margin-bottom: 30px;'>
                <h2 style='color: #0e463f; margin: 0;'>Password Reset Successful</h2>
                <p style='color: #666; margin-top: 5px;'>Your password has been successfully updated</p>
            </div>

            <!-- Greeting -->
            <p style='color: #333; font-size: 16px;'>Dear " . htmlspecialchars($username) . ",</p>

            <!-- Message Content -->
            <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                <p style='color: #333; line-height: 1.6;'>
                    This email confirms that your password has been successfully reset. You can now log in to your account using your new password.
                </p>
            </div>

            <!-- Security Notice -->
            <div style='margin: 20px 0; padding: 15px; border-left: 4px solid #0e463f; background-color: #f8f9fa;'>
                <p style='color: #666; margin: 0;'>
                    If you did not request this password reset, please contact our support team immediately.
                </p>
            </div>

            <!-- Login Button -->
            <div style='text-align: center; margin: 30px 0;'>
                <a href='http://localhost/try2/login.php' style='background-color: #0e463f; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>
                    Login to Your Account
                </a>
            </div>

            <!-- Footer -->
            <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center;'>
                <p style='color: #666; margin-bottom: 5px;'>Thank you for using SALCC Easy Reserve!</p>
                <p style='color: #999; font-size: 12px;'>This is an automated message, please do not reply.</p>
                <div style='margin-top: 15px;'>
                    <p style='color: #0e463f; margin: 0;'>SALCC Easy Reserve</p>
                    <small style='color: #999;'>Sir Arthur Lewis Community College</small>
                </div>
            </div>
        </div>";

        // Plain text version of the email
        $mail->AltBody = "Dear " . $username . ",

Your password has been successfully reset. You can now log in to your account using your new password.

If you did not request this password reset, please contact our support team immediately.

To login to your account, please visit: http://localhost/try2/login.php

Thank you for using SALCC Easy Reserve!

Best regards,
SALCC Easy Reserve Team
Sir Arthur Lewis Community College";

        // Send email and redirect
        if($mail->send()) {
?>
            <script>
                alert("Your password has been successfully reset. Please check your email for confirmation.");
                window.location.replace("/try2/login.php");
            </script>
<?php
        } else {
?>
            <script>
                alert("Password reset successful, but failed to send confirmation email.");
                window.location.replace("/try2/login.php");
            </script>
<?php
        }
    } else {
?>
        <script>
            alert("Please try again");
            window.location.replace("/try2/reset_psw.php");
        </script>
<?php
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />
    <link rel="stylesheet" href="css/style.css">
    <title>Reset Password - SALCC Easy Reserve</title>
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
            min-height: calc(100vh - 200px);
            margin-top: 120px;
        }

        .card {
            margin-top: 0;
            width: 450px;
            padding: 40px;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .logo-container img {
            width: 160px;
            margin-bottom: 20px;
        }

        .card-header {
            color: #333;
            margin-bottom: 30px;
            font-size: 24px;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 25px;
            text-align: left;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .password-container {
            position: relative;
            width: 100%;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }

        .form-control:focus {
            border-color: #0e463f;
            outline: none;
        }

        input[type="submit"] {
            background-color: #0e463f;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
            margin-top: 20px;
        }

        input[type="submit"]:hover {
            background-color: #0a322d;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="banner">
        <div class="banner-content">
            <div class="contact-info">
                <div>
                    <p><a href="tel:+1234567890"><i class="fa fa-phone"></i> Telephone: 123-456-7890</a></p>
                </div>
                <div>
                    <p><a href="mailto:salcceasyreserve@gmail.com"><i class="fa fa-envelope"></i> Email: salcceasyreserve@gmail.com</a></p>
                </div>
            </div>
        </div>
    </div>

    <header>
        <a href="/try2/index.php" class="logo">SALCC EASY RESERVE</a>
        <input type="checkbox" id="menu-bar">
        <label for="menu-bar"><i class='fas fa-bars'></i></label>
        <nav class="navbar">
            <ul>
                <li><a href="#"><i class="fa fa-book"></i> Learning Support</a></li>
                <li><a href="/try2/helpandsupport.php" target="_blank"><i class="fa-regular fa-circle-question"></i> Help & Support</a></li>
            </ul>
        </nav>
    </header>

    <div class="main-content">
        <div class="card">
            <div class="logo-container">
                <img src="https://elearn.salcc.edu.lc/pluginfile.php/1/theme_academi/logo/1729725012/salcc_black.png" alt="SALCC Logo">
            </div>
            <div class="card-header">Reset Password</div>
            <div class="card-body">
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" class="form-control" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <div class="password-container">
                            <input type="password" id="password" class="form-control" name="password" required>
                            <i class="toggle-password fas fa-eye" onclick="togglePassword('password')" title="Show/Hide Password"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="password-container">
                            <input type="password" id="confirm_password" class="form-control" name="confirm_password" required>
                            <i class="toggle-password fas fa-eye" onclick="togglePassword('confirm_password')" title="Show/Hide Password"></i>
                        </div>
                    </div>
                    <input type="submit" value="Reset Password" name="reset">
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const toggleIcon = passwordField.nextElementSibling;
            
            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.classList.remove("fa-eye");
                toggleIcon.classList.add("fa-eye-slash");
            } else {
                passwordField.type = "password";
                toggleIcon.classList.remove("fa-eye-slash");
                toggleIcon.classList.add("fa-eye");
            }
        }
    </script>

    <footer>
        <div class="footer-icons">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fa fa-envelope"></i></a>
        </div>
        <br>
        <div>
            <p>&copy; 2024 SALCC EASY RESERVE. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>