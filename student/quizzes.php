<?php
require_once '../config.php';
require_login();

if (!is_student()) {
    header('Location: ../login.php');
    exit();
}

$student_id = $_SESSION['user_id'];

// Get all quizzes with student's attempt info
$quizzes = $conn->query("
    SELECT q.id, q.title, q.description, q.total_questions, q.passing_score, q.time_limit, u.full_name,
           COUNT(qa.id) as attempt_count,
           MAX(qa.percentage) as best_score,
           MAX(qa.completed_at) as last_attempt
    FROM quizzes q
    JOIN users u ON q.teacher_id = u.id
    LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id AND qa.student_id = $student_id
    GROUP BY q.id
    ORDER BY q.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizzes - OLQS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">OLQS</div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><span class="icon">📊</span>Dashboard</a></li>
                <li><a href="lessons.php"><span class="icon">📚</span>Lessons</a></li>
                <li><a href="quizzes.php" class="active"><span class="icon">📝</span>Quizzes</a></li>
                <li><a href="profile.php"><span class="icon">👤</span>Profile</a></li>
                <li><a href="../logout.php"><span class="icon">🚪</span>Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1>Available Quizzes</h1>
            </div>

            <!-- Quizzes Grid -->
            <div class="card">
                <div class="card-body">
                    <?php if ($quizzes->num_rows > 0): ?>
                        <div class="grid grid-2">
                            <?php while ($quiz = $quizzes->fetch_assoc()): ?>
                                <div class="card" style="background-color: var(--light-color);">
                                    <div class="card-header">
                                        <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                                        <small>By: <?php echo htmlspecialchars($quiz['full_name']); ?></small>
                                    </div>
                                    <div class="card-body">
                                        <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                                        <div style="margin: 1rem 0; padding: 1rem; background-color: white; border-radius: 0.5rem;">
                                            <strong>Questions:</strong> <?php echo $quiz['total_questions']; ?><br>
                                            <strong>Passing Score:</strong> <?php echo $quiz['passing_score']; ?>%<br>
                                            <strong>Time Limit:</strong> <?php echo $quiz['time_limit'] > 0 ? $quiz['time_limit'] . ' min' : 'Unlimited'; ?><br>
                                            <strong>Attempts:</strong> <?php echo $quiz['attempt_count']; ?><br>
                                            <?php if ($quiz['best_score']): ?>
                                                <strong>Best Score:</strong> <span class="badge badge-success"><?php echo number_format($quiz['best_score'], 2); ?>%</span>
                                            <?php endif; ?>
                                            <?php if ($quiz['last_attempt']): ?>
                                                <br><strong>Last Attempt:</strong> <?php echo date('M d, Y H:i', strtotime($quiz['last_attempt'])); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <a href="take_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-primary">Take Quiz</a>
                                        <?php if ($quiz['attempt_count'] > 0): ?>
                                            <a href="quiz_history.php?id=<?php echo $quiz['id']; ?>" class="btn btn-secondary">History</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p>No quizzes available yet. Check back soon!</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
