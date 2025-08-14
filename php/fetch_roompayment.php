<?php
include 'config.php';

try {
    // Use assign_room table for payment analytics
    $stmt = $conn->prepare("SELECT COUNT(*) as payments FROM assign_room");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    echo json_encode(['status' => 'success', 'payments' => $row['payments']]);

  
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
