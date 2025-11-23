<?php
require_once '../config.php';
require_login();

if (!is_teacher()) {
    header('Location: ../login.php');
    exit();
}

$teacher_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle quiz creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $title = sanitize_input($_POST['title'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $passing_score = intval($_POST['passing_score'] ?? 60);
    $time_limit = intval($_POST['time_limit'] ?? 0);

    if (empty($title)) {
        $error = 'Quiz title is required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO quizzes (teacher_id, title, description, passing_score, time_limit) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issii", $teacher_id, $title, $description, $passing_score, $time_limit);

        if ($stmt->execute()) {
            $quiz_id = $stmt->insert_id;
            $message = 'Quiz created successfully!';
            header("Location: edit_quiz.php?id=$quiz_id");
            exit();
        } else {
            $error = 'Error creating quiz.';
        }
        $stmt->close();
    }
}

// Handle quiz deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $quiz_id = intval($_POST['quiz_id']);
    $stmt = $conn->prepare("DELETE FROM quizzes WHERE id = ? AND teacher_id = ?");
    $stmt->bind_param("ii", $quiz_id, $teacher_id);

    if ($stmt->execute()) {
        $message = 'Quiz deleted successfully!';
    } else {
        $error = 'Error deleting quiz.';
    }
    $stmt->close();
}

// Get all quizzes
$quizzes = $conn->query("SELECT * FROM quizzes WHERE teacher_id = $teacher_id ORDER BY created_at DESC");
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
                <h1>Quizzes</h1>
                <button class="btn btn-primary" onclick="openModal('createModal')">Create Quiz</button>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Quizzes List -->
            <div class="card">
                <div class="card-body">
                    <?php if ($quizzes->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Questions</th>
                                        <th>Passing Score</th>
                                        <th>Time Limit</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($quiz = $quizzes->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                                            <td><?php echo $quiz['total_questions']; ?></td>
                                            <td><?php echo $quiz['passing_score']; ?>%</td>
                                            <td><?php echo $quiz['time_limit'] > 0 ? $quiz['time_limit'] . ' min' : 'Unlimited'; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($quiz['created_at'])); ?></td>
                                            <td>
                                                <a href="edit_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                <a href="quiz_results.php?id=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-secondary">Results</a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No quizzes created yet. <a href="#" onclick="openModal('createModal')">Create your first quiz</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Create Quiz Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Create Quiz</h2>
                <button class="close-btn" onclick="closeModal('createModal')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                
                <div class="form-group">
                    <label for="title">Quiz Title *</label>
                    <input type="text" id="title" name="title" required placeholder="Enter quiz title">
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Enter quiz description"></textarea>
                </div>

                <div class="form-group">
                    <label for="passing_score">Passing Score (%) *</label>
                    <input type="number" id="passing_score" name="passing_score" value="60" min="0" max="100" required>
                </div>

                <div class="form-group">
                    <label for="time_limit">Time Limit (minutes)</label>
                    <input type="number" id="time_limit" name="time_limit" value="0" min="0" placeholder="0 for unlimited">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('createModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
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

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
            }
        }
    </script>
</body>
</html>
