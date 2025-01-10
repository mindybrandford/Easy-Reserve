<?php
session_start();
require_once 'database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=unauthorized");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['scheduleFile'])) {
    $response = ['success' => false, 'message' => ''];
    
    try {
        // Check file upload
        if ($_FILES['scheduleFile']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload failed');
        }

        // Check file type
        $mimeType = mime_content_type($_FILES['scheduleFile']['tmp_name']);
        if ($mimeType !== 'text/csv' && $mimeType !== 'application/vnd.ms-excel') {
            throw new Exception('Please upload a CSV file');
        }

        // Start transaction
        $conn->begin_transaction();

        // Read CSV file
        $handle = fopen($_FILES['scheduleFile']['tmp_name'], 'r');
        if (!$handle) {
            throw new Exception('Could not open file');
        }

        // Skip header row
        $header = fgetcsv($handle);
        if (!$header) {
            throw new Exception('Empty file');
        }

        // Expected columns: room_name,days,start_time,end_time
        $expectedColumns = ['room_name', 'days', 'start_time', 'end_time'];
        if (count(array_intersect($header, $expectedColumns)) !== count($expectedColumns)) {
            throw new Exception('Invalid CSV format. Required columns: room_name, days, start_time, end_time');
        }

        // Prepare statements for each table
        $tables = [
            'bus' => 'bus_rooms',
            'dtems' => 'dtems_rooms',
            'var' => 'var_rooms',
            'otw' => 'otw_rooms',
            'lft' => 'lft_rooms',
            'bed' => 'bed_rooms'
        ];

        $statements = [];
        foreach ($tables as $prefix => $table) {
            $statements[$prefix] = $conn->prepare(
                "UPDATE $table SET status = 0 
                 WHERE room_name = ? AND days = ? 
                 AND start_time = ? AND end_time = ?"
            );
        }

        // Process each row
        $rowCount = 0;
        $updatedRooms = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $rowCount++;
            
            // Validate row data
            if (count($row) !== 4) {
                throw new Exception("Invalid data in row $rowCount");
            }

            list($room_name, $days, $start_time, $end_time) = array_map('trim', $row);

            // Determine which table to update based on room prefix
            $prefix = strtolower(substr($room_name, 0, strpos($room_name, '_')));
            if (!isset($statements[$prefix])) {
                error_log("Warning: Unknown room prefix for room $room_name in row $rowCount");
                continue;
            }

            // Update the room status
            $stmt = $statements[$prefix];
            $stmt->bind_param('ssss', $room_name, $days, $start_time, $end_time);
            $stmt->execute();
            $updatedRooms += $stmt->affected_rows;
        }

        fclose($handle);

        // Commit transaction
        $conn->commit();
        
        $response['success'] = true;
        $response['message'] = "Successfully processed $rowCount rows and updated $updatedRooms room slots.";
        
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->rollback();
        }
        if (isset($handle)) {
            fclose($handle);
        }
        $response['message'] = $e->getMessage();
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>
