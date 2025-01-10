<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "easy_reserve";

$conn = new mysqli($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST["verify"])) {
    $otp = $_SESSION['otp'];
    $email = $_SESSION['mail'];
    $otp_code = $_POST['otp_code'];

    if ($otp != $otp_code) {
?>
        <script>
            alert("Invalid verification code");
        </script>
    <?php
    } else {
        mysqli_query($conn, "UPDATE users SET status = 1 WHERE email = '$email'")
    ?>
        <script>
            alert("Account verification succesful, you may login in now");
            window.location.replace("login.php");
        </script>
<?php
    }
}

// Handle resend code request
if (isset($_POST["resend"])) {
    if (isset($_SESSION['mail'])) {
        $email = $_SESSION['mail'];
        
        // Get user's full name from database
        $result = mysqli_query($conn, "SELECT fullname FROM users WHERE email = '$email'");
        $user = mysqli_fetch_assoc($result);
        $fullname = $user['fullname'];
        
        // Generate new OTP
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        
        require "Mail/phpmailer/PHPMailerAutoload.php";
        $mail = new PHPMailer;

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
        $mail->Subject = "SALCC Easy Reserve - Account Verification";

        // HTML version of the email
        $mail->Body = "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2E8B57; margin-bottom: 20px;'>Welcome to SALCC Easy Reserve!</h2>
                
                <p>Dear " . $fullname . ",</p>
                
                <p>Thank you for creating an account with SALCC Easy Reserve. To complete your registration, please use the verification code below:</p>
                
                <div style='background-color: #f5f5f5; padding: 15px; margin: 20px 0; text-align: center; border-radius: 5px;'>
                    <h2 style='color: #2E8B57; margin: 0;'>" . $otp . "</h2>
                </div>
                
                <p>Please enter this code on the verification page to activate your account.</p>
                
                <p>If you did not create an account with SALCC Easy Reserve, please ignore this email.</p>
                
                <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;'>
                    <p style='margin: 0;'>Best regards,</p>
                    <p style='margin: 5px 0; color: #2E8B57; font-weight: bold;'>SALCC Easy Reserve Team</p>
                </div>
            </div>
        </body>
        </html>";

        // Plain text version of the email
        $mail->AltBody = "Dear " . $fullname . ",\n\n" .
            "Welcome to SALCC Easy Reserve!\n\n" .
            "Thank you for creating an account. To complete your registration, please use the following verification code:\n\n" .
            $otp . "\n\n" .
            "Please enter this code on the verification page to activate your account.\n\n" .
            "If you did not create an account with SALCC Easy Reserve, please ignore this email.\n\n" .
            "Best regards,\n" .
            "SALCC Easy Reserve Team";

        if($mail->send()) {
?>
            <script>
                alert("New verification code has been sent to your email");
            </script>
<?php
        } else {
?>
            <script>
                alert("Failed to send new verification code. Please try again.");
            </script>
<?php
        }
    } else {
?>
        <script>
            alert("Session expired. Please sign up again.");
            window.location.replace("signup.php");
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
    <title>Verify Your Account</title>
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
            min-height: calc(100vh - 200px); /* Account for header and footer */
        }

        .verification-container {
            width: 400px;
            padding: 40px;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin: 0;
        }

        .verification-container img {
            width: 180px;
            margin-bottom: 20px;
        }

        .verification-container h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
        }

        .verification-container p {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
            line-height: 1.6;
        }

        .otp-input-container {
            position: relative;
            margin: 30px 0;
        }

        .otp-input-container i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .otp-input-container input {
            width: 100%;
            padding: 12px 40px;
            border: 1px solid #ddd;
            border-radius: 25px;
            font-size: 16px;
            letter-spacing: 2px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .otp-input-container input:focus {
            border-color: rgb(75, 168, 156);
            box-shadow: 0 0 0 2px rgba(75, 168, 156, 0.1);
            outline: none;
        }

        .verify-button {
            width: 100%;
            padding: 12px;
            background: rgb(75, 168, 156);
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
        }

        .verify-button:hover {
            background: rgb(14, 70, 63);
        }

        .resend-link {
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .resend-link a {
            color: rgb(75, 168, 156);
            text-decoration: none;
            font-weight: 600;
        }

        .resend-link a:hover {
            color: rgb(14, 70, 63);
        }

        footer {
            margin-top: auto;
        }

        @media (max-width: 480px) {
            .main-content {
                min-height: calc(100vh - 180px);
                padding: 40px 0;
            }
            
            .verification-container {
                width: 90%;
                padding: 30px 20px;
            }
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
        <div class="verification-container">
            <img src="https://elearn.salcc.edu.lc/pluginfile.php/1/theme_academi/logo/1729725012/salcc_black.png" alt="SALCC Logo">
            <h1>Verify Your Account</h1>
            <p>We've sent a verification code to your email address. Please enter the code below to verify your account.</p>
            
            <form action="#" method="POST">
                <div class="otp-input-container">
                    <i class="fas fa-key"></i>
                    <input type="text" id="otp" name="otp_code" placeholder="Enter verification code" required autofocus>
                </div>
                
                <button type="submit" name="verify" class="verify-button">
                    <i class="fas fa-check-circle"></i>
                    Verify Account
                </button>
            </form>
            
            <div class="resend-link">
                <form action="" method="POST" style="display: inline;">
                    <button type="submit" name="resend" style="background: none; border: none; color: rgb(75, 168, 156); text-decoration: none; font-weight: 600; cursor: pointer; padding: 0;">
                        Didn't receive the code? Resend Code
                    </button>
                </form>
            </div>
        </div>
    </div>

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