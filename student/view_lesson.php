<?php
require_once '../config.php';
require_login();

if (!is_student()) {
    header('Location: ../login.php');
    exit();
}

$student_id = $_SESSION['user_id'];
$lesson_id = intval($_GET['id'] ?? 0);

// Get lesson
$stmt = $conn->prepare("SELECT * FROM lessons WHERE id = ?");
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: lessons.php');
    exit();
}

$lesson = $result->fetch_assoc();
$stmt->close();

// Mark lesson as viewed
$stmt = $conn->prepare("INSERT IGNORE INTO student_lessons (student_id, lesson_id) VALUES (?, ?)");
$stmt->bind_param("ii", $student_id, $lesson_id);
$stmt->execute();
$stmt->close();

$file_path = LESSON_UPLOAD_DIR . $lesson['file_path'];

if (!file_exists($file_path)) {
    $file_not_found = true;
} else {
    $file_not_found = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Lesson - OLQS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">OLQS</div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="lessons.php" class="active">Lessons</a></li>
                <li><a href="quizzes.php">Quizzes</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1><?php echo htmlspecialchars($lesson['title']); ?></h1>
                <a href="lessons.php" class="btn btn-outline">Back</a>
            </div>

            <!-- Lesson Info -->
            <div class="card">
                <div class="card-header">
                    <h2>Lesson Information</h2>
                </div>
                <div class="card-body">
                    <div class="grid grid-2">
                        <div>
                            <strong>Title:</strong> <?php echo htmlspecialchars($lesson['title']); ?>
                        </div>
                        <div>
                            <strong>File Type:</strong> <?php echo strtoupper($lesson['file_type']); ?>
                        </div>
                        <div>
                            <strong>File Size:</strong> <?php echo round($lesson['file_size'] / 1024 / 1024, 2) . ' MB'; ?>
                        </div>
                        <div>
                            <strong>Created:</strong> <?php echo date('M d, Y H:i', strtotime($lesson['created_at'])); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lesson Content -->
            <div class="card">
                <div class="card-header">
                    <h2>Lesson Content</h2>
                </div>
                <div class="card-body">
                    <?php if ($lesson['description']): ?>
                        <div style="margin-bottom: 1.5rem; padding: 1rem; background-color: var(--light-color); border-radius: 0.5rem;">
                            <h3>Description</h3>
                            <p><?php echo htmlspecialchars($lesson['description']); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($file_not_found): ?>
                        <div class="alert alert-danger">
                            File not found. The lesson file may have been deleted.
                        </div>
                    <?php else: ?>
                        <div style="margin: 1.5rem 0; padding: 1.5rem; background-color: var(--light-color); border-radius: 0.5rem; text-align: center;">
                            <?php
                            $file_ext = strtolower($lesson['file_type']);
                            
                            if (in_array($file_ext, ['pdf'])) {
                                echo '<p><strong>PDF Document</strong></p>';
                                echo '<p>Click the download button to view or download this PDF file.</p>';
                            } elseif (in_array($file_ext, ['pptx', 'ppt'])) {
                                echo '<p><strong>PowerPoint Presentation</strong></p>';
                                echo '<p>Click the download button to view this presentation.</p>';
                            } elseif (in_array($file_ext, ['docx', 'doc'])) {
                                echo '<p><strong>Word Document</strong></p>';
                                echo '<p>Click the download button to view this document.</p>';
                            } elseif (in_array($file_ext, ['mp4', 'avi', 'mov'])) {
                                echo '<p><strong>Video File</strong></p>';
                                echo '<video width="100%" height="auto" controls style="max-width: 600px; border-radius: 0.5rem;">';
                                echo '<source src="' . htmlspecialchars($file_path) . '" type="video/' . $file_ext . '">';
                                echo 'Your browser does not support the video tag.';
                                echo '</video>';
                            } elseif (in_array($file_ext, ['jpg', 'jpeg', 'png'])) {
                                echo '<img src="' . htmlspecialchars($file_path) . '" alt="' . htmlspecialchars($lesson['title']) . '" style="max-width: 100%; height: auto; border-radius: 0.5rem;">';
                            }
                            ?>
                        </div>

                        <div style="text-align: center; margin-top: 1.5rem;">
                            <a href="download_lesson.php?id=<?php echo $lesson['id']; ?>" class="btn btn-primary btn-large">Download File</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Related Quizzes -->
            <div class="card">
                <div class="card-header">
                    <h2>Related Quizzes</h2>
                </div>
                <div class="card-body">
                    <p>After reviewing this lesson, you can test your knowledge by taking related quizzes.</p>
                    <a href="quizzes.php" class="btn btn-primary">View All Quizzes</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
