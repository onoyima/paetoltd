<?php
include 'config.php'; // Include your database connection

// Check if paymentId is provided in the URL
if (isset($_GET['paymentId'])) {
    $paymentId = $_GET['paymentId'];

    // Fetch payment information from database based on paymentId
    $stmt = $conn->prepare("SELECT * FROM payments WHERE paymentId = ?");
    $stmt->bind_param('i', $paymentId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if payment record exists
    if ($result->num_rows > 0) {
        $payment = $result->fetch_assoc();

        // Output file for download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($payment['paymentInfo']) . '"');
        echo $payment['paymentInfo']; // Output the file content directly
        exit;
    } else {
        // Payment record not found
        echo 'Payment record not found.';
    }

    $stmt->close();
} else {
    // PaymentId not provided
    echo 'PaymentId not provided.';
}

$conn->close();
?>
