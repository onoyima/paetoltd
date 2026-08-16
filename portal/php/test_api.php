<?php
include 'config.php';
include 'rbac.php';

if (!pt_is_admin()) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Access denied']);
    exit;
}

$response = ['status' => 'error', 'message' => 'Unknown error', 'users' => []];

try {
    $tableCheck = $conn->query("SHOW TABLES LIKE 'assign_room'");
    if ($tableCheck->num_rows == 0) {
        $response['message'] = 'Table assign_room does not exist';
        echo json_encode($response);
        exit;
    }
    
    $sql = "SELECT sn, student_name, matric_no, department, parent_number, level, student_number, room_bunk FROM assign_room";
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
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

header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
$conn->close();
?>