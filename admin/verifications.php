<?php
require_once '../config.php';
require_login();

if (!is_admin()) {
    header('Location: ../login.php');
    exit();
}

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'approve') {
        $token = bin2hex(random_bytes(16));
        $conn->query("UPDATE users SET is_verified = TRUE, verified_at = NOW() WHERE id = $user_id");
        $message = "✓ User account approved successfully!";
    } elseif ($action === 'reject') {
        $conn->query("DELETE FROM users WHERE id = $user_id");
        $message = "✗ User account rejected and deleted!";
    }
}

// Get all pending verifications
$pending = $conn->query("
    SELECT id, username, email, full_name, user_type, created_at
    FROM users
    WHERE is_verified = FALSE AND user_type != 'admin'
    ORDER BY created_at DESC
");

// Get all verified users
$verified = $conn->query("
    SELECT id, username, email, full_name, user_type, verified_at
    FROM users
    WHERE is_verified = TRUE AND user_type != 'admin'
    ORDER BY verified_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Verifications - OLQS Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">OLQS Admin</div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><span class="icon">📊</span>Dashboard</a></li>
                <li><a href="users.php"><span class="icon">👥</span>Users</a></li>
                <li><a href="verifications.php" class="active"><span class="icon">✅</span>Verifications</a></li>
                <li><a href="reports.php"><span class="icon">📈</span>Reports</a></li>
                <li><a href="settings.php"><span class="icon">⚙️</span>Settings</a></li>
                <li><a href="../logout.php"><span class="icon">🚪</span>Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1>Account Verifications</h1>
            </div>

            <?php if (isset($message)): ?>
                <div class="alert alert-success">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Pending Verifications -->
            <div class="section">
                <h2>⏳ Pending Verifications</h2>
                <?php if ($pending->num_rows > 0): ?>
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
                            <?php while ($user = $pending->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $user['user_type']; ?>">
                                            <?php echo ucfirst($user['user_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <a href="?action=approve&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-success">✓ Approve</a>
                                        <a href="?action=reject&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger">✗ Reject</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-info">
                        ✓ No pending verifications!
                    </div>
                <?php endif; ?>
            </div>

            <!-- Verified Users -->
            <div class="section">
                <h2>✓ Verified Users</h2>
                <?php if ($verified->num_rows > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Full Name</th>
                                <th>Type</th>
                                <th>Verified On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $verified->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $user['user_type']; ?>">
                                            <?php echo ucfirst($user['user_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($user['verified_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-info">
                        No verified users yet.
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

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            margin-right: 0.5rem;
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

        .alert-success {
            background-color: #dcfce7;
            color: #166534;
            border-left: 4px solid #10b981;
        }
    </style>
</body>
</html>
