<?php
include 'auth_admin.php';
include 'config.php';

pt_require('manage_hostel');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $categoryName = $_POST['categoryName'];
    $categoryRate = $_POST['categoryRate'];
    $hostelId = isset($_POST['hostel_id']) ? intval($_POST['hostel_id']) : 1;

    try {
        $stmt = $conn->prepare("INSERT INTO room_category (hostel_id, room_type, rate) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $hostelId, $categoryName, $categoryRate);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add room category']);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

    $conn->close();
}
?>
