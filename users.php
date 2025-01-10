
<?php
session_start();
require_once 'database.php';

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

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'update') {
        $userId = $_POST['user_id'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $fullname = $_POST['fullname'];
        $status = $_POST['status'];
        
        // Update user in database
        $sql = "UPDATE users SET username = ?, email = ?, role = ?, fullname = ?, status = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssii", $username, $email, $role, $fullname, $status, $userId);
        
        if ($stmt->execute()) {
            // If the updated user is the current logged-in user, update their session
            if ($_SESSION['user_id'] == $userId) {
                $_SESSION['username'] = $username;
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'User updated successfully',
                'isCurrentUser' => ($_SESSION['user_id'] == $userId),
                'newUsername' => $username
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating user']);
        }
        exit();
    }
    
    if ($_POST['action'] === 'delete') {
        $userId = $_POST['user_id'];
        
        // Don't allow admin to delete themselves
        if ($userId == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'You cannot delete your own account']);
            exit();
        }
        
        // Delete user from database
        $sql = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting user: ' . $conn->error]);
        }
        exit();
    }
    
    if ($_POST['action'] === 'add') {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $fullname = $_POST['fullname'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $status = 1; // Active status
        
        // Check if username already exists
        $check = $conn->prepare("SELECT username FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Username already exists']);
            exit();
        }
        
        // Insert new user
        $sql = "INSERT INTO users (username, email, password_hash, role, fullname, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $username, $email, $password, $role, $fullname, $status);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding user: ' . $conn->error]);
        }
        exit();
    }
}

// Handle user addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $status = 1;

    // Check if username exists
    $check_query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Username already exists!";
    } else {
        // Insert new user
        $insert_query = "INSERT INTO users (username, email, password_hash, role, fullname, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("sssssi", $username, $email, $password, $role, $fullname, $status);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "User added successfully!";
        } else {
            $_SESSION['error'] = "Error adding user: " . $conn->error;
        }
    }
    header("Location: users.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        // Get form data
        $newUsername = mysqli_real_escape_string($conn, $_POST['username']);
        $newEmail = mysqli_real_escape_string($conn, $_POST['email']);
        $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $newRole = mysqli_real_escape_string($conn, $_POST['role']);
        $newFullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $status = 1; // Active status

        // Check if username already exists
        $checkUser = "SELECT * FROM users WHERE username = '$newUsername'";
        $result = mysqli_query($conn, $checkUser);

        if (mysqli_num_rows($result) > 0) {
            $_SESSION['error'] = "Username already exists!";
        } else {
            // Insert new user
            $insertQuery = "INSERT INTO users (username, email, password_hash, role, fullname, status) 
                          VALUES ('$newUsername', '$newEmail', '$newPassword', '$newRole', '$newFullname', $status)";
            
            if (mysqli_query($conn, $insertQuery)) {
                $_SESSION['success'] = "User added successfully!";
            } else {
                $_SESSION['error'] = "Error adding user: " . mysqli_error($conn);
            }
        }
        header("Location: users.php");
        exit();
    }
}

// Get search term if provided
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Get all users with pagination and search
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Modify query to include search
$searchCondition = '';
if ($search) {
    $searchCondition = " WHERE fullname LIKE '%$search%' 
                        OR username LIKE '%$search%' 
                        OR email LIKE '%$search%' 
                        OR role LIKE '%$search%'";
}

$query = "SELECT user_id, fullname, username, email, role, status 
          FROM users" . $searchCondition . 
          " ORDER BY user_id DESC LIMIT $offset, $perPage";
$result = mysqli_query($conn, $query);

// Get total number of users for pagination (with search condition)
$countQuery = "SELECT COUNT(*) as total FROM users" . $searchCondition;
$countResult = mysqli_query($conn, $countQuery);
$totalRecords = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRecords / $perPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - SALCC Easy Reserve</title>
    
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
            height: calc(100vh - 180px);
            overflow-y: auto;
            z-index: 1;
        }

        .sidebar-logo {
            text-align: center;
            padding: 20px;
            background-color: white;
            margin: 0 15px 20px;
            border-radius: 5px;
        }

        .sidebar-logo img {
            width: 150px;
            height: auto;
        }

        .sidebar h2 {
            padding: 0 20px;
            margin: 20px 0;
            color: #ecf0f1;
            font-size: 24px;
            text-align: center;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            padding: 12px 20px;
            border-left: 4px solid transparent;
            transition: all 0.3s;
        }

        .sidebar-menu li:hover,
        .sidebar-menu li.active {
            background-color: #444444;
            border-left-color: #0e463f;
        }

        .sidebar-menu a {
            color: #ecf0f1;
            text-decoration: none;
            font-size: 16px;
            display: block;
            width: 100%;
            height: 100%;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 30px;
            min-width: 0; /* Allow content to shrink */
            padding-bottom: 100px;
            position: relative;
            z-index: 1;
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
            .menu-toggle {
                display: block;
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
            /* Add overlay when sidebar is active */
            .overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 999;
            }
            .overlay.active {
                display: block;
            }
        }

        @media screen and (max-width: 480px) {
            .sidebar {
                width: 85%;
                max-width: 300px;
                background-color: #2c3e50;
            }
            .sidebar-logo {
                padding: 15px;
                margin: 0 10px 15px;
            }
            .sidebar-logo img {
                width: 120px;
            }
            .sidebar h2 {
                font-size: 20px;
                margin: 15px 0;
            }
            .sidebar-menu {
                margin-top: 20px;
            }
            .sidebar-menu li {
                padding: 12px 15px;
            }
            .sidebar-menu a {
                font-size: 15px;
            }
            .menu-toggle {
                top: 120px;
                left: 10px;
                height: 32px;
                width: 32px;
                padding: 6px;
            }
            /* Enhance active state */
            .sidebar.active {
                transform: translateX(0);
                box-shadow: 3px 0 15px rgba(0, 0, 0, 0.3);
            }
            .sidebar-menu li.active {
                background-color: #34495e;
                border-left-color: #3498db;
            }
        }
        
        /* Table Styles */
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .users-table th,
        .users-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .users-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
            margin-top: 20px;
            background: white;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Column Widths */
        .users-table th:nth-child(1), /* ID */
        .users-table td:nth-child(1) {
            width: 5%;
            min-width: 50px;
        }

        .users-table th:nth-child(2), /* Full Name */
        .users-table td:nth-child(2) {
            width: 20%;
            min-width: 150px;
        }

        .users-table th:nth-child(3), /* Username */
        .users-table td:nth-child(3) {
            width: 15%;
            min-width: 120px;
        }

        .users-table th:nth-child(4), /* Email */
        .users-table td:nth-child(4) {
            width: 25%;
            min-width: 200px;
        }

        .users-table th:nth-child(5), /* Role */
        .users-table td:nth-child(5) {
            width: 10%;
            min-width: 100px;
        }

        .users-table th:nth-child(6), /* Status */
        .users-table td:nth-child(6) {
            width: 10%;
            min-width: 80px;
        }

        .users-table th:nth-child(7), /* Actions */
        .users-table td:nth-child(7) {
            width: 15%;
            min-width: 120px;
        }

        @media screen and (max-width: 1024px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
                width: 100%;
            }
            
            .table-container {
                margin: 20px -20px;
                width: calc(100% + 40px);
                border-radius: 0;
            }
        }

        @media screen and (max-width: 768px) {
            .users-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .search-container {
                width: 100%;
            }
            
            .search-container input {
                width: calc(100% - 40px);
            }

            .add-user-btn {
                width: 100%;
                margin-top: 10px;
            }

            .users-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
                font-size: 14px;
            }

            .users-table th,
            .users-table td {
                padding: 10px;
            }

            .modal-content {
                width: 90%;
                margin: 20px auto;
                padding: 15px;
            }

            .form-group {
                margin-bottom: 15px;
            }

            .form-group label {
                display: block;
                margin-bottom: 5px;
            }

            .form-group input,
            .form-group select {
                width: 100%;
            }
        }

        @media screen and (max-width: 480px) {
            .admin-container {
                margin-top: 100px;
            }

            .main-content {
                padding: 15px;
                margin-top: 20%;
            }

            .users-table {
                font-size: 13px;
            }

            .users-table th,
            .users-table td {
                padding: 8px;
            }

            .action-buttons {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }

            .edit-btn,
            .delete-btn {
                padding: 5px 10px;
                width: 100%;
                text-align: center;
            }

            .pagination {
                flex-wrap: wrap;
                justify-content: center;
                gap: 5px;
            }

            .pagination a {
                padding: 5px 10px;
                margin: 2px;
            }

            .menu-toggle {
                left: 15px;
                top: 145px;
                height: 30px;
                width: 30px;
                padding: 6px;
            }

            /* Make table scrollable horizontally on very small screens */
            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                margin: 0 -15px;
                padding: 0 15px;
            }

            /* Stack form fields in add/edit modals */
            .modal-content .form-group {
                margin-bottom: 20px;
            }

            .modal-buttons {
                flex-direction: column;
                gap: 10px;
            }

            .modal-buttons button {
                width: 100%;
                margin: 0;
            }

            .action-buttons {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }

            .action-btn {
                width: 100%;
                justify-content: center;
            }
        }
        
        /* Search Box Styles */
        .search-container {
            position: relative;
            margin: 20px 0;
            display: flex;
            justify-content: flex-end;
        }

        .search-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-box input[type="text"] {
            padding: 8px 35px 8px 35px;
            border: 1px solid #ddd;
            border-radius: 20px;
            font-size: 14px;
            width: 200px;
            transition: all 0.3s ease;
        }

        .search-box input[type="text"]:focus {
            width: 250px;
            border-color: #0e463f;
            outline: none;
            box-shadow: 0 0 5px rgba(14, 70, 63, 0.2);
        }

        .search-box .fa-search {
            position: absolute;
            left: 12px;
            color: #666;
        }

        .clear-search {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            display: none;
            padding: 0;
        }

        .search-box.has-value .clear-search {
            display: block;
        }

        @media screen and (max-width: 768px) {
            .search-container {
                justify-content: center;
                margin: 10px 0;
            }

            .search-box input[type="text"],
            .search-box input[type="text"]:focus {
                width: 100%;
                max-width: 300px;
            }
        }

        /* Action Buttons */
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 5px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .edit-btn {
            background-color: #0e463f;
            color: white;
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
        }

        .edit-btn:hover {
            background-color: #0a2e29;
            transform: translateY(-1px);
        }

        .delete-btn:hover {
            background-color: #c82333;
            transform: translateY(-1px);
        }

        /* Modal Styles */
        .modal, .edit-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow-y: auto;
        }

        .modal-content, .edit-modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 50%;
            max-width: 500px;
            position: relative;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        @media screen and (max-width: 768px) {
            .modal-content, .edit-modal-content {
                width: 90%;
                margin: 20px auto;
                padding: 15px;
            }

            .form-group input,
            .form-group select {
                width: 100%;
                padding: 8px;
            }

            .search-container input[type="search"]::-webkit-search-cancel-button {
                height: 10px;
                width: 10px;
            }
        }

        /* Status Badge Styles */
        .status-badge {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Add User Button */
        .add-user-btn {
            width: 32px;
            height: 32px;
            background-color: #0e463f;
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 10px;
        }

        .add-user-btn:hover {
            background-color: #0a2e29;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /* Notification Styles */
        .notification {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 4px;
            color: white;
            z-index: 1001;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease-out;
        }

        .notification.success {
            background-color: #28a745;
        }

        .notification.error {
            background-color: #dc3545;
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

        /* Responsive Styles for Modals */
        @media screen and (max-width: 768px) {
            .search-container input[type="search"]::-webkit-search-cancel-button {
                height: 10px;
                width: 10px;
            }

            .modal-content, .edit-modal-content {
                width: 90%;
                margin: 20px auto;
                padding: 15px;
            }

            .form-group input,
            .form-group select {
                width: 100%;
                padding: 8px;
            }

            .modal-buttons {
                flex-direction: column;
            }

            .modal-buttons button {
                width: 100%;
                margin: 5px 0;
            }

            .action-buttons {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }

            .action-btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media screen and (max-width: 480px) {
            .modal-content {
                margin: 5% auto;
                padding: 15px;
            }

            .modal h3 {
                font-size: 1.2rem;
            }

            .form-group {
                margin-bottom: 15px;
            }

            .form-group input,
            .form-group select {
                font-size: 13px;
            }

            .notification {
                width: 90%;
                left: 50%;
                transform: translateX(-50%);
                text-align: center;
            }
        }
        
        /* Search Bar X Button */
        .search-container input[type="search"]::-webkit-search-cancel-button {
            -webkit-appearance: none;
            height: 12px;
            width: 12px;
            background: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23777'><path d='M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z'/></svg>") no-repeat 50% 50%;
            background-size: contain;
            cursor: pointer;
        }

        @media screen and (max-width: 768px) {
            .search-container input[type="search"]::-webkit-search-cancel-button {
                height: 10px;
                width: 10px;
            }
        }
        
        /* Action Buttons */
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 5px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .edit-btn {
            background-color: #0e463f;
            color: white;
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
        }

        .edit-btn:hover {
            background-color: #0a2e29;
            transform: translateY(-1px);
        }

        .delete-btn:hover {
            background-color: #c82333;
            transform: translateY(-1px);
        }

        /* Responsive Styles for Delete Button */
        @media screen and (max-width: 768px) {
            .action-btn {
                width: 100%;
                margin-bottom: 5px;
                justify-content: center;
            }

            .edit-btn, .delete-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 8px;
                font-size: 14px;
            }

            td.actions {
                display: flex;
                flex-direction: column;
                gap: 5px;
                padding: 10px;
            }

            .action-btn i {
                margin-right: 5px;
            }
        }

        /* Pagination Styles */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 20px 0;
            flex-wrap: wrap;
            gap: 5px;
        }

        .pagination a {
            color: #2c3e50;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            margin: 0 4px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background-color: #2c3e50;
            color: white;
        }

        .pagination a.active {
            background-color: #2c3e50;
            color: white;
            border-color: #2c3e50;
        }

        @media screen and (max-width: 768px) {
            .pagination {
                padding: 10px;
            }
            
            .pagination a {
                padding: 6px 12px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <button class="menu-toggle" onclick="toggleSidebar()">⚙️</button>
    <div class="admin-container">
        <div class="sidebar">
            <div class="sidebar-logo">
                <img src="https://elearn.salcc.edu.lc/pluginfile.php/1/theme_academi/logo/1729725012/salcc_black.png" alt="SALCC Logo">
            </div>
            <h2>Admin Panel</h2>
            <ul class="sidebar-menu">
                <li><a href="admin.php">Dashboard</a></li>
                <li class="active"><a href="users.php">Users</a></li>
                <li><a href="bookings.php">Bookings</a></li>
                <li><a href="messages.php">Messages</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="search-container">
                <form class="search-box <?php echo $search ? 'has-value' : ''; ?>" method="GET" action="">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" id="searchInput" placeholder="Search users..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="button" class="clear-search" onclick="clearSearch()">
                        <i class="fas fa-times"></i>
                    </button>
                </form>
            </div>
            <div class="users-container">
                <div class="users-header">
                    <h2>User Management</h2>
                    <button class="add-user-btn" onclick="openAddUserModal()" title="Add User">+</button>
                </div>

                <div class="table-container">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = mysqli_fetch_assoc($result)): ?>
                            <tr data-user-id="<?php echo $user['user_id']; ?>">
                                <td><?php echo $user['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['role']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $user['status'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $user['status'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <button class="action-btn edit-btn" onclick="editUser(<?php echo $user['user_id']; ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="action-btn delete-btn" onclick="deleteUser(<?php echo $user['user_id']; ?>)">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo ($page-1); ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>">&laquo; Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" 
                           class="<?php echo $page == $i ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo ($page+1); ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification -->
    <div id="notification" class="notification">
        <span class="notification-message"></span>
        <span class="notification-close" onclick="closeNotification()">&times;</span>
    </div>

    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Delete</h3>
            <p>Are you sure you want to delete this user? This action cannot be undone.</p>
            <div class="modal-buttons">
                <button class="cancel-delete" onclick="closeDeleteModal()">Cancel</button>
                <button class="confirm-delete" onclick="confirmDelete()">Delete</button>
            </div>
        </div>
    </div>

    <div id="editModal" class="edit-modal">
        <div class="edit-modal-content">
            <h3>Edit User</h3>
            <form class="edit-form" id="editForm">
                <input type="hidden" id="editUserId" name="user_id">
                <div class="form-group">
                    <label for="editFullname">Full Name</label>
                    <input type="text" id="editFullname" name="fullname" required>
                </div>
                <div class="form-group">
                    <label for="editUsername">Username</label>
                    <input type="text" id="editUsername" name="username" required>
                </div>
                <div class="form-group">
                    <label for="editEmail">Email</label>
                    <input type="email" id="editEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="editRole">Role</label>
                    <select id="editRole" name="role" required>
                        <option value="student">Student</option>
                        <option value="lecturer">Lecturer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editStatus">Status</label>
                    <select id="editStatus" name="status" required>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="cancel-delete" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="save-btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <h3>Add New User</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" name="fullname" required>
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="student">Student</option>
                        <option value="lecturer">Lecturer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="cancel-btn" onclick="closeAddUserModal()">Cancel</button>
                    <button type="submit" name="add_user" class="submit-btn">Add User</button>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('sidebar-active');
        }

        // Close sidebar when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            const menuToggle = document.querySelector('.menu-toggle');
            
            if (window.innerWidth <= 768 && 
                sidebar.classList.contains('active') && 
                !sidebar.contains(event.target) && 
                !menuToggle.contains(event.target)) {
                
                sidebar.classList.remove('active');
                mainContent.classList.remove('sidebar-active');
            }
        });

        // Function to open add user modal
        function openAddUserModal() {
            document.getElementById('addUserModal').style.display = 'block';
        }

        // Function to close add user modal
        function closeAddUserModal() {
            document.getElementById('addUserModal').style.display = 'none';
        }

        // Function to edit user
        function editUser(userId) {
            // Get user data from the table row
            const row = document.querySelector(`tr[data-user-id="${userId}"]`);
            const fullname = row.children[1].textContent;
            const username = row.children[2].textContent;
            const email = row.children[3].textContent;
            const role = row.children[4].textContent;
            const status = row.querySelector('.status-badge').classList.contains('status-active') ? 1 : 0;

            // Populate edit modal with user data
            document.getElementById('editUserId').value = userId;
            document.getElementById('editFullname').value = fullname;
            document.getElementById('editUsername').value = username;
            document.getElementById('editEmail').value = email;
            document.getElementById('editRole').value = role;
            document.getElementById('editStatus').value = status;

            // Show edit modal
            document.getElementById('editModal').style.display = 'block';
        }

        // Function to close edit modal
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Function to delete user
        function deleteUser(userId) {
            // Store the user ID to be deleted
            document.getElementById('delete_user_id').value = userId;
            // Show delete confirmation modal
            document.getElementById('deleteModal').style.display = 'block';
        }

        // Function to close delete modal
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Function to confirm user deletion
        function confirmDelete() {
            const userId = document.getElementById('delete_user_id').value;
            
            // Send delete request
            fetch('users.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete&user_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the user row from the table
                    document.querySelector(`tr[data-user-id="${userId}"]`).remove();
                    showNotification('User deleted successfully', 'success');
                } else {
                    showNotification(data.message || 'Error deleting user', 'error');
                }
                closeDeleteModal();
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error deleting user', 'error');
                closeDeleteModal();
            });
        }

        // Function to save edited user
        function saveUser() {
            const userId = document.getElementById('editUserId').value;
            const username = document.getElementById('editUsername').value;
            const email = document.getElementById('editEmail').value;
            const role = document.getElementById('editRole').value;
            const fullname = document.getElementById('editFullname').value;
            const status = document.getElementById('editStatus').value;

            // Send update request
            fetch('users.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update&user_id=${userId}&username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}&role=${encodeURIComponent(role)}&fullname=${encodeURIComponent(fullname)}&status=${status}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the table row
                    const row = document.querySelector(`tr[data-user-id="${userId}"]`);
                    row.children[1].textContent = fullname;
                    row.children[2].textContent = username;
                    row.children[3].textContent = email;
                    row.children[4].textContent = role;
                    const statusBadge = row.querySelector('.status-badge');
                    statusBadge.textContent = status == 1 ? 'Active' : 'Inactive';
                    statusBadge.className = `status-badge ${status == 1 ? 'status-active' : 'status-inactive'}`;

                    showNotification('User updated successfully', 'success');
                    
                    // If current user was updated, update the display name
                    if (data.isCurrentUser) {
                        document.getElementById('userDisplayName').textContent = data.newUsername;
                    }
                } else {
                    showNotification(data.message || 'Error updating user', 'error');
                }
                closeEditModal();
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error updating user', 'error');
                closeEditModal();
            });
        }

        // Function to show notification
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            notification.className = `notification ${type}`;
            notification.querySelector('.notification-message').textContent = message;
            notification.style.display = 'block';
            
            // Hide after 3 seconds
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }

        // Function to clear search
        function clearSearch() {
            document.getElementById('searchInput').value = '';
            document.querySelector('.search-box').classList.remove('has-value');
            window.location.href = 'users.php';
        }

        // Add input event listener to search box
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchBox = document.querySelector('.search-box');
            if (this.value) {
                searchBox.classList.add('has-value');
            } else {
                searchBox.classList.remove('has-value');
            }
        });

        // Handle edit form submission
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            saveUser();
        });

        // Close modals when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addUserModal');
            const editModal = document.getElementById('editModal');
            const deleteModal = document.getElementById('deleteModal');
            
            if (event.target == addModal) {
                closeAddUserModal();
            }
            if (event.target == editModal) {
                closeEditModal();
            }
            if (event.target == deleteModal) {
                closeDeleteModal();
            }
        }
    </script>

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <h3>Add New User</h3>
            <form method="POST" action="users.php">
                <input type="hidden" name="add_user" value="1">
                <div class="form-group">
                    <label for="fullname">Full Name:</label>
                    <input type="text" id="fullname" name="fullname" required>
                </div>
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="role">Role:</label>
                    <select id="role" name="role" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="save-btn">Add User</button>
                    <button type="button" class="cancel-btn" onclick="closeAddUserModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Edit User</h3>
            <input type="hidden" id="edit_user_id">
            <div class="form-group">
                <label for="edit_fullname">Full Name:</label>
                <input type="text" id="edit_fullname" required>
            </div>
            <div class="form-group">
                <label for="edit_username">Username:</label>
                <input type="text" id="edit_username" required>
            </div>
            <div class="form-group">
                <label for="edit_email">Email:</label>
                <input type="email" id="edit_email" required>
            </div>
            <div class="form-group">
                <label for="edit_role">Role:</label>
                <select id="edit_role" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label for="edit_status">Status:</label>
                <select id="edit_status" required>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div class="modal-buttons">
                <button onclick="saveUser()" class="save-btn">Save Changes</button>
                <button onclick="closeEditModal()" class="cancel-btn">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Delete</h3>
            <p>Are you sure you want to delete this user? This action cannot be undone.</p>
            <input type="hidden" id="delete_user_id">
            <div class="modal-buttons">
                <button onclick="confirmDelete()" class="delete-btn">Delete</button>
                <button onclick="closeDeleteModal()" class="cancel-btn">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Notification -->
    <div id="notification" class="notification" style="display: none;"></div>
</body>
<?php include 'footer.php'; ?>
</html>