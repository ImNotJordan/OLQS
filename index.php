<?php
require_once 'config.php';

// If user is already logged in, redirect to appropriate dashboard
if (is_logged_in()) {
    redirect_by_role();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Learning & Quiz System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="landing-page">
        <nav class="navbar">
            <div class="container">
                <div class="logo">OLQS</div>
                <div class="nav-links">
                    <a href="login.php" class="btn btn-secondary">Login</a>
                    <a href="register.php" class="btn btn-primary">Register</a>
                </div>
            </div>
        </nav>

        <section class="hero">
            <div class="container">
                <div class="hero-content">
                    <h1>Online Learning & Quiz System</h1>
                    <p>Empower education through digital learning and intelligent assessment</p>
                    <div class="hero-buttons">
                        <a href="register.php" class="btn btn-primary btn-large">Get Started</a>
                        <a href="#features" class="btn btn-secondary btn-large">Learn More</a>
                    </div>
                </div>
            </div>
        </section>

        <section id="features" class="features">
            <div class="container">
                <h2>Key Features</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">📚</div>
                        <h3>Lesson Management</h3>
                        <p>Teachers can upload and organize lessons with various file formats including PDFs, presentations, and videos.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📝</div>
                        <h3>Quiz Creation</h3>
                        <p>Create interactive quizzes with multiple question types and automatic scoring for instant feedback.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📊</div>
                        <h3>Progress Tracking</h3>
                        <p>Monitor student performance with detailed analytics and comprehensive progress reports.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">👥</div>
                        <h3>Dual Accounts</h3>
                        <p>Separate teacher and student accounts with role-based access and permissions.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">⚡</div>
                        <h3>Instant Feedback</h3>
                        <p>Students receive immediate quiz results with detailed answer reviews.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🔒</div>
                        <h3>Secure Platform</h3>
                        <p>Enterprise-grade security with encrypted passwords and secure session management.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="how-it-works">
            <div class="container">
                <h2>How It Works</h2>
                <div class="steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <h3>Register</h3>
                        <p>Create an account as a teacher or student</p>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <h3>Learn</h3>
                        <p>Access lessons and learning materials</p>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <h3>Assess</h3>
                        <p>Take quizzes and get instant feedback</p>
                    </div>
                    <div class="step">
                        <div class="step-number">4</div>
                        <h3>Track</h3>
                        <p>Monitor progress and improve performance</p>
                    </div>
                </div>
            </div>
        </section>

        <footer class="footer">
            <div class="container">
                <p>&copy; 2025 Online Learning & Quiz System. All rights reserved.</p>
            </div>
        </footer>
    </div>
</body>
</html>
