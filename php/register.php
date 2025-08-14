<?php
include 'config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Array to hold response data
$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and sanitize form inputs
    $regNo = $_POST['regNo'];
    $firstName = $_POST['firstName'];
    $middleName = $_POST['middleName'];
    $lastName = $_POST['lastName'];
    $gender = $_POST['gender'];
    $contactNo = $_POST['contactNo'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Encrypt the password
    $regDate = date('Y-m-d H:i:s');
    $userImage = null;

    // Handle file upload
    if (isset($_FILES['userImage']) && $_FILES['userImage']['error'] === UPLOAD_ERR_OK) {
        $userImage = file_get_contents($_FILES['userImage']['tmp_name']);
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT email FROM userregistration WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Email already exists, prepare error response
        $response['error'] = "Error: Email already exists";
    } else {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO userregistration (regNo, firstName, middleName, lastName, gender, contactNo, email, password, userImage, regDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssssssss', $regNo, $firstName, $middleName, $lastName, $gender, $contactNo, $email, $password, $userImage, $regDate);

        if ($stmt->execute()) {
            // Success response
            $response['success'] = "Signup successful! You can now <a href='login.html'>login</a>.";
        } else {
            // Error response
            $response['error'] = "Error: " . $stmt->error;
        }
    }

    $stmt->close();
}

$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
