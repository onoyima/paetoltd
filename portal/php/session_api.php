<?php
require_once __DIR__ . '/rbac.php';

session_start();
pt_require('manage_session');

include 'config.php';

header('Content-Type: application/json');

$response = array('status' => 'error', 'message' => 'Invalid request');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if (!pt_is_superadmin()) {
        $response['message'] = 'Only a super admin can create or activate sessions.';
        echo json_encode($response);
        exit;
    }

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
    } elseif ($action === 'delete') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            $response['message'] = 'Session id is required';
        } else {
            $stmt = $conn->prepare("SELECT is_active FROM academic_session WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$row) {
                $response['message'] = 'Session not found.';
            } elseif ((int)$row['is_active'] === 1) {
                $response['message'] = 'Cannot delete the active session. Activate another session first.';
            } else {
                $referenced = false;
                foreach (array('assign_room', 'payments', 'reservations') as $table) {
                    $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM `$table` WHERE session_id = ?");
                    $stmt->bind_param('i', $id);
                    $stmt->execute();
                    $count = (int)$stmt->get_result()->fetch_assoc()['c'];
                    $stmt->close();
                    if ($count > 0) {
                        $referenced = true;
                        $response['message'] = 'Cannot delete this session — it has ' . $count . ' record(s) in ' . $table . '.';
                        break;
                    }
                }
                if (!$referenced) {
                    $stmt = $conn->prepare("DELETE FROM academic_session WHERE id = ?");
                    $stmt->bind_param('i', $id);
                    if ($stmt->execute()) {
                        $response = array('status' => 'success', 'message' => 'Session deleted.');
                    } else {
                        $response['message'] = 'Failed to delete session.';
                    }
                    $stmt->close();
                }
            }
        }
    }
}

$conn->close();
echo json_encode($response);
