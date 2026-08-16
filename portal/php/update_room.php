<?php
include 'auth_admin.php';
include 'config.php';

pt_require('manage_hostel');

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Unknown error'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $room_number = isset($_POST['room_number']) ? trim($_POST['room_number']) : '';
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $full_capacity = isset($_POST['full_capacity']) ? intval($_POST['full_capacity']) : 0;
    $available_space = isset($_POST['available_space']) ? intval($_POST['available_space']) : 0;
    $hostelId = isset($_POST['hostel_id']) ? intval($_POST['hostel_id']) : 0;

    if ($id <= 0 || $hostelId <= 0) {
        $response['message'] = 'Invalid room or hostel reference';
    } elseif ($room_number === '') {
        $response['message'] = 'Room number is required';
    } elseif ($category_id <= 0) {
        $response['message'] = 'Please choose a valid room category';
    } elseif ($full_capacity < 1) {
        $response['message'] = 'Capacity must be at least 1';
    } elseif ($available_space < 0) {
        $response['message'] = 'Available space cannot be negative';
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

        $sql = "UPDATE room SET room_number = ?, category_id = ?, full_capacity = ?, available_space = ? WHERE id = ? AND hostel_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siiiii", $room_number, $category_id, $full_capacity, $available_space, $id, $hostelId);

        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Room details updated successfully'];
        } else {
            $response['message'] = 'Failed to update room details';
        }
        $stmt->close();
    }
}

echo json_encode($response);
$conn->close();
?>
