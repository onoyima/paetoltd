<?php
session_start();
require_once __DIR__ . '/config.php'; // Guarded connection
require_once __DIR__ . '/academic_helper.php';

// Require an active student session
if (!isset($_SESSION['user_id']) || !isset($_SESSION['timeout']) || $_SESSION['timeout'] < time()) {
    header('Content-Type: application/json');
    echo json_encode(array('button_text' => 'Submit Proof', 'button_link' => 'book-hostel.php'));
    exit;
}

$_SESSION['timeout'] = time() + 1800;

// Default button configuration
$button_text = "Pending"; // Default to Pending if no payment found
$button_link = "book-hostel.php";

// Function to fetch user payments (active session only)
function fetch_user_payments($conn) {
    $user_payments = array();
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $session_id = pt_active_session_id();
        if ($session_id > 0) {
            $stmt = $conn->prepare("SELECT * FROM payments WHERE userId = ? AND session_id = ? ORDER BY uploadDate DESC");
            $stmt->bind_param('ii', $user_id, $session_id);
        } else {
            $stmt = $conn->prepare("SELECT * FROM payments WHERE userId = ? ORDER BY uploadDate DESC");
            $stmt->bind_param('i', $user_id);
        }
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
    
    switch (strtolower((string)$latest_payment['status'])) {
        case 'pending':
            $button_text = "Awaiting Approval";
            $button_link = "check-room.php";
            break;
        case 'confirmed':
            $button_text = "Payment Confirmed";
            $button_link = "check-room.php";
            break;
        case 'assigned':
        case 'approved':
            $button_text = "Your Room Number";
            $button_link = "check-room.php";
            break;
        case 'rejected':
            $button_text = "Not Eligible";
            $button_link = "#";
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
