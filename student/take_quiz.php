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

// Handle quiz submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_quiz') {
    $score = 0;
    $total_points = 0;

    // Get all questions
    $questions = $conn->query("SELECT * FROM questions WHERE quiz_id = $quiz_id");

    // Create quiz attempt record
    $stmt = $conn->prepare("INSERT INTO quiz_attempts (quiz_id, student_id, started_at, completed_at) VALUES (?, ?, NOW(), NOW())");
    $stmt->bind_param("ii", $quiz_id, $student_id);
    $stmt->execute();
    $attempt_id = $stmt->insert_id;
    $stmt->close();

    // Process each question
    while ($question = $questions->fetch_assoc()) {
        $question_id = $question['id'];
        $student_answer = sanitize_input($_POST["question_$question_id"] ?? '');
        $points = $question['points'];
        $total_points += $points;
        $is_correct = 0;
        $points_earned = 0;

        if ($question['question_type'] === 'multiple_choice' || $question['question_type'] === 'true_false') {
            // Check if answer is correct
            $correct_option = $conn->query("SELECT id FROM options WHERE question_id = $question_id AND is_correct = 1")->fetch_assoc();
            if ($correct_option && $student_answer == $correct_option['id']) {
                $is_correct = 1;
                $points_earned = $points;
                $score += $points;
            }
        } else {
            // Short answer - mark as needs review
            $is_correct = 0;
        }

        // Save student answer
        $stmt = $conn->prepare("INSERT INTO student_answers (attempt_id, question_id, student_answer, is_correct, points_earned) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisii", $attempt_id, $question_id, $student_answer, $is_correct, $points_earned);
        $stmt->execute();
        $stmt->close();
    }

    // Calculate percentage and if passed
    $percentage = $total_points > 0 ? ($score / $total_points) * 100 : 0;
    $passed = $percentage >= $quiz['passing_score'] ? 1 : 0;

    // Update quiz attempt with final score
    $stmt = $conn->prepare("UPDATE quiz_attempts SET score = ?, total_points = ?, percentage = ?, passed = ? WHERE id = ?");
    $stmt->bind_param("iidii", $score, $total_points, $percentage, $passed, $attempt_id);
    $stmt->execute();
    $stmt->close();

    // Update progress tracking
    $stmt = $conn->prepare("
        INSERT INTO progress_tracking (student_id, quiz_id, quiz_attempts, best_score, last_attempt)
        VALUES (?, ?, 1, ?, NOW())
        ON DUPLICATE KEY UPDATE
        quiz_attempts = quiz_attempts + 1,
        best_score = GREATEST(best_score, ?),
        last_attempt = NOW()
    ");
    $stmt->bind_param("iiii", $student_id, $quiz_id, $percentage, $percentage);
    $stmt->execute();
    $stmt->close();

    // Redirect to results
    header("Location: view_result.php?id=$attempt_id");
    exit();
}

// Get questions
$questions = $conn->query("SELECT * FROM questions WHERE quiz_id = $quiz_id ORDER BY id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Quiz - OLQS</title>
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
                    <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
                    <small><?php echo htmlspecialchars($quiz['description']); ?></small>
                </div>
                <a href="quizzes.php" class="btn btn-outline">Back</a>
            </div>

            <!-- Quiz Info -->
            <div class="card">
                <div class="card-header">
                    <h2>Quiz Information</h2>
                </div>
                <div class="card-body">
                    <div class="grid grid-3">
                        <div>
                            <strong>Total Questions:</strong> <?php echo $quiz['total_questions']; ?>
                        </div>
                        <div>
                            <strong>Passing Score:</strong> <?php echo $quiz['passing_score']; ?>%
                        </div>
                        <div>
                            <strong>Time Limit:</strong> <?php echo $quiz['time_limit'] > 0 ? $quiz['time_limit'] . ' minutes' : 'Unlimited'; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quiz Form -->
            <form method="POST" class="card">
                <input type="hidden" name="action" value="submit_quiz">
                
                <div class="card-header">
                    <h2>Questions</h2>
                </div>
                <div class="card-body">
                    <?php if ($questions->num_rows > 0): ?>
                        <?php $question_number = 1; while ($question = $questions->fetch_assoc()): ?>
                            <div style="margin-bottom: 2rem; padding: 1.5rem; background-color: var(--light-color); border-radius: 0.5rem;">
                                <h3>Question <?php echo $question_number; ?> (<?php echo $question['points']; ?> points)</h3>
                                <p style="margin: 1rem 0;"><strong><?php echo htmlspecialchars($question['question_text']); ?></strong></p>

                                <?php if ($question['question_type'] === 'multiple_choice' || $question['question_type'] === 'true_false'): ?>
                                    <?php
                                    $options = $conn->query("SELECT * FROM options WHERE question_id = " . $question['id']);
                                    ?>
                                    <div style="margin: 1rem 0;">
                                        <?php while ($option = $options->fetch_assoc()): ?>
                                            <div style="margin-bottom: 0.75rem;">
                                                <label style="display: flex; align-items: center; cursor: pointer;">
                                                    <input type="radio" name="question_<?php echo $question['id']; ?>" value="<?php echo $option['id']; ?>" style="margin-right: 0.5rem;">
                                                    <?php echo htmlspecialchars($option['option_text']); ?>
                                                </label>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <div style="margin: 1rem 0;">
                                        <textarea name="question_<?php echo $question['id']; ?>" placeholder="Enter your answer" style="width: 100%; min-height: 100px;"></textarea>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php $question_number++; endwhile; ?>
                    <?php else: ?>
                        <p>No questions in this quiz.</p>
                    <?php endif; ?>
                </div>

                <div class="card-footer">
                    <a href="quizzes.php" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure you want to submit? You cannot change your answers after submission.');">Submit Quiz</button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
