<?php
require_once '../config.php';
require_login();

if (!is_student()) {
    header('Location: ../login.php');
    exit();
}

$student_id = $_SESSION['user_id'];
$quiz_id = intval($_GET['id'] ?? 0);

// Get quiz
$stmt = $conn->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: quizzes.php');
    exit();
}

$quiz = $result->fetch_assoc();
$stmt->close();

// Get all attempts for this student on this quiz
$attempts = $conn->query("
    SELECT * FROM quiz_attempts
    WHERE quiz_id = $quiz_id AND student_id = $student_id
    ORDER BY completed_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz History - OLQS</title>
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
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div>
                    <h1><?php echo htmlspecialchars($quiz['title']); ?> - History</h1>
                </div>
                <a href="quizzes.php" class="btn btn-outline">Back</a>
            </div>

            <!-- Attempts History -->
            <div class="card">
                <div class="card-header">
                    <h2>Your Attempts</h2>
                </div>
                <div class="card-body">
                    <?php if ($attempts->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Attempt #</th>
                                        <th>Score</th>
                                        <th>Percentage</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $attempt_number = 1; while ($attempt = $attempts->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $attempt_number; ?></td>
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
                                    <?php $attempt_number++; endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>You haven't attempted this quiz yet. <a href="take_quiz.php?id=<?php echo $quiz_id; ?>">Take the quiz now!</a></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-footer">
                    <a href="take_quiz.php?id=<?php echo $quiz_id; ?>" class="btn btn-primary">Take Quiz Again</a>
                    <a href="quizzes.php" class="btn btn-secondary">Back to Quizzes</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
