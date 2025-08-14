<?php
header('Content-Type: application/json');
include 'config.php'; // Database connection

$response = ['status' => 'error', 'message' => 'Unknown error'];

try {
    // SQL to create the assign_room table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS `assign_room` (
        `sn` INT(11) NOT NULL AUTO_INCREMENT,
        `student_name` VARCHAR(255) NOT NULL,
        `matric_no` VARCHAR(50) NOT NULL,
        `department` VARCHAR(100) NOT NULL,
        `parent_number` VARCHAR(20) NOT NULL,
        `level` VARCHAR(20) NOT NULL,
        `student_number` VARCHAR(20) NOT NULL,
        `room_bunk` VARCHAR(50) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`sn`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"; 

    if ($conn->query($sql) === TRUE) {
        $response['status'] = 'success';
        $response['message'] = 'Table created successfully';
    } else {
        throw new Exception("Error creating table: " . $conn->error);
    }

} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>