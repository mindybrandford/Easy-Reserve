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



// Initialize stats array
$stats = array(
    'users' => 0,
    'bookings' => 0,
    'messages' => 0
);

// Get basic statistics
if ($conn) {
    // Count users
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
    if ($result) {
        $stats['users'] = mysqli_fetch_assoc($result)['count'];
    }

    // Count total bookings from all room tables
    $bookingsQuery = "SELECT 
        (SELECT COUNT(*) FROM bus_rooms WHERE user_id IS NOT NULL) +
        (SELECT COUNT(*) FROM dtems_rooms WHERE user_id IS NOT NULL) +
        (SELECT COUNT(*) FROM lft_rooms WHERE user_id IS NOT NULL) +
        (SELECT COUNT(*) FROM otw_rooms WHERE user_id IS NOT NULL) +
        (SELECT COUNT(*) FROM var_rooms WHERE user_id IS NOT NULL) +
        (SELECT COUNT(*) FROM bed_rooms WHERE user_id IS NOT NULL) as total_bookings";
    
    $result = mysqli_query($conn, $bookingsQuery);
    if ($result) {
        $stats['bookings'] = mysqli_fetch_assoc($result)['total_bookings'];
    }

    // Count messages
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM contact_us");
    if ($result) {
        $stats['messages'] = mysqli_fetch_assoc($result)['count'];
    }

    // For the graph, let's show distribution of bookings across different buildings
    $buildingStats = array();
    $userStats = array();

    // Get bookings per building
    $buildingQuery = "
        SELECT 'BUS' as building, COUNT(*) as count FROM bus_rooms WHERE user_id IS NOT NULL UNION ALL
        SELECT 'DTEMS', COUNT(*) FROM dtems_rooms WHERE user_id IS NOT NULL UNION ALL
        SELECT 'LFT', COUNT(*) FROM lft_rooms WHERE user_id IS NOT NULL UNION ALL
        SELECT 'OTW', COUNT(*) FROM otw_rooms WHERE user_id IS NOT NULL UNION ALL
        SELECT 'VAR', COUNT(*) FROM var_rooms WHERE user_id IS NOT NULL UNION ALL
        SELECT 'BED', COUNT(*) FROM bed_rooms WHERE user_id IS NOT NULL
    ";

    $result = mysqli_query($conn, $buildingQuery);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $buildingStats[] = $row['count'];
        }
    }

    // Get users per building (unique users)
    $usersQuery = "
        SELECT 'BUS' as building, COUNT(DISTINCT user_id) as count FROM bus_rooms WHERE user_id IS NOT NULL UNION ALL
        SELECT 'DTEMS', COUNT(DISTINCT user_id) FROM dtems_rooms WHERE user_id IS NOT NULL UNION ALL
        SELECT 'LFT', COUNT(DISTINCT user_id) FROM lft_rooms WHERE user_id IS NOT NULL UNION ALL
        SELECT 'OTW', COUNT(DISTINCT user_id) FROM otw_rooms WHERE user_id IS NOT NULL UNION ALL
        SELECT 'VAR', COUNT(DISTINCT user_id) FROM var_rooms WHERE user_id IS NOT NULL UNION ALL
        SELECT 'BED', COUNT(DISTINCT user_id) FROM bed_rooms WHERE user_id IS NOT NULL
    ";

    $result = mysqli_query($conn, $usersQuery);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $userStats[] = $row['count'];
        }
    }

    // Convert to JSON for JavaScript
    $monthlyStatsJSON = json_encode($buildingStats);
    $userStatsJSON = json_encode($userStats);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SALCC Easy Reserve</title>
    <?php 
    include 'header.php';
    ?>
    <!-- Add Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
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

        /* Main Layout */
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }

        .admin-container {
            flex: 1;
            display: flex;
            position: relative;
            margin-top: 120px;
            margin-bottom: 60px; /* Add space for footer */
            min-height: calc(100vh - 180px);
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: #333333;  
            color: #fff;
            padding-top: 40px;
            position: fixed;
            top: 120px;
            bottom: 60px; /* Space for footer */
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
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            min-height: calc(100vh - 180px);
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stats-card {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }

        .stats-card:hover {
            transform: translateY(-2px);
        }

        .stats-card h3 {
            margin: 0 0 10px 0;
            color: #0e463f;
            font-size: 18px;
        }

        .stats-card p {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            color: #2ecc71;
        }

        /* Graph Container */
        .graph-container {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-top: 30px;
        }

        .graph-container h2 {
            margin: 0 0 20px 0;
            color: #333;
            font-size: 20px;
        }

        .chart-wrapper {
            position: relative;
            height: 400px;
            margin: 0 auto;
        }

        /* Responsive Design */
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
            .menu-toggle {
                top: 120px;
            }
        }

        footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: rgb(62, 153, 141);
            color: white;
            text-align: center;
            padding: 15px 0;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <button class="menu-toggle" onclick="toggleSidebar()">⚙️</button>
        <div class="sidebar">
            <div class="sidebar-logo">
                <img src="https://elearn.salcc.edu.lc/pluginfile.php/1/theme_academi/logo/1729725012/salcc_black.png" alt="SALCC Logo">
            </div>
            <h2>Admin Panel</h2>
            <ul class="sidebar-menu">
                <li class="active"><a href="admin.php">Dashboard</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="bookings.php">Bookings</a></li>
                <li><a href="messages.php">Messages</a></li>

            </ul>
        </div>

        <div class="main-content">
            <div class="stats-container">
                <div class="stats-card">
                    <h3>Active Users</h3>
                    <p><?php echo number_format($stats['users']); ?></p>
                </div>
                <div class="stats-card">
                    <h3>Booked Rooms</h3>
                    <p><?php echo number_format($stats['bookings']); ?></p>
                </div>
                <div class="stats-card">
                    <h3>Contact Messages</h3>
                    <p><?php echo number_format($stats['messages']); ?></p>
                </div>
            </div>

            <div class="graph-container">
                <h2>Booking Statistics</h2>
                <div class="chart-wrapper">
                    <canvas id="bookingsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Use real data from PHP
        const buildings = ['BUS', 'DTEMS', 'LFT', 'OTW', 'VAR', 'BED'];
        const bookingData = <?php echo $monthlyStatsJSON; ?>;
        const userData = <?php echo $userStatsJSON; ?>;

        // Create the chart
        const ctx = document.getElementById('bookingsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: buildings,
                datasets: [{
                    label: 'Room Bookings',
                    data: bookingData,
                    backgroundColor: 'rgba(100, 181, 246, 0.7)',  
                    borderColor: 'rgba(100, 181, 246, 1)',
                    borderWidth: 1
                }, {
                    label: 'Active Users',
                    data: userData,
                    backgroundColor: 'rgba(255, 183, 77, 0.7)',   
                    borderColor: 'rgba(255, 183, 77, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#e0e0e0'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                }
            }
        });
    </script>
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
            
            if (sidebar.classList.contains('active') && 
                !sidebar.contains(event.target) && 
                !menuToggle.contains(event.target)) {
                sidebar.classList.remove('active');
                mainContent.classList.remove('sidebar-active');
            }
        });
    </script>
</body>
<footer>
    <?php include 'footer.php'; ?>
</footer>
</html>