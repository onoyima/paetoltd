<?php
include 'config.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $roomNumber = $_POST['roomNumber'];
    $roomType = $_POST['roomType'];
    $capacity = $_POST['capacity'];

    try {
        $stmt = $conn->prepare("INSERT INTO room (room_number, category_id, available_space) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $roomNumber, $roomType, $capacity);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add room']);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

    $conn->close();
}
?>
