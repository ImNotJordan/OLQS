<?php
require_once '../config.php';
require_login();

if (!is_teacher()) {
    header('Location: ../login.php');
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Get all quizzes with statistics
$quizzes_stats = $conn->query("
    SELECT q.id, q.title, q.total_questions, q.passing_score,
           COUNT(qa.id) as total_attempts,
           AVG(qa.percentage) as average_score,
           SUM(CASE WHEN qa.passed = 1 THEN 1 ELSE 0 END) as passed_count,
           SUM(CASE WHEN qa.passed = 0 THEN 1 ELSE 0 END) as failed_count
    FROM quizzes q
    LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id
    WHERE q.teacher_id = $teacher_id
    GROUP BY q.id
    ORDER BY q.created_at DESC
");

// Get student performance
$student_performance = $conn->query("
    SELECT u.id, u.full_name, u.username,
           COUNT(DISTINCT qa.quiz_id) as quizzes_attempted,
           COUNT(qa.id) as total_attempts,
           AVG(qa.percentage) as average_score,
           MAX(qa.percentage) as best_score,
           MIN(qa.percentage) as worst_score,
           SUM(CASE WHEN qa.passed = 1 THEN 1 ELSE 0 END) as passed_count
    FROM users u
    LEFT JOIN quiz_attempts qa ON u.id = qa.student_id
    LEFT JOIN quizzes q ON qa.quiz_id = q.id AND q.teacher_id = $teacher_id
    WHERE u.user_type = 'student'
    GROUP BY u.id
    ORDER BY average_score DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - OLQS</title>
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
                <li><a href="analytics.php" class="active">Analytics</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1>Analytics & Reports</h1>
            </div>

            <!-- Quiz Performance -->
            <div class="card">
                <div class="card-header">
                    <h2>Quiz Performance</h2>
                </div>
                <div class="card-body">
                    <?php if ($quizzes_stats->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Quiz Title</th>
                                        <th>Total Attempts</th>
                                        <th>Average Score</th>
                                        <th>Passed</th>
                                        <th>Failed</th>
                                        <th>Pass Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($quiz = $quizzes_stats->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                                            <td><?php echo $quiz['total_attempts']; ?></td>
                                            <td><?php echo $quiz['total_attempts'] > 0 ? number_format($quiz['average_score'], 2) . '%' : 'N/A'; ?></td>
                                            <td><span class="badge badge-success"><?php echo $quiz['passed_count']; ?></span></td>
                                            <td><span class="badge badge-danger"><?php echo $quiz['failed_count']; ?></span></td>
                                            <td>
                                                <?php 
                                                if ($quiz['total_attempts'] > 0) {
                                                    $pass_rate = ($quiz['passed_count'] / $quiz['total_attempts']) * 100;
                                                    echo number_format($pass_rate, 2) . '%';
                                                } else {
                                                    echo 'N/A';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No quiz data available yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Student Performance -->
            <div class="card">
                <div class="card-header">
                    <h2>Student Performance</h2>
                </div>
                <div class="card-body">
                    <?php if ($student_performance->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Quizzes Attempted</th>
                                        <th>Total Attempts</th>
                                        <th>Average Score</th>
                                        <th>Best Score</th>
                                        <th>Passed</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($student = $student_performance->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                            <td><?php echo $student['quizzes_attempted']; ?></td>
                                            <td><?php echo $student['total_attempts']; ?></td>
                                            <td><?php echo $student['total_attempts'] > 0 ? number_format($student['average_score'], 2) . '%' : 'N/A'; ?></td>
                                            <td><?php echo $student['total_attempts'] > 0 ? number_format($student['best_score'], 2) . '%' : 'N/A'; ?></td>
                                            <td><span class="badge badge-success"><?php echo $student['passed_count']; ?></span></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No student data available yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
