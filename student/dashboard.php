<?php
require_once '../config.php';
require_login();

if (!is_student()) {
    header('Location: ../login.php');
    exit();
}

$student_id = $_SESSION['user_id'];

// Get statistics
$lessons_viewed = $conn->query("SELECT COUNT(*) as count FROM student_lessons WHERE student_id = $student_id")->fetch_assoc()['count'];
$quizzes_attempted = $conn->query("SELECT COUNT(*) as count FROM quiz_attempts WHERE student_id = $student_id")->fetch_assoc()['count'];
$average_score = $conn->query("SELECT AVG(percentage) as avg FROM quiz_attempts WHERE student_id = $student_id")->fetch_assoc()['avg'];

// Get available quizzes
$available_quizzes = $conn->query("
    SELECT q.id, q.title, q.description, q.total_questions, q.passing_score, u.full_name,
           COUNT(qa.id) as attempt_count,
           MAX(qa.percentage) as best_score
    FROM quizzes q
    JOIN users u ON q.teacher_id = u.id
    LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id AND qa.student_id = $student_id
    GROUP BY q.id
    ORDER BY q.created_at DESC
    LIMIT 5
");

// Get recent quiz attempts
$recent_attempts = $conn->query("
    SELECT qa.id, qa.quiz_id, qa.score, qa.total_points, qa.percentage, qa.passed, qa.completed_at, q.title
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    WHERE qa.student_id = $student_id
    ORDER BY qa.completed_at DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - OLQS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">OLQS</div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active"><span class="icon">📊</span>Dashboard</a></li>
                <li><a href="lessons.php"><span class="icon">📚</span>Lessons</a></li>
                <li><a href="quizzes.php"><span class="icon">📝</span>Quizzes</a></li>
                <li><a href="profile.php"><span class="icon">👤</span>Profile</a></li>
                <li><a href="../logout.php"><span class="icon">🚪</span>Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1>Student Dashboard</h1>
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
                    <div class="stat-label">Lessons Viewed</div>
                    <div class="stat-value"><?php echo $lessons_viewed; ?></div>
                </div>
                <div class="stat-card success">
                    <div class="stat-label">Quizzes Attempted</div>
                    <div class="stat-value"><?php echo $quizzes_attempted; ?></div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-label">Average Score</div>
                    <div class="stat-value"><?php echo $average_score ? number_format($average_score, 1) . '%' : 'N/A'; ?></div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h2>Quick Actions</h2>
                </div>
                <div class="card-body">
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <a href="lessons.php" class="btn btn-primary">View Lessons</a>
                        <a href="quizzes.php" class="btn btn-secondary">Take Quiz</a>
                        <a href="profile.php" class="btn btn-outline">My Profile</a>
                    </div>
                </div>
            </div>

            <!-- Available Quizzes -->
            <div class="card">
                <div class="card-header">
                    <h2>Available Quizzes</h2>
                </div>
                <div class="card-body">
                    <?php if ($available_quizzes->num_rows > 0): ?>
                        <div class="grid grid-2">
                            <?php while ($quiz = $available_quizzes->fetch_assoc()): ?>
                                <div class="card" style="background-color: var(--light-color);">
                                    <div class="card-header">
                                        <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                                        <small><?php echo htmlspecialchars($quiz['full_name']); ?></small>
                                    </div>
                                    <div class="card-body">
                                        <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                                        <div style="margin: 1rem 0;">
                                            <strong>Questions:</strong> <?php echo $quiz['total_questions']; ?><br>
                                            <strong>Passing Score:</strong> <?php echo $quiz['passing_score']; ?>%<br>
                                            <strong>Attempts:</strong> <?php echo $quiz['attempt_count']; ?><br>
                                            <?php if ($quiz['best_score']): ?>
                                                <strong>Best Score:</strong> <span class="badge badge-success"><?php echo number_format($quiz['best_score'], 2); ?>%</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <a href="take_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-primary">Take Quiz</a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p>No quizzes available yet. Check back soon!</p>
                    <?php endif; ?>
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
                                        <th>Quiz Title</th>
                                        <th>Score</th>
                                        <th>Percentage</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($attempt = $recent_attempts->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($attempt['title']); ?></td>
                                            <td><?php echo $attempt['score'] . '/' . $attempt['total_points']; ?></td>
                                            <td><?php echo number_format($attempt['percentage'], 2); ?>%</td>
                                            <td>
                                                <span class="badge <?php echo $attempt['passed'] ? 'badge-success' : 'badge-danger'; ?>">
                                                    <?php echo $attempt['passed'] ? 'Passed' : 'Failed'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y H:i', strtotime($attempt['completed_at'])); ?></td>
                                            <td>
                                                <a href="view_result.php?id=<?php echo $attempt['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>You haven't attempted any quizzes yet. <a href="quizzes.php">Start taking quizzes now!</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
