<?php
include 'auth_admin.php'; // Admin-only gate
include 'config.php';

try {
    // Count rooms assigned when matric_no is not null
    $stmt = $conn->prepare("SELECT COUNT(*) as reservations FROM assign_room WHERE matric_no IS NOT NULL");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    echo json_encode(['status' => 'success', 'reservations' => $row['reservations']]);

  
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
