<?php
session_start();
include 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $paymentId = $_POST['paymentId'];
    $room = $_POST['room'];
    $bed = $_POST['bed'];

    // Update payment status and assign room/bed
    $stmt = $conn->prepare("UPDATE payments SET status = 'Confirmed', room = ?, bed = ? WHERE id = ?");
    $stmt->bind_param('ssi', $room, $bed, $paymentId);

    if ($stmt->execute()) {
        echo "Payment confirmed and room/bed assigned successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();








?>



