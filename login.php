<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />
    <link rel="stylesheet" href="css/style.css">
    <title>Login</title>
    <style>
        /* Error message styling */
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #f5c6cb;
        }

        /* Loading Screen Styles */
        .loading-screen {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .loading-spinner {
            width: 100px;
            height: 100px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #0e463f;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }

        .loading-text {
            color: #0e463f;
            font-size: 1.2em;
            font-weight: 500;
            margin-top: 15px;
        }

        .loading-logo {
            width: 150px;
            margin-bottom: 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Hide form while loading */
        .form-hidden {
            opacity: 0;
            pointer-events: none;
        }
    </style>
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
        <label id for="menu-bar"> <i class='fas fa-bars'></i></label>

        <nav class="navbar">
            <ul>
                <li><a href="#"><i class="	fa fa-book"></i> Learning Support</a></li>
                <li><a href="/try2/helpandsupport.php"  target="_blank"><i class="fa-regular fa-circle-question"></i> Help & Support</a></li>

            </ul>
        </nav>
    </header>
    <!--End of header-->


    <!--LogIN-->
    <!-- Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <img src="https://elearn.salcc.edu.lc/pluginfile.php/1/theme_academi/logo/1729725012/salcc_black.png" alt="SALCC Logo" class="loading-logo">
        <div class="loading-spinner"></div>
        <div class="loading-text">Logging in...</div>
    </div>

    <div class="login" id="loginform">
        <!--Image From elearn.salcc.edu.lc-->
        <img src="https://elearn.salcc.edu.lc/pluginfile.php/1/theme_academi/logo/1729725012/salcc_black.png" alt="SALCC Logo">
        <h1>Welcome Back!</h1>
        <p class="subtitle">Please login to your account</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <?php 
                    switch($_GET['error']) {
                        case 'unauthorized':
                            echo "Access denied. Admin privileges required.";
                            break;
                        case 'invalid':
                            echo "Invalid username or password!";
                            break;
                        default:
                            echo "An error occurred. Please try again.";
                    }
                ?>
            </div>
        <?php endif; ?>

        <form action="/try2/login-process.php" method="post" id="loginForm" onsubmit="return showLoading()">
            <div class="user-container">
                <label for="username">
                    <i class="fas fa-user"></i>
                </label>
                <input type="text" placeholder="Username" id="username" name="username" required>
            </div>
            
            <div class="pass-container">
                <label for="password">
                    <i class="fas fa-lock"></i>
                </label>
                <div class="password-toggle-container">
                    <input type="password" placeholder="Password" name="password" id="password" required>
                    <span id="togglePassword" onclick="togglePasswordVisibility()">
                        <i class="fas fa-eye-slash"></i>
                    </span> 
                </div>
            </div>

            <div class="login-options">
                <div class="remember">
                    <input type="checkbox" id="checkbox">
                    <label for="checkbox">Remember Me</label>
                </div>
                <div class="forgot">
                    <a href="/try2/recover_psw.php">Forgot Password?</a>
                </div>
            </div>

            <button type="submit">
                <i class="fas fa-sign-in-alt"></i> Log In
            </button>

            <div class="member">
                <span>Don't have an account?</span>
                <a href="/try2/signup.php">Sign Up</a>
            </div>
        </form>
    </div>




    

  

</body>

<script>
function showLoading() {
    const form = document.getElementById('loginform');
    const loadingScreen = document.getElementById('loadingScreen');
    
    // Show loading screen with fade in
    loadingScreen.style.display = 'flex';
    form.classList.add('form-hidden');
    
    // Allow form submission
    return true;
}

// Function to toggle password visibility
function togglePasswordVisibility() {
    const passwordField = document.getElementById('password');
    const toggleIcon = document.getElementById('togglePassword').querySelector('i');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    }
}
</script>

<script src="/try2/main.js">
</script>

</html>
<?php include 'footer.php'; ?>