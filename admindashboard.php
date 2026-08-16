<?php

session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: index.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $deleteId = (int) $_GET['id'];

    if ($deleteId !== (int) $_SESSION['user_id']) {
        $stmt = $conn->prepare('DELETE FROM users WHERE id = ? AND LOWER(role) = LOWER(?)');
        $role = 'user';
        $stmt->bind_param('is', $deleteId, $role);
        $stmt->execute();
    }

    header('Location: admindashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gender = strtolower(trim($_POST['gender'] ?? 'male'));
    $role = strtolower(trim($_POST['role'] ?? 'user'));

    if ($name !== '' && $email !== '' && $phone !== '') {
        $stmt = $conn->prepare('INSERT INTO users (name, email, password, phone, gender, role) VALUES (?, ?, ?, ?, ?, ?)');
        $defaultPassword = password_hash('123456', PASSWORD_BCRYPT);
        $stmt->bind_param('ssssss', $name, $email, $defaultPassword, $phone, $gender, $role);
        $stmt->execute();
    }

    header('Location: admindashboard.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}

$result = $conn->query('SELECT id, name, email, phone, gender, role, created_at FROM users ORDER BY created_at DESC');
$users = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$totalUsers = count($users);
$adminCount = count(array_filter($users, fn($u) => strtolower($u['role']) === 'admin'));
$standardCount = $totalUsers - $adminCount;
$maleCount = count(array_filter($users, fn($u) => strtolower($u['gender']) === 'male'));
$femaleCount = count(array_filter($users, fn($u) => strtolower($u['gender']) === 'female'));
$maleRatio = $totalUsers > 0 ? round(($maleCount / $totalUsers) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard · User Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background-color: #0b0f19;
            background-image: url('admin.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            color: #f8fafc;
            height: 100vh;
            display: flex;
            overflow: hidden;
        }

        .panel {
            background: rgba(10, 15, 29, 0.45);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        }

        .card {
            background: rgba(10, 15, 29, 0.42);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
            transition: all 0.25s ease;
        }

        .card:hover {
            border-color: rgba(245, 158, 11, 0.4);
            transform: translateY(-2px);
        }

        .sidebar {
            width: 80px;
            background: rgba(5, 8, 18, 0.55);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-right: 1px solid rgba(255, 255, 255, 0.15);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            padding: 24px 0;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 50;
        }

        .nav-button {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .nav-button.dashboard {
            background: rgba(245, 158, 11, 0.25);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.4);
            box-shadow: 0 0 20px rgba(245, 158, 11, 0.25);
        }

        .nav-button.dashboard::before {
            content: '';
            position: absolute;
            left: -16px;
            top: 10px;
            bottom: 10px;
            width: 5px;
            background: #f59e0b;
            border-radius: 0 4px 4px 0;
            box-shadow: 0 0 10px #f59e0b;
        }

        .nav-button.logout {
            background: rgba(244, 63, 94, 0.2);
            color: #fda4af;
            border: 1px solid rgba(244, 63, 94, 0.35);
        }

        .nav-button.logout:hover {
            background: rgba(244, 63, 94, 0.9);
            color: #ffffff;
            transform: scale(1.06);
        }

        .page-content {
            margin-left: 80px;
            padding: 24px 28px 24px 28px;
            width: calc(100% - 80px);
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .top-header {
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .header-title {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #ffffff;
            margin-top: 4px;
        }

        .header-title span {
            color: #f59e0b;
        }

        .header-text {
            font-size: 13px;
            color: #cbd5e1;
            margin-top: 6px;
            max-width: 650px;
            line-height: 1.5;
        }

        .page-tag {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: #f59e0b;
        }

        .add-user-btn {
            background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);
            color: #0b0f19;
            font-weight: 800;
            font-size: 13px;
            padding: 12px 22px;
            border-radius: 14px;
            border: 1px solid rgba(253, 230, 138, 0.4);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 10px 25px rgba(234, 88, 12, 0.35);
            transition: all 0.2s;
        }

        .add-user-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(234, 88, 12, 0.45);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
        }

        .stat-card {
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .stat-label {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #e2e8f0;
        }

        .stat-number {
            font-size: 42px;
            font-weight: 900;
            color: #ffffff;
            line-height: 1;
            margin: 8px 0;
        }

        .stat-note {
            font-size: 12px;
            color: #94a3b8;
        }

        .users-panel {
            max-height: 55vh;
            overflow-y: auto;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            scrollbar-width: thin;
            scrollbar-color: rgba(245, 158, 11, 0.6) rgba(15, 23, 42, 0.3);
        }

        .users-panel::-webkit-scrollbar {
            width: 10px;
        }

        .users-panel::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.3);
            backdrop-filter: blur(10px);
            border-radius: 10px;
        }

        .users-panel::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, rgba(245, 158, 11, 0.7), rgba(234, 88, 12, 0.7));
            border-radius: 10px;
            border: 2px solid rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(12px);
        }

        .users-panel::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, rgba(245, 158, 11, 0.9), rgba(234, 88, 12, 0.9));
            box-shadow: 0 0 12px rgba(245, 158, 11, 0.5);
        }

        .table-header {
            padding: 20px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 800;
            color: #ffffff;
        }

        .section-text {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 4px;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .users-table th {
            padding: 16px 24px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #fde68a;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(5, 8, 18, 0.3);
        }

        .users-table td {
            padding: 16px 24px;
            font-size: 13px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
            vertical-align: middle;
        }

        .users-table tr:hover td {
            background: rgba(255, 255, 255, 0.04);
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .admin-badge {
            background: rgba(244, 63, 94, 0.25);
            color: #fda4af;
            border: 1px solid rgba(244, 63, 94, 0.4);
        }

        .user-badge {
            background: rgba(16, 185, 129, 0.25);
            color: #6ee7b7;
            border: 1px solid rgba(16, 185, 129, 0.4);
        }

        .gender-badge {
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: capitalize;
        }

        .male-badge {
            background: rgba(59, 130, 246, 0.2);
            color: #93c5fd;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .female-badge {
            background: rgba(236, 72, 153, 0.2);
            color: #f9a8d4;
            border: 1px solid rgba(236, 72, 153, 0.3);
        }

        .delete-btn {
            background: rgba(244, 63, 94, 0.25);
            color: #fca5a5;
            border: 1px solid rgba(244, 63, 94, 0.4);
            padding: 7px 14px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .delete-btn:hover {
            background: #e11d48;
            color: #ffffff;
            border-color: #f43f5e;
        }

        .lock-tag {
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            padding: 5px 10px;
            background: rgba(15, 23, 42, 0.6);
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 16px;
        }

        .progress-bar {
            height: 8px;
            background: rgba(15, 23, 42, 0.6);
            border-radius: 99px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 8px;
        }

        .progress-fill {
            height: 100%;
            border-radius: 99px;
        }

        .dialog {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(5, 8, 18, 0.8);
            backdrop-filter: blur(12px);
            z-index: 100;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .dialog.active {
            display: flex;
        }

        .dialog-box {
            width: 100%;
            max-width: 480px;
            background: rgba(15, 23, 42, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.8);
        }

        .field-group {
            margin-bottom: 16px;
        }

        .field-label {
            display: block;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #cbd5e1;
            margin-bottom: 6px;
        }

        .text-input,
        .select-input {
            width: 100%;
            padding: 10px 14px;
            border-radius: 12px;
            background: rgba(5, 8, 18, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #ffffff;
            font-size: 13px;
            outline: none;
        }

        .text-input:focus,
        .select-input:focus {
            border-color: #f59e0b;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
                padding: 16px 0;
            }

            .nav-button {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }

            .page-content {
                margin-left: 60px;
                padding: 16px 12px 30px 12px;
                gap: 12px;
            }

            .welcome-title {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <div>
            <a href="?tab=dashboard" class="nav-button dashboard" title="Dashboard">
                <i class="fa-solid fa-table-cells-large"></i>
            </a>
        </div>
        <div>
            <a href="?action=logout" class="nav-button logout" title="Logout" onclick="return confirm('Are you sure you want to log out?');">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </a>
        </div>
    </aside>

    <main class="page-content">
        <section class="top-header panel">
            <div>
                <span class="page-tag">ADMINISTRATOR PORTAL · AUGUST 16 · 2026</span>
                <h1 class="header-title">Welcome back, <span>Travis</span></h1>
                <p class="header-text">
                    Admin privileges active. You can delete only regular <strong>USER</strong> accounts from your database. Admin accounts are protected.
                </p>
            </div>
            <div>
                <button onclick="document.getElementById('addUserModal').classList.add('active')" class="add-user-btn">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>+ Add User</span>
                </button>
            </div>
        </section>

        <section class="stats-grid">
            <div class="stat-card card">
                <div class="stat-header">
                    <span class="stat-label">Total Users</span>
                    <div class="stat-icon" style="background: rgba(16,185,129,0.2); color: #6ee7b7; border: 1px solid rgba(16,185,129,0.3);">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo $totalUsers; ?></div>
                <div class="stat-note"><i class="fa-solid fa-arrow-trend-up" style="color: #34d399;"></i> Database records</div>
            </div>

            <div class="stat-card card">
                <div class="stat-header">
                    <span class="stat-label">Protected Admins</span>
                    <div class="stat-icon" style="background: rgba(244,63,94,0.2); color: #fda4af; border: 1px solid rgba(244,63,94,0.3);">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo $adminCount; ?></div>
                <div class="stat-note"><i class="fa-solid fa-lock" style="color: #fb7185;"></i> Cannot be deleted</div>
            </div>

            <div class="stat-card card">
                <div class="stat-header">
                    <span class="stat-label">Regular Users</span>
                    <div class="stat-icon" style="background: rgba(245,158,11,0.2); color: #fde68a; border: 1px solid rgba(245,158,11,0.3);">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo $standardCount; ?></div>
                <div class="stat-note"><i class="fa-solid fa-trash-can" style="color: #fbbf24;"></i> Deletable by admin</div>
            </div>

            <div class="stat-card card">
                <div class="stat-header">
                    <span class="stat-label">Gender Ratio</span>
                    <div class="stat-icon" style="background: rgba(6,182,212,0.2); color: #67e8f9; border: 1px solid rgba(6,182,212,0.3);">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo $maleRatio; ?>%</div>
                <div class="stat-note"><?php echo $maleCount; ?> Male · <?php echo $femaleCount; ?> Female</div>
            </div>
        </section>

        <section class="users-panel panel">
            <div class="table-header">
                <div>
                    <h2 class="section-title">Registered Users</h2>
                    <p class="section-text">Admin Rule: Only accounts with 'USER' role can be deleted from the database.</p>
                </div>
                <div>
                    <span style="font-size: 12px; font-weight: 700; background: rgba(245,158,11,0.2); color: #fde68a; padding: 6px 14px; border-radius: 12px; border: 1px solid rgba(245,158,11,0.3);">
                        <?php echo count($users); ?> Total in Database
                    </span>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Gender</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <?php
                            $roleValue = strtoupper($user['role']);
                            $genderValue = strtolower($user['gender']);
                            $isCurrentAdmin = ((int) $user['id'] === (int) $_SESSION['user_id']);
                            ?>
                            <tr>
                                <td style="font-weight: 700; color: #94a3b8;">#<?php echo $user['id']; ?></td>
                                <td>
                                    <strong style="color: #ffffff; text-transform: capitalize;"><?php echo htmlspecialchars($user['name']); ?></strong>
                                    <?php if ($isCurrentAdmin): ?>
                                        <span style="font-size: 10px; color: #f59e0b; font-weight: 700; margin-left: 6px;">(You)</span>
                                    <?php endif; ?>
                                </td>
                                <td style="color: #cbd5e1;"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td style="color: #94a3b8;"><?php echo htmlspecialchars($user['phone']); ?></td>
                                <td>
                                    <span class="gender-badge <?php echo $genderValue === 'male' ? 'male-badge' : 'female-badge'; ?>">
                                        <?php echo htmlspecialchars($genderValue); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="role-badge <?php echo $roleValue === 'ADMIN' ? 'admin-badge' : 'user-badge'; ?>">
                                        <i class="fa-solid <?php echo $roleValue === 'ADMIN' ? 'fa-shield' : 'fa-user'; ?>"></i>
                                        <?php echo $roleValue; ?>
                                    </span>
                                </td>
                                <td style="color: #94a3b8;"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td style="text-align: right;">
                                    <?php if ($isCurrentAdmin): ?>
                                        <span class="lock-tag">(Current Admin)</span>
                                    <?php elseif ($roleValue === 'ADMIN'): ?>
                                        <span class="lock-tag"><i class="fa-solid fa-lock"></i> Protected</span>
                                    <?php else: ?>
                                        <a href="?action=delete&id=<?php echo $user['id']; ?>" class="delete-btn" onclick="return confirm('Delete user #<?php echo $user['id']; ?> (<?php echo htmlspecialchars($user['name']); ?>) from the database?');">
                                            <i class="fa-solid fa-trash-can"></i> Delete
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel" style="padding: 28px 32px;">
            <h3 style="font-size: 18px; font-weight: 800; color: #ffffff;">Database Breakdown</h3>
            <p style="font-size: 12px; color: #94a3b8; margin-top: 4px;">User distribution across roles and demographics</p>

            <div class="summary-grid">
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 700;">
                        <span>Male Users</span>
                        <span><?php echo $maleRatio; ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $maleRatio; ?>%; background: linear-gradient(90deg, #6366f1, #3b82f6);"></div>
                    </div>
                </div>

                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 700;">
                        <span>Female Users</span>
                        <span><?php echo 100 - $maleRatio; ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo 100 - $maleRatio; ?>%; background: linear-gradient(90deg, #10b981, #14b8a6);"></div>
                    </div>
                </div>

                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 700;">
                        <span>Protected Admins</span>
                        <span><?php echo $totalUsers > 0 ? round(($adminCount / $totalUsers) * 100) : 0; ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $totalUsers > 0 ? round(($adminCount / $totalUsers) * 100) : 0; ?>%; background: linear-gradient(90deg, #f43f5e, #ea580c);"></div>
                    </div>
                </div>

                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 700;">
                        <span>Deletable Users</span>
                        <span><?php echo $totalUsers > 0 ? round(($standardCount / $totalUsers) * 100) : 0; ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $totalUsers > 0 ? round(($standardCount / $totalUsers) * 100) : 0; ?>%; background: linear-gradient(90deg, #f59e0b, #eab308);"></div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div id="addUserModal" class="dialog">
        <div class="dialog-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 20px; font-weight: 800; color: #ffffff;">Add New User</h3>
                <button type="button" onclick="document.getElementById('addUserModal').classList.remove('active')" style="background: none; border: none; color: #94a3b8; font-size: 18px; cursor: pointer;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST">
                <div class="field-group">
                    <label class="field-label">Full Name</label>
                    <input type="text" name="name" class="text-input" placeholder="e.g. John Doe" required>
                </div>
                <div class="field-group">
                    <label class="field-label">Email Address</label>
                    <input type="email" name="email" class="text-input" placeholder="e.g. john@gmail.com" required>
                </div>
                <div class="field-group">
                    <label class="field-label">Phone Number</label>
                    <input type="text" name="phone" class="text-input" placeholder="e.g. 01700000000" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px;">
                    <div>
                        <label class="field-label">Gender</label>
                        <select name="gender" class="select-input">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Role</label>
                        <select name="role" class="select-input">
                            <option value="USER">USER (Deletable)</option>
                            <option value="ADMIN">ADMIN (Protected)</option>
                        </select>
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 12px;">
                    <button type="button" onclick="document.getElementById('addUserModal').classList.remove('active')" style="padding: 10px 18px; border-radius: 12px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); color: #ffffff; cursor: pointer; font-weight: 700; font-size: 13px;">Cancel</button>
                    <button type="submit" name="add_user" class="add-user-btn" style="padding: 10px 20px;">Save User</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>