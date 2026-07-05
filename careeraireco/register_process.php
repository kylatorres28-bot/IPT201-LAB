<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? 'New Student');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? 'demo123';
    $education = $_POST['education'] ?? 'Bachelor';
    $specialization = $_POST['specialization'] ?? 'Computer Science';

    // Check if user exists
    $existing = get_user_by_email($email);
    if ($existing) {
        $_SESSION['error'] = 'An account with this email already exists. Please login.';
        header('Location: login.php');
        exit;
    }

    $new_user = [
        'name' => $name,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'role' => 'student',
        'education' => $education,
        'specialization' => $specialization,
        'cgpa' => 85,
        'skills_score' => 75,
        'interest_profile' => 'Investigative & Realistic',
        'top_career' => 'AI & Software Engineer',
        'created_at' => date('Y-m-d H:i:s')
    ];

    $saved_user = save_user_account($new_user);
    $_SESSION['user'] = $saved_user;
    $_SESSION['success'] = 'Account created successfully! Welcome to CareerAI.';

    header('Location: dashboard_student.php');
    exit;
} else {
    header('Location: register.php');
    exit;
}
?>