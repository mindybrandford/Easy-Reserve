<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />
    <link rel="stylesheet" href="css/style.css">
    <title>Sign Up - SALCC Easy Reserve</title>
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            flex: 1;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 20px 0;
            min-height: calc(100vh - 200px);
            margin-top: 120px;
        }

        .signup-container {
            margin-top: 0;
            width: 450px;
            padding: 40px;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin: 0;
        }

        .signup-container img {
            width: 160px;
            margin-bottom: 20px;
        }

        .signup-container h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
        }

        .signup-container p {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .user-role {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .user-role h4 {
            margin: 0;
            color: #333;
            margin-right: 20px;
            white-space: nowrap;
        }

        .role-options {
            display: inline-flex;
            gap: 30px;
        }

        .role-option {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .role-option input[type="radio"] {
            margin: 0;
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .role-option label {
            font-size: 14px;
            color: #444;
            cursor: pointer;
            white-space: nowrap;
/* Login Styles */
.login {
    width: 450px;
    padding: 40px;
    background-color: #fff;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    text-align: center;
    margin: 120px auto 40px;
}

.login img {
    width: 160px;
    margin-bottom: 30px;
}

.login h1 {
    color: #333;
    margin-bottom: 10px;
    font-size: 24px;
}

.login .subtitle {
    color: #666;
    margin-bottom: 30px;
    font-size: 14px;
}

.user-container,
.pass-container {
    position: relative;
    margin-bottom: 20px;
}

.user-container label,
.pass-container label {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
}

.user-container input,
.pass-container input {
    width: 100%;
    padding: 12px 40px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.password-toggle-container {
    position: relative;
}

#togglePassword {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #666;
    background: none;
    border: none;
    padding: 0;
    font-size: 14px;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
}

#togglePassword:hover {
    color: #2E8B57;
}

.user-container input:focus,
.pass-container input:focus {
    border-color: #2E8B57;
    outline: none;
}

.login-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    font-size: 14px;
}

.remember {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #666;
}

.remember input[type="checkbox"] {
    margin: 0;
    cursor: pointer;
}

.forgot a {
    color: #666;
    text-decoration: none;
    transition: color 0.3s ease;
}

.forgot a:hover {
    color: #2E8B57;
}

.login button[type="submit"] {
    width: 100%;
    padding: 12px;
    background-color: #2E8B57;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.login button[type="submit"]:hover {
    background-color: #246B43;
}

.member {
    margin-top: 20px;
    color: #666;
    font-size: 14px;
}

.member a {
    color: #2E8B57;
    text-decoration: none;
    font-weight: 600;
    margin-left: 5px;
    transition: color 0.3s ease;
}

.member a:hover {
    color: #246B43;
}

/* Responsive adjustments */
@media (max-width: 991px) {
    .login {
        width: 90%;
        margin: 100px auto 40px;
        padding: 30px;
    }
}/* Login Styles */
.login {
    width: 450px;
    padding: 40px;
    background-color: #fff;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    text-align: center;
    margin: 120px auto 40px;
}

.login img {
    width: 160px;
    margin-bottom: 30px;
}

.login h1 {
    color: #333;
    margin-bottom: 10px;
    font-size: 24px;
}

.login .subtitle {
    color: #666;
    margin-bottom: 30px;
    font-size: 14px;
}

.user-container,
.pass-container {
    position: relative;
    margin-bottom: 20px;
}

.user-container label,
.pass-container label {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
}

.user-container input,
.pass-container input {
    width: 100%;
    padding: 12px 40px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.password-toggle-container {
    position: relative;
}

#togglePassword {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #666;
    background: none;
    border: none;
    padding: 0;
    font-size: 14px;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
}

#togglePassword:hover {
    color: #2E8B57;
}

.user-container input:focus,
.pass-container input:focus {
    border-color: #2E8B57;
    outline: none;
}

.login-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    font-size: 14px;
}

.remember {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #666;
}

.remember input[type="checkbox"] {
    margin: 0;
    cursor: pointer;
}

.forgot a {
    color: #666;
    text-decoration: none;
    transition: color 0.3s ease;
}

.forgot a:hover {
    color: #2E8B57;
}

.login button[type="submit"] {
    width: 100%;
    padding: 12px;
    background-color: #2E8B57;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.login button[type="submit"]:hover {
    background-color: #246B43;
}

.member {
    margin-top: 20px;
    color: #666;
    font-size: 14px;
}

.member a {
    color: #2E8B57;
    text-decoration: none;
    font-weight: 600;
    margin-left: 5px;
    transition: color 0.3s ease;
}

.member a:hover {
    color: #246B43;
}

/* Responsive adjustments */
@media (max-width: 991px) {
    .login {
        width: 90%;
        margin: 100px auto 40px;
        padding: 30px;
    }
}        }

        .input-container {
            position: relative;
            margin-bottom: 20px;
        }

        .input-container i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .input-container input {
            width: 100%;
            padding: 12px 40px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .input-container input[type="password"],
        .input-container input[type="text"] {
            padding-right: 40px;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            background: none;
            border: none;
            padding: 0;
            font-size: 14px;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
        }

        .toggle-password:hover {
            color: #2E8B57;
        }

        .toggle-password:focus {
            outline: none;
        }

        .terms {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 20px 0;
            padding: 0 10px;
        }

        .terms input[type="checkbox"] {
            width: 16px;
            height: 16px;
        }

        .terms label {
            font-size: 14px;
            color: #666;
        }

        .terms a {
            color: rgb(75, 168, 156);
            text-decoration: none;
            font-weight: 600;
        }

        .signup-button {
            width: 100%;
            padding: 12px;
            background: rgb(75, 168, 156);
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .signup-button:hover {
            background: rgb(14, 70, 63);
        }

        .member {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }

        .member a {
            color: rgb(75, 168, 156);
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
        }

        footer {
            margin-top: auto;
        }

        @media (max-width: 480px) {
            .signup-container {
                width: 90%;
                padding: 30px 20px;
            }

            .user-role {
                flex-direction: column;
                gap: 10px;
            }
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
        <label for="menu-bar"><i class='fas fa-bars'></i></label>
        <nav class="navbar">
            <ul>
                <li><a href="#"><i class="fa fa-book"></i> Learning Support</a></li>
                <li><a href="/try2/helpandsupport.php" target="_blank"><i class="fa-regular fa-circle-question"></i> Help & Support</a></li>
            </ul>
        </nav>
    </header>

    <div class="main-content">
        <div class="signup-container">
            <img src="https://elearn.salcc.edu.lc/pluginfile.php/1/theme_academi/logo/1729725012/salcc_black.png" alt="SALCC Logo">
            <h1>Create Account</h1>
            <p>Please fill in the information below to get started</p>

            <form action="/try2/process-signup.php" method="post" novalidate>
                <div class="user-role">
                    <h4>Select Role:</h4>
                    <div class="role-options">
                        <div class="role-option">
                            <input type="radio" id="student" name="role" value="student" checked>
                            <label for="student">Student</label>
                        </div>
                        <div class="role-option">
                            <input type="radio" id="lecturer" name="role" value="lecturer">
                            <label for="lecturer">Lecturer</label>
                        </div>
                    </div>
                </div>

                <div class="input-container">
                    <i class="fas fa-user"></i>
                    <input type="text" id="fullname" name="fullname" placeholder="Full Name" required>
                </div>

                <div class="input-container">
                    <i class="fas fa-user-circle"></i>
                    <input type="text" id="username" name="username" placeholder="Username" required>
                </div>

                <div class="input-container">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" placeholder="SALCC Email Address" required>
                </div>

                <div class="input-container">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>

                <div class="input-container">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password-confirmation" name="password-confirmation" placeholder="Confirm Password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('password-confirmation')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>

                <div class="terms">
                    <input type="checkbox" id="checkbox" required>
                    <label for="checkbox">I agree to the <a href="#">Terms & Conditions</a></label>
                </div>

                <button type="submit" class="signup-button">
                    <i class="fas fa-user-plus"></i>
                    Create Account
                </button>

                <div class="member">
                    Already have an account? <a href="/try2/login.php">Login</a>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <div class="footer-icons">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fa fa-envelope"></i></a>
        </div>
        <br>
        <div>
            <p>&copy; 2024 SALCC EASY RESERVE. All Rights Reserved.</p>
        </div>
    </footer>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const button = input.nextElementSibling;
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>