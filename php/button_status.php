<?php
session_start();
include 'config.php'; // Include your database connection

// Default button configuration
$button_text = "Pending"; // Default to Pending if no payment found
$button_link = "book-hostel.php";

// Function to fetch user payments
function fetch_user_payments($conn) {
    $user_payments = array();
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT * FROM payments WHERE userId = ? ORDER BY paymentDate DESC");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $user_payments[] = $row;
        }
        $stmt->close();
    }
    return $user_payments;
}

$user_payments = fetch_user_payments($conn);

if (!empty($user_payments)) {
    // User payment found, proceed with determining the latest payment status
    $latest_payment = $user_payments[0]; // Assuming the first payment is the latest
    
    switch ($latest_payment['status']) {
        case 'pending':
            $button_text = "Book Room";
            $button_link = "book-hostel.php";
            break;
        case 'confirmed':
            $button_text = "Payment Confirmed";
            $button_link = "#";
            break;
        case 'assigned':
            $button_text = "Your Room Number";
            $button_link = "book-hostel.php";
            break;
        default:
            $button_text = "Book Room";
            $button_link = "book-hostel.php";
            break;
    }
}

$conn->close();

// Return JSON response
$response = array(
    'button_text' => $button_text,
    'button_link' => $button_link
);

header('Content-Type: application/json');
echo json_encode($response);
?>
