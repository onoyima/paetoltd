<?php
include 'config.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $regNo = isset($_POST['regNo']) ? trim($_POST['regNo']) : '';
    $firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';
    $middleName = isset($_POST['middleName']) ? trim($_POST['middleName']) : '';
    $lastName = isset($_POST['lastName']) ? trim($_POST['lastName']) : '';
    $gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
    $department = isset($_POST['department']) ? trim($_POST['department']) : '';
    $level = isset($_POST['level']) ? trim($_POST['level']) : '';
    $contactNo = isset($_POST['contactNo']) ? trim($_POST['contactNo']) : '';
    $parentPhone = isset($_POST['parentPhone']) ? trim($_POST['parentPhone']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $secretQuestion = isset($_POST['secretQuestion']) ? trim($_POST['secretQuestion']) : '';
    $secretAnswer = isset($_POST['secretAnswer']) ? trim($_POST['secretAnswer']) : '';
    $regDate = date('Y-m-d H:i:s');

    if ($regNo === '' || $firstName === '' || $lastName === '' || $email === '' || $password === '') {
        $response['error'] = "Error: Required fields are missing.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = "Error: Invalid email address.";
    } elseif (strlen($password) < 6) {
        $response['error'] = "Error: Password must be at least 6 characters long.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $userImage = null;

        if (isset($_FILES['userImage']) && $_FILES['userImage']['error'] === UPLOAD_ERR_OK) {
            $imgType = isset($_FILES['userImage']['type']) ? $_FILES['userImage']['type'] : '';
            $imgSize = (int)$_FILES['userImage']['size'];
            if (in_array($imgType, array('image/jpeg', 'image/png', 'image/gif'), true) && $imgSize > 0 && $imgSize <= 2097152) {
                $userImage = file_get_contents($_FILES['userImage']['tmp_name']);
            } else {
                $response['error'] = "Error: Invalid or oversized image (max 2MB, JPEG/PNG/GIF only).";
            }
        }

        if (!isset($response['error'])) {
            // Check for duplicate email
            $stmt = $conn->prepare("SELECT email FROM userregistration WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $response['error'] = "Error: Email already exists";
            } else {
                $stmt->close();

                // Check for duplicate registration number
                $stmt = $conn->prepare("SELECT regNo FROM userregistration WHERE regNo = ?");
                $stmt->bind_param('s', $regNo);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $response['error'] = "Error: Matric number already exists";
                } else {
                    $stmt->close();

                    // Insert new user if both email and regNo are unique
                    $stmt = $conn->prepare("INSERT INTO userregistration (regNo, firstName, middleName, lastName, gender, department, level, contactNo, parentPhone, email, password, userImage, regDate, secret_question, secret_answer) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param('sssssssssssssss', $regNo, $firstName, $middleName, $lastName, $gender, $department, $level, $contactNo, $parentPhone, $email, $hashedPassword, $userImage, $regDate, $secretQuestion, $secretAnswer);

                    if ($stmt->execute()) {
                        $response['success'] = "Signup successful! You can now <a href='index.html'>login</a>.";
                    } else {
                        $response['error'] = "Error: " . $stmt->error;
                    }
                }
            }

            $stmt->close();
        }
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>
