<?php
include 'config.php';

$response = ['status' => 'error', 'message' => 'Unknown error'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $room_number = $_POST['room_number'];
    $category_id = $_POST['category_id'];
    $full_capacity = $_POST['full_capacity'];
    $available_space = $_POST['available_space'];

    $sql = "UPDATE room SET room_number = ?, category_id = ?, full_capacity = ?, available_space = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siiii", $room_number, $category_id, $full_capacity, $available_space, $id);

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
