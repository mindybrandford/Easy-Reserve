<?php
include 'database.php';

// Function to log auto updates
function logAutoUpdate($details) {
    $logFile = 'auto_booking_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] AUTO UPDATE: $details\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

try {
    $conn->begin_transaction();

    // Get all pending bookings that are more than 24 hours past their booking day
    $sql = "SELECT bh.*, u.email 
            FROM booking_history bh 
            JOIN users u ON bh.username = u.username 
            WHERE bh.booking_status = 'Pending' 
            AND STR_TO_DATE(CONCAT(bh.days, ' ', YEAR(CURRENT_DATE)), '%W %Y') < DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY)";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while($booking = $result->fetch_assoc()) {
            $booking_id = $booking['id'];
            $classroom = $booking['room_name'];
            $email = $booking['email'];
            
            // Get room type from classroom name
            $room_type = '';
            if (strpos($classroom, 'BUS') === 0) {
                $room_type = 'BUS';
                $table_name = 'bus_rooms';
            } elseif (strpos($classroom, 'LFT') === 0) {
                $room_type = 'LFT';
                $table_name = 'lft_rooms';
            } elseif (strpos($classroom, 'TRA') === 0 || strpos($classroom, 'DTEMS') === 0) {
                $room_type = 'TRA';
                $table_name = 'dtems_rooms';
            } elseif (strpos($classroom, 'OTW') === 0) {
                $room_type = 'OTW';
                $table_name = 'otw_rooms';
            } elseif (strpos($classroom, 'VAR') === 0) {
                $room_type = 'VAR';
                $table_name = 'var_rooms';
            } elseif (strpos($classroom, 'BED') === 0) {
                $room_type = 'BED';
                $table_name = 'bed_rooms';
            }

            // Reset room availability
            $resetRoomStmt = $conn->prepare("UPDATE $table_name SET status = 1 WHERE room_name = ? AND days = ? AND start_time = ?");
            $resetRoomStmt->bind_param("sss", $classroom, $booking['days'], $booking['start_time']);
            $resetRoomStmt->execute();

            // Update booking status to Used
            $updateStmt = $conn->prepare("UPDATE booking_history SET booking_status = 'Used' WHERE id = ?");
            $updateStmt->bind_param("i", $booking_id);
            $updateStmt->execute();

            // Send email notification
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
            $mail->Subject = "Booking Automatically Marked as Used - SALCC Easy Reserve";

            // Compose email content
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
                <!-- Header with SALCC Logo -->
                <div style='text-align: center; margin-bottom: 20px; padding: 20px; background-color: #f8f9fa; border-radius: 5px;'>
                    <img src='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ4gsx6i7zEklHgqbY68CkEVwPrRVb5WP-Mgw&s' alt='SALCC Logo' style='max-width: 200px; height: auto;'>
                </div>

                <!-- Auto Update Message -->
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h2 style='color: #0e463f; margin: 0;'>Booking Auto-Update</h2>
                    <p style='color: #666; margin-top: 10px;'>Your booking has been automatically marked as used as it is more than 24 hours past the scheduled date.</p>
                </div>

                <!-- Booking Details -->
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;'>
                    <p style='margin: 5px 0; color: #333;'>Your reservation for <strong>{$classroom}</strong> has been automatically marked as completed.</p>
                    <p style='margin: 5px 0; color: #333;'>We look forward to serving you again soon!</p>
                </div>

                <!-- Footer -->
                <div style='text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;'>
                    <p style='color: #666; font-size: 12px;'>This is an automated message from SALCC Easy Reserve. Please do not reply to this email.</p>
                </div>
            </div>";

            $mail->send();
            
            logAutoUpdate("Booking ID: $booking_id automatically marked as Used. Room: $classroom reset to Available");
        }
    }

    $conn->commit();
    echo "Auto-update process completed successfully.\n";

} catch (Exception $e) {
    if ($conn->connect_error === false) {
        $conn->rollback();
    }
    logAutoUpdate("Error: " . $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
