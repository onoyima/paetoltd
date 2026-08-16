<?php
header('Content-Type: application/json');
include 'config.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read JSON input instead of $_POST
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
        echo json_encode($data); // 

    // Check if JSON was parsed successfully
    if ($data === null) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data.']);
        exit;
    }

    $email = isset($data['email']) ? $data['email'] : '';
    $secretQuestion = isset($data['secretQuestion']) ? $data['secretQuestion'] : '';
    $secretAnswer = isset($data['secretAnswer']) ? $data['secretAnswer'] : '';
    $newPassword = '12345678'; // New default password

    if (empty($email) || empty($secretQuestion) || empty($secretAnswer)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    // Securely fetch the user from the database
    $stmt = $conn->prepare("SELECT id FROM userregistration WHERE email = ? AND secret_question = ? AND secret_answer = ?");
    $stmt->bind_param('sss', $email, $secretQuestion, $secretAnswer);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($userId);
        $stmt->fetch();

        // Update the user's password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare("UPDATE userregistration SET password = ? WHERE id = ?");
        $updateStmt->bind_param('si', $hashedPassword, $userId);

        if ($updateStmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Password reset successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error updating password.']);
        }

        $updateStmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or secret answer.']);
    }

    $stmt->close();
    $conn->close();
}
