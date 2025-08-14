<?php
include 'config.php'; // Include your database connection configuration

// Check if POST data is received
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Assuming you receive userId and userData array from frontend
    $userId = $_POST['userId'];
    $userData = $_POST['userData'];

    // Sanitize and validate inputs (for security, depending on your application needs)

    // Prepare SQL statement to update user data
    $sql = "UPDATE userregistration SET
            regNo = ?,
            firstName = ?,
            middleName = ?,
            lastName = ?,
            gender = ?,
            contactNo = ?,
            email = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    if (
        $stmt &&
        $stmt->bind_param(
            "sssssssi",
            $userData['regNo'],
            $userData['firstName'],
            $userData['middleName'],
            $userData['lastName'],
            $userData['gender'],
            $userData['contactNo'],
            $userData['email'],
            $userId
        ) &&
        $stmt->execute()
    ) {
        // Successful update
        echo json_encode(['status' => 'success']);
    } else {
        // Error in SQL statement or execution
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
} else {
    // Handle invalid request method
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
