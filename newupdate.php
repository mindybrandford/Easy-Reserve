<?php
session_start();

// Function to validate time format
function validateTimeFormat($time) {
    // Accept both HH:MM:SS and HH:MM formats
    return preg_match("/^([01]?[0-9]|2[0-3]):([0-5][0-9])(?::([0-5][0-9]))?$/", $time);
}

// Function to ensure time is in HH:MM:SS format
function normalizeTimeFormat($time) {
    if (preg_match("/^([01]?[0-9]|2[0-3]):([0-5][0-9])$/", $time)) {
        return $time . ":00";
    }
    return $time;
}

// Function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Function to log booking attempts
function logBookingAttempt($user, $status, $details) {
    $logFile = 'booking_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] USER: $user, STATUS: $status, DETAILS: $details\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Function to convert 24hr to 12hr time format
function convertTo12Hour($time) {
    // Split the time into hours, minutes, and seconds
    $parts = explode(':', $time);
    $hour = intval($parts[0]);
    $minutes = $parts[1];
    
    // Determine AM/PM
    $meridiem = ($hour >= 12) ? 'PM' : 'AM';
    
    // Convert hour to 12-hour format
    if ($hour > 12) {
        $hour -= 12;
    } elseif ($hour === 0) {
        $hour = 12;
    }
    
    return sprintf("%d:%s %s", $hour, $minutes, $meridiem);
}

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the user is logged in
    if (!isset($_SESSION['username'])) {
        logBookingAttempt("Unknown", "Failed", "Not logged in");
        echo json_encode(array("success" => false, "message" => "You need to be logged in to book a slot."));
        exit;
    }

    // Check if user_id is set in the session
    if (!isset($_SESSION['user_id'])) {
        logBookingAttempt($_SESSION['username'], "Failed", "User ID not set");
        echo json_encode(array("success" => false, "message" => "User ID is not set in the session."));
        exit;
    }

    // Ensure fullname is set in the session
    if (!isset($_SESSION['fullname'])) {
        logBookingAttempt($_SESSION['username'], "Failed", "Full name not set");
        echo json_encode(array("success" => false, "message" => "Full name is not set in the session."));
        exit;
    }

    // Ensure email is set in the session
    if (!isset($_SESSION['email'])) {
        logBookingAttempt($_SESSION['username'], "Failed", "Email not set");
        echo json_encode(array("success" => false, "message" => "Email address is not set in the session."));
        exit;
    }

    // Include database connection logic
    include_once 'database.php';

    try {
        // Retrieve and sanitize form data
        $classroom = sanitizeInput($_POST['classroom'] ?? '');
        $day = sanitizeInput($_POST['days'] ?? $_POST['day'] ?? ''); // Accept both 'days' and 'day'
        $start_time = sanitizeInput($_POST['start_time'] ?? '');
        $end_time = sanitizeInput($_POST['end_time'] ?? '');

        // Validate form data
        if (empty($classroom) || empty($day) || empty($start_time)) {
            throw new Exception("Required fields are missing.");
        }

        // Normalize time formats
        $start_time = normalizeTimeFormat($start_time);
        if (!empty($end_time)) {
            $end_time = normalizeTimeFormat($end_time);
        }

        // Determine which table to use based on the classroom prefix
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
            throw new Exception("Invalid classroom type");
        }

        // Check if the slot is already booked
        $checkStmt = $conn->prepare("SELECT status FROM $table_name WHERE room_name = ? AND days = ? AND start_time = ?");
        if (!$checkStmt) {
            throw new Exception("Failed to prepare check statement: " . $conn->error);
        }

        $checkStmt->bind_param("sss", $classroom, $day, $start_time);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Time slot not found.");
        }
        
        $row = $result->fetch_assoc();
        if ($row['status'] == 0) {
            throw new Exception("This time slot is already booked.");
        }
        
        $checkStmt->close();

        // Prepare SQL statement to update the status of the booked slot
        $stmt = $conn->prepare("UPDATE $table_name SET status = 0, user_id = ? WHERE room_name = ? AND days = ? AND start_time = ?");
        if (!$stmt) {
            throw new Exception("Failed to prepare update statement: " . $conn->error);
        }

        // Bind parameters and execute
        $stmt->bind_param("isss", $_SESSION['user_id'], $classroom, $day, $start_time);
        
        if ($stmt->execute()) {
            // Log the successful booking
            logBookingAttempt($_SESSION['username'], "Success", "Booked $classroom on $day at $start_time");

            // Insert into booking_history table
            $historyStmt = $conn->prepare("INSERT INTO booking_history (fullname, username, room_name, start_time, end_time, days, booking_date, booking_status) VALUES (?, ?, ?, ?, ?, ?, CURRENT_DATE, 'Pending')");
            if (!$historyStmt) {
                throw new Exception("Failed to prepare history statement: " . $conn->error);
            }

            // Bind parameters and execute
            $historyStmt->bind_param("ssssss", $_SESSION['fullname'], $_SESSION['username'], $classroom, $start_time, $end_time, $day);
            if (!$historyStmt->execute()) {
                throw new Exception("Failed to save booking history: " . $historyStmt->error);
            }
            $historyStmt->close();

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
            $mail->addAddress($_SESSION["email"]);

            $mail->isHTML(true);
            $mail->Subject = "SALCC Easy Reserve Booking Confirmation";

            // Convert times to 12-hour format for email
            $start_time_12hr = convertTo12Hour($start_time);
            $end_time_12hr = !empty($end_time) ? convertTo12Hour($end_time) : '';

            // Compose email content
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
                <!-- Header with SALCC Logo -->
                <div style='text-align: center; margin-bottom: 20px; padding: 20px; background-color: #f8f9fa; border-radius: 5px;'>
                    <img src='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ4gsx6i7zEklHgqbY68CkEVwPrRVb5WP-Mgw&s' alt='SALCC Logo' style='max-width: 200px; height: auto;'>
                </div>

                <!-- Booking Confirmation Title -->
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h2 style='color: #0e463f; margin: 0;'>Booking Confirmation</h2>
                    <p style='color: #666; margin-top: 5px;'>Your room has been successfully booked!</p>
                </div>

                <!-- Greeting -->
                <p style='color: #333; font-size: 16px;'>Dear " . htmlspecialchars($_SESSION['fullname']) . ",</p>

                <!-- Booking Details -->
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                    <h3 style='color: #0e463f; margin-top: 0;'>Booking Details:</h3>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px 0; color: #666;'><strong>Classroom:</strong></td>
                            <td style='padding: 8px 0; color: #333;'>" . htmlspecialchars($classroom) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; color: #666;'><strong>Day:</strong></td>
                            <td style='padding: 8px 0; color: #333;'>" . htmlspecialchars($day) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; color: #666;'><strong>Start Time:</strong></td>
                            <td style='padding: 8px 0; color: #333;'>" . htmlspecialchars($start_time_12hr) . "</td>
                        </tr>" .
                        (!empty($end_time_12hr) ? "
                        <tr>
                            <td style='padding: 8px 0; color: #666;'><strong>End Time:</strong></td>
                            <td style='padding: 8px 0; color: #333;'>" . htmlspecialchars($end_time_12hr) . "</td>
                        </tr>" : "") . "
                    </table>
                </div>

                <!-- Additional Information -->
                <div style='margin: 20px 0; padding: 15px; border-left: 4px solid #0e463f; background-color: #f8f9fa;'>
                    <p style='color: #666; margin: 0;'>Please arrive at your booked room on time. If you need to cancel your booking, please do so at least 1 hour before the scheduled time.</p>
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

            // Set plain text version
            $mail->AltBody = "Dear " . $_SESSION['fullname'] . ",\n\n" .
                "Your booking has been successfully completed!\n\n" .
                "Booking Details:\n" .
                "Classroom: " . $classroom . "\n" .
                "Day: " . $day . "\n" .
                "Start Time: " . $start_time_12hr . "\n" .
                (!empty($end_time_12hr) ? "End Time: " . $end_time_12hr . "\n" : "") .
                "\nYou can view your booking history at any time by visiting the Booking History page.\n";

            if ($mail->send()) {
                echo json_encode(array(
                    "success" => true, 
                    "message" => "Booking successful! Confirmation email sent.",
                    "redirect" => "bookinghistory.php"
                ));
            } else {
                echo json_encode(array(
                    "success" => true,
                    "message" => "Booking successful! But failed to send confirmation email.",
                    "redirect" => "bookinghistory.php"
                ));
            }
        } else {
            throw new Exception("Failed to update booking status: " . $stmt->error);
        }

        $stmt->close();

    } catch (Exception $e) {
        logBookingAttempt($_SESSION['username'], "Failed", $e->getMessage());
        echo json_encode(array("success" => false, "message" => $e->getMessage()));
    }

} else {
    // Handle invalid request method
    logBookingAttempt("Unknown", "Failed", "Invalid request method");
    echo json_encode(array("success" => false, "message" => "Invalid request method."));
    exit;
}
?>
