<?php
session_start(); // Resume session

// Database connection
include 'config.php';

// Initialize response array
$response = array();

// Check if user is logged in
if (isset($_SESSION['userId'])) {
    $userId = $_SESSION['userId'];

    // Prepare SQL query to fetch payments for the logged-in user
    $stmt = $conn->prepare("SELECT paymentId, bankName, payers_name, status, uploadDate FROM payments WHERE userId = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize payments array
    $payments = array();

    // Fetch payments data
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }

    // Close statement
    $stmt->close();

    // Check if payments were found
    if (!empty($payments)) {
        $response['success'] = "Payments data retrieved successfully";
        $response['payments'] = $payments;
    } else {
        $response['error'] = "No payments found for this user";
    }
} else {
    // User is not logged in
    $response['error'] = "User not authenticated";
}

$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
