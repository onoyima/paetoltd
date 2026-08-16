<?php
require_once __DIR__ . '/rbac.php';

session_start();

// Payment receipts are sensitive: confirm_payment permission required
pt_require('confirm_payment');

include 'config.php';

if (isset($_GET['paymentId'])) {
    $paymentId = intval($_GET['paymentId']);

    $stmt = $conn->prepare("SELECT id, payment_file, paymentInfo FROM payments WHERE id = ?");
    $stmt->bind_param('i', $paymentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $payment = $result->fetch_assoc();
        $stmt->close();
        $conn->close();

        header('Content-Disposition: attachment; filename="payment_receipt_' . (int)$payment['id'] . '.jpg"');

        // New uploads live on disk; stream the file directly.
        if (!empty($payment['payment_file'])) {
            $file = __DIR__ . '/../' . ltrim($payment['payment_file'], '/');
            if (is_file($file) && is_readable($file)) {
                header('Content-Type: image/jpeg');
                header('Content-Length: ' . filesize($file));
                readfile($file);
                exit;
            }
            echo 'Payment receipt file not found.';
            exit;
        }

        // Legacy uploads are stored as raw bytes in the BLOB.
        header('Content-Type: image/jpeg');
        echo $payment['paymentInfo'];
        exit;
    } else {
        echo 'Payment record not found.';
    }

    $stmt->close();
} else {
    echo 'PaymentId not provided.';
}

$conn->close();
