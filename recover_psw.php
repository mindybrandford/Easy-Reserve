<?php 
session_start();
include('database.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />
    <link rel="stylesheet" href="css/style.css">
    <title>Recover Password - SALCC Easy Reserve</title>
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
            <div class="card-header">Recover Password</div>
            <div class="card-body">
                <form action="#" method="POST" name="recover_psw">
                    <div class="form-group">
                        <label for="email_address">Email Address</label>
                        <input type="email" id="email_address" class="form-control" name="email" required autofocus placeholder="Enter your email">
                    </div>
                    <input type="submit" value="Reset Password" name="recover">
                </form>
            </div>
        </div>
    </div>

    <?php 
    if(isset($_POST["recover"])){
       
        $email = $_POST["email"];

        $sql = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        $query = mysqli_num_rows($sql);
          $fetch = mysqli_fetch_assoc($sql);

        if(mysqli_num_rows($sql) <= 0){
            ?>
            <script>
                alert("<?php  echo "This email does not exist in database. "?>");
            </script>
            <?php
        }else if($fetch["status"] == 0){
            ?>
               <script>
                   alert("Account must be verified before recovering password !");
                   window.location.replace("index.php");
               </script>
           <?php
        }else{
            // generate token by binaryhexa 
            $token = bin2hex(random_bytes(50));

            //session_start ();
            $_SESSION['token'] = $token;
            $_SESSION['email'] = $email;

            // send email to user
             require "Mail/phpmailer/PHPMailerAutoload.php";
            $mail = new PHPMailer;

            $mail->isSMTP();
            $mail->Host='smtp.gmail.com';
            $mail->Port=587;
            $mail->SMTPAuth=true;
            $mail->SMTPSecure='tls';

            // gmail account credentials
            $mail->Username='salcceasyreserve@gmail.com';
            $mail->Password='gimp abqn slnj vjhi';

            // send byeasy reserve account
            $mail->setFrom('salcceasyreserve@gmail.com', 'Salcc Easy Reserve');
            
            // get email from input
            $mail->addAddress($_POST["email"]);
            //$mail->addReplyTo('salcceasyreserve@gmail.com');

            // Assuming $email contains the email address of the user
    $sql = mysqli_query($conn, "SELECT username FROM users WHERE email='$email'");
    $row = mysqli_fetch_assoc($sql);
    $username = $row['username'];

    // HTML body of the email
    $mail->isHTML(true);
    $mail->Subject = "Password Recovery Request";
    $mail->Body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
        <!-- Header with SALCC Logo -->
        <div style='text-align: center; margin-bottom: 20px; padding: 20px; background-color: #f8f9fa; border-radius: 5px;'>
            <img src='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ4gsx6i7zEklHgqbY68CkEVwPrRVb5WP-Mgw&s' alt='SALCC Logo' style='max-width: 200px; height: auto;'>
        </div>

        <!-- Password Recovery Title -->
        <div style='text-align: center; margin-bottom: 30px;'>
            <h2 style='color: #0e463f; margin: 0;'>Password Recovery Request</h2>
            <p style='color: #666; margin-top: 5px;'>We received a request to reset your password</p>
        </div>

        <!-- Greeting and Content -->
        <div style='background-color: #ffffff; padding: 20px; border-radius: 5px;'>
            <p style='color: #333; font-size: 16px; margin-bottom: 20px;'>Dear $username,</p>
            
            <p style='color: #555; line-height: 1.5;'>We have received a request to reset the password associated with your account.</p>
            
            <div style='text-align: center; margin: 30px 0;'>
                <a href='http://localhost/try2/reset_psw.php' style='background-color: #0e463f; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>Reset Password</a>
            </div>
            
            <p style='color: #555; line-height: 1.5;'>If you did not initiate this password reset request or believe it to be in error, please disregard this email. Your current password will remain unchanged.</p>
            
            <p style='color: #555; line-height: 1.5;'>For security reasons, please ensure that you choose a strong and unique password that is not easily guessable.</p>
            
            <p style='color: #555; line-height: 1.5;'>If you encounter any issues or require further assistance, please do not hesitate to contact our support team by replying to this email.</p>
        </div>

        <!-- Footer -->
        <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center;'>
            <p style='color: #666; margin-bottom: 10px;'>Best regards,</p>
            <p style='color: #0e463f; font-weight: bold; margin: 0;'>SALCC Easy Reserve Team</p>
        </div>
    </div>";

    $mail->AltBody = "Dear $username,

    We have received a request to reset the password associated with your account.

    Please visit the following link to reset your password:
    http://localhost/try2/reset_psw.php

    If you did not initiate this request, please ignore this email.

    Best regards,
    SALCC Easy Reserve Team";
            if(!$mail->send()){
                ?>
                    <script>
                        alert("<?php echo " Invalid Email "?>");
                    </script>
                <?php
            }else{
                ?>
                    <script>
                        alert("Password reset instructions have been sent to your email");
                        window.location.replace("reset_psw.php");
                    </script>
                <?php
            }
        }
    }

    // Check if we need to show the popup
    $show_popup = isset($_SESSION['show_popup']) && $_SESSION['show_popup'];
    if($show_popup) {
        // Clear the session variable
        unset($_SESSION['show_popup']);
    }
    ?>
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
