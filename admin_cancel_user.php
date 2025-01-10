<?php
session_start();
require_once 'database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if we have the required parameters
if (!isset($_POST['building']) || !isset($_POST['room_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$building = strtolower($_POST['building']);
$room_id = $_POST['room_id'];

// Map building prefix to table name
$tableMap = [
    'bus' => 'bus_rooms',
    'dtems' => 'dtems_rooms',
    'var' => 'var_rooms',
    'otw' => 'otw_rooms',
    'lft' => 'lft_rooms',
    'bed' => 'bed_rooms'
];

if (!isset($tableMap[$building])) {
    echo json_encode(['success' => false, 'message' => 'Invalid building type']);
    exit();
}

$table = $tableMap[$building];

try {
    // Start transaction
    $conn->begin_transaction();

    // Update the room status
    $stmt = $conn->prepare("UPDATE $table SET status = 1, user_id = NULL WHERE room_id = ?");
    $stmt->bind_param('i', $room_id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception('Room not found or already cancelled');
    }

    // Get room details for the response
    $stmt = $conn->prepare("SELECT room_name, days, start_time FROM $table WHERE room_id = ?");
    $stmt->bind_param('i', $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $roomDetails = $result->fetch_assoc();

    // Commit the transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "Successfully cancelled booking for {$roomDetails['room_name']} on {$roomDetails['days']} at {$roomDetails['start_time']}"
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
