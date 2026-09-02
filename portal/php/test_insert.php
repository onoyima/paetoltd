<?php
include 'config.php';
$userId = 1;
$sessionId = 1;
$hostelId = 1;
$relPath = 'test/path.jpg';
$bankName = 'Test Bank';
$payersName = 'Test Payer';

$stmt_insert = $conn->prepare("INSERT INTO payments (userId, session_id, hostel_id, paymentInfo, payment_file, bankName, payers_name, status, uploadDate) VALUES (?, ?, ?, NULL, ?, ?, ?, 'Pending', NOW())");

if (!$stmt_insert) {
    die("Prepare failed: " . $conn->error . "\n");
}

$stmt_insert->bind_param("iissss", $userId, $sessionId, $hostelId, $relPath, $bankName, $payersName);

if ($stmt_insert->execute()) {
    echo "Success! ID: " . $stmt_insert->insert_id . "\n";
} else {
    echo "Execute failed: " . $stmt_insert->error . "\n";
}
$stmt_insert->close();
$conn->close();
?>
