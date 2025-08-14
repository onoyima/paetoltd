<?php
header('Content-Type: application/json');
include 'config.php'; // Database connection

$response = ['status' => 'error', 'message' => 'Unknown error'];

try {
    // Check if ID is provided
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        throw new Exception('Student ID is required');
    }
    
    $id = intval($_POST['id']);
    
    // Prepare and execute delete statement
    $stmt = $conn->prepare("DELETE FROM `assign_room` WHERE `sn` = ?");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response['status'] = 'success';
            $response['message'] = 'Room assignment revoked successfully';
        } else {
            throw new Exception('No record found with the provided ID');
        }
    } else {
        throw new Exception("Error deleting record: " . $stmt->error);
    }
    
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>