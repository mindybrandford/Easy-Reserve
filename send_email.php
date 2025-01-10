<?php

session_start();

include('database.php');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    try {
        // First, get all admin users' emails
        $adminEmailsStmt = $conn->prepare("SELECT email FROM users WHERE role = 'admin'");
        $adminEmailsStmt->execute();
        $adminEmails = $adminEmailsStmt->get_result();
        
        // Insert into contact_us table
        $stmt = $conn->prepare("INSERT INTO contact_us (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);

        // Execute the SQL query
        if ($stmt->execute()) {
            require "Mail/phpmailer/PHPMailerAutoload.php";
            $mail = new PHPMailer;

            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 587;
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'tls';

            // Email credentials
            $mail->Username = 'salcceasyreserve@gmail.com';
            $mail->Password = 'gimp abqn slnj vjhi';

            // Sender settings
            $mail->setFrom('salcceasyreserve@gmail.com', 'SALCC Easy Reserve');
            
            // Add all admin users as recipients
            while ($row = $adminEmails->fetch_assoc()) {
                $mail->addAddress($row['email']);
            }

            $mail->isHTML(true);
            $mail->Subject = "New Contact Form Message: $subject";

            // Compose styled email content
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
                <!-- Header with SALCC Logo -->
                <div style='text-align: center; margin-bottom: 20px; padding: 20px; background-color: #f8f9fa; border-radius: 5px;'>
                    <img src='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ4gsx6i7zEklHgqbY68CkEVwPrRVb5WP-Mgw&s' alt='SALCC Logo' style='max-width: 200px; height: auto;'>
                </div>

                <!-- Contact Message Title -->
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h2 style='color: #0e463f; margin: 0;'>New Contact Form Message</h2>
                    <p style='color: #666; margin-top: 10px;'>A new message has been submitted through the contact form.</p>
                </div>

                <!-- Message Details -->
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;'>
                    <p style='margin: 5px 0; color: #333;'><strong>From:</strong> " . htmlspecialchars($name) . "</p>
                    <p style='margin: 5px 0; color: #333;'><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                    <p style='margin: 5px 0; color: #333;'><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
                    <p style='margin: 15px 0 5px 0; color: #333;'><strong>Message:</strong></p>
                    <p style='margin: 5px 0; color: #333; background: white; padding: 15px; border-radius: 5px;'>" . nl2br(htmlspecialchars($message)) . "</p>
                </div>

                <!-- Footer -->
                <div style='text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;'>
                    <p style='color: #666; font-size: 12px;'>This is an automated message from SALCC Easy Reserve. You can reply directly to the sender by using their email address above.</p>
                </div>
            </div>";

            // Send confirmation email to the user who submitted the form
            $userMail = clone $mail;
            $userMail->clearAddresses();
            $userMail->addAddress($email, $name);
            $userMail->Subject = "Thank you for contacting SALCC Easy Reserve";
            $userMail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
                <!-- Header with SALCC Logo -->
                <div style='text-align: center; margin-bottom: 20px; padding: 20px; background-color: #f8f9fa; border-radius: 5px;'>
                    <img src='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ4gsx6i7zEklHgqbY68CkEVwPrRVb5WP-Mgw&s' alt='SALCC Logo' style='max-width: 200px; height: auto;'>
                </div>

                <!-- Thank You Message -->
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h2 style='color: #0e463f; margin: 0;'>Thank You for Contacting Us!</h2>
                    <p style='color: #666; margin-top: 10px;'>We have received your message and will get back to you shortly.</p>
                </div>

                <!-- Message Details -->
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;'>
                    <p style='margin: 5px 0; color: #333;'>We have received your message regarding: <strong>" . htmlspecialchars($subject) . "</strong></p>
                    <p style='margin: 5px 0; color: #333;'>Our team will review your message and respond as soon as possible.</p>
                </div>

                <!-- Footer -->
                <div style='text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;'>
                    <p style='color: #666; font-size: 12px;'>This is an automated confirmation message. Please do not reply to this email.</p>
                </div>
            </div>";

            // Send both emails
            if ($mail->send() && $userMail->send()) {
                echo "<script>alert('Message sent successfully! Check your email for confirmation.'); window.location.href = '/try2/index.php';</script>";
                exit();
            } else {
                echo "<script>alert('Message could not be sent. Mailer Error: {$mail->ErrorInfo}');</script>";
            }
        } else {
            echo "<script>alert('Message could not be sent. Please try again.');</script>";
        }

        // Close statements
        $stmt->close();
        $adminEmailsStmt->close();
    } catch (Exception $e) {
        echo "<script>alert('Error: {$e->getMessage()}');</script>";
    }
}

?>
