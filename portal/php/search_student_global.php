<?php
// php/search_student_global.php — Global student search API.
// Searches across userregistration, payments, and assign_room tables.
// Returns: student info, payment history (all sessions), room assignment history (all sessions).

include 'auth_admin.php';
header('Content-Type: application/json');
include 'config.php';
require_once __DIR__ . '/academic_helper.php';

$response = ['status' => 'error', 'message' => 'Unknown error'];

try {
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    if ($q === '') {
        $response['message'] = 'Please enter a search term.';
        echo json_encode($response);
        exit;
    }

    // Search userregistration by name, regNo, or email (LIKE for partial match)
    $like = '%' . $q . '%';
    $userStmt = $conn->prepare(
        "SELECT id, regNo, firstName, middleName, lastName, gender, department, level, contactNo, parentPhone, email, userImage
         FROM userregistration
         WHERE firstName LIKE ? OR lastName LIKE ? OR CONCAT(firstName, ' ', lastName) LIKE ?
            OR regNo LIKE ? OR email LIKE ? OR contactNo LIKE ?
         ORDER BY firstName ASC, lastName ASC
         LIMIT 20"
    );
    if (!$userStmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $userStmt->bind_param('ssssss', $like, $like, $like, $like, $like, $like);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $users = [];
    while ($row = $userResult->fetch_assoc()) {
        if (!empty($row['userImage'])) {
            $row['userImage'] = base64_encode($row['userImage']);
        }
        $users[] = $row;
    }
    $userStmt->close();

    if (empty($users)) {
        $response = ['status' => 'success', 'message' => 'No students found matching your search.', 'users' => [], 'payments' => [], 'assignments' => []];
        echo json_encode($response);
        exit;
    }

    // Collect user IDs for payment and room lookups
    $userIds = array_map(function ($u) { return (int)$u['id']; }, $users);
    $placeholders = implode(',', array_fill(0, count($userIds), '?'));
    $idTypes = str_repeat('i', count($userIds));

    // Fetch payment history for all matched users across all sessions
    $payStmt = $conn->prepare(
        "SELECT p.id AS payment_id, p.userId, p.status, p.room, p.bed, p.bankName, p.payers_name,
                p.uploadDate, p.session_id, s.name AS session_name, p.hostel_id, h.name AS hostel_name
         FROM payments p
         LEFT JOIN academic_session s ON p.session_id = s.id
         LEFT JOIN hostel h ON p.hostel_id = h.id
         WHERE p.userId IN ($placeholders)
         ORDER BY p.uploadDate DESC"
    );
    $payments = [];
    if ($payStmt) {
        $payStmt->bind_param($idTypes, ...$userIds);
        $payStmt->execute();
        $payResult = $payStmt->get_result();
        while ($row = $payResult->fetch_assoc()) {
            $payments[] = $row;
        }
        $payStmt->close();
    }

    // Fetch room assignment history for all matched users across all sessions
    $assignStmt = $conn->prepare(
        "SELECT ar.id, ar.sn, ar.hostel_id, h.name AS hostel_name, ar.student_name, ar.matric_no,
                ar.department, ar.parent_number, ar.level, ar.student_number, ar.room_bunk, ar.bed_space,
                ar.session_id, s.name AS session_name
         FROM assign_room ar
         LEFT JOIN hostel h ON h.id = ar.hostel_id
         LEFT JOIN academic_session s ON s.id = ar.session_id
         WHERE ar.matric_no IN (" . implode(',', array_fill(0, count($userIds), '?')) . ")
            OR ar.student_name IN (" . implode(',', array_fill(0, count($users), '?')) . ")
         ORDER BY ar.session_id DESC, ar.sn ASC"
    );
    $assignments = [];
    if ($assignStmt) {
        // Bind matric_no params (strings)
        $bindParams = [];
        $bindTypes = '';
        foreach ($users as $u) {
            $regNo = $u['regNo'] ?? '';
            $bindParams[] = $regNo;
            $bindTypes .= 's';
        }
        foreach ($users as $u) {
            $fullName = trim(($u['firstName'] ?? '') . ' ' . ($u['middleName'] ?? '') . ' ' . ($u['lastName'] ?? ''));
            $bindParams[] = $fullName;
            $bindTypes .= 's';
        }
        $assignStmt->bind_param($bindTypes, ...$bindParams);
        $assignStmt->execute();
        $assignResult = $assignStmt->get_result();
        while ($row = $assignResult->fetch_assoc()) {
            $assignments[] = $row;
        }
        $assignStmt->close();
    }

    $response = [
        'status' => 'success',
        'users' => $users,
        'payments' => $payments,
        'assignments' => $assignments
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>
