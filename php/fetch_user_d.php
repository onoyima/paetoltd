<?php

include 'config.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Buffer output to prevent any stray output
ob_start();

// Check if user ID is provided
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Fetch user details from the userregistration table and payment details from the payments table
    $sql = "SELECT u.firstName, u.lastName, u.email, u.contactNo, u.userImage, p.bankName, p.payers_name, p.uploadDate AS paymentDate, p.room AS roomNumber, p.bed AS bedNumber, p.paymentInfo
            FROM userregistration u
            LEFT JOIN payments p ON u.id = p.userId
            WHERE u.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Encode user image as base64 if it exists
        if (!empty($row['userImage'])) {
            $row['userImage'] = base64_encode($row['userImage']);
        } else {
            $row['userImage'] = null; // or handle differently if no image available
        }

        // Encode payment info as base64 if it exists
        if (!empty($row['paymentInfo'])) {
            $row['paymentInfo'] = base64_encode($row['paymentInfo']);
        } else {
            $row['paymentInfo'] = null; // or handle differently if no payment info available
        }

        // Clean the buffer and return user and payment details as JSON response
        ob_end_clean();
        echo json_encode($row);
    } else {
        // Clean the buffer and return specific error message
        ob_end_clean();
        echo json_encode(array('error' => 'No user found with ID: ' . $userId));
    }

    $stmt->close();
    $conn->close();
} else {
    // Clean the buffer and return specific error message
    ob_end_clean();
    echo json_encode(array('error' => 'User ID not provided'));
}

?>
