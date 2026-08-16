<?php
include 'auth_admin.php';
include 'config.php';

pt_require('manage_hostel');

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Unknown error'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $hostelId = isset($_POST['hostel_id']) ? intval($_POST['hostel_id']) : 0;

    if ($id <= 0 || $hostelId <= 0) {
        $response['message'] = 'Invalid room or hostel reference';
    } else {
        $checkStmt = $conn->prepare("SELECT id FROM room WHERE id = ? AND hostel_id = ?");
        $checkStmt->bind_param('ii', $id, $hostelId);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows === 0) {
            $checkStmt->close();
            $response['message'] = 'Room not found in the selected hostel';
            echo json_encode($response);
            $conn->close();
            exit;
        }
        $checkStmt->close();

        $stmt = $conn->prepare("DELETE FROM room WHERE id = ? AND hostel_id = ?");
        $stmt->bind_param('ii', $id, $hostelId);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $response = ['status' => 'success', 'message' => 'Room deleted successfully'];
        } else {
            $response['message'] = 'Failed to delete room';
        }
        $stmt->close();
    }
}

echo json_encode($response);
$conn->close();
