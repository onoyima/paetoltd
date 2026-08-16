<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($email === '' || $password === '') {
        echo "Error: Email and password are required.";
        exit;
    }

    $stmt = $conn->prepare("SELECT id, email, username, password, role FROM admin WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            session_start();
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            $_SESSION['timeout'] = time() + 1800;

            if (isset($_POST['rememberMe']) && $_POST['rememberMe'] == 'on') {
                setcookie('remember_user', $email, time() + (86400 * 30), "/");
            }

            header('Location: ../admin-dashboard.php');
            exit();
        } else {
            $error = "Error: Incorrect password";
        }
    } else {
        $error = "Error: User not found";
    }

    $stmt->close();
}

$conn->close();

if (isset($error)) {
    echo $error;
}
?>
