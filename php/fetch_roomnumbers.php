<?php
include 'config.php';

try {
    $stmt = $conn->prepare("SELECT COUNT(*) as rooms FROM room");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    echo json_encode(['status' => 'success', 'rooms' => $row['rooms']]);

  
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
