<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare('SELECT id, name, email, phone, gender, role, created_at FROM users WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: #f9f1eb;
        }

        .dashboard-box {
            max-width: 760px;
            margin: 60px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(143, 44, 36, 0.2);
        }

        .role-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            background: #dcfce7;
            color: #166534;
            font-weight: bold;
            text-transform: uppercase;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }

        .info-card {
            background: rgba(255, 255, 255, 0.8);
            padding: 18px;
            border-radius: 12px;
            border: 1px solid rgba(143, 44, 36, 0.15);
        }

        .info-card span {
            display: block;
            color: #8f2c24;
            font-size: 0.8rem;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .logout-btn {
            display: inline-block;
            background: #8f2c24;
            color: white;
            padding: 10px 18px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="dashboard-box">
        <div class="topbar">
            <div>
                <h2>Welcome, <?= htmlspecialchars($user['name']) ?></h2>
                <p>Role: <span class="role-badge"><?= htmlspecialchars($user['role']) ?></span></p>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <h3>Your account information</h3>
        <div class="info-grid">
            <div class="info-card">
                <span>User ID</span>
                <strong>#<?= (int) $user['id'] ?></strong>
            </div>
            <div class="info-card">
                <span>Email</span>
                <strong><?= htmlspecialchars($user['email']) ?></strong>
            </div>
            <div class="info-card">
                <span>Phone</span>
                <strong><?= htmlspecialchars($user['phone']) ?></strong>
            </div>
            <div class="info-card">
                <span>Gender</span>
                <strong><?= htmlspecialchars($user['gender']) ?></strong>
            </div>
            <div class="info-card" style="grid-column: 1 / -1;">
                <span>Member Since</span>
                <strong><?= date('F j, Y - g:i A', strtotime($user['created_at'])) ?></strong>
            </div>
        </div>
    </div>
</body>

</html>