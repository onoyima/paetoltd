<?php
require_once __DIR__ . '/rbac.php';

session_start();

include 'config.php';

if (!isset($_GET['pid'])) {
    http_response_code(400);
    echo 'Payment ID not provided.';
    exit;
}

$paymentId = (int)$_GET['pid'];
if ($paymentId <= 0) {
    http_response_code(400);
    echo 'Invalid payment ID.';
    exit;
}

$stmt = $conn->prepare("SELECT userId, payment_file, paymentInfo FROM payments WHERE id = ?");
$stmt->bind_param('i', $paymentId);
$stmt->execute();
$result = $stmt->get_result();
$payment = $result ? $result->fetch_assoc() : null;
$stmt->close();
$conn->close();

if (!$payment) {
    http_response_code(404);
    echo 'Payment record not found.';
    exit;
}

// Payment receipts are sensitive: admins need the confirm_payment permission,
// students may only ever see their own receipt.
$isOwner = isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$payment['userId'];
if (!pt_can('confirm_payment') && !$isOwner) {
    header('Content-Type: application/json');
    echo json_encode(array('status' => 'error', 'message' => 'Access denied.'));
    exit;
}

// New uploads are stored as files on disk (JPEG after compression)
if (!empty($payment['payment_file'])) {
    $file = __DIR__ . '/../' . ltrim($payment['payment_file'], '/');
    if (is_file($file) && is_readable($file)) {
        header('Content-Type: image/jpeg');
        header('Cache-Control: private, max-age=3600');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
    http_response_code(404);
    echo 'Payment receipt file not found.';
    exit;
}

// Legacy uploads are stored as raw bytes in the BLOB
if (!empty($payment['paymentInfo'])) {
    if (strpos($payment['paymentInfo'], '%PDF-') === 0) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="payment_receipt_' . $paymentId . '.pdf"');
    } else {
        header('Content-Type: image/jpeg');
    }
    header('Cache-Control: private, max-age=3600');
    echo $payment['paymentInfo'];
    exit;
}

http_response_code(404);
echo 'No payment receipt attached.';
