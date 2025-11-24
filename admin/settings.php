<?php
require_once '../config.php';
require_login();

if (!is_admin()) {
    header('Location: ../login.php');
    exit();
}

$admin_id = $_SESSION['user_id'];
$message = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $full_name = sanitize_input($_POST['full_name']);
        $email = sanitize_input($_POST['email']);

        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $full_name, $email, $admin_id);
        if ($stmt->execute()) {
            $_SESSION['full_name'] = $full_name;
            $message = "✓ Profile updated successfully!";
        }
    } elseif ($_POST['action'] === 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        $user = $conn->query("SELECT password FROM users WHERE id = $admin_id")->fetch_assoc();

        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $conn->query("UPDATE users SET password = '$hashed_password' WHERE id = $admin_id");
                $message = "✓ Password changed successfully!";
            } else {
                $message = "✗ Passwords do not match!";
            }
        } else {
            $message = "✗ Current password is incorrect!";
        }
    }
}

// Get admin info
$admin = $conn->query("SELECT * FROM users WHERE id = $admin_id")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - OLQS Admin</title>
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
                <li><a href="verifications.php"><span class="icon">✅</span>Verifications</a></li>
                <li><a href="reports.php"><span class="icon">📈</span>Reports</a></li>
                <li><a href="settings.php" class="active"><span class="icon">⚙️</span>Settings</a></li>
                <li><a href="../logout.php"><span class="icon">🚪</span>Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1>Admin Settings</h1>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo strpos($message, '✓') !== false ? 'success' : 'error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Profile Settings -->
            <div class="section">
                <h2>Profile Settings</h2>
                <form method="POST" class="form">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="section">
                <h2>Change Password</h2>
                <form method="POST" class="form">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Change Password</button>
                </form>
            </div>

            <!-- System Information -->
            <div class="section">
                <h2>System Information</h2>
                <ul>
                    <li><strong>System:</strong> Online Learning & Quiz System (OLQS)</li>
                    <li><strong>Version:</strong> 1.0</li>
                    <li><strong>Database:</strong> MySQL 5.7+</li>
                    <li><strong>PHP Version:</strong> 7.4+</li>
                </ul>
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

        .form {
            max-width: 500px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark-color);
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background-color: #dcfce7;
            color: #166534;
            border-left: 4px solid #10b981;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .section ul {
            list-style: none;
            padding: 0;
        }

        .section li {
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        .section li:last-child {
            border-bottom: none;
        }
    </style>
</body>
</html>
