<?php
require_once __DIR__ . '/rbac.php';

session_start();

// Only admins with the confirm_payment permission may confirm payments
pt_require('confirm_payment');

include 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $paymentId = isset($_POST['paymentId']) ? intval($_POST['paymentId']) : 0;
    $room = isset($_POST['room']) ? trim($_POST['room']) : '';
    $bed = isset($_POST['bed']) ? trim($_POST['bed']) : '';

    if ($paymentId <= 0 || $room === '' || $bed === '') {
        echo json_encode(['status' => 'error', 'message' => 'Payment ID, room and bed are required.']);
        exit;
    }

    // Update payment status and assign room/bed
    $stmt = $conn->prepare("UPDATE payments SET status = 'Confirmed', room = ?, bed = ? WHERE id = ?");
    $stmt->bind_param('ssi', $room, $bed, $paymentId);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Payment confirmed and room/bed assigned successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>
