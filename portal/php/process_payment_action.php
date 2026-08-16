<?php
require_once __DIR__ . '/rbac.php';

session_start();

// Payment receipt lookup must only be available to payment-confirming admins
pt_require('confirm_payment');

include 'config.php';

header('Content-Type: application/json');

if (isset($_POST['userid'])) {
    $userid = (int)$_POST['userid'];

    $sql = "SELECT u.firstName, u.lastName, p.paymentInfo, p.status
            FROM payments p
            INNER JOIN userregistration u ON p.userId = u.id
            WHERE p.userId = ?
            ORDER BY p.id DESC
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userid);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        if (!empty($row['paymentInfo'])) {
            $row['paymentInfo'] = base64_encode($row['paymentInfo']);
        }
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'No data found']);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'User ID not provided']);
}

$conn->close();
?>
