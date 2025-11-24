<?php
require_once '../config.php';
require_login();

if (!is_admin()) {
    header('Location: ../login.php');
    exit();
}

$admin_id = $_SESSION['user_id'];

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type != 'admin'")->fetch_assoc()['count'];
$verified_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_verified = TRUE AND user_type != 'admin'")->fetch_assoc()['count'];
$pending_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_verified = FALSE AND user_type != 'admin'")->fetch_assoc()['count'];
$teachers = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'teacher'")->fetch_assoc()['count'];
$students = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'student'")->fetch_assoc()['count'];

// Get pending verifications
$pending_verifications = $conn->query("
    SELECT id, username, email, full_name, user_type, created_at
    FROM users
    WHERE is_verified = FALSE AND user_type != 'admin'
    ORDER BY created_at DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - OLQS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">OLQS Admin</div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active"><span class="icon">📊</span>Dashboard</a></li>
                <li><a href="users.php"><span class="icon">👥</span>Users</a></li>
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
                <h1>Admin Dashboard</h1>
                <div class="user-info">
                    <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
                    <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-content">
                        <h3><?php echo $total_users; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <h3><?php echo $verified_users; ?></h3>
                        <p>Verified Users</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-content">
                        <h3><?php echo $pending_users; ?></h3>
                        <p>Pending Verification</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🎓</div>
                    <div class="stat-content">
                        <h3><?php echo $teachers; ?></h3>
                        <p>Teachers</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-content">
                        <h3><?php echo $students; ?></h3>
                        <p>Students</p>
                    </div>
                </div>
            </div>

            <!-- Pending Verifications Section -->
            <div class="section">
                <h2>Pending Account Verifications</h2>
                <?php if ($pending_verifications->num_rows > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Full Name</th>
                                <th>Type</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $pending_verifications->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $user['user_type']; ?>">
                                            <?php echo ucfirst($user['user_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <a href="verifications.php?action=approve&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-success">Approve</a>
                                        <a href="verifications.php?action=reject&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger">Reject</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-info">
                        ✓ No pending verifications. All users are verified!
                    </div>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <div class="section">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <a href="users.php" class="btn btn-primary">Manage Users</a>
                    <a href="verifications.php" class="btn btn-primary">View All Verifications</a>
                    <a href="reports.php" class="btn btn-primary">View Reports</a>
                    <a href="settings.php" class="btn btn-primary">System Settings</a>
                </div>
            </div>
        </main>
    </div>

    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            font-size: 2.5rem;
        }

        .stat-content h3 {
            margin: 0;
            font-size: 1.8rem;
            color: var(--primary-color);
        }

        .stat-content p {
            margin: 0.25rem 0 0 0;
            color: var(--text-color);
            font-size: 0.9rem;
        }

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

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }

        .btn-success {
            background-color: var(--secondary-color);
            color: white;
        }

        .btn-success:hover {
            background-color: #059669;
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
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
