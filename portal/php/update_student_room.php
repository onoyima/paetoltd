<?php
include 'auth_admin.php'; // Admin-only gate
header('Content-Type: application/json');
include 'config.php'; // Database connection

pt_require('assign_room');

require_once __DIR__ . '/academic_helper.php';

$response = ['status' => 'error', 'message' => 'Unknown error'];

try {
    // Check if ID is provided
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        throw new Exception('Student ID is required');
    }

    $id = intval($_POST['id']);

    // Load the current record so we can keep the hostel/session context and
    // still find the reservation when the matric number changes.
    $curStmt = $conn->prepare("SELECT sn, hostel_id, matric_no, session_id FROM assign_room WHERE sn = ?");
    if (!$curStmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $curStmt->bind_param('i', $id);
    $curStmt->execute();
    $current = $curStmt->get_result()->fetch_assoc();
    $curStmt->close();

    if (!$current) {
        throw new Exception('No record found with the provided ID');
    }

    $hostelId = isset($_POST['hostel_id']) ? intval($_POST['hostel_id']) : 0;
    if ($hostelId <= 0) {
        $hostelId = (int)$current['hostel_id'];
    }
    $sessionId = (int)$current['session_id'];

    // Get the data to update
    $student_name = isset($_POST['student_name']) ? trim($_POST['student_name']) : '';
    $matric_no = isset($_POST['matric_no']) ? trim($_POST['matric_no']) : '';
    $department = isset($_POST['department']) ? trim($_POST['department']) : '';
    $parent_number = isset($_POST['parent_number']) ? trim($_POST['parent_number']) : '';
    $level = isset($_POST['level']) ? trim($_POST['level']) : '';
    $student_number = isset($_POST['student_number']) ? trim($_POST['student_number']) : '';
    $room_bunk = isset($_POST['room_bunk']) ? trim($_POST['room_bunk']) : '';
    $bed_space = isset($_POST['bed_space']) ? trim($_POST['bed_space']) : '';

    if ($room_bunk === '') {
        throw new Exception('Room bunk is required.');
    }
    if ($matric_no === '') {
        throw new Exception('Matric number is required.');
    }

    // Derive the bed space from the room_bunk tail when none is provided
    // (mirrors upload_csv.php, e.g. "ROOM 323-2U" -> "Bunk 2 Up").
    if ($bed_space === '') {
        $bed_space = pt_derive_bed_space($room_bunk);
    }

    // Guard the unique (room_bunk, hostel_id, session_id) constraint
    if ($hostelId > 0 && $sessionId > 0) {
        $dupStmt = $conn->prepare("SELECT sn FROM assign_room WHERE room_bunk = ? AND hostel_id = ? AND session_id = ? AND sn != ? LIMIT 1");
        $dupStmt->bind_param('siii', $room_bunk, $hostelId, $sessionId, $id);
        $dupStmt->execute();
        $dupStmt->store_result();
        $dupExists = $dupStmt->num_rows > 0;
        $dupStmt->close();
        if ($dupExists) {
            throw new Exception('Room bunk "' . $room_bunk . '" is already assigned to another student in this hostel and session.');
        }
    }

    // Guard the unique (matric_no, session_id) constraint when the matric changes
    if ($sessionId > 0 && $matric_no !== $current['matric_no']) {
        $matDupStmt = $conn->prepare("SELECT sn FROM assign_room WHERE matric_no = ? AND session_id = ? AND sn != ? LIMIT 1");
        $matDupStmt->bind_param('sii', $matric_no, $sessionId, $id);
        $matDupStmt->execute();
        $matDupStmt->store_result();
        $matDupExists = $matDupStmt->num_rows > 0;
        $matDupStmt->close();
        if ($matDupExists) {
            throw new Exception('Matric number "' . $matric_no . '" is already assigned in this session.');
        }
    }

    // Prepare and execute update statement
    $stmt = $conn->prepare("UPDATE `assign_room` SET 
                          `student_name` = ?, 
                          `matric_no` = ?, 
                          `department` = ?, 
                          `parent_number` = ?, 
                          `level` = ?, 
                          `student_number` = ?, 
                          `room_bunk` = ?, 
                          `bed_space` = ?, 
                          `updated_at` = NOW() 
                          WHERE `sn` = ?");

    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("ssssssssi", $student_name, $matric_no, $department, $parent_number, $level, $student_number, $room_bunk, $bed_space, $id);

    if (!$stmt->execute()) {
        throw new Exception('Error updating record: ' . $stmt->error);
    }
    $stmt->close();

    // Sync the student's reservation so it carries the same room_bunk and bed_space.
    // Match the student account by the (possibly new) matric number, falling back
    // to the original matric number if the new one has no account yet.
    $userId = null;
    $regNos = array_unique(array_filter(array($matric_no, $current['matric_no'])));
    $userStmt = $conn->prepare("SELECT id FROM userregistration WHERE regNo = ? LIMIT 1");
    if ($userStmt) {
        foreach ($regNos as $reg) {
            $userStmt->bind_param('s', $reg);
            $userStmt->execute();
            $uRes = $userStmt->get_result();
            if ($uRes && $uRes->num_rows > 0) {
                $userId = (int)$uRes->fetch_assoc()['id'];
                break;
            }
        }
        $userStmt->close();
    }

    if ($userId !== null && $sessionId > 0 && $hostelId > 0) {
        $resStmt = $conn->prepare("
            INSERT INTO reservations (user_id, session_id, hostel_id, room_bunk, bed_space, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                hostel_id = VALUES(hostel_id),
                room_bunk = VALUES(room_bunk),
                bed_space = VALUES(bed_space),
                updated_at = NOW()
        ");
        if (!$resStmt) {
            throw new Exception('Assignment saved, but reservation sync failed: ' . $conn->error);
        }
        $resStmt->bind_param("iiiss", $userId, $sessionId, $hostelId, $room_bunk, $bed_space);
        if (!$resStmt->execute()) {
            $resStmt->close();
            throw new Exception('Assignment saved, but reservation sync failed: ' . $resStmt->error);
        }
        $resStmt->close();
    }

    $response['status'] = 'success';
    $response['message'] = 'Student information updated successfully';

} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>
