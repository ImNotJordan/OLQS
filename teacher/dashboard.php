<?php
require_once '../config.php';
require_login();

if (!is_teacher()) {
    header('Location: ../login.php');
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Get statistics
$lessons_count = $conn->query("SELECT COUNT(*) as count FROM lessons WHERE teacher_id = $teacher_id")->fetch_assoc()['count'];
$quizzes_count = $conn->query("SELECT COUNT(*) as count FROM quizzes WHERE teacher_id = $teacher_id")->fetch_assoc()['count'];
$total_attempts = $conn->query("SELECT COUNT(*) as count FROM quiz_attempts WHERE quiz_id IN (SELECT id FROM quizzes WHERE teacher_id = $teacher_id)")->fetch_assoc()['count'];

// Get recent quiz attempts
$recent_attempts = $conn->query("
    SELECT qa.id, qa.student_id, qa.quiz_id, qa.score, qa.total_points, qa.percentage, qa.completed_at,
           u.full_name, q.title
    FROM quiz_attempts qa
    JOIN users u ON qa.student_id = u.id
    JOIN quizzes q ON qa.quiz_id = q.id
    WHERE q.teacher_id = $teacher_id
    ORDER BY qa.completed_at DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - OLQS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">OLQS</div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="lessons.php">Lessons</a></li>
                <li><a href="quizzes.php">Quizzes</a></li>
                <li><a href="analytics.php">Analytics</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1>Teacher Dashboard</h1>
                <div class="user-info">
                    <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
                    <div>
                        <div><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                        <small><?php echo htmlspecialchars($_SESSION['username']); ?></small>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-3">
                <div class="stat-card">
                    <div class="stat-label">Total Lessons</div>
                    <div class="stat-value"><?php echo $lessons_count; ?></div>
                </div>
                <div class="stat-card success">
                    <div class="stat-label">Total Quizzes</div>
                    <div class="stat-value"><?php echo $quizzes_count; ?></div>
                </div>
                <div class="stat-card danger">
                    <div class="stat-label">Quiz Attempts</div>
                    <div class="stat-value"><?php echo $total_attempts; ?></div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h2>Quick Actions</h2>
                </div>
                <div class="card-body">
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <a href="lessons.php?action=upload" class="btn btn-primary">Upload Lesson</a>
                        <a href="quizzes.php?action=create" class="btn btn-secondary">Create Quiz</a>
                        <a href="analytics.php" class="btn btn-outline">View Analytics</a>
                    </div>
                </div>
            </div>

            <!-- Recent Quiz Attempts -->
            <div class="card">
                <div class="card-header">
                    <h2>Recent Quiz Attempts</h2>
                </div>
                <div class="card-body">
                    <?php if ($recent_attempts->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Quiz Title</th>
                                        <th>Score</th>
                                        <th>Percentage</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($attempt = $recent_attempts->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($attempt['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($attempt['title']); ?></td>
                                            <td><?php echo $attempt['score'] . '/' . $attempt['total_points']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $attempt['percentage'] >= 60 ? 'badge-success' : 'badge-danger'; ?>">
                                                    <?php echo number_format($attempt['percentage'], 2); ?>%
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y H:i', strtotime($attempt['completed_at'])); ?></td>
                                            <td>
                                                <a href="view_attempt.php?id=<?php echo $attempt['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No quiz attempts yet. Create a quiz and share it with students!</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Getting Started Guide -->
            <div class="card">
                <div class="card-header">
                    <h2>Getting Started</h2>
                </div>
                <div class="card-body">
                    <ol style="padding-left: 1.5rem;">
                        <li style="margin-bottom: 1rem;"><strong>Upload Lessons:</strong> Go to the Lessons section to upload PDF files, presentations, or videos for your students.</li>
                        <li style="margin-bottom: 1rem;"><strong>Create Quizzes:</strong> Create interactive quizzes with multiple question types in the Quizzes section.</li>
                        <li style="margin-bottom: 1rem;"><strong>Share with Students:</strong> Students can access your lessons and quizzes from their dashboard.</li>
                        <li style="margin-bottom: 1rem;"><strong>Track Progress:</strong> Monitor student performance through the Analytics section.</li>
                    </ol>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
