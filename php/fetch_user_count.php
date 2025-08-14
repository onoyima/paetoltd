<?php
include 'config.php';

try {
    $stmt = $conn->prepare("SELECT COUNT(*) as user_count FROM userregistration");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    echo json_encode(['status' => 'success', 'user_count' => $row['user_count']]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
