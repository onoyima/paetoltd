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
    
    // Get the data to update
    $student_name = isset($_POST['student_name']) ? $_POST['student_name'] : null;
    $matric_no = isset($_POST['matric_no']) ? $_POST['matric_no'] : null;
    $department = isset($_POST['department']) ? $_POST['department'] : null;
    $parent_number = isset($_POST['parent_number']) ? $_POST['parent_number'] : null;
    $level = isset($_POST['level']) ? $_POST['level'] : null;
    $student_number = isset($_POST['student_number']) ? $_POST['student_number'] : null;
    
    // Prepare and execute update statement
    $stmt = $conn->prepare("UPDATE `assign_room` SET 
                          `student_name` = ?, 
                          `matric_no` = ?, 
                          `department` = ?, 
                          `parent_number` = ?, 
                          `level` = ?, 
                          `student_number` = ? 
                          WHERE `sn` = ?");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("ssssssi", $student_name, $matric_no, $department, $parent_number, $level, $student_number, $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response['status'] = 'success';
            $response['message'] = 'Student information updated successfully';
        } else {
            $response['status'] = 'success';
            $response['message'] = 'No changes made to the record';
        }
    } else {
        throw new Exception("Error updating record: " . $stmt->error);
    }
    
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>