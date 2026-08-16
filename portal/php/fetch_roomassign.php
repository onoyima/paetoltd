<?php
include 'auth_admin.php'; // Admin-only gate
include 'config.php';
require_once __DIR__ . '/academic_helper.php';

try {
    // Count rooms assigned for the active academic session when matric_no is not null
    $sessionId = pt_active_session_id();
    if ($sessionId <= 0) {
        echo json_encode(['status' => 'success', 'reservations' => 0]);
        exit;
    }
    $stmt = $conn->prepare("SELECT COUNT(*) as reservations FROM assign_room WHERE matric_no IS NOT NULL AND session_id = ?");
    $stmt->bind_param('i', $sessionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    echo json_encode(['status' => 'success', 'reservations' => $row['reservations']]);

  
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
