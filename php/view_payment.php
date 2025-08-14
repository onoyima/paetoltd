<?php
include 'config.php';

if (!isset($_GET['id'])) {
    echo "Invalid request.";
    exit();
}

$paymentId = $_GET['id'];
$stmt = $conn->prepare("SELECT paymentInfo FROM payments WHERE id = ?");
$stmt->bind_param('i', $paymentId);
$stmt->execute();
$stmt->bind_result($paymentInfo);
$stmt->fetch();
$stmt->close();
$conn->close();

header("Content-type: application/pdf");
echo $paymentInfo;
?>
