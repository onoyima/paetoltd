<?php
require_once __DIR__ . '/rbac.php';

session_start();

// Admin-only endpoint (used by confirm-payments modal)
if (!pt_is_admin()) {
    header('Content-Type: application/json');
    echo json_encode(array('error' => 'Access denied. Admin login required.'));
    exit;
}

include 'config.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Buffer output to prevent any stray output
ob_start();

// Check if user ID is provided
if (isset($_GET['id'])) {
    $userId = (int)$_GET['id'];
    $paymentId = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;

    // Fetch user details + the specific payment being verified. The receipt
    // image itself is NOT sent here (use php/payment_proof.php?pid=... to
    // stream it), keeping this endpoint fast.
    $sql = "SELECT u.firstName, u.lastName, u.email, u.contactNo,
                   p.id AS payment_id, p.bankName, p.payers_name,
                   p.uploadDate AS paymentDate, p.room AS roomNumber, p.bed AS bedNumber,
                   p.payment_file, (p.paymentInfo IS NOT NULL) AS has_blob
            FROM userregistration u
            LEFT JOIN payments p ON u.id = p.userId AND (p.id = ? OR (0 = ? AND p.id = (SELECT MAX(p2.id) FROM payments p2 WHERE p2.userId = u.id)))
            WHERE u.id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iii', $paymentId, $paymentId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $row['has_proof'] = !empty($row['payment_file']) || !empty($row['has_blob']);

        // Keep the response tiny; the image is streamed separately
        unset($row['has_blob']);

        ob_end_clean();
        echo json_encode($row);
    } else {
        ob_end_clean();
        echo json_encode(array('error' => 'No user found with ID: ' . $userId));
    }

    $stmt->close();
    $conn->close();
} else {
    ob_end_clean();
    echo json_encode(array('error' => 'User ID not provided'));
}
