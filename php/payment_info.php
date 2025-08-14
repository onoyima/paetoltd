<?php
session_start(); // Resume session

// Database connection
include 'config.php';

// Initialize response array
$response = array();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $userId = $_POST['id'];
    $bankName = $_POST['bankName'];
    $payersName = $_POST['payers_name'];
    
    // Check if user has already submitted payment info
    $stmt_check = $conn->prepare("SELECT userId FROM payments WHERE userId = ?");
    $stmt_check->bind_param("i", $userId);
    $stmt_check->execute();
    $stmt_check->store_result();
    
    if ($stmt_check->num_rows > 0) {
        // User has already submitted payment info
        $response['error'] = "User has already submitted payment information";
    } else {
        // Handle file upload if a file is selected
        if (isset($_FILES['paymentInfo']) && $_FILES['paymentInfo']['error'] === UPLOAD_ERR_OK) {
            // Check file type
            $allowed_types = array('application/pdf', 'image/jpeg','image/jpg', 'image/png');
            $file_type = $_FILES['paymentInfo']['type'];

            if (!in_array($file_type, $allowed_types)) {
                $response['error'] = "File type not supported. Please upload PDF, JPEG, jpg, or PNG files.";
            } else {
                // Get file data
                $fileTmpPath = $_FILES['paymentInfo']['tmp_name'];
                $fileData = file_get_contents($fileTmpPath);
                
                // Prepare SQL query
                $stmt_insert = $conn->prepare("INSERT INTO payments (userId, paymentInfo, bankName, payers_name, status, uploadDate) VALUES (?, ?, ?, ?, 'Pending', NOW())");
                $stmt_insert->bind_param("isss", $userId, $fileData, $bankName, $payersName);
                
                // Execute the query
                if ($stmt_insert->execute()) {
                    // Success
                    $response['success'] = "Payment information uploaded successfully";
                } else {
                    // Error
                    $response['error'] = "Failed to upload payment information";
                }
                
                $stmt_insert->close();
            }
        } else {
            // No file selected
            $response['error'] = "Please select a file to upload";
        }
    }
    
    $stmt_check->close();
}

$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
