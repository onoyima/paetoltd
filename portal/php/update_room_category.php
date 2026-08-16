<?php
include 'auth_admin.php';
include 'config.php';

pt_require('manage_hostel');

$response = ['status' => 'error', 'message' => 'Unknown error'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $room_type = $_POST['room_type'];
    $rate = $_POST['rate'];
    $hostelId = isset($_POST['hostel_id']) ? intval($_POST['hostel_id']) : 1;

    $sql = "UPDATE room_category SET room_type = ?, rate = ? WHERE id = ? AND hostel_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $room_type, $rate, $id, $hostelId);

    if ($stmt->execute()) {
        $response = ['status' => 'success'];
    } else {
        $response['message'] = 'Failed to update category details';
    }

    $stmt->close();
}

echo json_encode($response);
$conn->close();
?>
