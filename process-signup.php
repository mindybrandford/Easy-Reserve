<?php
session_start();
include('database.php');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Array to hold error messages
$error_messages = [];

// Check for required fields
if (empty($_POST['fullname'])) {
    $error_messages[] = "Full name is required";
}
if (empty($_POST['username'])) {
    $error_messages[] = "Username is required";
}
if (empty($_POST['email'])) {
    $error_messages[] = "Email is required";
}
if (empty($_POST['password'])) {
    $error_messages[] = "Password is required";
}
if (empty($_POST['password-confirmation'])) {
    $error_messages[] = "Password confirmation is required";
}

// Validate email
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $error_messages[] = "Valid email is required";
} elseif (!preg_match('/@salcc\.edu\.lc$/', $_POST['email'])) {
    $error_messages[] = "Only SALCC email addresses are allowed";
    echo "<script>alert('Only SALCC email addresses are allowed');</script>";
}

// Check password requirements
if (strlen($_POST['password']) < 8 || !preg_match('/[a-z]/i', $_POST['password']) || !preg_match('/[0-9]/', $_POST['password']) || !preg_match('/[!@#$%^&*()]/', $_POST['password'])) {
    $error_messages[] = "Password must be at least 8 characters long and contain at least one letter, one number, and one symbol";
}

// Check if passwords match
if ($_POST['password'] != $_POST['password-confirmation']) {
    $error_messages[] = "Passwords do not match";
}

// Check if there are any error messages before proceeding
if (!empty($error_messages)) {
    // Display alert messages
    foreach ($error_messages as $error) {
        echo "<script>alert('$error');</script>";
    }
    // Redirect back to signup page with error messages
    $_SESSION['error_messages'] = $error_messages;
    echo "<script>window.location.href='/try2/signup.php';</script>";
    exit();
}

// Check if email is already in use
$email = $_POST['email'];
$sql_check_email = "SELECT * FROM users WHERE email = ?";
$stmt_check_email = $conn->prepare($sql_check_email);
$stmt_check_email->bind_param("s", $email);
$stmt_check_email->execute();
$result_email = $stmt_check_email->get_result();

if ($result_email->num_rows > 0) {
    echo "<script>
        alert('This email address is already registered. Please use a different email or login to your existing account.');
        window.location.href='/try2/signup.php';
    </script>";
    exit();
}

// Check if username is already in use
$username = $_POST['username'];
$sql_check_username = "SELECT * FROM users WHERE username = ?";
$stmt_check_username = $conn->prepare($sql_check_username);
$stmt_check_username->bind_param("s", $username);
$stmt_check_username->execute();
$result_username = $stmt_check_username->get_result();

if ($result_username->num_rows > 0) {
    $error_messages[] = "Username already in use";
    echo "<script>alert('Username already in use');</script>";
    echo "<script>window.location.href='/try2/signup.php';</script>";
    exit();
}

// If no errors, proceed with account creation
$password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Retrieve the role selected by the user
$role = $_POST['role'];


// Insert new user into database
$sql = "INSERT INTO users (fullname, username, email, password_hash, role) VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

$stmt->bind_param("sssss", $_POST['fullname'], $_POST['username'], $_POST['email'], $password_hash, $role);

//Sending Verification Email
if ($stmt->execute()) {
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['mail'] = $email;
    require "Mail/phpmailer/PHPMailerAutoload.php";
    $mail = new PHPMailer;

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls';

    $mail->Username = 'salcceasyreserve@gmail.com';
    $mail->Password = 'gimp abqn slnj vjhi';

    $mail->setFrom('salcceasyreserve@gmail.com', 'SALCC Easy Reserve');
    $mail->addAddress($_POST["email"]);

    $mail->isHTML(true);
    $mail->Subject = "SALCC Easy Reserve - Account Verification";

    // HTML version of the email
    $mail->Body = "
    <html>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #2E8B57; margin-bottom: 20px;'>Welcome to SALCC Easy Reserve!</h2>
            
            <p>Dear " . $_POST['fullname'] . ",</p>
            
            <p>Thank you for creating an account with SALCC Easy Reserve. To complete your registration, please use the verification code below:</p>
            
            <div style='background-color: #f5f5f5; padding: 15px; margin: 20px 0; text-align: center; border-radius: 5px;'>
                <h2 style='color: #2E8B57; margin: 0;'>" . $otp . "</h2>
            </div>
            
            <p>Please enter this code on the verification page to activate your account.</p>
            
            <p>If you did not create an account with SALCC Easy Reserve, please ignore this email.</p>
            
            <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;'>
                <p style='margin: 0;'>Best regards,</p>
                <p style='margin: 5px 0; color: #2E8B57; font-weight: bold;'>SALCC Easy Reserve Team</p>
            </div>
        </div>
    </body>
    </html>";

    // Plain text version of the email
    $mail->AltBody = "Dear " . $_POST['fullname'] . ",\n\n" .
        "Welcome to SALCC Easy Reserve!\n\n" .
        "Thank you for creating an account. To complete your registration, please use the following verification code:\n\n" .
        $otp . "\n\n" .
        "Please enter this code on the verification page to activate your account.\n\n" .
        "If you did not create an account with SALCC Easy Reserve, please ignore this email.\n\n" .
        "Best regards,\n" .
        "SALCC Easy Reserve Team";

    if (!$mail->send()) {
?>
        <script>
            alert("<?php echo "Signup Failed, Invalid Email Address" ?>");
        </script>
    <?php
    } else {
    ?>
        <script>
            alert("<?php echo "Signup Successful, verification code sent to " . $email ?>");
            window.location.replace('verification.php');
        </script>
<?php
    }
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$stmt->close();
$conn->close();

?>