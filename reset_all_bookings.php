<?php
session_start();
require_once 'database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=unauthorized");
    exit();
}

// Function to reset a specific room table
function resetRoomTable($conn, $tableName) {
    // Only reset user_id to NULL and status to 1 (available)
    $query = "UPDATE $tableName SET user_id = NULL, status = 1 WHERE user_id IS NOT NULL OR status = 0";
    return $conn->query($query);
}

try {
    // Start transaction
    $conn->begin_transaction();

    // List of all room tables
    $tables = ['bus_rooms', 'dtems_rooms', 'var_rooms', 'otw_rooms', 'lft_rooms', 'bed_rooms'];
    
    // Reset each table
    foreach ($tables as $table) {
        if (!resetRoomTable($conn, $table)) {
            throw new Exception("Failed to reset table: $table");
        }
    }

    // If we got here, everything worked
    $conn->commit();
    header("Location: bookings.php?success=1");
    exit();

} catch (Exception $e) {
    // Something went wrong, rollback changes
    $conn->rollback();
    error_log("Reset bookings error: " . $e->getMessage());
    header("Location: bookings.php?error=1");
    exit();
}
?>
