<?php
include 'auth_admin.php';
include 'config.php';

pt_require('manage_hostel');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    $conn->close();
    exit;
}

$roomNumber = isset($_POST['roomNumber']) ? trim($_POST['roomNumber']) : '';
$roomType = isset($_POST['roomType']) ? intval($_POST['roomType']) : 0;
$capacity = isset($_POST['capacity']) ? intval($_POST['capacity']) : 0;
$hostelId = isset($_POST['hostel_id']) ? intval($_POST['hostel_id']) : 0;

if ($hostelId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Please select a valid hostel']);
    $conn->close();
    exit;
}
if ($roomNumber === '') {
    echo json_encode(['status' => 'error', 'message' => 'Room number is required']);
    $conn->close();
    exit;
}
if ($roomType <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Please choose a room category']);
    $conn->close();
    exit;
}
if ($capacity < 1) {
    echo json_encode(['status' => 'error', 'message' => 'Capacity must be at least 1']);
    $conn->close();
    exit;
}

// Ensure the hostel exists
$hostelStmt = $conn->prepare("SELECT id FROM hostel WHERE id = ?");
$hostelStmt->bind_param('i', $hostelId);
$hostelStmt->execute();
if ($hostelStmt->get_result()->num_rows === 0) {
    $hostelStmt->close();
    echo json_encode(['status' => 'error', 'message' => 'Selected hostel does not exist']);
    $conn->close();
    exit;
}
$hostelStmt->close();

// Check for a duplicate room number within the same hostel
$dupStmt = $conn->prepare("SELECT id FROM room WHERE hostel_id = ? AND room_number = ?");
$dupStmt->bind_param('is', $hostelId, $roomNumber);
$dupStmt->execute();
if ($dupStmt->get_result()->num_rows > 0) {
    $dupStmt->close();
    echo json_encode(['status' => 'error', 'message' => 'A room with this number already exists in the selected hostel']);
    $conn->close();
    exit;
}
$dupStmt->close();

$stmt = $conn->prepare("INSERT INTO room (hostel_id, room_number, category_id, full_capacity, available_space, status) VALUES (?, ?, ?, ?, ?, 'Available')");
$stmt->bind_param("isiii", $hostelId, $roomNumber, $roomType, $capacity, $capacity);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['status' => 'success', 'message' => 'Room added successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to add room']);
}

$stmt->close();
$conn->close();
?>
