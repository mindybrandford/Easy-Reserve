<?php
session_start();

// Destroy the session
session_destroy();

// Redirect to the desired page
header('Location: /try2/index.php');
exit(); // Stop further execution
?>
