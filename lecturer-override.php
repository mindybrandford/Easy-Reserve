<?php
// Prevent any HTML output from error messages
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Ensure we're outputting JSON
header('Content-Type: application/json');

session_start();
include_once 'database.php';
require "Mail/phpmailer/PHPMailerAutoload.php";

function sendJsonResponse($success, $message = '', $data = []) {
    echo json_encode([
        'success' => $success,
        'error' => $message,
        'data' => $data
    ]);
    exit;
}

// Check if user is logged in and is a lecturer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'lecturer') {
    sendJsonResponse(false, 'Unauthorized access');
}

// Validate POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendJsonResponse(false, 'Invalid request method');
}

// Validate required parameters
$required = ['room', 'day', 'time', 'student_id', 'room_type'];
foreach ($required as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        sendJsonResponse(false, "Missing required field: {$field}");
    }
}

try {
    $room = $_POST['room'];
    $day = $_POST['day'];
    $time = $_POST['time'];
    $lecturer_id = $_SESSION['user_id'];
    $student_id = $_POST['student_id'];
    $room_type = $_POST['room_type'];

    // Determine the table name based on room type
    $table_name = '';
    $room_type = strtolower(trim($_POST['room_type']));

    switch ($room_type) {
        case 'bus':
            $table_name = 'bus_rooms';
            break;
        case 'dtems':
        case 'tra':  
            $table_name = 'dtems_rooms';
            break;
        case 'bed':
            $table_name = 'bed_rooms';
            break;
        case 'lft':
            $table_name = 'lft_rooms';
            break;
        case 'otw':
            $table_name = 'otw_rooms';
            break;
        case 'var':
            $table_name = 'var_rooms';
            break;
            
        default:
            throw new Exception("Invalid room type: $room_type. Expected one of: bus, dtems, tra, bed, lft, otw, var");
    }

    // Start transaction
    $conn->begin_transaction();

    // First verify that the booking exists and belongs to the student
    $verify_sql = "SELECT br.*, u.role, u.email, u.username, u.fullname 
                  FROM {$table_name} br 
                  JOIN users u ON br.user_id = u.user_id 
                  WHERE br.room_name = ? 
                  AND br.days = ? 
                  AND br.start_time = ? 
                  AND br.user_id = ?";
    
    $stmt = $conn->prepare($verify_sql);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $stmt->bind_param("sssi", $room, $day, $time, $student_id);
    if (!$stmt->execute()) {
        throw new Exception("Query execution failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception('Booking not found or does not belong to the specified student');
    }

    $booking = $result->fetch_assoc();
    $student_email = $booking['email'];
    $student_username = $booking['username'];
    $student_fullname = $booking['fullname'];

    // Convert time to 12-hour format
    $time_12hr = date("g:i A", strtotime($time));
    
    // Update the booking to the lecturer's ID
    $update_sql = "UPDATE {$table_name} 
                  SET user_id = ?
                  WHERE room_name = ? 
                  AND days = ? 
                  AND start_time = ? 
                  AND user_id = ?";
    
    $stmt = $conn->prepare($update_sql);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $stmt->bind_param("isssi", $lecturer_id, $room, $day, $time, $student_id);
    if (!$stmt->execute()) {
        throw new Exception("Update failed: " . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception('No booking was updated');
    }

    // Send email using PHPMailer
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
    $mail->addAddress($student_email);
    
    // Email content
    $mail->isHTML(true);
    $mail->Subject = "SALCC Easy Reserve - Room Booking Override Notice";
    
    // Get department name for email
    $department = '';
    switch ($room_type) {
        case 'bus':
            $department = 'Business';
            break;
        case 'dtems':
        case 'tra':
            $department = 'DTEMS';
            break;
        case 'bed':
            $department = 'BED';
            break;

        
    }
    
    $mail->Body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
        <!-- Header with SALCC Logo -->
        <div style='text-align: center; margin-bottom: 20px; padding: 20px; background-color: #f8f9fa; border-radius: 5px;'>
            <img src='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ4gsx6i7zEklHgqbY68CkEVwPrRVb5WP-Mgw&s' alt='SALCC Logo' style='max-width: 200px; height: auto;'>
        </div>

        <!-- Notice Title -->
        <div style='text-align: center; margin-bottom: 30px;'>
            <h2 style='color: #0e463f; margin: 0;'>Room Booking Override Notice</h2>
            <p style='color: #666; margin-top: 5px;'>Your room booking has been overridden by a lecturer</p>
        </div>

        <!-- Greeting -->
        <p style='color: #333; font-size: 16px;'>Dear " . htmlspecialchars($student_fullname) . ",</p>

        <!-- Override Details -->
        <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>
            <h3 style='color: #0e463f; margin-top: 0;'>Booking Details:</h3>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <td style='padding: 8px 0; color: #666;'><strong>Room:</strong></td>
                    <td style='padding: 8px 0; color: #333;'>" . htmlspecialchars($room) . "</td>
                </tr>
                <tr>
                    <td style='padding: 8px 0; color: #666;'><strong>Day:</strong></td>
                    <td style='padding: 8px 0; color: #333;'>" . htmlspecialchars($day) . "</td>
                </tr>
                <tr>
                    <td style='padding: 8px 0; color: #666;'><strong>Time:</strong></td>
                    <td style='padding: 8px 0; color: #333;'>" . htmlspecialchars($time_12hr) . "</td>
                </tr>
            </table>
        </div>

        <!-- Additional Information -->
        <div style='margin: 20px 0; padding: 15px; border-left: 4px solid #0e463f; background-color: #f8f9fa;'>
            <p style='color: #666; margin: 0;'>This booking has been overridden by a lecturer for academic purposes. Please contact your lecturer if you have any questions.</p>
        </div>

        <!-- Footer -->
        <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center;'>
            <p style='color: #666; font-size: 14px;'>This is an automated message from SALCC Easy Reserve. Please do not reply to this email.</p>
        </div>
    </div>";

    if (!$mail->send()) {
        throw new Exception('Failed to send email: ' . $mail->ErrorInfo);
    }

    $conn->commit();
    sendJsonResponse(true, '', ['message' => 'Override successful']);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    error_log("Lecturer Override Error: " . $e->getMessage());
    sendJsonResponse(false, $e->getMessage());
}
