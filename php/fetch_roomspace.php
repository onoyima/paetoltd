<?php
include 'config.php';

try {
    // Calculate total room capacity
    $capacityStmt = $conn->prepare("SELECT SUM(full_capacity) as total_capacity FROM room");
    $capacityStmt->execute();
    $capacityResult = $capacityStmt->get_result();
    $capacityRow = $capacityResult->fetch_assoc();
    $totalCapacity = $capacityRow['total_capacity'] ?: 0;
    
  // Calculate rooms available when level is 1000
$assignedStmt = $conn->prepare("
    SELECT COUNT(*) AS assign_room
    FROM assign_room
    WHERE level = 1000
");
$assignedStmt->execute();
$assignedResult = $assignedStmt->get_result();
$assignedRow = $assignedResult->fetch_assoc();
$assignedRooms = $assignedRow['assign_room'] ?: 0;



    
    // Available space is the count of rooms with level=1000
    $availableSpace = $assignedRooms;
    
    echo json_encode(['status' => 'success', 'total_available_space' => $availableSpace]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
