<?php
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_SESSION['user_id'];
    $paymentInfo = null;

    // Handle file upload
    if (isset($_FILES['paymentInfo']) && $_FILES['paymentInfo']['error'] === UPLOAD_ERR_OK) {
        $paymentInfo = file_get_contents($_FILES['paymentInfo']['tmp_name']);
    }

    if ($paymentInfo) {
        $stmt = $conn->prepare("INSERT INTO payments (userId, paymentInfo, status, uploadDate) VALUES (?, ?, 'Pending', NOW())");
        $stmt->bind_param('is', $userId, $paymentInfo);

        if ($stmt->execute()) {
            echo "Payment information uploaded successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error uploading payment information.";
    }

    $conn->close();
}
?>
