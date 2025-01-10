<?php
session_start();
require_once 'database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=unauthorized");
    exit();
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

// Function to generate a random color
function getRandomColor($seed) {
    // List of pleasant, professional colors
    $colors = [
        '#1abc9c', '#2ecc71', '#3498db', '#9b59b6', '#34495e',
        '#16a085', '#27ae60', '#2980b9', '#8e44ad', '#2c3e50',
        '#e74c3c', '#d35400', '#c0392b', '#f39c12', '#f1c40f'
    ];
    
    // Use the seed to consistently get the same color for the same name
    $index = hexdec(substr(md5($seed), 0, 8)) % count($colors);
    return $colors[$index];
}

// Handle marking messages as read/unread
if (isset($_POST['message_id']) && isset($_POST['action'])) {
    $message_id = $_POST['message_id'];
    $status = ($_POST['action'] === 'mark_read') ? 0 : 1;
    
    $updateQuery = "UPDATE contact_us SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ii", $status, $message_id);
    $stmt->execute();
}

// Get search term if any
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination settings
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 10; // Number of messages per page
$offset = ($page - 1) * $itemsPerPage;

// Count total messages
$countQuery = "SELECT COUNT(*) as total FROM contact_us WHERE 1=1";
if (!empty($search)) {
    $countQuery .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
}

$stmt = $conn->prepare($countQuery);
if (!empty($search)) {
    $searchParam = "%$search%";
    $stmt->bind_param("ssss", $searchParam, $searchParam, $searchParam, $searchParam);
}
$stmt->execute();
$totalResult = $stmt->get_result()->fetch_assoc();
$totalMessages = $totalResult['total'];
$totalPages = ceil($totalMessages / $itemsPerPage);

// Fetch messages with search condition and pagination
$messagesQuery = "SELECT * FROM contact_us WHERE 1=1";
if (!empty($search)) {
    $messagesQuery .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
}
$messagesQuery .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";

$stmt = $conn->prepare($messagesQuery);
if (!empty($search)) {
    $searchParam = "%$search%";
    $stmt->bind_param("ssssii", $searchParam, $searchParam, $searchParam, $searchParam, $itemsPerPage, $offset);
} else {
    $stmt->bind_param("ii", $itemsPerPage, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Admin Panel</title>
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

        /* Messages Table Styles */
        .messages-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .messages-header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }

        .search-container {
            display: flex;
            align-items: center;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 20px;
            padding: 5px 15px;
            width: 300px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            position: relative;
        }

        .search-container input {
            border: none;
            outline: none;
            padding: 8px;
            padding-right: 25px;
            width: 100%;
            font-size: 14px;
        }

        .search-container i {
            color: #666;
            margin-right: 8px;
        }

        .clear-search {
            position: absolute;
            right: 12px;
            top: 8px;
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            padding: 0;
            font-size: 12px;
            width: 16px;
            height: 16px;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .clear-search:hover {
            color: #666;
            background-color: #f0f0f0;
        }

        .search-container.has-value .clear-search {
            display: block;
        }

        .user-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            margin-right: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 20px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: 500;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .unread {
            background-color: #e8f4f8;
        }

        .message-content {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Action Buttons */
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
        }

        .read-btn {
            background-color: #0e463f;
            color: white;
        }

        .read-btn:hover {
            background-color: #0a332e;
        }

        .unread-btn {
            background-color: #6c757d;
            color: white;
        }

        .unread-btn:hover {
            background-color: #5a6268;
        }

        /* Status badge */
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-unread {
            background-color: #e8f4f8;
            color: #0e463f;
        }

        .status-read {
            background-color: #e9ecef;
            color: #495057;
        }

        /* Responsive Design */
        @media screen and (max-width: 1024px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .messages-table {
                font-size: 14px;
            }
            
            .message-content {
                max-width: 200px;
            }
        }

        @media screen and (max-width: 768px) {
            .main-content {
                padding: 10px;
                margin-left: 0;
                margin-top: 60px;
            }

            /* Table Responsive Styles */
            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                margin: 5px 0;
            }

            table {
                width: 100%;
                min-width: 600px;
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

            .messages-header {
                flex-direction: column;
                gap: 15px;
            }
            
            .search-container {
                width: 100%;
            }
            
            .search-container input {
                width: calc(100% - 40px);
            }
            
            .messages-table {
                display: block;
                overflow-x: auto;
            }
            
            .messages-table th, 
            .messages-table td {
                padding: 10px;
            }
            
            .message-content {
                max-width: 150px;
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
        }

        @media screen and (max-width: 480px) {
            .admin-container {
                margin-top: 100px;
            }
            
            .main-content {
                padding: 15px;
            }
            
            .messages-table th, 
            .messages-table td {
                padding: 8px;
                font-size: 13px;
            }
            
            .message-content {
                max-width: 120px;
            }
            
            h1 {
                font-size: 24px;
                margin-bottom: 15px;
            }
            
            .menu-toggle {
                top: 105px;
            }
        }

        @media (max-width: 768px) {
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
        }

        @media (max-width: 576px) {
            .search-container {
                width: 100%;
            }
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
            .main-content {
                margin-top: 20%;
                margin-left: 0;
                padding-left: 60px; /* Add space for the toggle button */
                width: calc(100% - 60px);
                transition: all 0.3s ease;
            }
            .main-content.sidebar-active {
                margin-left: 250px;
                padding-left: 30px;
            }
        }

        @media screen and (max-width: 480px) {
            .menu-toggle {
                left: 15px;
                top: 145px;
                height: 30px;
                width: 30px;
                padding: 6px;
            }
            .main-content {
               
                padding-left: 45px;
                width: calc(100% - 45px);
                bottom: 50px;
            }

            .
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
                <li><a href="bookings.php">Bookings</a></li>
                <li class="active"><a href="messages.php">Messages</a></li>
               
            </ul>
        </div>

        <div class="main-content">
            <div class="messages-header">
                <h1>Messages</h1>
                <div class="search-container">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Search messages..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                    <button type="button" class="clear-search" onclick="clearSearch()">×</button>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($messages)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No messages found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($messages as $message): ?>
                                <tr class="<?php echo $message['status'] == 1 ? 'unread' : ''; ?>">
                                    <td>
                                        <div style="display: flex; align-items: center;">
                                            <div class="user-icon" style="background-color: <?php echo getRandomColor($message['name']); ?>">
                                                <?php echo strtoupper(substr($message['name'], 0, 1)); ?>
                                            </div>
                                            <?php echo htmlspecialchars($message['name']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($message['email']); ?></td>
                                    <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                    <td class="message-content"><?php echo htmlspecialchars($message['message']); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($message['created_at'])); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $message['status'] == 1 ? 'status-unread' : 'status-read'; ?>">
                                            <?php echo $message['status'] == 1 ? 'Unread' : 'Read'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                            <?php if ($message['status'] == 1): ?>
                                                <input type="hidden" name="action" value="mark_read">
                                                <button type="submit" class="action-btn read-btn">Mark as Read</button>
                                            <?php else: ?>
                                                <input type="hidden" name="action" value="mark_unread">
                                                <button type="submit" class="action-btn unread-btn">Mark as Unread</button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

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
        </div>
    </div>

    <footer>
        &copy; 2023 SALCC
    </footer>

    <script>
        // Handle search input
        const searchInput = document.querySelector('input[name="search"]');
        const searchContainer = document.querySelector('.search-container');
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchContainer.classList.toggle('has-value', this.value.length > 0);
            searchTimeout = setTimeout(() => {
                window.location.href = `?page=1&search=${this.value}`;
            }, 500);
        });

        // Initialize search container state
        searchContainer.classList.toggle('has-value', searchInput.value.length > 0);

        // Clear search function
        function clearSearch() {
            searchInput.value = '';
            searchContainer.classList.remove('has-value');
            window.location.href = 'messages.php';
        }

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
    </script>
</body>
</html>
<?php include 'footer.php'; ?>