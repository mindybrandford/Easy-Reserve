<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$booking_id = $_POST['booking_id'] ?? null;
$status = $_POST['status'] ?? null;

if (!$booking_id || !$status) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

// Validate status
$allowed_statuses = ['Done', 'Cancelled'];
if (!in_array($status, $allowed_statuses)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid status']);
    exit;
}

// Update the booking status
$stmt = $conn->prepare("UPDATE booking SET booking_status = ? WHERE id = ? AND user_id = ?");
$stmt->bind_param("sii", $status, $booking_id, $_SESSION['user_id']);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Booking status updated successfully']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update booking status']);
}

$stmt->close();
$conn->close();
?>
