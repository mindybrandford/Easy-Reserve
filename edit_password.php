<?php
session_start();

// Include database connection
include 'database.php';

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if user ID and new password are set
    if (isset($_POST['userId']) && isset($_POST['newPassword'])) {
        $userId = $_POST['userId'];
        $newPassword = $_POST['newPassword'];

        // Hash the new password (for security)
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Prepare and execute SQL statement to update the user's password
        $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashedPassword, $userId);

        if ($stmt->execute()) {

            // Retrieve user's email address
            $getEmailStmt = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
            $getEmailStmt->bind_param("i", $userId);
            $getEmailStmt->execute();
            $result = $getEmailStmt->get_result();
            $user = $result->fetch_assoc();
            $userEmail = $user['email'];

            // Send email to user
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

            // Sender and recipient settings
            $mail->setFrom('salcceasyreserve@gmail.com', 'SALCC Easy Reserve');
            $mail->addAddress($userEmail); // Recipient email
            $mail->addReplyTo('your_email@gmail.com', 'Your Name');

            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = 'Password Change Notification';
            // Retrieve user's username
            $getUsernameStmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
            $getUsernameStmt->bind_param("i", $userId);
            $getUsernameStmt->execute();
            $resultUsername = $getUsernameStmt->get_result();
            $userUsername = $resultUsername->fetch_assoc();
            $username = $userUsername['username'];

            $mail->Body = "
            <html>
            <body>
                <p>Dear $username,</p>
                <p>Your password has been successfully reset.</p>
                <p>Your new password is: <strong>$newPassword</strong>.</p>
                <p>After you have successfully logged in, please ensure to edit your profile and change the password, as this is a default password and not safe for long-term use.</p>
                <br>
                <p>Best regards,<br>The SALCC Easy Reserve Team</p>
            </body>
            </html>";

            if ($mail->send()) {
                // Email sent successfully
                echo "Password updated successfully. Email notification sent to user.";
            } else {
                // Email sending failed
                echo "Error: Email notification could not be sent.";
            }
        } else {
            // Error occurred while updating password
            echo "Error: Unable to update password for user ID: " . $userId;
        }

        // Close statements
        $stmt->close();
        $getEmailStmt->close();
    } else {
        // Invalid parameters
        echo "Error: Missing parameters";
    }
} else {
    // Invalid request method
    echo "Error: Invalid request method";
}

// Close database connection
$conn->close();
?>