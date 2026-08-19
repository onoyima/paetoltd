<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/academic_helper.php';

// Require an active student session
if (!isset($_SESSION['user_id']) || !isset($_SESSION['timeout']) || $_SESSION['timeout'] < time()) {
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json');
    echo json_encode(array('button_text' => 'Submit Proof', 'button_link' => 'book-hostel.php'));
    exit;
}

$_SESSION['timeout'] = time() + 1800;

$userId = (int)$_SESSION['user_id'];
$button_text = "Submit Proof";
$button_link = "book-hostel.php";

// Get active session
$activeSession = pt_active_session();
$sessionId = $activeSession ? (int)$activeSession['id'] : 0;

if ($sessionId > 0) {
    // Get user's matric_no
    $stmtUser = $conn->prepare("SELECT regNo FROM userregistration WHERE id = ?");
    $stmtUser->bind_param('i', $userId);
    $stmtUser->execute();
    $resUser = $stmtUser->get_result();
    $regNo = '';
    if ($resUser && $resUser->num_rows > 0) {
        $rowUser = $resUser->fetch_assoc();
        $regNo = $rowUser['regNo'] ?? '';
    }
    $stmtUser->close();

    // Check assign_room — if matric_no exists in any session, user has a room
    if ($regNo !== '') {
        $stmtAssign = $conn->prepare("SELECT id FROM assign_room WHERE matric_no = ? LIMIT 1");
        $stmtAssign->bind_param('s', $regNo);
        $stmtAssign->execute();
        $resAssign = $stmtAssign->get_result();
        if ($resAssign && $resAssign->num_rows > 0) {
            $button_text = "Check Your Room";
            $button_link = "check-room.php";
            $stmtAssign->close();
            $stmtUser->close();
            $conn->close();
            while (ob_get_level()) { ob_end_clean(); }
            header('Content-Type: application/json');
            echo json_encode(array('button_text' => $button_text, 'button_link' => $button_link));
            exit;
        }
        $stmtAssign->close();
    }

    // Not in assign_room — check payments table
    $stmtPay = $conn->prepare("SELECT status FROM payments WHERE userId = ? AND session_id = ? ORDER BY uploadDate DESC LIMIT 1");
    $stmtPay->bind_param('ii', $userId, $sessionId);
    $stmtPay->execute();
    $resPay = $stmtPay->get_result();
    if ($resPay && $resPay->num_rows > 0) {
        $payment = $resPay->fetch_assoc();
        switch (strtolower((string)$payment['status'])) {
            case 'pending':
                $button_text = "Awaiting Approval";
                $button_link = "check-room.php";
                break;
            case 'confirmed':
                $button_text = "Payment Confirmed";
                $button_link = "check-room.php";
                break;
            case 'assigned':
            case 'approved':
                $button_text = "Check Your Room";
                $button_link = "check-room.php";
                break;
            case 'rejected':
                $button_text = "Not Eligible";
                $button_link = "#";
                break;
            default:
                $button_text = "Submit Proof";
                $button_link = "book-hostel.php";
                break;
        }
    }
    $stmtPay->close();
}

$conn->close();

while (ob_get_level()) { ob_end_clean(); }
header('Content-Type: application/json');
echo json_encode(array('button_text' => $button_text, 'button_link' => $button_link));
