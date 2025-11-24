<?php
require_once 'config.php';

$error = '';
$migration_needed = false;

// Check if verification columns exist
$check_columns = $conn->query("SHOW COLUMNS FROM users LIKE 'is_verified'");
if ($check_columns->num_rows === 0) {
    $migration_needed = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($migration_needed) {
        $error = 'Database needs to be updated. Please run the migration first.';
    } elseif (empty($username) || empty($password)) {
        $error = 'Username and password are required.';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, user_type, full_name, is_verified FROM users WHERE username = ? AND user_type = 'admin'");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['login_time'] = time();

                header('Location: admin/dashboard.php');
                exit();
            } else {
                $error = 'Invalid password.';
            }
        } else {
            $error = 'Admin account not found.';
        }
        $stmt->close();
    }
}

// If already logged in, redirect
if (is_logged_in()) {
    redirect_by_role();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - OLQS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1>OLQS Admin</h1>
                <p>Administrator Login</p>
            </div>

            <?php if ($migration_needed): ?>
                <div class="alert alert-warning" style="background-color: #fef3c7; color: #92400e; border-left: 4px solid #f59e0b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                    <strong>⚠️ Database Migration Required</strong><br>
                    The database needs to be updated with verification fields.<br>
                    <a href="migrate_db.php" style="color: #92400e; text-decoration: underline; font-weight: bold;">Run Migration Now</a>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="username">Admin Username</label>
                    <input type="text" id="username" name="username" required placeholder="Enter admin username">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter password">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login as Admin</button>
            </form>

            <div class="auth-footer">
                <p><a href="login.php">User Login</a></p>
                <p><a href="index.php">Back to Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>
