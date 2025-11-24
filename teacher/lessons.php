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

// Handle lesson upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    $title = sanitize_input($_POST['title'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');

    if (empty($title)) {
        $error = 'Lesson title is required.';
    } elseif (!isset($_FILES['lesson_file']) || $_FILES['lesson_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please select a valid file.';
    } else {
        $file = $_FILES['lesson_file'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $file_size = $file['size'];

        if (!in_array($file_ext, ALLOWED_EXTENSIONS)) {
            $error = 'File type not allowed. Allowed types: ' . implode(', ', ALLOWED_EXTENSIONS);
        } elseif ($file_size > MAX_FILE_SIZE) {
            $error = 'File size exceeds maximum limit of 50MB.';
        } else {
            $file_name = uniqid() . '_' . basename($file['name']);
            $file_path = LESSON_UPLOAD_DIR . $file_name;

            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                $stmt = $conn->prepare("INSERT INTO lessons (teacher_id, title, description, file_path, file_type, file_size) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issssi", $teacher_id, $title, $description, $file_name, $file_ext, $file_size);

                if ($stmt->execute()) {
                    $message = 'Lesson uploaded successfully!';
                } else {
                    $error = 'Error saving lesson to database.';
                    unlink($file_path);
                }
                $stmt->close();
            } else {
                $error = 'Error uploading file.';
            }
        }
    }
}

// Handle lesson deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $lesson_id = intval($_POST['lesson_id']);
    $stmt = $conn->prepare("SELECT file_path FROM lessons WHERE id = ? AND teacher_id = ?");
    $stmt->bind_param("ii", $lesson_id, $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $lesson = $result->fetch_assoc();
        $file_path = LESSON_UPLOAD_DIR . $lesson['file_path'];

        if (file_exists($file_path)) {
            unlink($file_path);
        }

        $delete_stmt = $conn->prepare("DELETE FROM lessons WHERE id = ? AND teacher_id = ?");
        $delete_stmt->bind_param("ii", $lesson_id, $teacher_id);
        if ($delete_stmt->execute()) {
            $message = 'Lesson deleted successfully!';
        } else {
            $error = 'Error deleting lesson.';
        }
        $delete_stmt->close();
    }
    $stmt->close();
}

// Get all lessons
$lessons = $conn->query("SELECT * FROM lessons WHERE teacher_id = $teacher_id ORDER BY created_at DESC");
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
                <li><a href="analytics.php"><span class="icon">📈</span>Analytics</a></li>
                <li><a href="profile.php"><span class="icon">👤</span>Profile</a></li>
                <li><a href="../logout.php"><span class="icon">🚪</span>Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1>Lessons</h1>
                <button class="btn btn-primary" onclick="openModal('uploadModal')">Upload Lesson</button>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Lessons List -->
            <div class="card">
                <div class="card-body">
                    <?php if ($lessons->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>File Type</th>
                                        <th>File Size</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($lesson = $lessons->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($lesson['title']); ?></td>
                                            <td><span class="badge badge-primary"><?php echo strtoupper($lesson['file_type']); ?></span></td>
                                            <td><?php echo round($lesson['file_size'] / 1024 / 1024, 2) . ' MB'; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($lesson['created_at'])); ?></td>
                                            <td>
                                                <a href="download_lesson.php?id=<?php echo $lesson['id']; ?>" class="btn btn-sm btn-secondary">Download</a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="lesson_id" value="<?php echo $lesson['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No lessons uploaded yet. <a href="#" onclick="openModal('uploadModal')">Upload your first lesson</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Upload Lesson</h2>
                <button class="close-btn" onclick="closeModal('uploadModal')">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload">
                
                <div class="form-group">
                    <label for="title">Lesson Title *</label>
                    <input type="text" id="title" name="title" required placeholder="Enter lesson title">
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Enter lesson description"></textarea>
                </div>

                <div class="form-group">
                    <label for="lesson_file">Select File *</label>
                    <input type="file" id="lesson_file" name="lesson_file" required accept=".pdf,.pptx,.ppt,.docx,.doc,.mp4,.avi,.mov,.jpg,.jpeg,.png">
                    <small>Allowed: PDF, PPTX, DOCX, MP4, AVI, MOV, JPG, PNG (Max 50MB)</small>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('uploadModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
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
