<?php
require_once '../config.php';
require_login();

if (!is_student()) {
    header('Location: ../login.php');
    exit();
}

$student_id = $_SESSION['user_id'];

// Get all lessons
$lessons = $conn->query("
    SELECT l.*, u.full_name, 
           CASE WHEN sl.id IS NOT NULL THEN 1 ELSE 0 END as viewed
    FROM lessons l
    JOIN users u ON l.teacher_id = u.id
    LEFT JOIN student_lessons sl ON l.id = sl.lesson_id AND sl.student_id = $student_id
    ORDER BY l.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lessons - OLQS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">OLQS</div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><span class="icon">📊</span>Dashboard</a></li>
                <li><a href="lessons.php" class="active"><span class="icon">📚</span>Lessons</a></li>
                <li><a href="quizzes.php"><span class="icon">📝</span>Quizzes</a></li>
                <li><a href="profile.php"><span class="icon">👤</span>Profile</a></li>
                <li><a href="../logout.php"><span class="icon">🚪</span>Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1>Lessons</h1>
            </div>

            <!-- Lessons Grid -->
            <div class="card">
                <div class="card-body">
                    <?php if ($lessons->num_rows > 0): ?>
                        <div class="grid grid-2">
                            <?php while ($lesson = $lessons->fetch_assoc()): ?>
                                <div class="card" style="background-color: var(--light-color);">
                                    <div class="card-header">
                                        <h3><?php echo htmlspecialchars($lesson['title']); ?></h3>
                                        <small>By: <?php echo htmlspecialchars($lesson['full_name']); ?></small>
                                    </div>
                                    <div class="card-body">
                                        <p><?php echo htmlspecialchars($lesson['description']); ?></p>
                                        <div style="margin: 1rem 0; padding: 1rem; background-color: white; border-radius: 0.5rem;">
                                            <strong>File Type:</strong> <?php echo strtoupper($lesson['file_type']); ?><br>
                                            <strong>File Size:</strong> <?php echo round($lesson['file_size'] / 1024 / 1024, 2) . ' MB'; ?><br>
                                            <strong>Status:</strong> 
                                            <span class="badge <?php echo $lesson['viewed'] ? 'badge-success' : 'badge-warning'; ?>">
                                                <?php echo $lesson['viewed'] ? 'Viewed' : 'Not Viewed'; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <a href="view_lesson.php?id=<?php echo $lesson['id']; ?>" class="btn btn-primary">View Lesson</a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p>No lessons available yet. Check back soon!</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
