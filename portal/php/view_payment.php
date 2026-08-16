<?php
include 'auth_admin.php'; // Admin-only gate
include 'config.php';

pt_require('confirm_payment');

if (!isset($_GET['id'])) {
    echo "Invalid request.";
    exit();
}

$paymentId = intval($_GET['id']);
$stmt = $conn->prepare("SELECT paymentInfo FROM payments WHERE id = ?");
$stmt->bind_param('i', $paymentId);
$stmt->execute();
$stmt->bind_result($paymentInfo);
$stmt->fetch();
$stmt->close();
$conn->close();

if (empty($paymentInfo)) {
    echo "Payment receipt not found.";
    exit();
}

// Receipts are stored as raw image/PDF bytes; serve the most likely type.
if (strpos($paymentInfo, '%PDF-') === 0) {
    header("Content-type: application/pdf");
} else {
    header("Content-type: image/jpeg");
}
echo $paymentInfo;
?>
