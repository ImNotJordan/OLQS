<?php
require_once '../config.php';
require_login();

if (!is_admin()) {
    header('Location: ../login.php');
    exit();
}

// Get all users
$users = $conn->query("
    SELECT id, username, email, full_name, user_type, is_verified, created_at
    FROM users
    WHERE user_type != 'admin'
    ORDER BY created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - OLQS Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">OLQS Admin</div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><span class="icon">📊</span>Dashboard</a></li>
                <li><a href="users.php" class="active"><span class="icon">👥</span>Users</a></li>
                <li><a href="verifications.php"><span class="icon">✅</span>Verifications</a></li>
                <li><a href="reports.php"><span class="icon">📈</span>Reports</a></li>
                <li><a href="settings.php"><span class="icon">⚙️</span>Settings</a></li>
                <li><a href="../logout.php"><span class="icon">🚪</span>Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1>Manage Users</h1>
            </div>

            <!-- Users Table -->
            <div class="section">
                <h2>All Users</h2>
                <?php if ($users->num_rows > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Full Name</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $user['user_type']; ?>">
                                            <?php echo ucfirst($user['user_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['is_verified']): ?>
                                            <span class="badge badge-verified">✓ Verified</span>
                                        <?php else: ?>
                                            <span class="badge badge-pending">⏳ Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-info">
                        No users found.
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <style>
        .section {
            background: white;
            padding: 2rem;
            border-radius: 0.75rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .section h2 {
            margin-top: 0;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .data-table thead {
            background-color: var(--light-color);
        }

        .data-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--dark-color);
            border-bottom: 2px solid var(--border-color);
        }

        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .data-table tbody tr:hover {
            background-color: var(--light-color);
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 0.25rem;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-teacher {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-student {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge-verified {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin: 1rem 0;
        }

        .alert-info {
            background-color: #dbeafe;
            color: #1e40af;
            border-left: 4px solid #3b82f6;
        }
    </style>
</body>
</html>
