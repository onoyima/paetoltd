<?php
require_once __DIR__ . '/rbac.php';

session_start();

// Payment receipts are sensitive: confirm_payment permission required
pt_require('confirm_payment');

include 'config.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo "Invalid request.";
    exit();
}

$paymentId = intval($_GET['id']);
$stmt = $conn->prepare("SELECT payment_file, paymentInfo FROM payments WHERE id = ?");
$stmt->bind_param('i', $paymentId);
$stmt->execute();
$result = $stmt->get_result();
$payment = $result ? $result->fetch_assoc() : null;
$stmt->close();
$conn->close();

// New uploads live on disk; stream the file directly.
if (!empty($payment['payment_file'])) {
    $file = __DIR__ . '/../' . ltrim($payment['payment_file'], '/');
    if (is_file($file) && is_readable($file)) {
        header('Content-Type: image/jpeg');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit();
    }
    http_response_code(404);
    echo "Payment receipt file not found.";
    exit();
}

// Legacy receipts are stored as raw image/PDF bytes; serve the most likely type.
if (empty($payment['paymentInfo'])) {
    http_response_code(404);
    echo "Payment receipt not found.";
    exit();
}

if (strpos($payment['paymentInfo'], '%PDF-') === 0) {
    header("Content-type: application/pdf");
} else {
    header("Content-type: image/jpeg");
}
echo $payment['paymentInfo'];
