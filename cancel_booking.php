<?php
session_start();
include 'database.php';

// Prevent any output before our JSON response
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

// Start output buffering to catch any unwanted output
ob_start();

// Include PHPMailer
$phpmailer_path = "Mail/phpmailer/PHPMailerAutoload.php";
if (!file_exists($phpmailer_path)) {
    error_log("PHPMailer not found at: $phpmailer_path");
}

// Function to log cancellation attempts
function logCancellation($user, $status, $details) {
    $logFile = 'booking_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] USER: $user, STATUS: Cancellation $status, DETAILS: $details\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Function to send cancellation email
function sendCancellationEmail($userEmail, $classroom, $day, $time_12hr) {
    try {
        if (!class_exists('PHPMailer')) {
            require "Mail/phpmailer/PHPMailerAutoload.php";
        }
        
        $mail = new PHPMailer(true);
        
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';

        $mail->Username = 'salcceasyreserve@gmail.com';
        $mail->Password = 'gimp abqn slnj vjhi';
        
        $mail->setFrom('salcceasyreserve@gmail.com', 'SALCC Easy Reserve');
        $mail->addAddress($userEmail);

        $mail->isHTML(true);
        $mail->Subject = "SALCC Easy Reserve - Booking Cancellation Confirmation";
        
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
            <!-- Header with SALCC Logo -->
            <div style='text-align: center; margin-bottom: 20px; padding: 20px; background-color: #f8f9fa; border-radius: 5px;'>
                <img src='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ4gsx6i7zEklHgqbY68CkEVwPrRVb5WP-Mgw&s' alt='SALCC Logo' style='max-width: 200px; height: auto;'>
            </div>

            <!-- Notice Title -->
            <div style='text-align: center; margin-bottom: 30px;'>
                <h2 style='color: #0e463f; margin: 0;'>Booking Cancellation Confirmation</h2>
                <p style='color: #666; margin-top: 5px;'>Your room booking has been cancelled</p>
            </div>

            <!-- Greeting -->
            <p style='color: #333; font-size: 16px;'>Dear User,</p>

            <!-- Cancellation Details -->
            <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                <h3 style='color: #0e463f; margin-top: 0;'>Cancelled Booking Details:</h3>
                <table style='width: 100%; border-collapse: collapse;'>
                    <tr>
                        <td style='padding: 8px 0; color: #666;'><strong>Room:</strong></td>
                        <td style='padding: 8px 0; color: #333;'>" . htmlspecialchars($classroom) . "</td>
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
                <p style='color: #666; margin: 0;'>The room is now available for other users to book. If you need to book another room, please visit the booking page.</p>
            </div>

            <!-- Footer -->
            <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center;'>
                <p style='color: #666; font-size: 14px;'>This is an automated message from SALCC Easy Reserve. Please do not reply to this email.</p>
            </div>
        </div>";

        return $mail->send();
    } catch (Exception $e) {
        error_log("Error sending cancellation email: " . $e->getMessage());
        return false;
    }
}

// Function to update room status and booking history
function cancelBooking($conn, $classroom, $day, $start_time, $user_id = null) {
    // Determine which table to use based on classroom prefix
    $table_name = '';
    if (strpos($classroom, 'BUS') === 0) {
        $table_name = 'bus_rooms';
    } elseif (strpos($classroom, 'LFT') === 0) {
        $table_name = 'lft_rooms';
    } elseif (strpos($classroom, 'TRA') === 0 || strpos($classroom, 'DTEMS') === 0) {
        $table_name = 'dtems_rooms';
    } elseif (strpos($classroom, 'OTW') === 0) {
        $table_name = 'otw_rooms';
    } elseif (strpos($classroom, 'VAR') === 0) {
        $table_name = 'var_rooms';
    } elseif (strpos($classroom, 'BED') === 0) {
        $table_name = 'bed_rooms';
    } else {
        throw new Exception("Invalid classroom type: $classroom");
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update room status
        $updateStmt = $conn->prepare("UPDATE $table_name SET status = 1, user_id = NULL WHERE room_name = ? AND days = ? AND start_time = ?");
        $updateStmt->bind_param("sss", $classroom, $day, $start_time);
        $updateStmt->execute();
        
        // Update booking history
        $historyStmt = $conn->prepare("UPDATE booking_history SET booking_status = 'Cancelled' WHERE room_name = ? AND days = ? AND start_time = ? AND username = ?");
        $historyStmt->bind_param("ssss", $classroom, $day, $start_time, $_SESSION['username']);
        $historyStmt->execute();
        
        // Get user's email
        $emailStmt = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
        $emailStmt->bind_param("i", $_SESSION['user_id']);
        $emailStmt->execute();
        $emailResult = $emailStmt->get_result();
        $userEmail = $emailResult->fetch_assoc()['email'];
        
        // Convert time to 12-hour format
        $time_parts = explode(':', $start_time);
        $hour = intval($time_parts[0]);
        $meridiem = ($hour >= 12) ? 'PM' : 'AM';
        if ($hour > 12) $hour -= 12;
        elseif ($hour === 0) $hour = 12;
        $time_12hr = sprintf("%d:%s %s", $hour, $time_parts[1], $meridiem);
        
        // Send cancellation email
        if (!sendCancellationEmail($userEmail, $classroom, $day, $time_12hr)) {
            // Log email failure but continue with cancellation
            error_log("Failed to send cancellation email to $userEmail");
        }
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }
    
    // Handle both booking history and direct room cancellations
    if (isset($_POST['booking_id'])) {
        // Get booking details from booking_history
        $stmt = $conn->prepare("SELECT * FROM booking_history WHERE id = ? AND username = ?");
        $stmt->bind_param("is", $_POST['booking_id'], $_SESSION['username']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $success = cancelBooking($conn, $row['room_name'], $row['days'], $row['start_time']);
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully']);
            } else {
                throw new Exception('Failed to cancel booking');
            }
        } else {
            throw new Exception('Booking not found or unauthorized');
        }
    } else {
        // Handle direct room cancellation
        $classroom = $_POST['classroom'] ?? '';
        $day = $_POST['days'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        
        if (empty($classroom) || empty($day) || empty($start_time)) {
            throw new Exception('Missing required parameters');
        }
        
        $success = cancelBooking($conn, $classroom, $day, $start_time);
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully']);
        } else {
            throw new Exception('Failed to cancel booking');
        }
    }
} catch (Exception $e) {
    error_log("Error in cancel_booking.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Clean up any buffered output
ob_end_clean();

$conn->close();
?>
