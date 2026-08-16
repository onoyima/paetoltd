<?php
include 'auth_admin.php';
include 'config.php';

pt_require('manage_hostel');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $roomNumber = isset($_POST['roomNumber']) ? trim($_POST['roomNumber']) : '';
    $roomType = isset($_POST['roomType']) ? intval($_POST['roomType']) : 0;
    $capacity = isset($_POST['capacity']) ? intval($_POST['capacity']) : 0;
    $hostelId = isset($_POST['hostel_id']) ? intval($_POST['hostel_id']) : 1;

    if ($roomNumber === '' || $roomType <= 0 || $capacity < 1) {
        echo json_encode(['status' => 'error', 'message' => 'Room number, category and a capacity of at least 1 are required']);
        $conn->close();
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO room (hostel_id, room_number, category_id, full_capacity, available_space, status) VALUES (?, ?, ?, ?, ?, 'active')");
    $stmt->bind_param("isiii", $hostelId, $roomNumber, $roomType, $capacity, $capacity);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add room']);
    }

    $stmt->close();
    $conn->close();
}
?>
