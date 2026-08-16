<?php

session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$userId = $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT id, name, email, phone, gender, role, DATE_FORMAT(created_at, "%M %d, %Y - %h:%i %p") AS created_at FROM users WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$currentUser = $result->fetch_assoc();

if (!$currentUser) {
    session_destroy();
    header('Location: index.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
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
    <title>User Dashboard · Account Information</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background-color: #0b0f19;
            background-image: url('user.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            overflow: hidden;
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

        .sidebar-btn {
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

        .sidebar-btn.dashboard {
            background: rgba(245, 158, 11, 0.25);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.4);
            box-shadow: 0 0 20px rgba(245, 158, 11, 0.25);
        }

        .sidebar-btn.dashboard::before {
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

        .sidebar-btn.logout {
            background: rgba(244, 63, 94, 0.2);
            color: #fda4af;
            border: 1px solid rgba(244, 63, 94, 0.35);
        }

        .sidebar-btn.logout:hover {
            background: rgba(244, 63, 94, 0.9);
            color: #ffffff;
            transform: scale(1.06);
        }

        .main-wrapper {
            margin-left: 80px;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 36px 24px;
            min-height: 100vh;
        }

        .account-card {
            width: 100%;
            max-width: 880px;
            background: rgba(10, 15, 29, 0.35);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 36px;
            padding: 40px;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
        }

        .account-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #34d399 0%, #2dd4bf 50%, #fbbf24 100%);
            opacity: 0.9;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            flex-wrap: wrap;
            gap: 16px;
        }

        .portal-eyebrow {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #fde68a;
            margin-bottom: 4px;
            display: block;
        }

        .user-title {
            font-size: 32px;
            font-weight: 900;
            color: #ffffff;
            letter-spacing: -0.02em;
        }

        .user-title span {
            color: #f59e0b;
        }

        .role-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 6px;
            font-size: 13px;
            font-weight: 600;
            color: #cbd5e1;
        }

        .role-pill {
            background: rgba(16, 185, 129, 0.25);
            color: #6ee7b7;
            font-weight: 800;
            font-size: 11px;
            padding: 3px 10px;
            border-radius: 99px;
            border: 1px solid rgba(35, 133, 100, 0.4);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .btn-logout {
            background: rgba(126, 34, 31, 0.9);
            color: #ffffff;
            font-weight: 700;
            font-size: 13px;
            padding: 10px 22px;
            border-radius: 16px;
            border: 1px solid rgba(244, 63, 94, 0.4);
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 16px rgba(126, 34, 31, 0.4);
            transition: all 0.2s ease;
        }

        .btn-logout:hover {
            background: #922825;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(126, 34, 31, 0.6);
        }

        .section-heading {
            font-size: 16px;
            font-weight: 800;
            color: #ffffff;
            margin: 28px 0 18px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .info-grid-top {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 16px;
        }

        .info-grid-mid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 16px;
        }

        .info-box {
            background: rgba(255, 255, 255, 0.09);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            transition: all 0.2s ease;
        }

        .info-box:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(245, 158, 11, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.25);
        }

        .info-label {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #fca5a5;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-value {
            font-size: 20px;
            font-weight: 900;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .info-box-full {
            background: rgba(255, 255, 255, 0.09);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 20px;
            padding: 20px;
            width: 100%;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            transition: all 0.2s ease;
        }

        .info-box-full:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(245, 158, 11, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.25);
        }

        .card-footer {
            margin-top: 28px;
            padding-top: 18px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #94a3b8;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
            }

            .main-wrapper {
                margin-left: 60px;
                padding: 20px 14px;
            }

            .info-grid-top,
            .info-grid-mid {
                grid-template-columns: 1fr;
            }

            .user-title {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>

    <aside class="sidebar">
        <div>
            <a href="admindashboard.php" class="sidebar-btn dashboard" title="Dashboard">
                <i class="fa-solid fa-table-cells-large"></i>
            </a>
        </div>
        <div>
            <a href="?action=logout" class="sidebar-btn logout" title="Logout" onclick="return confirm('Are you sure you want to log out?');">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </a>
        </div>
    </aside>

    <main class="main-wrapper">
        <div class="account-card">

            <div class="card-header">
                <div>
                    <span class="portal-eyebrow">USER ACCOUNT PORTAL</span>
                    <h1 class="user-title">Welcome, <span><?php echo htmlspecialchars($currentUser['name']); ?></span></h1>
                    <div class="role-wrapper">
                        <span>Role:</span>
                        <span class="role-pill"><?php echo strtoupper($currentUser['role']); ?></span>
                    </div>
                </div>

            </div>

            <div class="section-heading">
                <span>Your account information</span>
                <span style="font-size: 11px; color: #cbd5e1;">Record #<?php echo $currentUser['id']; ?></span>
            </div>

            <div class="info-grid-top">
                <div class="info-box">
                    <div class="info-label"><i class="fa-solid fa-hashtag"></i> USER ID</div>
                    <div class="info-value">#<?php echo $currentUser['id']; ?></div>
                </div>

                <div class="info-box">
                    <div class="info-label"><i class="fa-solid fa-envelope"></i> EMAIL</div>
                    <div class="info-value"><?php echo htmlspecialchars($currentUser['email']); ?></div>
                </div>

                <div class="info-box">
                    <div class="info-label"><i class="fa-solid fa-phone"></i> PHONE</div>
                    <div class="info-value"><?php echo htmlspecialchars($currentUser['phone']); ?></div>
                </div>
            </div>

            <div class="info-grid-mid">
                <div class="info-box">
                    <div class="info-label"><i class="fa-solid fa-user"></i> GENDER</div>
                    <div class="info-value" style="text-transform: capitalize;"><?php echo htmlspecialchars($currentUser['gender']); ?></div>
                </div>
            </div>

            <div class="info-box-full">
                <div class="info-label"><i class="fa-solid fa-calendar"></i> MEMBER SINCE</div>
                <div class="info-value"><?php echo $currentUser['created_at']; ?></div>
            </div>

            <div class="card-footer">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></span>
                    <span style="color: #6ee7b7; font-weight: 700;">Connected to XAMPP MySQL Database</span>
                </div>
                <span>Logged in as standard member account</span>
            </div>

        </div>
    </main>

</body>

</html>