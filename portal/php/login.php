<?php
include 'config.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($email === '' || $password === '') {
        $response['error'] = "Error: Email and password are required.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM userregistration WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $response['redirect'] = "dashboard.php";

                session_start();
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['username'] = $user['firstName'] . ' ' . $user['lastName'];

                $_SESSION['timeout'] = time() + 1800;

                if (isset($_POST['rememberMe']) && $_POST['rememberMe'] == 'on') {
                    setcookie('remember_user', $email, time() + (86400 * 30), "/");
                }
            } else {
                $response['error'] = "Error: Incorrect password";
            }
        } else {
            $response['error'] = "Error: User not found";
        }

        $stmt->close();
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>
