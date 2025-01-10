<?php
session_start();
include_once 'database.php';

// Function to check if the user is logged in
function isLoggedIn() {
    return isset($_SESSION['username']);
}

// Check if the user is not logged in, display an alert message
if (!isLoggedIn()) {
    echo '<script>alert("Please log in to access the maps page."); window.location.href = "login.php";</script>';
    exit();
}

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get the logged-in user's name
function getUserName() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sir Arthur Lewis Community College Map</title>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-fullscreen/dist/leaflet.fullscreen.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-search/dist/leaflet-search.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    
    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-fullscreen/dist/Leaflet.fullscreen.min.js"></script>
    <script src="https://unpkg.com/leaflet-search/dist/leaflet-search.min.js"></script>
    
    <?php include 'header.php'; ?>

    <style>
        .map-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 70vh;
            margin: 20px 0;
            padding: 0 20px;
        }

        #map {
            height: 600px;
            width: 100%;
            max-width: 1200px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .instructions-container {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .instructions {
            font-size: 16px;
            color: #495057;
            margin: 0;
        }

        .dashboard-container {
            position: relative;
            width: 85%;
            margin: 100px auto 0;
            padding: 20px;
        }

        .dashboard-container .top {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .dashboard-container .top h1 {
            color: seagreen;
            text-align: center;
            margin: 0;
            font-size: 2em;
        }

        .leaflet-popup-content {
            font-family: 'Arial', sans-serif;
            padding: 10px;
        }

        .popup-title {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .popup-content {
            font-size: 14px;
            color: #34495e;
            line-height: 1.4;
        }

        .map-legend {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .legend-item {
            display: flex;
            align-items: center;
            padding: 5px 0;
            gap: 10px;
        }

        .legend-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .custom-marker {
            text-align: center;
        }

        .marker-container {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
            transition: transform 0.2s;
        }

        .marker-container:hover {
            transform: scale(1.1);
        }

        .popup-container {
            min-width: 200px;
        }

        .popup-header {
            padding: 10px;
            color: white;
            border-radius: 4px 4px 0 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .popup-body {
            padding: 10px;
            background: white;
            border-radius: 0 0 4px 4px;
            line-height: 1.5;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <div class="top">
            <h1>Campus Map</h1>
        </div>
    </div>

    <div class="instructions-container">
        <p class="instructions">🔍 Search for buildings or click markers to view details. Use the fullscreen button for a better view.</p>
    </div>

    <div class="map-container">
        <div id="map"></div>
    </div>

    <script>
        // Initialize the map with a different style
        var map = L.map('map', {
            fullscreenControl: true,
            zoomControl: false
        }).setView([13.998, -60.995], 17);

        // Add zoom control to the top-right
        L.control.zoom({
            position: 'topright'
        }).addTo(map);

        // Add OpenStreetMap tile layer with custom styling
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 25,
            attribution: ' OpenStreetMap contributors'
        }).addTo(map);

        // Building information with descriptions and modern colors
        var buildings = [
            {
                name: "DTEMS",
                lat: 13.999413,
                lng: -60.996698,
                color: "#4CAF50",
                icon: "fa-building",
                description: "Division of Technical Education and Management Studies. Houses technical and vocational programs."
            },
            {
                name: "BUSINESS",
                lat: 13.997044748618716,
                lng: -60.995150442064826,
                color: "#2196F3",
                icon: "fa-briefcase",
                description: "Business Division offering programs in Business Administration, Accounting, and Management."
            },
            {
                name: "ADMIN BUILDING",
                lat: 13.998691,
                lng: -60.995186,
                color: "#9C27B0",
                icon: "fa-landmark",
                description: "Main Administrative Building housing offices and student services."
            },
            {
                name: "LFT",
                lat: 13.997233,
                lng: -60.994326,
                color: "#FF9800",
                icon: "fa-chalkboard-teacher",
                description: "Learning and Faculty Training center for professional development."
            },
            {
                name: "OLD TECHNICAL WORKSHOP",
                lat: 13.997734011943495,
                lng: -60.99446866616974,
                color: "#795548",
                icon: "fa-tools",
                description: "Workshop facilities for technical training and practical sessions."
            },
            {
                name: "VAR",
                lat: 13.997134,
                lng: -60.993932,
                color: "#607D8B",
                icon: "fa-book",
                description: "Various Academic Resources building with study spaces and resources."
            },
            {
                name: "NURSING",
                lat: 13.99736,
                lng: -60.9951,
                color: "#E91E63",
                icon: "fa-hospital",
                description: "Nursing Division providing healthcare education and training facilities."
            },
            {
                name: "INNOVATION HUB",
                lat: 13.99848,
                lng: -60.99465,
                color: "#00BCD4",
                icon: "fa-lightbulb",
                description: "Modern facility for technology and innovation projects."
            }
        ];

        // Create a marker layer group to store all markers
        var markerGroup = L.layerGroup().addTo(map);
        var markers = {};  // Store markers by building name

        // Create custom markers and add them to the map
        buildings.forEach(function(building) {
            var customIcon = L.divIcon({
                className: 'custom-marker',
                html: `
                    <div class="marker-container" style="background-color: ${building.color};">
                        <i class="fas ${building.icon}"></i>
                    </div>
                `,
                iconSize: [40, 40],
                iconAnchor: [20, 40],
                popupAnchor: [0, -40]
            });

            var marker = L.marker([building.lat, building.lng], {
                icon: customIcon,
                title: building.name
            }).bindPopup(`
                <div class="popup-container">
                    <div class="popup-header" style="background-color: ${building.color}">
                        <i class="fas ${building.icon}"></i>
                        <span>${building.name}</span>
                    </div>
                    <div class="popup-body">
                        ${building.description}
                    </div>
                </div>
            `);
            
            marker.addTo(markerGroup);
            markers[building.name.toLowerCase()] = marker;  // Store marker reference
        });

        // Add search input
        var searchDiv = L.DomUtil.create('div', 'search-container');
        searchDiv.innerHTML = `
            <input type="text" id="searchInput" placeholder="Search buildings..." style="
                padding: 8px;
                margin: 10px;
                width: 200px;
                border: 1px solid #ccc;
                border-radius: 4px;
                font-size: 14px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            ">
            <div id="searchResults" style="
                display: none;
                position: absolute;
                background: white;
                border: 1px solid #ccc;
                border-radius: 4px;
                max-height: 200px;
                overflow-y: auto;
                width: 200px;
                margin-left: 10px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                z-index: 1000;
            "></div>
        `;

        // Create custom control
        var SearchControl = L.Control.extend({
            options: {
                position: 'topright'
            },
            onAdd: function() {
                return searchDiv;
            }
        });
        map.addControl(new SearchControl());

        // Add search functionality
        var searchInput = document.getElementById('searchInput');
        var searchResults = document.getElementById('searchResults');

        searchInput.addEventListener('input', function(e) {
            var searchText = e.target.value.toLowerCase();
            var results = '';

            if (searchText.length > 0) {
                buildings.forEach(function(building) {
                    if (building.name.toLowerCase().includes(searchText)) {
                        results += `
                            <div class="search-result" style="
                                padding: 8px;
                                cursor: pointer;
                                border-bottom: 1px solid #eee;
                                display: flex;
                                align-items: center;
                                gap: 8px;
                            " data-name="${building.name}">
                                <i class="fas ${building.icon}" style="color: ${building.color}"></i>
                                ${building.name}
                            </div>
                        `;
                    }
                });

                if (results) {
                    searchResults.innerHTML = results;
                    searchResults.style.display = 'block';

                    // Add click handlers to results
                    document.querySelectorAll('.search-result').forEach(function(result) {
                        result.addEventListener('click', function() {
                            var buildingName = this.getAttribute('data-name').toLowerCase();
                            var marker = markers[buildingName];
                            
                            if (marker) {
                                var latLng = marker.getLatLng();
                                map.setView(latLng, 19);
                                marker.openPopup();
                                searchResults.style.display = 'none';
                                searchInput.value = '';
                            }
                        });
                    });
                } else {
                    searchResults.innerHTML = '<div style="padding: 8px;">No results found</div>';
                    searchResults.style.display = 'block';
                }
            } else {
                searchResults.style.display = 'none';
            }
        });

        // Hide results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchDiv.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });

        // Prevent map zoom when scrolling the search results
        searchResults.addEventListener('wheel', function(e) {
            e.stopPropagation();
        });

        // Add legend
        var legend = L.control({position: 'bottomright'});
        legend.onAdd = function(map) {
            var div = L.DomUtil.create('div', 'map-legend');
            div.innerHTML = '<h4 style="margin: 0 0 8px 0;">Buildings</h4>';
            buildings.forEach(function(building) {
                div.innerHTML += `
                    <div class="legend-item">
                        <div class="legend-icon" style="background: ${building.color};">
                            <i class="fas ${building.icon}"></i>
                        </div>
                        <span>${building.name}</span>
                    </div>
                `;
            });
            return div;
        };
        legend.addTo(map);
    </script>
</body>
</html>
<?php include 'footer.php'; ?>
<?php include('scroll_to_top.php'); ?>