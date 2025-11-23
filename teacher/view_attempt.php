<?php
require_once '../config.php';
require_login();

if (!is_teacher()) {
    header('Location: ../login.php');
    exit();
}

$teacher_id = $_SESSION['user_id'];
$attempt_id = intval($_GET['id'] ?? 0);

// Get attempt
$stmt = $conn->prepare("
    SELECT qa.*, q.title, q.teacher_id, u.full_name
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    JOIN users u ON qa.student_id = u.id
    WHERE qa.id = ? AND q.teacher_id = ?
");
$stmt->bind_param("ii", $attempt_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: dashboard.php');
    exit();
}

$attempt = $result->fetch_assoc();
$stmt->close();

// Get student answers
$answers = $conn->query("
    SELECT sa.*, q.question_text, q.question_type, q.points,
           o.option_text as correct_answer_text
    FROM student_answers sa
    JOIN questions q ON sa.question_id = q.id
    LEFT JOIN options o ON o.question_id = q.id AND o.is_correct = 1
    WHERE sa.attempt_id = $attempt_id
    ORDER BY sa.id
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attempt - OLQS</title>
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
                <h1>Quiz Attempt Review</h1>
                <a href="dashboard.php" class="btn btn-outline">Back</a>
            </div>

            <!-- Attempt Summary -->
            <div class="card">
                <div class="card-header">
                    <h2><?php echo htmlspecialchars($attempt['title']); ?></h2>
                    <small>Student: <?php echo htmlspecialchars($attempt['full_name']); ?></small>
                </div>
                <div class="card-body">
                    <div class="grid grid-4">
                        <div>
                            <strong>Score:</strong><br>
                            <span style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color);">
                                <?php echo $attempt['score'] . '/' . $attempt['total_points']; ?>
                            </span>
                        </div>
                        <div>
                            <strong>Percentage:</strong><br>
                            <span style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color);">
                                <?php echo number_format($attempt['percentage'], 2); ?>%
                            </span>
                        </div>
                        <div>
                            <strong>Status:</strong><br>
                            <span class="badge <?php echo $attempt['passed'] ? 'badge-success' : 'badge-danger'; ?>" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                <?php echo $attempt['passed'] ? 'PASSED' : 'FAILED'; ?>
                            </span>
                        </div>
                        <div>
                            <strong>Completed:</strong><br>
                            <?php echo date('M d, Y H:i', strtotime($attempt['completed_at'])); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Answers -->
            <div class="card">
                <div class="card-header">
                    <h2>Detailed Answers</h2>
                </div>
                <div class="card-body">
                    <?php if ($answers->num_rows > 0): ?>
                        <?php $question_number = 1; while ($answer = $answers->fetch_assoc()): ?>
                            <div style="margin-bottom: 2rem; padding: 1.5rem; background-color: var(--light-color); border-radius: 0.5rem; border-left: 4px solid <?php echo $answer['is_correct'] ? 'var(--secondary-color)' : 'var(--danger-color)'; ?>;">
                                <h3>Question <?php echo $question_number; ?> (<?php echo $answer['points']; ?> points)</h3>
                                <p style="margin: 1rem 0;"><strong><?php echo htmlspecialchars($answer['question_text']); ?></strong></p>

                                <div style="margin: 1rem 0; padding: 1rem; background-color: white; border-radius: 0.5rem;">
                                    <strong>Student's Answer:</strong><br>
                                    <?php 
                                    if ($answer['question_type'] === 'multiple_choice' || $answer['question_type'] === 'true_false') {
                                        $option = $conn->query("SELECT option_text FROM options WHERE id = " . $answer['student_answer'])->fetch_assoc();
                                        echo htmlspecialchars($option['option_text'] ?? 'Not answered');
                                    } else {
                                        echo htmlspecialchars($answer['student_answer'] ?? 'Not answered');
                                    }
                                    ?>
                                </div>

                                <?php if (!$answer['is_correct']): ?>
                                    <div style="margin: 1rem 0; padding: 1rem; background-color: white; border-radius: 0.5rem;">
                                        <strong>Correct Answer:</strong><br>
                                        <?php echo htmlspecialchars($answer['correct_answer_text']); ?>
                                    </div>
                                <?php endif; ?>

                                <div style="margin-top: 1rem;">
                                    <span class="badge <?php echo $answer['is_correct'] ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo $answer['is_correct'] ? 'Correct' : 'Incorrect'; ?> (<?php echo $answer['points_earned']; ?>/<?php echo $answer['points']; ?> points)
                                    </span>
                                </div>
                            </div>
                        <?php $question_number++; endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
