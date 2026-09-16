<?php
require_once __DIR__ . '/php/rbac.php';

// Database connection
include 'php/config.php';
require_once __DIR__ . '/php/academic_helper.php';

// Admin-only endpoint with the assign_room permission
session_start();
pt_require('assign_room');

// Assignments can only be made for the active academic session
$activeSession = pt_active_session();
if (!$activeSession) {
    echo json_encode(['status' => 'error', 'message' => 'No active session. Activate a session before assigning rooms.']);
    exit;
}
$sessionId = (int)$activeSession['id'];

$data = json_decode(file_get_contents("php://input"), true);

// Validate input data
if (isset($data['userId'], $data['bedSpace'])) {
    $userId = (int)$data['userId'];
    $assignRoomId = (int)$data['bedSpace']; // bedSpace is the ID from assign_room

    if ($userId <= 0 || $assignRoomId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'User and Bed Space are required']);
        exit;
    }

    // Fetch necessary info from userregistration
    $userQ = $conn->prepare("SELECT firstName, middleName, lastName, regNo, department, parentPhone, level, contactNo FROM userregistration WHERE id = ?");
    $userQ->bind_param('i', $userId);
    $userQ->execute();
    $res = $userQ->get_result();
    
    if ($res->num_rows == 0) {
        $userQ->close();
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
        exit;
    }
    
    $user = $res->fetch_assoc();
    $userQ->close();
    
    $student_name = trim($user['firstName'] . ' ' . $user['middleName'] . ' ' . $user['lastName']);
    $matric_no = $user['regNo'];
    $department = $user['department'];
    $parent_number = $user['parentPhone'];
    $level = $user['level'];
    $student_number = $user['contactNo'];

    // Check if the assign_room row is still available
    $checkQ = $conn->prepare("SELECT id FROM assign_room WHERE id = ? AND student_name IS NULL AND matric_no IS NULL");
    $checkQ->bind_param('i', $assignRoomId);
    $checkQ->execute();
    $checkRes = $checkQ->get_result();
    
    if ($checkRes->num_rows == 0) {
        $checkQ->close();
        echo json_encode(['status' => 'error', 'message' => 'The selected bed space is no longer available']);
        exit;
    }
    $checkQ->close();

    // Clear any previous assignment for this student in the current session
    if (!empty($matric_no)) {
        $unassign = $conn->prepare("UPDATE assign_room SET student_name = NULL, matric_no = NULL, department = NULL, parent_number = NULL, level = NULL, student_number = NULL, updated_at = NOW() WHERE matric_no = ? AND session_id = ?");
        $unassign->bind_param('si', $matric_no, $sessionId);
        $unassign->execute();
        $unassign->close();
    }

    // Update the assign_room record
    $stmt = $conn->prepare("UPDATE assign_room SET student_name = ?, matric_no = ?, department = ?, parent_number = ?, level = ?, student_number = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param('ssssssi', $student_name, $matric_no, $department, $parent_number, $level, $student_number, $assignRoomId);

    if ($stmt->execute()) {
        // Mark the payment as assigned so the student dashboard reflects it
        $upd = $conn->prepare("UPDATE payments SET status = 'Assigned' WHERE userId = ?");
        $upd->bind_param('i', $userId);
        $upd->execute();
        $upd->close();

        $stmt->close();
        $conn->close();
        echo json_encode(['status' => 'success', 'message' => 'Room assigned successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Room assignment failed']);
        $stmt->close();
        $conn->close();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
}
?>
