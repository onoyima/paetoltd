<?php
include 'config.php';

try {
    $stmt = $conn->prepare("SELECT COUNT(*) as userRoomCategory FROM room_category");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    echo json_encode(['status' => 'success', 'userRoomCategory' => $row['userRoomCategory']]);

  
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
