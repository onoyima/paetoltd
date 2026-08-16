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
        $response['message'] = 'Invalid category or hostel reference';
    } else {
        $checkStmt = $conn->prepare("SELECT id FROM room_category WHERE id = ? AND hostel_id = ?");
        $checkStmt->bind_param('ii', $id, $hostelId);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows === 0) {
            $checkStmt->close();
            $response['message'] = 'Category not found in the selected hostel';
            echo json_encode($response);
            $conn->close();
            exit;
        }
        $checkStmt->close();

        $usageStmt = $conn->prepare("SELECT COUNT(*) c FROM room WHERE category_id = ?");
        $usageStmt->bind_param('i', $id);
        $usageStmt->execute();
        $usage = (int)$usageStmt->get_result()->fetch_assoc()['c'];
        $usageStmt->close();

        if ($usage > 0) {
            $response['message'] = 'Cannot delete: ' . $usage . ' room(s) currently use this category';
            echo json_encode($response);
            $conn->close();
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM room_category WHERE id = ? AND hostel_id = ?");
        $stmt->bind_param('ii', $id, $hostelId);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $response = ['status' => 'success', 'message' => 'Room category deleted successfully'];
        } else {
            $response['message'] = 'Failed to delete room category';
        }
        $stmt->close();
    }
}

echo json_encode($response);
$conn->close();
