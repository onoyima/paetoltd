<?php
include 'config.php';

$response = ['status' => 'error', 'message' => 'Unknown error'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $room_type = $_POST['room_type'];
    $rate = $_POST['rate'];

    $sql = "UPDATE room_category SET room_type = ?, rate = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $room_type, $rate, $id);

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
