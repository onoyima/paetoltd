<?php
header('Content-Type: application/json');
include 'config.php'; // Database connection

$response = ['status' => 'error', 'message' => 'Unknown error'];

try {
    // Create the table if it doesn't exist
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

    if (!$conn->query($sql)) {
        throw new Exception("Error creating table: " . $conn->error);
    }
    
    // Sample data to insert
    $sampleData = [
        [
            'student_name' => 'John Doe',
            'matric_no' => 'MAT/2024/001',
            'department' => 'Computer Science',
            'parent_number' => '08012345678',
            'level' => '300',
            'student_number' => '09087654321',
            'room_bunk' => 'Block A - Room 101 - Bunk 1'
        ],
        [
            'student_name' => 'Jane Smith',
            'matric_no' => 'MAT/2024/002',
            'department' => 'Electrical Engineering',
            'parent_number' => '08023456789',
            'level' => '200',
            'student_number' => '09076543210',
            'room_bunk' => 'Block B - Room 202 - Bunk 2'
        ],
        [
            'student_name' => 'Michael Johnson',
            'matric_no' => 'MAT/2024/003',
            'department' => 'Mechanical Engineering',
            'parent_number' => '08034567890',
            'level' => '400',
            'student_number' => '09065432109',
            'room_bunk' => 'Block C - Room 303 - Bunk 1'
        ]
    ];
    
    // Prepare and execute insert statements
    $stmt = $conn->prepare("INSERT INTO `assign_room` 
                          (`student_name`, `matric_no`, `department`, `parent_number`, `level`, `student_number`, `room_bunk`) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("sssssss", $student_name, $matric_no, $department, $parent_number, $level, $student_number, $room_bunk);
    
    $insertCount = 0;
    foreach ($sampleData as $data) {
        $student_name = $data['student_name'];
        $matric_no = $data['matric_no'];
        $department = $data['department'];
        $parent_number = $data['parent_number'];
        $level = $data['level'];
        $student_number = $data['student_number'];
        $room_bunk = $data['room_bunk'];
        
        if ($stmt->execute()) {
            $insertCount++;
        } else {
            throw new Exception("Error inserting data: " . $stmt->error);
        }
    }
    
    $response['status'] = 'success';
    $response['message'] = "Successfully inserted $insertCount sample records";
    
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>