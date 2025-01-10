<?php
session_start();

include_once 'database.php';

// Function to check if the user is logged in
function isLoggedIn()
{
    return isset($_SESSION['username']);
}

// Check if the user is not logged in, display an alert message
if (!isLoggedIn()) {
    echo '<script>alert("Please log in to access the booking page."); window.location.href = "login.php";</script>';
    exit(); // Stop further execution
}

// Check if user is admin (for display purposes)
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';



// Function to get the logged-in user's name
function getUserName()
{
    return isset($_SESSION['username']) ? $_SESSION['username'] : '';
}
?>

<?php include 'header.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel stylesheet href="styele.css">
    <title>Booking</title>
    <style>
        :root {
            --primary-color: #0e463b;
            --secondary-color: rgb(77, 178, 164);
            --accent-color: seagreen;
            --text-color: #333;
            --light-bg: #f5f5f5;
            --border-color: #ddd;
            --box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        [data-theme="dark"] {
            --text-color: #ffffff;
            --department-text: #ffffff;
            --search-text: #ffffff;
            --title-color: #4db2a4;
            --border-color: #000000;
        }

        html, body {
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--light-bg);
            color: var(--text-color);
        }

        .dashboard-container {
            width: 100%;
            min-height: 100vh;
            margin-top: 7.0%;
            padding: 0 20px;
        }

        .page-header {
            text-align: center;
            padding: 1rem 0;
            margin-top: 7rem;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2.5rem;
            color: var(--title-color, var(--primary-color));
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }

        .search-container {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 2rem;
            padding: 0 2rem;
            color: none;
        }

        .search-wrapper {
            position: relative;
            max-width: 250px;
            width: 100%;
        }

        #searchInput {
            width: 100%;
            padding: 10px 35px 10px 15px;
            border: 2px solid var(--border-color);
            border-radius: 25px;
            font-size: 1rem;
            transition: all 0.3s ease;
            color: var(--search-text, var(--text-color));
            background-color: transparent;
        }

        #searchInput:focus {
            border-color: var(--secondary-color);
            box-shadow: var(--box-shadow);
            outline: none;
        }

        #clearSearch {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #000;
            font-size: 18px;
            padding: 0;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #clearSearch:hover {
            color: var(--accent-color);
        }

        .departments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 300px));
            gap: 30px;
            justify-content: center;
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .department-card {
            background: var(--card-bg, white);
            border-radius: 10px;
            padding: 25px;
            box-shadow: var(--box-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            color: var(--department-text, var(--text-color));
        }

        .department-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        .img-box {
            width: 100%;
            height: 200px;
            overflow: hidden;
        }

        .images {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .department-card:hover .images {
            transform: scale(1.05);
        }

        .department-info {
            padding: 20px;
            text-align: center;
        }

        .department-info h3 {
            color: var(--department-text, var(--text-color));
            font-size: 1.5rem;
            margin: 0 0 15px 0;
        }

        .department-info i {
            color: var(--secondary-color);
            margin-right: 8px;
        }

        .bbuton {
            width: 100%;
            padding: 12px 25px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 15px;
        }

        .bbuton:hover {
            background-color: var(--secondary-color);
        }

        .no-results {
            text-align: center;
            padding: 40px;
            color: var(--text-color);
            font-size: 1.1rem;
            background: white;
            border-radius: 10px;
            margin: 20px auto;
            max-width: 600px;
            box-shadow: var(--box-shadow);
        }

        @media (max-width: 768px) {
            .dashboard-container {
                margin-top: 7%;
                padding: 0 20px;
            }

            .page-title {
                font-size: 2rem;
                margin-bottom: 30px;
            }

            .departments-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 300px));
                gap: 25px;
                padding: 20px;
            }

            .img-box {
                height: 180px;
            }
        }

        @media (max-width: 480px) {
            .dashboard-container {
                margin-top: 7%;
                padding: 0 10px;
            }

            .page-title {
                font-size: 1.8rem;
            }

            .search-container {
                padding: 0 1rem;
            }

            .departments-grid {
                grid-template-columns: 1fr;
                gap: 15px;
                padding: 10px;
            }

            .department-info h3 {
                font-size: 1.3rem;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <div class="page-header">
            <h1 class="page-title">Book A Room</h1>
        </div>
        
        <div class="search-container">
            <div class="search-wrapper">
                <input type="text" id="searchInput" placeholder="Search departments...">
                <button id="clearSearch" type="button">×</button>
            </div>
        </div>

        <div class="departments-grid" id="root">
            <!-- Department cards will be populated here by JavaScript -->
        </div>

        <div class="no-results" id="noResults" style="display: none;">
            No departments found matching your search.
        </div>
    </div>

    <script>
        const product = [
            {
                id: 0,
                image: "https://images.pexels.com/photos/256395/pexels-photo-256395.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1",
                title: '<h3><i class="fas fa-map-marker-alt"></i> DTEMS </h3>',
                link: '/try2/dtems_room.php',
                category: 'DTEMS',
                description: 'Department of Technical Education and Management Studies'
            },
            {
                id: 1,
                image: "https://images.pexels.com/photos/159844/cellular-education-classroom-159844.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1",
                title: '<h3><i class="fas fa-map-marker-alt"></i> BUS </h3>',
                link: '/try2/bus_room.php',
                category: 'BUS',
                description: 'Business Department'
            },
            {
                id: 2,
                image: "https://images.pexels.com/photos/8197545/pexels-photo-8197545.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1",
                title: '<h3><i class="fas fa-map-marker-alt"></i> BED </h3>',
                link: '/try2/bed_room.php',
                category: 'BED',
                description: 'Department of Education'
            },
            {
                id: 3,
                image: "https://images.pexels.com/photos/9490389/pexels-photo-9490389.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1",
                title: '<h3><i class="fas fa-map-marker-alt"></i> LFT </h3>',
                link: '/try2/lft_room.php',
                category: 'LFT',
                description: 'Department of Life Skills'
            },
            {
                id: 4,
                image: "https://images.pexels.com/photos/7092347/pexels-photo-7092347.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1",
                title: '<h3><i class="fas fa-map-marker-alt"></i> VAR </h3>',
                link: '/try2/var_room.php',
                category: 'VAR',
                description: 'Visual and Related Arts'
            },
            {
                id: 5,
                image: "https://images.pexels.com/photos/9490389/pexels-photo-9490389.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1",
                title: '<h3><i class="fas fa-map-marker-alt"></i> OTW </h3>',
                link: '/try2/otw_room.php',
                category: 'OTW',
                description: 'Other Departments'
            }
        ];

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const clearSearch = document.getElementById('clearSearch');
            const noResults = document.getElementById('noResults');
            const root = document.getElementById('root');

            function displayItems(items) {
                root.innerHTML = items.map(item => `
                    <div class="department-card" data-category="${item.category}">
                        <div class="img-box">
                            <img class="images" src="${item.image}" alt="${item.category}">
                        </div>
                        <div class="department-info">
                            <h3><i class="fas fa-map-marker-alt"></i>${item.category}</h3>
                            <p>${item.description}</p>
                            <button class="bbuton" onclick="window.location.href='${item.link}'">
                                View Rooms
                            </button>
                        </div>
                    </div>
                `).join('');
            }

            function performSearch() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                const filteredItems = product.filter(item => 
                    item.category.toLowerCase().includes(searchTerm) ||
                    item.description.toLowerCase().includes(searchTerm)
                );

                displayItems(filteredItems);
                noResults.style.display = filteredItems.length === 0 ? 'block' : 'none';
                clearSearch.style.display = searchTerm ? 'block' : 'none';
            }

            searchInput.addEventListener('input', performSearch);
            clearSearch.addEventListener('click', function() {
                searchInput.value = '';
                performSearch();
                searchInput.focus();
            });

            // Initial display
            displayItems(product);
        });
    </script>
    <?php include 'footer.php'; ?>
    <?php include('scroll_to_top.php'); ?>
</body>

</html>
