<?php
require_once __DIR__ . '/rbac.php';

session_start();
pt_require('manage_session');

include 'config.php';

header('Content-Type: application/json');

$response = array('status' => 'error', 'message' => 'Invalid request');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($action === 'create') {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        if ($name === '') {
            $response['message'] = 'Session name is required (e.g. 2026/2027)';
        } else {
            $stmt = $conn->prepare("INSERT INTO academic_session (name, is_active) VALUES (?, 0)");
            $stmt->bind_param('s', $name);
            if ($stmt->execute()) {
                $response = array('status' => 'success', 'message' => 'Session created. Activate it to open bookings.');
            } else {
                $response['message'] = 'Failed to create session. A session with that name may already exist.';
            }
            $stmt->close();
        }
    } elseif ($action === 'activate') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            $response['message'] = 'Session id is required';
        } else {
            // Atomically deactivate the current session and activate the chosen one.
            $conn->begin_transaction();
            try {
                $upd = $conn->prepare("UPDATE academic_session SET is_active = 0 WHERE is_active = 1");
                $upd->execute();
                $upd->close();

                $stmt = $conn->prepare("UPDATE academic_session SET is_active = 1, activated_at = NOW() WHERE id = ?");
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $affected = $stmt->affected_rows;
                $stmt->close();

                $conn->commit();
                $response = $affected > 0
                    ? array('status' => 'success', 'message' => 'Session activated. The previous session is now closed.')
                    : array('status' => 'error', 'message' => 'Session not found.');
            } catch (Exception $e) {
                $conn->rollback();
                $response['message'] = 'Activation failed: ' . $e->getMessage();
            }
        }
    }
}

$conn->close();
echo json_encode($response);
