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
if (isset($data['userId'], $data['roomCategory'], $data['roomNumber'], $data['bedSpace'])) {
    $userId = (int)$data['userId'];
    $roomCategory = (int)$data['roomCategory'];
    $roomNumber = (int)$data['roomNumber'];
    $bedSpace = trim($data['bedSpace']);

    if ($userId <= 0 || $roomCategory <= 0 || $roomNumber <= 0 || $bedSpace === '') {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
        exit;
    }

    // Verify the selected room exists and belongs to the chosen category
    $roomCheck = $conn->prepare("SELECT id FROM room WHERE id = ? AND category_id = ?");
    $roomCheck->bind_param('ii', $roomNumber, $roomCategory);
    $roomCheck->execute();
    $roomCheck->store_result();

    if ($roomCheck->num_rows == 0) {
        $roomCheck->close();
        echo json_encode(['status' => 'error', 'message' => 'Invalid room selection']);
        exit;
    }
    $roomCheck->close();

    // Remove any existing reservation for this user in this session before assigning a new one
    $del = $conn->prepare("DELETE FROM reservations WHERE user_id = ? AND session_id = ?");
    $del->bind_param('ii', $userId, $sessionId);
    $del->execute();
    $del->close();

    // Store the room assignment
    $stmt = $conn->prepare("INSERT INTO reservations (user_id, session_id, room_category, room_id, bed_space) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('iiiis', $userId, $sessionId, $roomCategory, $roomNumber, $bedSpace);

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
