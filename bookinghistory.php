<?php
session_start();

// Include the database connection
include_once 'database.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to check if the user is logged in
function isLoggedIn() {
    return isset($_SESSION['username']);
}

// Function to get the logged-in user's name
function getUserName() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : '';
}
// Function to check if the user is logged out
function isLoggedOut() {
    return !isset($_SESSION['username']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Booking History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* General page styling */
        body {
            font-family: 'Montserrat', 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fc;
            color: #333;
            line-height: 1.8;
        }

        .container {
            margin: 50px auto;
            margin-top: 150px;
            max-width: 90%;
            padding: 40px;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 35px;
            padding: 20px 0;
        }

        .logo-container img {
            max-width: 180px;
            height: auto;
        }

        h2 {
            color: #0e463f;
            text-align: center;
            font-size: 2.2rem;
            margin-bottom: 40px;
            font-weight: 600;
            position: relative;
            padding-bottom: 15px;
        }

        h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 3px;
            background-color: #0e463f;
        }

        .table-responsive {
            overflow-x: auto;
            margin-top: 35px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            padding: 5px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
            margin: 10px 0;
        }

        th, td {
            padding: 20px 25px;
            text-align: left;
            font-size: 1rem;
            border: none;
            vertical-align: middle;
        }

        th {
            background-color: #0e463f;
            color: #fff;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        th:first-child {
            border-top-left-radius: 12px;
        }

        th:last-child {
            border-top-right-radius: 12px;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #f0f4f3;
            transition: background-color 0.3s ease;
        }

        tr:last-child td:first-child {
            border-bottom-left-radius: 12px;
        }

        tr:last-child td:last-child {
            border-bottom-right-radius: 12px;
        }

        td {
            border-bottom: 1px solid #eee;
            line-height: 1.6;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 5px;
        }

        .btn-danger {
            background-color: #dc3545;
            border: none;
            color: white;
            padding: 12px 24px;
        }

        .btn-danger:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.2);
        }

        .alert {
            padding: 20px 25px;
            border-radius: 12px;
            margin: 30px 0;
            border: none;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border-left: 5px solid #ffeeba;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 5px solid #f5c6cb;
        }

        /* Modal styling */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background-color: #0e463f;
            color: white;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            padding: 20px 25px;
        }

        .modal-body {
            padding: 25px;
            font-size: 1.1rem;
        }

        .modal-footer {
            border-bottom-left-radius: 15px;
            border-bottom-right-radius: 15px;
            padding: 15px 25px;
        }

        /* Success popup styling */
        .success-popup {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            background-color: #28a745;
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1050;
            display: none;
            animation: slideIn 0.5s ease-out;
        }

        .btn-done {
            background-color: #28a745;
            color: white;
            margin-right: 5px;
            width: 100%;
        }

        .btn-done:hover {
            background-color: #218838;
            color: white;
        }

        .btn-cancel {
            background-color: #dc3545;
            color: white;
            width: 100%;
        }

        .btn-cancel:hover {
            background-color: #c82333;
            color: white;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 5px;
            white-space: nowrap;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .success-popup i {
            margin-right: 10px;
        }

        .success-popup.show {
            display: block;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                margin-top: 120px;
                padding: 25px;
                max-width: 95%;
            }

            h2 {
                font-size: 1.8rem;
                margin-bottom: 30px;
            }

            th, td {
                padding: 15px 20px;
                font-size: 0.9rem;
            }

            .btn {
                padding: 8px 16px;
                font-size: 0.85rem;
            }

            .alert {
                padding: 15px 20px;
                font-size: 1rem;
            }

            .logo-container {
                margin-bottom: 25px;
            }

            .logo-container img {
                max-width: 150px;
            }
        }

        /* Add styles for status colors */
        .text-success {
            color: #28a745 !important;
            font-weight: 500;
        }

        .text-danger {
            color: #dc3545 !important;
            font-weight: 500;
        }

        .text-warning {
            color: #ffc107 !important;
            font-weight: 500;
        }

        .text-info {
            color: #17a2b8 !important;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="logo-container">
            <img src="https://elearn.salcc.edu.lc/pluginfile.php/1/theme_academi/logo/1729725012/salcc_black.png" alt="SALCC Logo">
        </div>
        <h2>Booking History</h2>
        
        <?php
        if (isLoggedIn()) {
            $username = $_SESSION['username'];
            $sql = "SELECT * FROM booking_history WHERE username = '$username'";
            $result = $conn->query($sql);
            
            if (!$result) {
                echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
            } else {
                if ($result->num_rows > 0) {
                    echo '<div class="table-responsive">';
                    echo '<table class="table table-striped table-bordered">';
                    echo '<thead><tr>';
                    echo '<th>Full Name</th>';
                    echo '<th>Room Name</th>';
                    echo '<th>Days</th>';
                    echo '<th>Start Time</th>';
                    echo '<th>End Time</th>';
                    echo '<th>Booking Date</th>';
                    echo '<th>Status</th>';
                    echo '<th>Actions</th>';
                    echo '</tr></thead><tbody>';

                    while ($row = $result->fetch_assoc()) {
                        // Get the booking date and time
                        $booking_date = $row['days'];
                        $booking_end_time = $row['end_time'];
                        $current_date = date('Y-m-d');
                        
                        // Convert dates for comparison
                        $booking_timestamp = strtotime($booking_date . ' ' . $booking_end_time);
                        $current_timestamp = strtotime($current_date);
                        
                        // Show buttons if booking is in the future
                        $show_button = $booking_timestamp > $current_timestamp;

                        // Get status class for styling
                        $status_class = '';
                        switch($row['booking_status']) {
                            case 'Used':
                                $status_class = 'text-success';
                                break;
                            case 'Cancelled':
                                $status_class = 'text-danger';
                                break;
                            case 'Overridden':
                                $status_class = 'text-warning';
                                break;
                            default:
                                $status_class = 'text-info';
                        }

                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['room_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['days']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['start_time']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['end_time']) . "</td>";
                        echo "<td>" . date('M d, Y', strtotime($row['booking_date'])) . "</td>";
                        echo "<td class='{$status_class}'>" . htmlspecialchars($row['booking_status']) . "</td>";

                        // Only show action buttons if status is 'Pending'
                        if ($row['booking_status'] === 'Pending') {
                            echo "<td class='action-buttons'>";
                            echo "<button class='done-btn btn btn-done btn-sm' data-booking-id='" . $row['id'] . "'>Done with reservation</button>";
                            echo "<button class='cancel-btn btn btn-cancel btn-sm' data-booking-id='" . $row['id'] . "'>Cancel</button>";
                            echo "</td>";
                        } else {
                            echo "<td>No actions available</td>";
                        }

                        echo "</tr>";
                    }

                    echo '</tbody></table>';
                    echo '</div>';
                } else {
                    echo "<div class='alert alert-warning'>No booking history found.</div>";
                }
            }
        } else {
            echo "<div class='alert alert-warning'>You need to log in to view your booking history.</div>";
        }

        // Close the database connection at the end
        $conn->close();
        ?>
    </div>

<!-- Success Popup -->
<div class="success-popup" id="successPopup">
    <i class="fas fa-check-circle"></i>
    <span id="successMessage">Action completed successfully!</span>
</div>

<!-- Modal for showing cancellation status -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">Booking Cancellation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="cancelModalMessage">
                <!-- Cancellation message will be displayed here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        // Handle Done button click
        $('.done-btn').click(function() {
            var bookingId = $(this).data('booking-id');
            if (confirm('Have you completed this booking?')) {
                $.ajax({
                    url: 'update_booking_status.php',
                    method: 'POST',
                    data: {
                        booking_id: bookingId,
                        status: 'Done'
                    },
                    success: function(response) {
                        alert('Booking marked as completed!');
                        location.reload();
                    },
                    error: function() {
                        alert('Error updating booking status');
                    }
                });
            }
        });

        // Handle Cancel button click
        $('.cancel-btn').click(function() {
            var bookingId = $(this).data('booking-id');
            if (confirm('Are you sure you want to cancel this booking?')) {
                $.ajax({
                    url: 'cancel_booking.php',
                    method: 'POST',
                    data: {
                        booking_id: bookingId
                    },
                    success: function(response) {
                        try {
                            var result = typeof response === 'string' ? JSON.parse(response) : response;
                            if (result.success) {
                                // Show success message and reload
                                alert('Booking has been cancelled successfully!');
                                location.reload();
                            } else {
                                // Show error message from server
                                alert(result.message || 'Error cancelling booking');
                            }
                        } catch (e) {
                            // If JSON parsing fails, show generic error
                            console.error('Error parsing response:', e);
                            alert('Error processing server response');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        alert('Error connecting to server. Please try again.');
                    }
                });
            }
        });
    });
</script>
</body>
</html>
<?php include 'footer.php'; ?>