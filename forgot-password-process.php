<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "easy_reserve";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Get email from form submission
    $email = $_POST['email'];

    // Check if the email exists in the database
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Email exists, prompt user to enter new password
        echo '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Reset Password</title>
                <link rel="stylesheet" href="style.css"> <!-- Include your CSS file -->
            </head>
          
            <style> {
                font-family: Arial, sans-serif;
                background-color: #f0f0f0;
            }
    
            .password-reset-form {
                max-width: 400px;
                margin: 50px auto;
                padding: 20px;
                background-color: #fff;
                border-radius: 5px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }
    
            h2 {
                text-align: center;
                margin-bottom: 20px;
            }
    
            input[type="password"] {
                width: 100%;
                padding: 10px;
                margin-bottom: 15px;
                border: 1px solid #ccc;
                border-radius: 4px;
                box-sizing: border-box;
            }
    
            button[type="submit"] {
                width: 100%;
                padding: 10px;
                background-color:rgb(77, 178, 164);
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }
    
            button[type="submit"]:hover {
                background-color: rgb(77, 178, 142);
            }
    
            .error {
                color: red;
                margin-bottom: 15px;
            }
        </style>
            <body>

            
                <div class="password-reset-form">
                    <h2>Reset Password</h2>
                    <form action="update-password.php" method="post">
                        <input type="hidden" name="email" value="' . $email . '">
                        <input type="password" name="new_password" placeholder="Enter new password" required>
                        <input type="password" name="confirm_password" placeholder="Confirm new password" required>
                        <button type="submit" name="submit">Reset Password</button>
                    </form>
                </div>
            </body>
            </html>
        ';
    } else {
        // Email does not exist in the database
        echo "Email not found";
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>

