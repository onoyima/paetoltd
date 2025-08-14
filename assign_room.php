<?php
// Database connection
include 'php/config.php';

$data = json_decode(file_get_contents("php://input"), true);

// Validate input data
if (isset($data['userId'], $data['roomCategory'], $data['roomNumber'], $data['bedSpace'])) {
    $userId = $data['userId'];
    $roomCategory = $data['roomCategory'];
    $roomNumber = $data['roomNumber'];
    $bedSpace = $data['bedSpace'];

    // Perform SQL update
    $stmt = $pdo->prepare("UPDATE users SET room_category_id = ?, room_number_id = ?, bed_space = ? WHERE id = ?");
    $stmt->execute([$roomCategory, $roomNumber, $bedSpace, $userId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Room assignment failed']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
}
?>
