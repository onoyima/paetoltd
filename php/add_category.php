<?php
include 'config.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $categoryName = $_POST['categoryName'];
    $categoryRate = $_POST['categoryRate'];

    try {
        $stmt = $conn->prepare("INSERT INTO room_category (room_type, rate) VALUES (?, ?)");
        $stmt->bind_param("ss", $categoryName, $categoryRate);
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
