<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    $redirect = ($_SESSION['role'] === 'admin') ? 'admindashboard.php' : 'userdashboard.php';
    header('Location: ' . $redirect);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['login_error'] = 'Invalid request.';
    header('Location: index.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $_SESSION['login_error'] = 'Please enter both email and password.';
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
if (!$stmt) {
    $_SESSION['login_error'] = 'Database error. Please try again.';
    header('Location: index.php');
    exit;
}

$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['user_name'] = $user['name'];

    $redirect = ($user['role'] === 'admin') ? 'admindashboard.php' : 'userdashboard.php';
    header('Location: ' . $redirect);
    exit;
}

$_SESSION['login_error'] = 'Invalid email or password.';
header('Location: index.php');
exit;
