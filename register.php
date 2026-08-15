<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    $redirect = ($_SESSION['role'] === 'admin') ? 'admindashboard.php' : 'userdashboard.php';
    header('Location: ' . $redirect);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['register_error'] = 'Invalid request.';
    header('Location: index.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$phone = trim($_POST['phone'] ?? '');
$gender = $_POST['gender'] ?? '';
$role = ($_POST['role'] ?? 'user') === 'admin' ? 'admin' : 'user';

if ($name === '' || $email === '' || $password === '' || $phone === '' || $gender === '') {
    $_SESSION['register_error'] = 'All fields are required.';
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
if (!$stmt) {
    $_SESSION['register_error'] = 'Database error. Please try again.';
    header('Location: index.php');
    exit;
}

$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->fetch_assoc()) {
    $_SESSION['register_error'] = 'This email is already registered.';
    header('Location: index.php');
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

$stmt = $conn->prepare('INSERT INTO users (name, email, password, phone, gender, role) VALUES (?, ?, ?, ?, ?, ?)');
if (!$stmt) {
    $_SESSION['register_error'] = 'Unable to create account right now.';
    header('Location: index.php');
    exit;
}

$stmt->bind_param('ssssss', $name, $email, $hashedPassword, $phone, $gender, $role);
if (!$stmt->execute()) {
    if ($conn->errno == 1062) {
        $_SESSION['register_error'] = 'This email is already registered.';
        header('Location: index.php');
        exit;
    }

    $_SESSION['register_error'] = 'Unable to create account right now.';
    header('Location: index.php');
    exit;
}

$_SESSION['user_id'] = (int) $conn->insert_id;
$_SESSION['role'] = $role;
$_SESSION['user_name'] = $name;

$redirect = ($role === 'admin') ? 'admindashboard.php' : 'userdashboard.php';
header('Location: ' . $redirect);
exit;
