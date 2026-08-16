<?php
// Include the connection once (config.php is guarded against double-include)
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/academic_helper.php';

// Function to fetch user details
function fetch_user_details() {
    global $conn;

    $students_info = array();

    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT * FROM userregistration WHERE id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $students_info = $result->fetch_assoc();
        }

        $stmt->close();
    }

    return $students_info;
}

// Function to fetch user payments
function fetch_user_payments() {
    global $conn;

    $user_payments = array();

    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $session_id = pt_active_session_id();
        // Only the active session's payments matter to the student dashboard;
        // old sessions become read-only/hidden once a new session is activated.
        if ($session_id > 0) {
            $stmt = $conn->prepare("SELECT * FROM payments WHERE userId = ? AND session_id = ? ORDER BY uploadDate DESC");
            $stmt->bind_param('ii', $user_id, $session_id);
        } else {
            $stmt = $conn->prepare("SELECT * FROM payments WHERE userId = ? ORDER BY uploadDate DESC");
            $stmt->bind_param('i', $user_id);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            // paymentInfo holds the raw uploaded file bytes (image or PDF).
            // Keep it untouched; the view layer decides how to render it.
            $user_payments[] = $row;
        }

        $stmt->close();
    }

    return $user_payments;
}
?>
