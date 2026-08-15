<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "login_system_db";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->query("
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(30) NOT NULL,
        gender VARCHAR(20) NOT NULL,
        role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

$check = $conn->query("SHOW INDEX FROM users WHERE Key_name = 'email_unique'");
if ($check && $check->num_rows === 0) {
    $conn->query("ALTER TABLE users ADD UNIQUE KEY email_unique (email)");
}
