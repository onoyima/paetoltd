<?php
include 'auth_admin.php'; // Admin-only gate
header('Content-Type: application/json');
include 'config.php'; // Database connection
require_once __DIR__ . '/academic_helper.php';

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
    
    // Hostel filter (0 = all hostels)
    $hostelId = isset($_GET['hostel_id']) ? intval($_GET['hostel_id']) : 0;
    
    // Session filter (defaults to the active academic session)
    $sessionId = isset($_GET['session_id']) ? intval($_GET['session_id']) : pt_active_session_id();
    if ($sessionId <= 0) {
        $response['message'] = 'No active academic session';
        echo json_encode($response);
        exit;
    }
    
    // Query to fetch assigned rooms and student details for the session,
    // optionally filtered by hostel
    $sql = "SELECT 
                ar.`sn`,
                ar.`hostel_id`,
                h.`name` AS `hostel_name`,
                ar.`student_name`,
                ar.`matric_no`,
                ar.`department`,
                ar.`parent_number`,
                ar.`level`,
                ar.`student_number`,
                ar.`room_bunk` 
            FROM `assign_room` ar
            LEFT JOIN `hostel` h ON h.id = ar.hostel_id
            WHERE ar.`session_id` = ?";
    $params = [$sessionId];
    $types = 'i';
    if ($hostelId > 0) {
        $sql .= " AND ar.`hostel_id` = ?";
        $params[] = $hostelId;
        $types .= 'i';
    }
    $sql .= " ORDER BY ar.`sn` ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'sn' => $row['sn'],
            'hostel_id' => (int)$row['hostel_id'],
            'hostel_name' => $row['hostel_name'],
            'student_name' => $row['student_name'],
            'matric_no' => $row['matric_no'],
            'department' => $row['department'],
            'parent_number' => $row['parent_number'],
            'level' => $row['level'],
            'student_number' => $row['student_number'],
            'room_bunk' => $row['room_bunk']
        ];
    }
    $stmt->close();

    $response['status'] = 'success';
    $response['message'] = 'Data fetched successfully';
    $response['users'] = $users;
    $response['session_id'] = $sessionId;
    $response['hostel_id'] = $hostelId;

} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

// Ensure we're sending valid JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
$conn->close();
?>