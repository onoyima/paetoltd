<?php
// Function to fetch user details
function fetch_user_details() {
    include 'config.php'; // Include your database connection

    // Initialize variable to hold user information
    $students_info = array();

    // Check if user is logged in
    if (isset($_SESSION['user_id'])) {
        // Fetch user details from database using user_id from session
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT * FROM userregistration WHERE id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $students_info = $result->fetch_assoc();
        }

        $stmt->close();
    }

    $conn->close();

    return $students_info;
}
// Function to fetch user payments
// Function to fetch user payments
// Function to fetch user payments
function fetch_user_payments() {
    include 'config.php'; // Include your database connection

    // Initialize variable to hold payments information
    $user_payments = array();

    // Check if user is logged in
    if (isset($_SESSION['user_id'])) {
        // Fetch user payments from database using user_id from session
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT * FROM payments WHERE userId = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch all payments for the user
        while ($row = $result->fetch_assoc()) {
            // Check if paymentInfo is a PDF (assuming 'paymentInfo' is the Blob column)
            if (strpos($row['paymentInfo'], '%PDF-') === 0) {
                // It's a PDF, decode it
                $decoded_pdf = base64_decode($row['paymentInfo']);
                $row['decoded_payment_info'] = $decoded_pdf;
                $row['is_pdf'] = true;
            } else {
                // It's an image or other format, handle accordingly
                $row['decoded_payment_info'] = $row['paymentInfo']; // Assuming image data directly
                $row['is_pdf'] = false;
            }
            $user_payments[] = $row;
        }

        $stmt->close();
    }

    $conn->close();

    return $user_payments;
}



?>
