<?php
session_start();

// Add success/error message handling for mass delete
if (isset($_GET['success']) && $_GET['success'] == 1) {
    echo '<div class="alert success">All bookings have been reset successfully!</div>';
}
if (isset($_GET['error']) && $_GET['error'] == 1) {
    echo '<div class="alert error">An error occurred while resetting bookings.</div>';
}

require_once 'database.php';
require_once 'admin/functions.php';

// Handle AJAX delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $response = array('success' => false, 'message' => '');
    
    if (!isset($_POST['booking_id']) || !isset($_POST['room_type'])) {
        $response['message'] = 'Missing required parameters';
        echo json_encode($response);
        exit;
    }
    
    $booking_id = $_POST['booking_id'];
    $room_type = $_POST['room_type'];
    
    // Determine which table to update based on room_type
    $table = $room_type; // e.g., bus_rooms, dtems_rooms, etc.
    
    $query = "UPDATE $table SET user_id = NULL, status = 1 WHERE room_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $booking_id);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Booking cancelled successfully';
    } else {
        $response['message'] = 'Error cancelling booking: ' . $conn->error;
    }
    
    echo json_encode($response);
    exit;
}

// Function to check if the user is logged in
function isLoggedIn()
{
    return isset($_SESSION['username']);
}

// Function to get the logged-in user's name
function getUserName()
{
    return isset($_SESSION['username']) ? $_SESSION['username'] : '';
}

// Check if user is logged in and is admin
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=unauthorized");
    exit();
}

// Get search term if provided
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get current page number
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 10; // Number of bookings per page
$offset = ($page - 1) * $itemsPerPage;

// Get all users
$users = getAllUsers();
if (!$users) {
    die("Error fetching users");
}

// Create a lookup array for user details
$userLookup = array();
foreach ($users as $user) {
    $userLookup[$user['user_id']] = $user;
}

// Count total bookings query
$countQuery = "
    SELECT COUNT(*) as total FROM (
        SELECT room_id FROM bus_rooms WHERE user_id IS NOT NULL
        " . ($search ? "AND (room_name LIKE ? OR days LIKE ? OR status LIKE ?)" : "") . "
        UNION ALL
        SELECT room_id FROM dtems_rooms WHERE user_id IS NOT NULL
        " . ($search ? "AND (room_name LIKE ? OR days LIKE ? OR status LIKE ?)" : "") . "
        UNION ALL
        SELECT room_id FROM var_rooms WHERE user_id IS NOT NULL
        " . ($search ? "AND (room_name LIKE ? OR days LIKE ? OR status LIKE ?)" : "") . "
        UNION ALL
        SELECT room_id FROM otw_rooms WHERE user_id IS NOT NULL
        " . ($search ? "AND (room_name LIKE ? OR days LIKE ? OR status LIKE ?)" : "") . "
        UNION ALL
        SELECT room_id FROM lft_rooms WHERE user_id IS NOT NULL
        " . ($search ? "AND (room_name LIKE ? OR days LIKE ? OR status LIKE ?)" : "") . "
        UNION ALL
        SELECT room_id FROM bed_rooms WHERE user_id IS NOT NULL
        " . ($search ? "AND (room_name LIKE ? OR days LIKE ? OR status LIKE ?)" : "") . "
    ) as combined_bookings";

$stmt = $conn->prepare($countQuery);
if ($search) {
    $searchParam = "%$search%";
    $params = array();
    for ($i = 0; $i < 18; $i++) { // 6 tables * 3 search conditions
        $params[] = $searchParam;
    }
    $types = str_repeat("s", count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$totalResult = $stmt->get_result()->fetch_assoc();
$totalBookings = $totalResult['total'];
$totalPages = ceil($totalBookings / $itemsPerPage);

// Fetch bookings from all tables with search condition and pagination
$bookingsQuery = "
    SELECT 
        'BUS' as building,
        room_id,
        room_name,
        days,
        start_time,
        end_time,
        status,
        user_id,
        class_block_id
    FROM bus_rooms 
    WHERE user_id IS NOT NULL
    " . ($search ? "AND (room_name LIKE ? OR days LIKE ? OR status LIKE ?)" : "") . "

    UNION ALL

    SELECT 
        'DTEMS' as building,
        room_id,
        room_name,
        days,
        start_time,
        end_time,
        status,
        user_id,
        class_block_id
    FROM dtems_rooms 
    WHERE user_id IS NOT NULL
    " . ($search ? "AND (room_name LIKE ? OR days LIKE ? OR status LIKE ?)" : "") . "

    UNION ALL

    SELECT 
        'VAR' as building,
        room_id,
        room_name,
        days,
        start_time,
        end_time,
        status,
        user_id,
        class_block_id
    FROM var_rooms 
    WHERE user_id IS NOT NULL
    " . ($search ? "AND (room_name LIKE ? OR days LIKE ? OR status LIKE ?)" : "") . "

    UNION ALL

    SELECT 
        'OTW' as building,
        room_id,
        room_name,
        days,
        start_time,
        end_time,
        status,
        user_id,
        class_block_id
    FROM otw_rooms 
    WHERE user_id IS NOT NULL
    " . ($search ? "AND (room_name LIKE ? OR days LIKE ? OR status LIKE ?)" : "") . "

    UNION ALL

    SELECT 
        'LFT' as building,
        room_id,
        room_name,
        days,
        start_time,
        end_time,
        status,
        user_id,
        class_block_id
    FROM lft_rooms 
    WHERE user_id IS NOT NULL
    " . ($search ? "AND (room_name LIKE ? OR days LIKE ? OR status LIKE ?)" : "") . "

    UNION ALL

    SELECT 
        'BED' as building,
        room_id,
        room_name,
        days,
        start_time,
        end_time,
        status,
        user_id,
        class_block_id
    FROM bed_rooms 
    WHERE user_id IS NOT NULL
    " . ($search ? "AND (room_name LIKE ? OR days LIKE ? OR status LIKE ?)" : "") . "

    ORDER BY building, room_name
    LIMIT ? OFFSET ?";

$stmt = $conn->prepare($bookingsQuery);
if ($search) {
    $searchParam = "%$search%";
    $params = array();
    for ($i = 0; $i < 18; $i++) { // 6 tables * 3 search conditions
        $params[] = $searchParam;
    }
    $params[] = $itemsPerPage;
    $params[] = $offset;
    $types = str_repeat("s", count($params) - 2) . "ii";
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $itemsPerPage, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

$bookings = array();

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        if (isset($userLookup[$row['user_id']])) {
            $bookings[] = array_merge($row, array(
                'username' => $userLookup[$row['user_id']]['username'],
                'email' => $userLookup[$row['user_id']]['email']
            ));
        }
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'delete') {
        $bookingId = $_POST['booking_id'];
        $roomType = $_POST['room_type'];
        
        // Determine the table name based on room type
        $tableName = $roomType;
        
        // Delete booking from database
        $sql = "UPDATE $tableName SET user_id = NULL, status = 1, WHERE room_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $bookingId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error cancelling booking: ' . $conn->error]);
        }
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings Management - SALCC Easy Reserve</title>
    <?php include 'header.php'; ?>
    <style>
        /* Main Layout */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }

        .admin-container {
            display: flex;
            min-height: calc(100vh - 180px); /* Adjusted to account for footer */
            margin-top: 120px;
            position: relative;
            z-index: 1;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: #333333;  
            color: #fff;
            padding-top: 40px;
            position: fixed;
            top: 120px;
            height: calc(100vh - 180px); /* Adjusted to account for footer */
            overflow-y: auto;
            z-index: 1;
        }

        .sidebar-logo {
            text-align: center;
            margin-bottom: 20px;
            padding: 20px 20px 0;
            background-color: white;
            margin: 0 20px 20px;
            border-radius: 5px;
        }

        .sidebar-logo img {
            width: 150px;
            height: auto;
            margin-bottom: 25px;
        }

        .sidebar h2 {
            padding: 0 20px;
            margin: 30px 0;
            color: #ecf0f1;
            font-size: 24px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 15px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            padding: 15px 20px;
            border-left: 4px solid transparent;
            transition: all 0.3s;
        }

        .sidebar-menu li:hover {
            background-color: #444444;
            border-left-color: #0e463f;
        }

        .sidebar-menu li.active {
            background-color: #444444;
            border-left-color: #0e463f;
        }

        .sidebar-menu a {
            color: #ecf0f1;
            text-decoration: none;
            font-size: 16px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 30px;
            padding-bottom: 100px; /* Increased bottom padding */
            position: relative;
            z-index: 1;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #0e463f;
            color: white;
            font-weight: bold;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        /* Button Styles */
        .action-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        /* Search Styles */
        .search-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
            padding: 0 20px;
        }

        .search-box {
            display: flex;
            align-items: center;
            background: white;
            border: 1px solid #ddd;
            border-radius: 20px;
            padding: 5px 15px;
            width: 300px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .search-box input {
            border: none;
            outline: none;
            padding: 5px;
            padding-right: 25px;
            width: 100%;
            font-size: 14px;
        }

        .search-box i.fa-search {
            color: #666;
            margin-right: 8px;
        }

        .clear-search {
            color: #999;
            cursor: pointer;
            font-size: 12px;
            background: none;
            border: none;
            padding: 0;
            margin-left: -25px;
            width: 20px;
            height: 20px;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .clear-search:hover {
            color: #666;
        }

        .search-box.has-value .clear-search {
            display: flex;
        }

        .search-box:focus-within {
            border-color: #0e463f;
            box-shadow: 0 2px 4px rgba(14,70,63,0.1);
        }

        /* Notification Styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 4px;
            color: white;
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 1000;
        }

        .notification.success {
            background-color: #28a745;
        }

        .notification.error {
            background-color: #dc3545;
        }

        .notification.show {
            opacity: 1;
        }

        /* Alert Styles */
        .alert {
            padding: 15px;
            margin: 20px;
            border-radius: 4px;
            text-align: center;
        }

        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

       
       

        /* Pagination Styles */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            margin-bottom: 80px; /* Added more space before footer */
            gap: 10px;
        }

        .pagination a, .pagination span {
            padding: 8px 12px;
            text-decoration: none;
            border: 1px solid #ddd;
            color: #333;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .pagination a:hover {
            background-color: #f5f5f5;
        }

        .pagination .active {
            background-color: #0e463f;
            color: white;
            border-color: #0e463f;
        }

        .pagination .disabled {
            color: #999;
            pointer-events: none;
        }

        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #2A9D8F;
            color: white;
            text-align: center;
            padding: 15px 0;
            z-index: 1000; /* Ensure footer is above admin panel */
        }

        /* Menu Toggle Button */
        .menu-toggle {
            display: none;
            position: fixed;
            top: 130px;
            left: 20px;
            z-index: 1001;
            background-color: #0066cc;
            color: white;
            border: none;
            padding: 8px;
            height: 35px;
            width: 35px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .menu-toggle:hover {
            background-color: #0052a3;
        }

        @media screen and (max-width: 768px) {
            .menu-toggle {
                display: block;
                margin-right: auto;
            }
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                width: 250px;
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
                background-color: #2c3e50;
                z-index: 1000;
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                width: 100%;
                transition: margin-left 0.3s ease;
            }
            .sidebar-menu li {
                padding: 15px 20px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }
            .sidebar-menu li:last-child {
                border-bottom: none;
            }
            .sidebar-menu a {
                font-size: 16px;
                display: block;
                width: 100%;
                padding: 0;
                color: #ecf0f1;
            }
            .sidebar-menu a:hover {
                color: #3498db;
            }
        }

        @media screen and (max-width: 480px) {
            .menu-toggle {
                left: 10px;
                top: 125px;
                height: 30px;
                width: 30px;
                padding: 6px;
            }
            .main-content {
                padding-left: 45px;
                width: calc(100% - 45px);
            }
        }

        /* Responsive Styles */
        @media screen and (max-width: 1024px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
                margin-left: 200px;
            }
        }

        @media screen and (max-width: 768px) {
            .sidebar {
                width: 250px;
            }
            .main-content {
                margin-left: 0;
                width: 100%;
                transition: margin-left 0.3s ease;
            }
            .main-content.sidebar-active {
                margin-left: 0;
            }
        }

        @media screen and (max-width: 480px) {
            .sidebar {
                width: 100%;
                max-width: 300px;
            }
            .sidebar-logo img {
                width: 120px;
            }
            .sidebar h2 {
                font-size: 20px;
            }
            .sidebar-menu a {
                font-size: 14px;
                padding: 12px 20px;
            }
            .bookings-table {
                font-size: 14px;
            }
            .menu-toggle {
                top: 120px;
            }
        }

        /* Responsive Styles for Mobile Devices */
        @media screen and (max-width: 768px) {
            .main-content {
                padding: 10px;
                margin-left: 0;
                margin-top: 60px;
            }

            .content-header {
                flex-direction: column;
                gap: 10px;
                padding: 10px;
            }

            .search-container {
                width: 100%;
                margin: 5px 0;
            }

            .search-container input[type="search"] {
                width: 100%;
                max-width: none;
            }

            .action-buttons {
                display: flex;
                gap: 5px;
                margin: 5px 0;
            }

            .action-buttons button {
                padding: 8px;
                font-size: 14px;
            }

            /* Table Responsive Styles */
            .bookings-table {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                margin: 5px 0;
            }

            table {
                width: 100%;
                min-width: 600px; /* Reduced from 800px */
                font-size: 14px;
            }

            th, td {
                padding: 8px;
                font-size: 13px;
            }

            .pagination {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 5px;
                padding: 10px;
            }

            .pagination a {
                padding: 6px 10px;
                font-size: 14px;
            }
        }

        /* Additional styles for very small devices */
        @media screen and (max-width: 380px) {
            .content-header h1 {
                font-size: 18px;
            }

            .action-buttons button {
                padding: 6px;
                font-size: 13px;
            }
        }

        /* Header Actions Styles */
        .header-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .reset-all-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .reset-all-btn:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }

        .reset-all-btn .icon {
            font-size: 18px;
            font-weight: bold;
        }

        .upload-schedule-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .upload-schedule-btn:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        .upload-schedule-btn .icon {
            font-size: 18px;
        }

        .random-status-btn {
            background-color: #17a2b8;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .random-status-btn:hover {
            background-color: #138496;
            transform: translateY(-2px);
        }

        .random-status-btn .icon {
            font-size: 18px;
        }

        #fileInput {
            display: none;
        }

        .schedule-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }

        .schedule-info h4 {
            color: #0e463f;
            margin-top: 0;
        }

        .schedule-info pre {
            background-color: #fff;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
            position: relative;
        }

        .modal-header {
            margin-bottom: 20px;
        }

        .modal-header h2 {
            color: #dc3545;
            margin: 0;
        }

        .modal-body {
            margin-bottom: 20px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .modal-btn {
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            font-weight: 500;
        }

        .modal-btn.confirm {
            background-color: #dc3545;
            color: white;
        }

        .modal-btn.cancel {
            background-color: #6c757d;
            color: white;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <button class="menu-toggle" onclick="toggleSidebar()">⚙️</button>
    <div class="admin-container">
        <div class="sidebar">
            <div class="sidebar-logo">
                <img src="https://elearn.salcc.edu.lc/pluginfile.php/1/theme_academi/logo/1729725012/salcc_black.png" alt="SALCC Logo">
            </div>
            <h2>Admin Panel</h2>
            <ul class="sidebar-menu">
                <li><a href="admin.php">Dashboard</a></li>
                <li><a href="users.php">Users</a></li>
                <li class="active"><a href="bookings.php">Bookings</a></li>
                <li><a href="messages.php">Messages</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="content-header">
                <h1>Bookings Management</h1>
                <div class="header-actions">
                    <button class="random-status-btn" onclick="confirmRandomStatus()">
                        <span class="icon">🎲</span>
                        Set Random Status
                    </button>
                    <button class="upload-schedule-btn" onclick="confirmScheduleUpload()">
                        <span class="icon">📅</span>
                        Upload Class Schedule
                    </button>
                    <button class="reset-all-btn" onclick="confirmResetAll()">
                        <span class="icon">-</span>
                        Reset All Rooms
                    </button>
                </div>
            </div>

            <div class="search-container">
                <form class="search-box <?php echo $search ? 'has-value' : ''; ?>" method="GET" action="">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" id="searchInput" placeholder="Search bookings..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="button" class="clear-search" onclick="clearSearch()">
                        <i class="fas fa-times"></i>
                    </button>
                </form>
            </div>

           

            <table>
                <thead>
                    <tr>
                        <th>Building</th>
                        <th>Room Name</th>
                        <th>Day</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No bookings found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr data-booking-id="<?php echo $booking['room_id']; ?>" data-building="<?php echo $booking['building']; ?>">
                                <td><?php echo htmlspecialchars($booking['building']); ?></td>
                                <td><?php echo htmlspecialchars($booking['room_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['days']); ?></td>
                                <td><?php echo htmlspecialchars($booking['start_time']); ?></td>
                                <td><?php echo htmlspecialchars($booking['end_time']); ?></td>
                                <td><?php echo htmlspecialchars($booking['status']); ?></td>
                                <td>
                                    <button class="action-btn delete-btn" onclick="deleteBooking('<?php echo $booking['room_id']; ?>', '<?php echo strtolower($booking['building']); ?>_rooms')">
                                        <i class="fas fa-trash"></i> Cancel
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination">
                <?php if ($totalPages > 1): ?>
                    <?php if ($page > 1): ?>
                        <a href="?page=1<?php echo $search ? '&search=' . urlencode($search) : ''; ?>">&laquo; First</a>
                        <a href="?page=<?php echo ($page - 1) . ($search ? '&search=' . urlencode($search) : ''); ?>">&lsaquo; Prev</a>
                    <?php else: ?>
                        <span class="disabled">&laquo; First</span>
                        <span class="disabled">&lsaquo; Prev</span>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    
                    if ($start > 1) {
                        echo '<span class="disabled">...</span>';
                    }
                    
                    for ($i = $start; $i <= $end; $i++) {
                        if ($i == $page) {
                            echo '<span class="active">' . $i . '</span>';
                        } else {
                            echo '<a href="?page=' . $i . ($search ? '&search=' . urlencode($search) : '') . '">' . $i . '</a>';
                        }
                    }
                    
                    if ($end < $totalPages) {
                        echo '<span class="disabled">...</span>';
                    }
                    ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo ($page + 1) . ($search ? '&search=' . urlencode($search) : ''); ?>">Next &rsaquo;</a>
                        <a href="?page=<?php echo $totalPages . ($search ? '&search=' . urlencode($search) : ''); ?>">Last &raquo;</a>
                    <?php else: ?>
                        <span class="disabled">Next &rsaquo;</span>
                        <span class="disabled">Last &raquo;</span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Delete Confirmation Modal -->
            <div id="deleteModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Confirm Cancellation</h2>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to cancel this booking?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="modal-btn cancel" onclick="closeDeleteModal()">No, Keep it</button>
                        <button class="modal-btn confirm" onclick="confirmDelete()">Yes, Cancel it</button>
                    </div>
                </div>
            </div>

            <!-- Reset Confirmation Modal -->
            <div id="resetModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>⚠️ Reset Room Availability</h2>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to reset all room availability? This will:</p>
                        <ul>
                            <li>Set all rooms back to available status</li>
                            <li>Remove all current user assignments from rooms</li>
                            <li>Allow rooms to be booked again for the next week</li>
                        </ul>
                        <p><strong>Note:</strong> This will not affect the booking history - all past booking records will be preserved.</p>
                        <p><strong>This action cannot be undone!</strong></p>
                    </div>
                    <div class="modal-footer">
                        <button class="modal-btn cancel" onclick="closeResetModal()">Cancel</button>
                        <button class="modal-btn confirm" onclick="resetAllBookings()">Reset All Rooms</button>
                    </div>
                </div>
            </div>

            <!-- Random Status Modal -->
            <div id="randomStatusModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>🎲 Set Random Status</h2>
                    </div>
                    <div class="modal-body">
                        <p>This will randomly set approximately 30% of room slots to unavailable (status = 0).</p>
                        <p>This is useful for:</p>
                        <ul>
                            <li>Testing the booking system</li>
                            <li>Simulating a class schedule</li>
                            <li>Demonstrating room availability</li>
                        </ul>
                        <p><strong>Note:</strong> Existing unavailable slots may be affected.</p>
                    </div>
                    <div class="modal-footer">
                        <button class="modal-btn cancel" onclick="closeRandomStatusModal()">Cancel</button>
                        <button class="modal-btn confirm" onclick="setRandomStatus()">Set Random Status</button>
                    </div>
                </div>
            </div>

            <!-- Schedule Upload Modal -->
            <div id="scheduleModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>📅 Upload Class Schedule</h2>
                    </div>
                    <div class="modal-body">
                        <p>Upload a CSV file containing the class schedule. This will mark rooms as unavailable (status = 0) for the specified times.</p>
                        
                        <div class="schedule-info">
                            <h4>CSV Format Requirements:</h4>
                            <p>The CSV file should have the following columns:</p>
                            <pre>room_name,days,start_time,end_time</pre>
                            <p>Example:</p>
                            <pre>room_name,days,start_time,end_time
BUS_101,Monday,09:00,10:30
DTEMS_LAB1,Tuesday,14:00,15:30</pre>
                        </div>

                        <input type="file" id="fileInput" accept=".csv" />
                        <button class="modal-btn confirm" onclick="document.getElementById('fileInput').click()">
                            Choose File
                        </button>
                        <span id="selectedFileName"></span>
                    </div>
                    <div class="modal-footer">
                        <button class="modal-btn cancel" onclick="closeScheduleModal()">Cancel</button>
                        <button class="modal-btn confirm" onclick="uploadSchedule()" id="uploadButton" disabled>
                            Upload Schedule
                        </button>
                    </div>
                </div>
            </div>

            <!-- Notification -->
            <div id="notification" class="notification">
                <span class="notification-message"></span>
                <span class="notification-close" onclick="closeNotification()">&times;</span>
            </div>
        </div>
    </div>

    <footer>
        &copy; 2023 SALCC Easy Reserve
    </footer>

    <script>
    let bookingIdToDelete = null;
    let roomTypeToDelete = null;

    function deleteBooking(bookingId, roomType) {
        bookingIdToDelete = bookingId;
        roomTypeToDelete = roomType;
        document.getElementById('deleteModal').style.display = 'block';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
        bookingIdToDelete = null;
        roomTypeToDelete = null;
    }

    function showNotification(message, isError = false) {
        const notification = document.getElementById('notification');
        const messageSpan = notification.querySelector('.notification-message');
        messageSpan.textContent = message;
        
        notification.style.display = 'block';
        notification.className = 'notification' + (isError ? ' error' : '');
        
        setTimeout(() => {
            closeNotification();
        }, 3000);
    }

    function closeNotification() {
        const notification = document.getElementById('notification');
        notification.style.display = 'none';
    }

    function confirmDelete() {
        if (!bookingIdToDelete || !roomTypeToDelete) return;

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('booking_id', bookingIdToDelete);
        formData.append('room_type', roomTypeToDelete);

        fetch('bookings.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showNotification('Booking cancelled successfully');
                
                // Close the modal
                closeDeleteModal();
                
                // Reload the page after a short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showNotification(data.message || 'Error cancelling booking', true);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error processing request', true);
        });
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modals = {
            'scheduleModal': closeScheduleModal,
            'resetModal': closeResetModal,
            'randomStatusModal': closeRandomStatusModal,
            'deleteModal': closeDeleteModal
        };

        for (let [modalId, closeFunction] of Object.entries(modals)) {
            if (event.target == document.getElementById(modalId)) {
                closeFunction();
            }
        }
    }

    function clearSearch() {
        document.getElementById('searchInput').value = '';
        document.querySelector('form').submit();
    }

    // Add event listener for real-time search
    document.getElementById('searchInput').addEventListener('input', function(e) {
        if (this.value.length >= 2 || this.value.length === 0) {
            document.querySelector('form').submit();
        }
    });

    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        sidebar.classList.toggle('active');
    }

    // Close sidebar when clicking outside
    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.sidebar');
        const menuToggle = document.querySelector('.menu-toggle');
        
        if (window.innerWidth <= 768 && 
            sidebar.classList.contains('active') && 
            !sidebar.contains(event.target) && 
            !menuToggle.contains(event.target)) {
            sidebar.classList.remove('active');
        }
    });

    function confirmResetAll() {
        document.getElementById('resetModal').style.display = 'block';
    }

    function closeResetModal() {
        document.getElementById('resetModal').style.display = 'none';
    }

    function resetAllBookings() {
        window.location.href = 'reset_all_bookings.php';
    }

    function confirmRandomStatus() {
        document.getElementById('randomStatusModal').style.display = 'block';
    }

    function closeRandomStatusModal() {
        document.getElementById('randomStatusModal').style.display = 'none';
    }

    function setRandomStatus() {
        fetch('set_random_status.php')
            .then(response => response.json())
            .then(data => {
                closeRandomStatusModal();
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                closeRandomStatusModal();
                showNotification('Error setting random status', 'error');
            });
    }

    function confirmScheduleUpload() {
        document.getElementById('scheduleModal').style.display = 'block';
    }

    function closeScheduleModal() {
        document.getElementById('scheduleModal').style.display = 'none';
        document.getElementById('fileInput').value = '';
        document.getElementById('selectedFileName').textContent = '';
        document.getElementById('uploadButton').disabled = true;
    }

    // Handle file selection
    document.getElementById('fileInput').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name;
        if (fileName) {
            document.getElementById('selectedFileName').textContent = fileName;
            document.getElementById('uploadButton').disabled = false;
        }
    });

    function uploadSchedule() {
        const fileInput = document.getElementById('fileInput');
        if (!fileInput.files[0]) return;

        const formData = new FormData();
        formData.append('scheduleFile', fileInput.files[0]);

        fetch('upload_class_schedule.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            closeScheduleModal();
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            closeScheduleModal();
            showNotification('Error uploading schedule', 'error');
        });
    }
    </script>
</body>
</html>
<?php include 'footer.php'; ?>