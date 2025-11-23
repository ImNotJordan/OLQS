<?php
require_once '../config.php';
require_login();

if (!is_teacher()) {
    header('Location: ../login.php');
    exit();
}

$teacher_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Get teacher info
$teacher = $conn->query("SELECT * FROM users WHERE id = $teacher_id")->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');

    if (empty($full_name) || empty($email)) {
        $error = 'All fields are required.';
    } else {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $full_name, $email, $teacher_id);

        if ($stmt->execute()) {
            $_SESSION['full_name'] = $full_name;
            $message = 'Profile updated successfully!';
            $teacher['full_name'] = $full_name;
            $teacher['email'] = $email;
        } else {
            $error = 'Error updating profile.';
        }
        $stmt->close();
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All password fields are required.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } else {
        if (password_verify($current_password, $teacher['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $teacher_id);

            if ($stmt->execute()) {
                $message = 'Password changed successfully!';
            } else {
                $error = 'Error changing password.';
            }
            $stmt->close();
        } else {
            $error = 'Current password is incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - OLQS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">OLQS</div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="lessons.php">Lessons</a></li>
                <li><a href="quizzes.php">Quizzes</a></li>
                <li><a href="analytics.php">Analytics</a></li>
                <li><a href="profile.php" class="active">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1>My Profile</h1>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Profile Information -->
            <div class="card">
                <div class="card-header">
                    <h2>Profile Information</h2>
                </div>
                <form method="POST" class="card-body">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($teacher['full_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="username">Username (Cannot be changed)</label>
                        <input type="text" id="username" value="<?php echo htmlspecialchars($teacher['username']); ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label for="user_type">Account Type</label>
                        <input type="text" id="user_type" value="Teacher" disabled>
                    </div>

                    <div class="form-group">
                        <label for="created_at">Member Since</label>
                        <input type="text" id="created_at" value="<?php echo date('M d, Y', strtotime($teacher['created_at'])); ?>" disabled>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="card">
                <div class="card-header">
                    <h2>Change Password</h2>
                </div>
                <form method="POST" class="card-body">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required placeholder="Minimum 6 characters">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
