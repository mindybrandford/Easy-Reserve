<?php
session_start();
require_once 'database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=unauthorized");
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();

    // List of all room tables
    $tables = ['bus_rooms', 'dtems_rooms', 'var_rooms', 'otw_rooms', 'lft_rooms', 'bed_rooms'];
    $updates = 0;

    foreach ($tables as $table) {
        // Get total number of rows in the table
        $countQuery = "SELECT COUNT(*) as total FROM $table";
        $result = $conn->query($countQuery);
        $total = $result->fetch_assoc()['total'];

        // Calculate how many rows to update (30% of total)
        $updateCount = ceil($total * 0.3);

        // Update random rows to status = 0
        $updateQuery = "
            UPDATE $table 
            SET status = 0 
            WHERE room_id IN (
                SELECT room_id 
                FROM (
                    SELECT room_id 
                    FROM $table 
                    ORDER BY RAND() 
                    LIMIT $updateCount
                ) as random_rows
            )";
        
        $conn->query($updateQuery);
        $updates += $conn->affected_rows;
    }

    // Commit transaction
    $conn->commit();
    
    $response = [
        'success' => true,
        'message' => "Successfully set $updates room slots to unavailable"
    ];

} catch (Exception $e) {
    // Something went wrong, rollback changes
    $conn->rollback();
    $response = [
        'success' => false,
        'message' => "Error: " . $e->getMessage()
    ];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
