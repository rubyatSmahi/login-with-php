<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

if (isset($_GET['delete_id'])) {
    $deleteId = (int) $_GET['delete_id'];
    if ($deleteId !== (int) $_SESSION['user_id']) {
        $del = $conn->prepare('DELETE FROM users WHERE id = ?');
        $del->bind_param('i', $deleteId);
        $del->execute();
    }
    header('Location: admindashboard.php');
    exit;
}

$totalUsers = $conn->query('SELECT COUNT(*) FROM users')->fetch_row()[0];
$totalAdmins = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetch_row()[0];
$result = $conn->query('SELECT id, name, email, phone, gender, role, created_at FROM users ORDER BY created_at DESC');
$users = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: #f9f1eb;
        }

        .admin-box {
            max-width: 1100px;
            margin: 40px auto;
            padding: 24px;
            background: rgba(255, 255, 255, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(143, 44, 36, 0.2);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(143, 44, 36, 0.15);
            border-radius: 12px;
            padding: 18px;
        }

        .stat-card h4 {
            margin: 0 0 10px;
            color: #8f2c24;
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        .stat-card strong {
            font-size: 2rem;
            color: #8f2c24;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            overflow: hidden;
        }

        th,
        td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid rgba(143, 44, 36, 0.15);
        }

        th {
            background: rgba(143, 44, 36, 0.08);
            color: #8f2c24;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .admin-badge {
            background: #fee2e2;
            color: #991b1b;
        }

        .user-badge {
            background: #dcfce7;
            color: #166534;
        }

        .delete-btn {
            background: #fee2e2;
            color: #991b1b;
            text-decoration: none;
            padding: 6px 10px;
            border-radius: 6px;
            font-weight: bold;
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
    <div class="admin-box">
        <div class="topbar">
            <div>
                <h2>Admin Dashboard</h2>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h4>Total Users</h4>
                <strong><?= (int) $totalUsers ?></strong>
            </div>
            <div class="stat-card">
                <h4>Admins</h4>
                <strong><?= (int) $totalAdmins ?></strong>
            </div>
        </div>

        <h3>Registered Users</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Gender</th>
                    <th>Role</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>#<?= (int) $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['phone']) ?></td>
                        <td><?= htmlspecialchars($user['gender']) ?></td>
                        <td>
                            <span class="badge <?= $user['role'] === 'admin' ? 'admin-badge' : 'user-badge' ?>">
                                <?= htmlspecialchars($user['role']) ?>
                            </span>
                        </td>
                        <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                        <td>
                            <?php if ((int) $user['id'] !== (int) $_SESSION['user_id']): ?>
                                <a href="admindashboard.php?delete_id=<?= (int) $user['id'] ?>" class="delete-btn" onclick="return confirm('Delete this user?');">Delete</a>
                            <?php else: ?>
                                <span>(You)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>