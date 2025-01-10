<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css"> 
    <title>Forgot Password</title>
</head>
<style>
    /* style.css */

body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

.forgot-password-form {
    max-width: 400px;
    margin: 50px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.forgot-password-form h2 {
    text-align: center;
    margin-bottom: 20px;
}

.forgot-password-form input[type="email"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
}

.forgot-password-form button[type="submit"] {
    width: 100%;
    padding: 10px;
    background-color:rgb(77, 178, 164);
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.forgot-password-form button[type="submit"]:hover {
    background-color: rgb(77, 178, 142);
    transition: background-color 0.3s;
}

    </style>
<body>

    <div class="forgot-password-form">
        <h2>Forgot Password</h2>
        <form action="forgot-password-process.php" method="post">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit" name="submit">Reset Password</button>
        </form>
    </div>

</body>
</html>
