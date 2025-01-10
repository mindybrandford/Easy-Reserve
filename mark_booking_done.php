<?php
session_start();
include 'database.php';

// Function to log status updates
function logStatusUpdate($user, $status, $details) {
    $logFile = 'booking_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] USER: $user, STATUS: Booking marked as $status, DETAILS: $details\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get POST parameters
$booking_id = $_POST['booking_id'] ?? '';

// Validate input
if (empty($booking_id)) {
    logStatusUpdate($_SESSION['username'], "Failed", "Missing booking ID");
    echo json_encode(['success' => false, 'message' => 'Missing booking ID']);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Get booking details including email
    $getBookingStmt = $conn->prepare("SELECT bh.*, u.email 
                                     FROM booking_history bh 
                                     JOIN users u ON bh.username = u.username 
                                     WHERE bh.id = ?");
    if (!$getBookingStmt) {
        throw new Exception("Failed to prepare booking query: " . $conn->error);
    }

    $getBookingStmt->bind_param("i", $booking_id);
    $getBookingStmt->execute();
    $result = $getBookingStmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Booking not found");
    }

    $bookingData = $result->fetch_assoc();
    $classroom = $bookingData['room_name'];
    $email = $bookingData['email'];
    $day = $bookingData['days'];
    $start_time = $bookingData['start_time'];

    // Log the room name for debugging
    logStatusUpdate($_SESSION['username'], "Debug", "Room Name: " . $classroom);

    // Extract the room type from the room name (e.g., "BUS" from "BUS-0R-01")
    $room_type = strtoupper(substr($classroom, 0, 3));

    // Determine which table to update based on room type
    $table_name = '';
    switch ($room_type) {
        case 'BED':
            $table_name = 'bed_rooms';
            break;
        case 'OTW':
            $table_name = 'otw_rooms';
            break;
        case 'VAR ':
            $table_name = 'var_rooms';
            break;
        case 'BUS':
            $table_name = 'bus_rooms';
            break;
        case 'LFT':
            $table_name = 'lft_rooms';
            break;
        case 'TRA':
            $table_name = 'dtems_rooms';
            break;
        default:
            throw new Exception("Invalid classroom type for room: " . $classroom . " (type: " . $room_type . ")");
    }

    // Log the determined table name
    logStatusUpdate($_SESSION['username'], "Debug", "Table Name: " . $table_name);

    // Reset the room availability in the corresponding table
    $resetRoomStmt = $conn->prepare("UPDATE $table_name SET user_id = NULL, status = 'Available' WHERE room_name = ? AND days = ? AND start_time = ?");
    if (!$resetRoomStmt) {
        throw new Exception("Failed to prepare room reset statement: " . $conn->error);
    }

    $resetRoomStmt->bind_param("sss", $classroom, $day, $start_time);
    if (!$resetRoomStmt->execute()) {
        throw new Exception("Failed to reset room availability: " . $resetRoomStmt->error);
    }

    // Update the booking status to 'Used'
    $update_sql = "UPDATE booking_history SET booking_status = 'Used' WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();

    // Send thank you email
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
    $mail->Subject = "Thank You for Using SALCC Easy Reserve";

    // Compose email content
    $mail->Body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
        <!-- Header with SALCC Logo -->
        <div style='text-align: center; margin-bottom: 20px; padding: 20px; background-color: #f8f9fa; border-radius: 5px;'>
            <img src='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ4gsx6i7zEklHgqbY68CkEVwPrRVb5WP-Mgw&s' alt='SALCC Logo' style='max-width: 200px; height: auto;'>
        </div>

        <!-- Thank You Message -->
        <div style='text-align: center; margin-bottom: 30px;'>
            <h2 style='color: #0e463f; margin: 0;'>Thank You!</h2>
            <p style='color: #666; margin-top: 10px;'>Thank you for using SALCC Easy Reserve. We hope you had a great experience!</p>
        </div>

        <!-- Booking Details -->
        <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;'>
            <p style='margin: 5px 0; color: #333;'>Your reservation for <strong>{$classroom}</strong> has been marked as completed.</p>
            <p style='margin: 5px 0; color: #333;'>We look forward to serving you again soon!</p>
        </div>

        <!-- Footer -->
        <div style='text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;'>
            <p style='color: #666; font-size: 12px;'>This is an automated message from SALCC Easy Reserve. Please do not reply to this email.</p>
        </div>
    </div>";

    $mail->send();

    // Commit transaction
    $conn->commit();

    // Log successful update
    logStatusUpdate($_SESSION['username'], "Success", "Booking ID: $booking_id marked as Used and room reset to Available");
    
    echo json_encode(['success' => true, 'message' => 'Booking marked as used and room is now available']);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->connect_error === false) {
        $conn->rollback();
    }
    logStatusUpdate($_SESSION['username'], "Failed", $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Close any open statements
if (isset($getBookingStmt)) $getBookingStmt->close();
if (isset($resetRoomStmt)) $resetRoomStmt->close();
if (isset($stmt)) $stmt->close();
$conn->close();
