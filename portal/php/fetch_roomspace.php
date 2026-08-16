<?php
include 'auth_admin.php'; // Admin-only gate
include 'config.php';

try {
    // Total bed capacity across all rooms
    $capacityStmt = $conn->prepare("SELECT SUM(full_capacity) as total_capacity FROM room");
    $capacityStmt->execute();
    $capacityResult = $capacityStmt->get_result();
    $capacityRow = $capacityResult->fetch_assoc();
    $totalCapacity = (int)($capacityRow['total_capacity'] ?: 0);

    // Count beds already reserved
    $assignedStmt = $conn->prepare("SELECT COUNT(*) AS assigned_count FROM reservations");
    $assignedStmt->execute();
    $assignedResult = $assignedStmt->get_result();
    $assignedRow = $assignedResult->fetch_assoc();
    $assignedCount = (int)($assignedRow['assigned_count'] ?: 0);

    $availableSpace = max(0, $totalCapacity - $assignedCount);

    echo json_encode(['status' => 'success', 'total_available_space' => $availableSpace]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
