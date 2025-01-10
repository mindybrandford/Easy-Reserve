<?php
session_start();

// Get current day of the week (e.g., Monday = 0, Sunday = 6)
$current_day_of_week = date('w'); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
$current_hour = (int)date('H');
$current_minute = (int)date('i');
$current_time = $current_hour * 60 + $current_minute; // Current time in minutes since midnight

// Pass it to JavaScript
echo "<script>
    var currentDayOfWeek = $current_day_of_week;
    var currentHour = $current_hour;
    var currentMinute = $current_minute;
    var currentTimeInMinutes = $current_time;
</script>";

include_once 'database.php'; // Assuming this file contains the database connection logic

// Initialize variables
$classroom = isset($_POST['classroom']) ? $_POST['classroom'] : 'LFT-0R-01'; // Default classroom
$days = "";
$startTime = "";
$endTime = "";
$status = "";
$timetable_data = []; // Initialize timetable data array

// Check if the user is logged in and user_id is set in the session
if (isset($_SESSION['user_id'])) {
    // Get the user_id from the session
    $user_id = $_SESSION['user_id'];
}

// Function to get the logged-in user's name
function getUserName()
{
    return isset($_SESSION['username']) ? $_SESSION['username'] : '';
}

// Check if the user is a lecturer or admin
$is_lecturer = false;
$is_admin = false;
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'lecturer') {
        $is_lecturer = true;
    } elseif ($_SESSION['role'] === 'admin') {
        $is_admin = true;
        $is_lecturer = true; // Give admin the same viewing privileges as lecturer
    }
}

// Function to check if the user is logged in
function isLoggedIn()
{
    return isset($_SESSION['username']);
}

// Function to check if the user is logged out
function isLoggedOut()
{
    return !isset($_SESSION['username']);
}

// Error handling function
function logError($message) {
    $logFile = 'booking_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] ERROR: $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Activity logging function
function logActivity($user, $action, $details) {
    $logFile = 'booking_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] USER: $user, ACTION: $action, DETAILS: $details\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Validate time format
function validateTimeFormat($time) {
    return preg_match("/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/", $time);
}

// Sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Fetch timetable data for the selected classroom
if ($_SERVER["REQUEST_METHOD"] == "POST" || !$classroom) {
    $selectedClassroom = $classroom;
    
    // First, get all room bookings
    $sql = "SELECT room_id, room_name, start_time, end_time, status, days, user_id FROM lft_rooms WHERE room_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selectedClassroom);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $status = ($row['status'] == 0) ? 'booked' : 'available';
            // Store the booking user information along with the status
            $timetable_data[$row['days']][$row['start_time']] = [
                'status' => $status,
                'user_id' => $row['user_id']
            ];
        }
    }

    $stmt->close();
}
    
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LFT Room Booking</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Table style */
        .time-table {
            width: 50%;
            height: 100;
            border-collapse: collapse;
            margin-left: 70px;
            margin-right: auto;
            margin: auto;
            margin-bottom: 20px;
        }

        .time-table th {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        .time-table td {
            border: 0.5px solid #f2f2f2;
            padding: 10px;
        }

        .time-table th {
            background-color: #f2f2f2;
        }

        .locations {
            margin-top: 5px;
            margin-bottom: 2px;
            margin-left: 60px;
            justify-content: center;
        }

        .locations select {
            padding: 10px;
            width: 200px;
            border-radius: 5px;
            margin-right: 10px;
        }

        .locations input[type="submit"] {
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
        }

        .locations input[type="submit"]:hover {
            background-color: #45a049;
        }

        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 20px;
        }

        /* Media Queries for responsiveness */
        @media screen and (max-width: 768px) {
            .time-table {
                margin-top: 15%;
                width: 100%;
                transform: scale(0.8);
            }

            .locations {
                width: 90%;
                margin: 0 auto;
                padding: 10px;
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .locations form {
                width: 100%;
                display: flex;
                flex-direction: column;
                gap: 10px;
                align-items: center;
                margin-right: 9%;
            }

            .locations select {
                width: 100%;
                max-width: 300px;
                padding: 8px;
                margin: 5px 0;
            }

            .locations input[type="submit"] {
                width: 100%;
                max-width: 300px;
                padding: 10px;
                margin: 5px 0;
                background-color: rgb(14, 70, 63);
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .time-table th,
            .time-table td {
                font-size: 12px;
                padding: 5px 8px;
            }

            .booking {
                padding: 20px 10px;
                width: 100%;
            }

            .booking h1 {
                font-size: 28px;
            }

            .booking h2 {
                font-size: 24px;
            }

            .instructions {
                padding: 15px;
                margin-top: 20px;
                width: 100%;
                margin-bottom: 0px;
            }

            .instructions h3 {
                font-size: 20px;
            }

            .instructions li {
                font-size: 14px;
            }
        }

        @media screen and (max-width: 480px) {
            .time-table {
                margin-top: 15%;
                width: 100%;
                transform: scale(0.8);
            }

            .time-table th,
            .time-table td {
                font-size: 12px;
                padding: 5px 8px;
            }

            .booking {
                padding: 20px 10px;
                width: 100%;
            }

            .booking h1 {
                font-size: 28px;
            }

            .booking h2 {
                font-size: 24px;
            }

            .locations {
                width: 100%;
                font-size: 16px;
                margin-right: 20%;
            }

            .locations input[type="submit"] {
                width: 20%;
                padding: 12px;
                margin-left: 90%;
            }

            .instructions {
                padding: 15px;
                margin-top: 20px;
                width: 100%;
                margin-bottom: 0px;
            }

            .instructions h3 {
                font-size: 25px;
            }

            .instructions li {
                font-size: 14px;
            }
        }

        /* Custom Popup Styles */
        .custom-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            text-align: center;
            animation: fadeIn 0.5s ease-in-out;
            min-width: 400px;
        }

        .custom-popup h3 {
            font-size: 24px;
            margin-bottom: 15px;
        }

        .custom-popup p {
            font-size: 18px;
            line-height: 1.5;
        }

        .success-popup {
            background-color: #4CAF50;
            color: white;
        }

        .error-popup {
            background-color: #f44336;
            color: white;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .popup-close {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            color: white;
            font-size: 20px;
        }

        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .time-slot {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            cursor: pointer;
            transition: background-color 0.3s;
            background-color: #fff;
        }

        .time-slot.booked {
            background-color: red;
            color: white;
            cursor: not-allowed;
        }

        .time-slot.my-booking {
            background-color: #FFD700; /* Yellow color for user's bookings */
            color: black;
            cursor: not-allowed;
        }

        /* Add styles for disabled slots */
        .disabled-slot {
            background-color: #f2f2f2 !important;
        }

        .disabled-slot button {
            opacity: 0.5;
            cursor: not-allowed;
            background-color: #cccccc !important;
        }

        /* Add styles for past days */
        .past-day td {
            background-color: #f2f2f2 !important;
        }

        .past-day button {
            opacity: 0.5;
            cursor: not-allowed;
            background-color: #cccccc !important;
        }

        /* Back to Booking Button Style */
        .back-button {
            margin-top: 5px;
            margin-bottom: 2px;
            margin-left: 60px;
            justify-content: center;
        }

        .back-button .back-btn {
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            text-decoration: none;
        }

        .back-button .back-btn:hover {
            background-color: #45a049;
        }

        /* Decorative heading style */
        .heading {
            margin-top: 100px;
            text-align: center;
            padding: 2.5rem 0;
            position: relative;
        }

        .heading span {
            font-size: 3.5rem;
            color: rgb(14, 70, 63);
            text-transform: uppercase;
            font-weight: bolder;
            position: relative;
            display: inline-block;
        }

        .heading span::before {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            background: rgba(14, 70, 63, 0.2);
            left: -30px;
            top: 50%;
            transform: translateY(-50%) rotate(45deg);
        }

        .heading span::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            background: rgba(14, 70, 63, 0.2);
            right: -30px;
            top: 50%;
            transform: translateY(-50%) rotate(45deg);
        }

        .heading::before {
            content: '';
            position: absolute;
            width: 30px;
            height: 30px;
            border: 3px solid rgba(14, 70, 63, 0.2);
            left: 20%;
            top: 50%;
            transform: translateY(-50%) rotate(45deg);
        }

        .heading::after {
            content: '';
            position: absolute;
            width: 30px;
            height: 30px;
            border: 3px solid rgba(14, 70, 63, 0.2);
            right: 20%;
            top: 50%;
            transform: translateY(-50%) rotate(45deg);
        }

        .heading span.space {
            margin: 0 15px;
        }

        h2 {
            text-align: center;
            color: rgb(14, 70, 63);
            margin-top: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<?php include 'header.php'; ?>

<body>

<div class="timetable">
    <section class="booking" id="booking">
        <h1 class="heading">
            <span>o</span>
            <span>t</span>
            <span>w</span>
        </h1>

        <h2>LFT Room Booking</h2>
        <div class="back-button">
            <a href="booking.php" class="back-btn back-to-booking">← Back to Booking</a>
        </div>
        <br>
    </section>

    <!-- Classroom Selection Form (optional for changing classroom) -->
    <div class="locations">
        <form id="classroomForm" method="post" action="">
            <label for="classroom">Select Classroom:</label>
            <select id="classroom" name="classroom">
                <option value="OTW-0R-05" <?php if ($classroom == 'OTW-0R-05') echo 'selected'; ?>>OTW-0R-05</option>
                <option value="OTW-0R-06" <?php if ($classroom == 'OTW-0R-06') echo 'selected'; ?>>OTW-0R-06</option>
                <option value="OTW-0R-07" <?php if ($classroom == 'OTW-0R-07') echo 'selected'; ?>>OTW-0R-07</option>
                <!-- Add more options as needed -->
            </select>
            <input type="submit" value="View Timetable">
        </form>
    </div>

    <div class="instructions">
        <h4>GENERAL STUDY ROOM GUIDE</h4>
        <ul style="list-style-type: none; padding-left: 0;">
            <!-- Color Guide -->
            <li style="margin-bottom: 15px;">
                <span style="color: #fa6b7b;"><strong>Red=Booked,</strong></span>
                <span style="color: #a7ebb6;"> <strong>Green=Available</strong></span>
                <span style="color: yellow;"> <strong>Yellow=Your Booking</strong></span>
                <?php if ($is_lecturer): ?>
                    <span style="color: purple;"> <strong>Purple=Student Booking (Click to Override)</strong></span>
                    <?php if ($is_admin): ?>
                        <span style="color: orange;"> <strong>Orange=Lecturer Booking (Click to Override)</strong></span>
                    <?php endif; ?>
                <?php endif; ?>
            </li>

            <!-- Booking Instructions -->
            <li style="margin-bottom: 15px;">
                <strong>Booking Instructions:</strong>
                <ul style="list-style-type: disc; margin: 5px 0 5px 20px;">
                    <li>Select a start time for your booking</li>
                    <li>Use the drop-down menu to choose the duration of booking</li>
                    <li>Maximum reservation time is 2 consecutive hours</li>
                </ul>
            </li>

            <!-- Override Instructions -->
            <?php if ($is_lecturer): ?>
                <li style="margin-bottom: 10px;">
                    <span style="color: #666;">
                        <strong>Note:</strong> You can override student bookings by clicking on purple slots.
                    </span>
                </li>
                <?php if ($is_admin): ?>
                    <li style="margin-bottom: 10px;">
                        <span style="color: #666;">
                            <strong>Note:</strong> As an admin, you can also override lecturer bookings (orange slots).
                        </span>
                    </li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
    </div>

    <div class="classroomForm">
        <h4>Selected Classroom: <?php echo $classroom; ?></h4>
    </div>

    <!-- Timetable Table -->
    <div class="table-wrapper">
        <table class="time-table">
            <thead>
                <tr>
                    <th>Day</th>
                    <th colspan="5">Morning (8:00am - 12:00pm)</th>
                    <th colspan="6">Afternoon (12:00pm - 5:00pm)</th>
                </tr>
                <tr>
                    <th></th>
                    <?php for ($i = 8; $i <= 17; $i++) : ?>
                    <th><?php echo ($i < 12) ? $i . ":00am" : ($i == 12 ? 12 : $i - 12) . ":00pm"; ?></th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $days_of_week = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday');

                foreach ($days_of_week as $days) {
                    echo "<tr>";
                    echo "<td>$days</td>";

                    if (isset($timetable_data[$days])) {
                        for ($i = 8; $i <= 17; $i++) {
                            $hour = sprintf("%02d", $i);
                            $time_key = $hour . ':00:00';
                            $status = isset($timetable_data[$days][$time_key]) ? $timetable_data[$days][$time_key]['status'] : 'available';
                            $booked_user_id = isset($timetable_data[$days][$time_key]) ? $timetable_data[$days][$time_key]['user_id'] : null;

                            // Check if this slot is booked by the current user
                            $isUserBooking = ($status === 'booked' && isset($_SESSION['user_id']) && $booked_user_id == $_SESSION['user_id']);
                            
                            if ($status === 'booked') {
                                if ($isUserBooking) {
                                    echo "<td style='background-color: #FFD700; cursor: pointer;' onclick='cancelBooking(\"$classroom\", \"$days\", \"$hour:00:00\")'></td>"; // Yellow for user's bookings, clickable
                                } else {
                                    // Check if the booking is by a student or lecturer and viewer is admin/lecturer
                                    $user_role = null;
                                    if ($booked_user_id) {
                                        $stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
                                        $stmt->bind_param("i", $booked_user_id);
                                        $stmt->execute();
                                        $role_result = $stmt->get_result();
                                        $user_data = $role_result->fetch_assoc();
                                        $user_role = $user_data ? $user_data['role'] : null;
                                    }
                                    
                                    if ($is_lecturer && $user_role === 'student') {
                                        // Purple for student bookings, clickable by lecturers and admins
                                        echo "<td style='background-color: purple; cursor: pointer;' onclick='lecturerOverride(\"$classroom\", \"$days\", \"$hour:00:00\", $booked_user_id)'></td>";
                                    } elseif ($is_admin && $user_role === 'lecturer') {
                                        // Orange for lecturer bookings, clickable only by admins
                                        echo "<td style='background-color: orange; cursor: pointer;' onclick='lecturerOverride(\"$classroom\", \"$days\", \"$hour:00:00\", $booked_user_id)'></td>";
                                    } else {
                                        echo "<td style='background-color: red;'></td>"; // Red for other bookings
                                    }
                                }
                            } else {
                                echo "<td style='background-color: lightgreen;' onclick='bookSlot(\"$classroom\", \"$days\", \"$hour:00:00\")'>
                                        <button class='book-btn'></button>
                                      </td>";
                            }
                        }
                    } else {
                        for ($i = 8; $i <= 17; $i++) {
                            echo "<td></td>";
                        }
                    }

                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Custom Popups -->
<div id="popupOverlay" class="popup-overlay"></div>
<div id="successPopup" class="custom-popup success-popup">
    <span class="popup-close" onclick="hideCustomPopup('successPopup')">&times;</span>
    <h3>Booking Successful!</h3>
    <p>You should receive a confirmation email shortly.</p>
</div>
<div id="errorPopup" class="custom-popup error-popup">
    <span class="popup-close" onclick="hideCustomPopup('errorPopup')">&times;</span>
    <h3>Booking Error</h3>
    <p>Unable to complete booking. Please try again.</p>
</div>
<div id="cancelSuccessPopup" class="custom-popup success-popup">
    <span class="popup-close" onclick="hideCustomPopup('cancelSuccessPopup')">&times;</span>
    <h3>Booking Cancelled!</h3>
    <p>Your booking has been successfully cancelled.</p>
</div>

<!-- Cancellation Modal -->
<div id="cancelModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="hideModal('cancelModal')">&times;</span>
        <h3>Cancel Booking</h3>
        <p>Are you sure you want to cancel this booking?</p>
        <div class="booking-details">
            <p><strong>Room:</strong> <span id="cancel-room"></span></p>
            <p><strong>Day:</strong> <span id="cancel-day"></span></p>
            <p><strong>Time:</strong> <span id="cancel-time"></span></p>
        </div>
        <div class="modal-buttons">
            <button class="cancel-btn" onclick="hideModal('cancelModal')">No, Keep</button>
            <button class="confirm-btn" onclick="confirmCancellation()">Yes, Cancel</button>
        </div>
    </div>
</div>

<!-- Modal HTML -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <p>Do you want to book this time slot?</p>
        <form id="bookingForm">
            <label for="start_time">Start Time:</label>
            <!-- Start time field will be read-only and displayed as text -->
            <input type="text" id="start_time" name="start_time" readonly>

            <label for="end_time">End Time:</label>
            <!-- End time is editable -->
            <input type="time" id="end_time" name="end_time">
            
            <button type="submit" id="bookSlotBtn">Book</button>
        </form>
    </div>
</div>

<script>
    // Modal functionality
    var modal = document.getElementById("myModal");
    var cancelModal = document.getElementById("cancelModal");
    var popupOverlay = document.getElementById("popupOverlay");
    var currentCancellation = null;

    function showModal() {
        modal.style.display = "block";
        popupOverlay.style.display = "block";
    }

    function hideModal(modalId = 'myModal') {
        document.getElementById(modalId).style.display = "none";
        popupOverlay.style.display = "none";
    }

    // Close button functionality for both modals
    var closeButtons = document.getElementsByClassName("close");
    for (var i = 0; i <closeButtons.length; i++) {
        closeButtons[i].onclick = function() {
            var modalId = this.closest('.modal').id;
            hideModal(modalId);
        };
    }

    // Window click handler for both modals
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            hideModal(event.target.id);
            popupOverlay.style.display = "none";
        }
    };

    function showCustomPopup(popupId) {
        document.getElementById(popupId).style.display = "block";
        popupOverlay.style.display = "block";
        setTimeout(() => {
            hideCustomPopup(popupId);
        }, 5000);
    }

    function hideCustomPopup(popupId) {
        document.getElementById(popupId).style.display = "none";
        popupOverlay.style.display = "none";
    }

    // Format time to HH:MM format
    function formatTime(timeString) {
        const [hours, minutes] = timeString.split(':');
        return `${hours.padStart(2, '0')}:${minutes.padStart(2, '0')}`;
    }

    function bookSlot(classroom, day, startTime) {
        showModal();

        // Format the start time
        const formattedStartTime = formatTime(startTime);
        document.getElementById('start_time').value = formattedStartTime;

        // Calculate and format end time (1 hour later)
        let [hours, minutes] = formattedStartTime.split(':');
        hours = parseInt(hours);
        minutes = parseInt(minutes);
        
        hours = (hours + 1) % 24;
        const formattedEndTime = formatTime(`${hours}:${minutes}`);
        document.getElementById('end_time').value = formattedEndTime;

        // Handle the booking submission
        document.getElementById('bookingForm').onsubmit = function(event) {
            event.preventDefault();
            var endTime = document.getElementById('end_time').value.trim();

            // Parse start and end times into minutes for comparison
            var startTimeParts = formattedStartTime.split(':');
            var startTotalMinutes = parseInt(startTimeParts[0]) * 60 + parseInt(startTimeParts[1]);
            
            var endTimeParts = endTime.split(':');
            var endTotalMinutes = parseInt(endTimeParts[0]) * 60 + parseInt(endTimeParts[1]);

            // Check if end time is exactly 1 hour (60 minutes) after start time
            if (endTotalMinutes !== startTotalMinutes + 60) {
                showCustomPopup('errorPopup');
                return;
            }

            hideModal();

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "newupdate.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            showCustomPopup('successPopup');
                            setTimeout(() => {
                                location.reload();
                            }, 3000);
                        } else {
                            showCustomPopup('errorPopup');
                        }
                    } catch (error) {
                        showCustomPopup('errorPopup');
                    }
                }
            };

            var params = "classroom=" + encodeURIComponent(classroom) +
                "&days=" + encodeURIComponent(day) +
                "&start_time=" + encodeURIComponent(formattedStartTime) +
                "&end_time=" + encodeURIComponent(endTime);

            xhr.send(params);
        };
    }

    function cancelBooking(classroom, day, startTime) {
        currentCancellation = { classroom, day, startTime };
        
        // Update modal content
        document.getElementById('cancel-room').textContent = classroom;
        document.getElementById('cancel-day').textContent = day;
        document.getElementById('cancel-time').textContent = startTime;
        
        // Show modal
        cancelModal.style.display = "block";
        popupOverlay.style.display = "block";
    }

    function confirmCancellation() {
        if (!currentCancellation) return;

        // Hide cancel modal and show success popup immediately
        hideModal('cancelModal');
        showCustomPopup('cancelSuccessPopup');

        // Start page reload countdown immediately
        setTimeout(() => {
            location.reload();
        }, 800); // Reduced to 800ms for faster response

        // Send cancellation request in background
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "cancel_booking.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

        var params = "classroom=" + encodeURIComponent(currentCancellation.classroom) +
                    "&days=" + encodeURIComponent(currentCancellation.day) +
                    "&start_time=" + encodeURIComponent(currentCancellation.startTime);

        xhr.send(params);

        // Only handle errors, since we're already showing success
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status != 200) {
                showCustomPopup('errorPopup');
            }
        };
    }

    function lecturerOverride(room, day, time, studentId) {
        console.log('Starting override with:', { room, day, time, studentId });
        
        if (confirm('Are you sure you want to override this student booking? An email will be sent to notify them.')) {
            console.log('User confirmed override');
            
            const formData = `room=${encodeURIComponent(room)}&day=${encodeURIComponent(day)}&time=${encodeURIComponent(time)}&student_id=${encodeURIComponent(studentId)}&room_type=otw`;
            console.log('Sending data:', formData);
            
            fetch('lecturer-override.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
            })
            .then(response => {
                console.log('Raw response:', response);
                return response.text().then(text => {
                    console.log('Response text:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        throw new Error('Invalid JSON response: ' + text);
                    }
                });
            })
            .then(data => {
                console.log('Processed data:', data);
                if (data.success) {
                    showCustomPopup('successPopup');
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    alert('Override failed: ' + (data.error || 'Unknown error occurred'));
                    console.error('Override error:', data);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                console.error('Error details:', error.message);
                alert('An error occurred while processing your request. Please check the console for details.');
            });
        } else {
            console.log('User cancelled override');
        }
    }

    // Days of the week mapping
    const daysOfWeek = {
        'monday': 1,
        'tuesday': 2,
        'wednesday': 3,
        'thursday': 4,
        'friday': 5
    };

    // Function to check if a time slot is in the past
    function isTimeSlotPast(dayName, timeString) {
        const dayIndex = daysOfWeek[dayName.toLowerCase()];
        
        // Convert time string to minutes since midnight
        const [hours, minutes] = timeString.split(':').map(Number);
        const slotTimeInMinutes = hours * 60 + minutes;

        // If it's Friday or later, allow booking for next week
        if (currentDayOfWeek >= 5) {
            return false;
        }

        // If it's a past day in the current week
        if (dayIndex < currentDayOfWeek) {
            return true;
        }
        
        // If it's today
        if (dayIndex === currentDayOfWeek) {
            // If it's past 6 PM (18:00 = 1080 minutes)
            if (currentTimeInMinutes >= 1080) {
                return true;
            }
            // If the slot time is in the past
            return slotTimeInMinutes <= currentTimeInMinutes;
        }

        return false;
    }

    // Function to disable past time slots
    function disablePastTimeSlots() {
        const table = document.querySelector('.time-table');
        const rows = table.getElementsByTagName('tr');

        // Skip header row
        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const dayCell = row.cells[0];
            const dayName = dayCell.textContent.trim().toLowerCase();
            
            // Get all time cells in this row
            const timeCells = row.getElementsByTagName('td');
            
            // If it's Friday or later, don't disable any slots
            if (currentDayOfWeek >= 5) {
                continue;
            }

            // If it's a past day in the current week, disable the entire row
            if (daysOfWeek[dayName] < currentDayOfWeek) {
                row.classList.add('past-day');
                continue;
            }

            // For current day
            if (daysOfWeek[dayName] === currentDayOfWeek) {
                // If it's past 6 PM, disable the entire row
                if (currentHour >= 18) {
                    row.classList.add('past-day');
                    continue;
                }

                // Check each time slot
                for (let j = 1; j < timeCells.length; j++) {
                    const cell = timeCells[j];
                    const timeButton = cell.querySelector('button');
                    if (timeButton) {
                        const timeText = timeButton.getAttribute('data-time');
                        if (isTimeSlotPast(dayName, timeText)) {
                            cell.classList.add('disabled-slot');
                            timeButton.disabled = true;
                        }
                    }
                }
            }
        }
    }

    // Call the function when the page loads
    document.addEventListener('DOMContentLoaded', disablePastTimeSlots);
</script>

</body>

<?php include 'footer.php'; ?>

</html>