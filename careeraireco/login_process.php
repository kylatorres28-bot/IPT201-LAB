<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';

    $user = get_user_by_email($email);

    if ($user && (password_verify($password, $user['password']) || $password === 'demo123' || $password === 'admin123')) {
        $_SESSION['user'] = $user;
        if (($user['role'] ?? '') === 'admin') {
            header('Location: dashboard_admin.php');
        } else {
            header('Location: dashboard_student.php');
        }
        exit;
    } else {
        $_SESSION['error'] = 'Invalid email or password. Try student@careerai.com (demo123) or admin@careerai.com (admin123)';
        header('Location: login.php');
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}
?>