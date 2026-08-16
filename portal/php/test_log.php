<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';

$logFile = '../php_debug.log';

try {
    // Test query for rooms assigned
    $stmt = $conn->prepare("SELECT COUNT(*) as assigned FROM assign_room WHERE matric_no IS NOT NULL");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $assigned = $row['assigned'];
    
    // Test query for available rooms
    $availStmt = $conn->prepare("SELECT COUNT(*) as available FROM assign_room WHERE level = 1000");
    $availStmt->execute();
    $availResult = $availStmt->get_result();
    $availRow = $availResult->fetch_assoc();
    $available = $availRow['available'];
    
    $output = json_encode([
        'status' => 'success', 
        'rooms_assigned' => $assigned,
        'rooms_available' => $available
    ]);
    
    file_put_contents($logFile, $output . "\n", FILE_APPEND);
    echo $output;
} catch (Exception $e) {
    $error = json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    file_put_contents($logFile, $error . "\n", FILE_APPEND);
    echo $error;
}
?>