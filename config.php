<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'olqs_db');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Session configuration
session_start();
define('SESSION_TIMEOUT', 3600); // 1 hour

// File upload configuration
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('LESSON_UPLOAD_DIR', UPLOAD_DIR . 'lessons/');
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
define('ALLOWED_EXTENSIONS', ['pdf', 'pptx', 'ppt', 'docx', 'doc', 'mp4', 'avi', 'mov', 'jpg', 'jpeg', 'png']);

// Create upload directories if they don't exist
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
if (!is_dir(LESSON_UPLOAD_DIR)) {
    mkdir(LESSON_UPLOAD_DIR, 0755, true);
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// Helper function to sanitize input
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Helper function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

// Helper function to check user type
function is_teacher() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'teacher';
}

function is_student() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student';
}

// Redirect to login if not authenticated
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit();
    }
}

// Redirect based on user type
function redirect_by_role() {
    if (is_teacher()) {
        header('Location: teacher/dashboard.php');
    } elseif (is_student()) {
        header('Location: student/dashboard.php');
    } else {
        header('Location: login.php');
    }
    exit();
}
?>
