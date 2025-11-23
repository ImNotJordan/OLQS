<?php
require_once '../config.php';
require_login();

if (!is_teacher()) {
    header('Location: ../login.php');
    exit();
}

$teacher_id = $_SESSION['user_id'];
$quiz_id = intval($_GET['id'] ?? 0);

// Get quiz
$stmt = $conn->prepare("SELECT * FROM quizzes WHERE id = ? AND teacher_id = ?");
$stmt->bind_param("ii", $quiz_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: quizzes.php');
    exit();
}

$quiz = $result->fetch_assoc();
$stmt->close();

// Get all attempts for this quiz
$attempts = $conn->query("
    SELECT qa.id, qa.student_id, qa.score, qa.total_points, qa.percentage, qa.passed, qa.completed_at,
           u.full_name, u.username
    FROM quiz_attempts qa
    JOIN users u ON qa.student_id = u.id
    WHERE qa.quiz_id = $quiz_id
    ORDER BY qa.completed_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results - OLQS</title>
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
                <li><a href="quizzes.php" class="active">Quizzes</a></li>
                <li><a href="analytics.php">Analytics</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div>
                    <h1><?php echo htmlspecialchars($quiz['title']); ?> - Results</h1>
                </div>
                <a href="quizzes.php" class="btn btn-outline">Back</a>
            </div>

            <!-- Quiz Statistics -->
            <div class="card">
                <div class="card-header">
                    <h2>Quiz Statistics</h2>
                </div>
                <div class="card-body">
                    <div class="grid grid-3">
                        <div>
                            <strong>Total Attempts:</strong> <?php echo $attempts->num_rows; ?>
                        </div>
                        <div>
                            <strong>Passing Score:</strong> <?php echo $quiz['passing_score']; ?>%
                        </div>
                        <div>
                            <strong>Total Questions:</strong> <?php echo $quiz['total_questions']; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attempts Table -->
            <div class="card">
                <div class="card-header">
                    <h2>Student Attempts</h2>
                </div>
                <div class="card-body">
                    <?php if ($attempts->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Score</th>
                                        <th>Percentage</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $attempts->data_seek(0); while ($attempt = $attempts->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($attempt['full_name']); ?></td>
                                            <td><?php echo $attempt['score'] . '/' . $attempt['total_points']; ?></td>
                                            <td><?php echo number_format($attempt['percentage'], 2); ?>%</td>
                                            <td>
                                                <span class="badge <?php echo $attempt['passed'] ? 'badge-success' : 'badge-danger'; ?>">
                                                    <?php echo $attempt['passed'] ? 'Passed' : 'Failed'; ?>
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
                        <p>No attempts for this quiz yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
