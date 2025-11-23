<?php
require_once '../config.php';
require_login();

if (!is_teacher()) {
    header('Location: ../login.php');
    exit();
}

$teacher_id = $_SESSION['user_id'];
$quiz_id = intval($_GET['id'] ?? 0);
$message = '';
$error = '';

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

// Handle adding question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_question') {
    $question_text = sanitize_input($_POST['question_text'] ?? '');
    $question_type = sanitize_input($_POST['question_type'] ?? 'multiple_choice');
    $points = intval($_POST['points'] ?? 1);

    if (empty($question_text)) {
        $error = 'Question text is required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO questions (quiz_id, question_text, question_type, points) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $quiz_id, $question_text, $question_type, $points);

        if ($stmt->execute()) {
            $question_id = $stmt->insert_id;
            
            // Add options for multiple choice
            if ($question_type === 'multiple_choice') {
                for ($i = 1; $i <= 4; $i++) {
                    $option_text = sanitize_input($_POST["option_$i"] ?? '');
                    $is_correct = isset($_POST["correct_option"]) && $_POST["correct_option"] == $i ? 1 : 0;

                    if (!empty($option_text)) {
                        $opt_stmt = $conn->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
                        $opt_stmt->bind_param("isi", $question_id, $option_text, $is_correct);
                        $opt_stmt->execute();
                        $opt_stmt->close();
                    }
                }
            } elseif ($question_type === 'true_false') {
                $true_text = 'True';
                $false_text = 'False';
                $correct_answer = isset($_POST["correct_answer"]) ? $_POST["correct_answer"] : 'true';

                $opt_stmt = $conn->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
                $opt_stmt->bind_param("isi", $question_id, $true_text, $correct_answer === 'true' ? 1 : 0);
                $opt_stmt->execute();
                $opt_stmt->close();

                $opt_stmt = $conn->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
                $opt_stmt->bind_param("isi", $question_id, $false_text, $correct_answer === 'false' ? 1 : 0);
                $opt_stmt->execute();
                $opt_stmt->close();
            }

            // Update total questions count
            $update_stmt = $conn->prepare("UPDATE quizzes SET total_questions = total_questions + 1 WHERE id = ?");
            $update_stmt->bind_param("i", $quiz_id);
            $update_stmt->execute();
            $update_stmt->close();

            $message = 'Question added successfully!';
            header("refresh:2;url=edit_quiz.php?id=$quiz_id");
        } else {
            $error = 'Error adding question.';
        }
        $stmt->close();
    }
}

// Handle deleting question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_question') {
    $question_id = intval($_POST['question_id']);
    
    $stmt = $conn->prepare("DELETE FROM questions WHERE id = ? AND quiz_id = ?");
    $stmt->bind_param("ii", $question_id, $quiz_id);
    
    if ($stmt->execute()) {
        $update_stmt = $conn->prepare("UPDATE quizzes SET total_questions = total_questions - 1 WHERE id = ?");
        $update_stmt->bind_param("i", $quiz_id);
        $update_stmt->execute();
        $update_stmt->close();
        
        $message = 'Question deleted successfully!';
    } else {
        $error = 'Error deleting question.';
    }
    $stmt->close();
}

// Get questions
$questions = $conn->query("SELECT * FROM questions WHERE quiz_id = $quiz_id ORDER BY id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quiz - OLQS</title>
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
                    <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
                    <small><?php echo htmlspecialchars($quiz['description']); ?></small>
                </div>
                <a href="quizzes.php" class="btn btn-outline">Back</a>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

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

            <!-- Questions -->
            <div class="card">
                <div class="card-header">
                    <h2>Questions</h2>
                    <button class="btn btn-primary" onclick="openModal('addQuestionModal')" style="float: right;">Add Question</button>
                </div>
                <div class="card-body">
                    <?php if ($questions->num_rows > 0): ?>
                        <?php $question_number = 1; while ($question = $questions->fetch_assoc()): ?>
                            <div class="card" style="margin-bottom: 1.5rem; background-color: var(--light-color);">
                                <div class="card-header">
                                    <h3>Question <?php echo $question_number; ?> (<?php echo ucfirst(str_replace('_', ' ', $question['question_type'])); ?>) - <?php echo $question['points']; ?> points</h3>
                                </div>
                                <div class="card-body">
                                    <p><strong><?php echo htmlspecialchars($question['question_text']); ?></strong></p>
                                    
                                    <?php
                                    $options = $conn->query("SELECT * FROM options WHERE question_id = " . $question['id']);
                                    if ($options->num_rows > 0):
                                    ?>
                                        <ul style="padding-left: 1.5rem;">
                                            <?php while ($option = $options->fetch_assoc()): ?>
                                                <li>
                                                    <?php echo htmlspecialchars($option['option_text']); ?>
                                                    <?php if ($option['is_correct']): ?>
                                                        <span class="badge badge-success">Correct</span>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endwhile; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer">
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?');">
                                        <input type="hidden" name="action" value="delete_question">
                                        <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php $question_number++; endwhile; ?>
                    <?php else: ?>
                        <p>No questions added yet. <a href="#" onclick="openModal('addQuestionModal')">Add your first question</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Question Modal -->
    <div id="addQuestionModal" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h2>Add Question</h2>
                <button class="close-btn" onclick="closeModal('addQuestionModal')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_question">
                
                <div class="form-group">
                    <label for="question_text">Question Text *</label>
                    <textarea id="question_text" name="question_text" required placeholder="Enter your question"></textarea>
                </div>

                <div class="form-group">
                    <label for="question_type">Question Type *</label>
                    <select id="question_type" name="question_type" onchange="updateQuestionType()" required>
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="true_false">True/False</option>
                        <option value="short_answer">Short Answer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="points">Points *</label>
                    <input type="number" id="points" name="points" value="1" min="1" required>
                </div>

                <!-- Multiple Choice Options -->
                <div id="multipleChoiceOptions">
                    <h3>Options</h3>
                    <div class="form-group">
                        <label>Option 1 *</label>
                        <input type="text" name="option_1" placeholder="Enter option 1">
                    </div>
                    <div class="form-group">
                        <label>Option 2 *</label>
                        <input type="text" name="option_2" placeholder="Enter option 2">
                    </div>
                    <div class="form-group">
                        <label>Option 3</label>
                        <input type="text" name="option_3" placeholder="Enter option 3">
                    </div>
                    <div class="form-group">
                        <label>Option 4</label>
                        <input type="text" name="option_4" placeholder="Enter option 4">
                    </div>
                    <div class="form-group">
                        <label for="correct_option">Correct Answer *</label>
                        <select id="correct_option" name="correct_option" required>
                            <option value="1">Option 1</option>
                            <option value="2">Option 2</option>
                            <option value="3">Option 3</option>
                            <option value="4">Option 4</option>
                        </select>
                    </div>
                </div>

                <!-- True/False Options -->
                <div id="trueFalseOptions" style="display: none;">
                    <h3>Correct Answer</h3>
                    <div class="form-group">
                        <label for="correct_answer">Select Correct Answer *</label>
                        <select id="correct_answer" name="correct_answer">
                            <option value="true">True</option>
                            <option value="false">False</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addQuestionModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Question</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('show');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        function updateQuestionType() {
            const type = document.getElementById('question_type').value;
            const mcOptions = document.getElementById('multipleChoiceOptions');
            const tfOptions = document.getElementById('trueFalseOptions');

            if (type === 'multiple_choice') {
                mcOptions.style.display = 'block';
                tfOptions.style.display = 'none';
            } else if (type === 'true_false') {
                mcOptions.style.display = 'none';
                tfOptions.style.display = 'block';
            } else {
                mcOptions.style.display = 'none';
                tfOptions.style.display = 'none';
            }
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
            }
        }
    </script>
</body>
</html>
