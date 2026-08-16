<?php
include 'auth_admin.php';
include 'config.php';

pt_require('manage_hostel');

$response = ['status' => 'error', 'message' => 'Unknown error'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $room_number = $_POST['room_number'];
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $full_capacity = isset($_POST['full_capacity']) ? intval($_POST['full_capacity']) : 0;
    $available_space = isset($_POST['available_space']) ? intval($_POST['available_space']) : 0;
    $hostelId = isset($_POST['hostel_id']) ? intval($_POST['hostel_id']) : 1;

    $sql = "UPDATE room SET room_number = ?, category_id = ?, full_capacity = ?, available_space = ? WHERE id = ? AND hostel_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siiiii", $room_number, $category_id, $full_capacity, $available_space, $id, $hostelId);

    if ($stmt->execute()) {
        $response = ['status' => 'success'];
    } else {
        $response['message'] = 'Failed to update room details';
    }

    $stmt->close();
}

echo json_encode($response);
$conn->close();
?>
