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

$categoryName = isset($_POST['categoryName']) ? trim($_POST['categoryName']) : '';
$categoryRate = isset($_POST['categoryRate']) ? trim($_POST['categoryRate']) : '';
$hostelId = isset($_POST['hostel_id']) ? intval($_POST['hostel_id']) : 0;

if ($hostelId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Please select a valid hostel']);
    $conn->close();
    exit;
}
if ($categoryName === '') {
    echo json_encode(['status' => 'error', 'message' => 'Category name is required']);
    $conn->close();
    exit;
}
if ($categoryRate === '' || !is_numeric($categoryRate)) {
    echo json_encode(['status' => 'error', 'message' => 'A valid rate is required']);
    $conn->close();
    exit;
}

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

// Check for duplicate category name within the same hostel
$dupStmt = $conn->prepare("SELECT id FROM room_category WHERE hostel_id = ? AND room_type = ?");
$dupStmt->bind_param('is', $hostelId, $categoryName);
$dupStmt->execute();
if ($dupStmt->get_result()->num_rows > 0) {
    $dupStmt->close();
    echo json_encode(['status' => 'error', 'message' => 'A category with this name already exists in the selected hostel']);
    $conn->close();
    exit;
}
$dupStmt->close();

$stmt = $conn->prepare("INSERT INTO room_category (hostel_id, room_type, rate) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $hostelId, $categoryName, $categoryRate);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['status' => 'success', 'message' => 'Room category added successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to add room category']);
}

$stmt->close();
$conn->close();
?>
