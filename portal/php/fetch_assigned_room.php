<?php
include 'auth_admin.php'; // Admin-only gate
header('Content-Type: application/json');
include 'config.php'; // Database connection

// Enable error reporting for debugging

$response = ['status' => 'error', 'message' => 'Unknown error', 'users' => []];

try {
    // Debug connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Check if table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'assign_room'");
    if ($tableCheck->num_rows == 0) {
        $response['message'] = 'Table assign_room does not exist';
        echo json_encode($response);
        exit;
    }
    
    // Query to fetch assigned rooms and student details
    $sql = "SELECT 
                `sn`,
                `student_name`,
                `matric_no`,
                `department`,
                `parent_number`,
                `level`,
                `student_number`,
                `room_bunk` 
            FROM `assign_room`";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'sn' => $row['sn'],
            'student_name' => $row['student_name'],
            'matric_no' => $row['matric_no'],
            'department' => $row['department'],
            'parent_number' => $row['parent_number'],
            'level' => $row['level'],
            'student_number' => $row['student_number'],
            'room_bunk' => $row['room_bunk']
        ];
    }

    $response['status'] = 'success';
    $response['message'] = 'Data fetched successfully';
    $response['users'] = $users;

} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

// Set success status if we got this far
if (isset($users) && count($users) > 0) {
    $response['status'] = 'success';
    $response['message'] = 'Data fetched successfully';
    $response['users'] = $users;
} else {
    $response['status'] = 'success';
    $response['message'] = 'No assigned rooms found';
    $response['users'] = [];
}

// Ensure we're sending valid JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
$conn->close();
?>