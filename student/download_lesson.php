<?php
require_once '../config.php';
require_login();

if (!is_student()) {
    header('Location: ../login.php');
    exit();
}

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

$file_path = LESSON_UPLOAD_DIR . $lesson['file_path'];

if (!file_exists($file_path)) {
    header('Location: lessons.php');
    exit();
}

// Download file
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($lesson['file_path']) . '"');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit();
?>
