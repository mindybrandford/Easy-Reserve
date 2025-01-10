<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('admin-nav-option-handling.php');

?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/light-theme.css">
    <title>HomePage</title>
</head>

<body>
    <div class="banner">
        <div class="banner-content">
            <div class="contact-info">
                <div>
                    <p><a href="tel:+1234567890"><i class="fa fa-phone"></i> Telephone: 123-456-7890</a></p>
                </div>
                <div>
                    <p><a href="mailto:salcceasyreserve@gmail.com"><i class="fa fa-envelope"></i> Email: salcceasyreserve@gmail.com</a></p>
                </div>
            </div>
        </div>
    </div>

    <header>
        <a href="/try2/index.php" class="logo">SALCC EASY RESERVE</a>
    
        <input type="checkbox" id="menu-bar">
        <label for="menu-bar"><i class='fas fa-bars'></i></label>

        <nav class="navbar">
            <ul>
                <li><a href="/try2/index.php"><i class="fas fa-home" class="active"></i> Home</a></li>
                <li class="dropdown">
                    <a href="/try2/booking.php"><i class="fas fa-book"></i> Booking</a>
                    <ul class="dropdown-content">
                        <li><a href="/try2/booking.php"><i class="fas fa-book"></i> Booking</a></li>
                        <li><a href="/try2/map.php"><i class="fas fa-map-marked-alt"></i> Maps</a></li>
                    </ul>
                </li>
                <li><a href="/try2/about.php"><i class="fas fa-info-circle"></i> About</a></li>

                <li class="dropdown">
                    <?php if (isLoggedIn()) : ?>
                        <a href="#"><i class="fas fa-user"></i> <?php echo htmlspecialchars(getUserName()); ?></a>
                        <ul class="dropdown-content">
                            <li id="Dash" style="display: none;"><a href="admin.php"><i class="fa-solid fa-user-shield"></i> Dashboard </a> </li>
                            <li><a href="/try2/editprofile.php"><i class="fa-regular fa-user"></i> Edit Profile</a></li>
    
                            <li><a href="/try2/bookinghistory.php"><i class="far fa-bookmark"></i> Booking History</a></li>
                            <li><a href="/try2/helpandsupport.php" target="_blank"><i class="fa-regular fa-circle-question"></i> Help & Support</a></li>
                            <li><a href="/try2/logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log out</a></li>
                        </ul>
                    <?php else : ?>
                        <a href="/try2/login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <?php endif; ?>
                </li>
            </ul>
        </nav>

        <script>
            // Check if user is admin and show dashboard
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') : ?>
                document.getElementById('Dash').style.display = 'block';
            <?php endif; ?>
        </script>
    </header>
    <!--End of header-->