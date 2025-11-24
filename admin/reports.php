<?php
require_once '../config.php';
require_login();

if (!is_admin()) {
    header('Location: ../login.php');
    exit();
}

// Get system statistics
$total_lessons = $conn->query("SELECT COUNT(*) as count FROM lessons")->fetch_assoc()['count'];
$total_quizzes = $conn->query("SELECT COUNT(*) as count FROM quizzes")->fetch_assoc()['count'];
$total_attempts = $conn->query("SELECT COUNT(*) as count FROM quiz_attempts")->fetch_assoc()['count'];
$avg_score = $conn->query("SELECT AVG(percentage) as avg FROM quiz_attempts")->fetch_assoc()['avg'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - OLQS Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">OLQS Admin</div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><span class="icon">📊</span>Dashboard</a></li>
                <li><a href="users.php"><span class="icon">👥</span>Users</a></li>
                <li><a href="verifications.php"><span class="icon">✅</span>Verifications</a></li>
                <li><a href="reports.php" class="active"><span class="icon">📈</span>Reports</a></li>
                <li><a href="settings.php"><span class="icon">⚙️</span>Settings</a></li>
                <li><a href="../logout.php"><span class="icon">🚪</span>Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1>System Reports</h1>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-content">
                        <h3><?php echo $total_lessons; ?></h3>
                        <p>Total Lessons</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-content">
                        <h3><?php echo $total_quizzes; ?></h3>
                        <p>Total Quizzes</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <h3><?php echo $total_attempts; ?></h3>
                        <p>Quiz Attempts</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <h3><?php echo round($avg_score, 2); ?>%</h3>
                        <p>Average Score</p>
                    </div>
                </div>
            </div>

            <!-- Reports Section -->
            <div class="section">
                <h2>System Overview</h2>
                <p>Comprehensive reports and analytics for the Online Learning & Quiz System.</p>
                <ul>
                    <li>Total Lessons Created: <strong><?php echo $total_lessons; ?></strong></li>
                    <li>Total Quizzes Created: <strong><?php echo $total_quizzes; ?></strong></li>
                    <li>Total Quiz Attempts: <strong><?php echo $total_attempts; ?></strong></li>
                    <li>Average Student Score: <strong><?php echo round($avg_score, 2); ?>%</strong></li>
                </ul>
            </div>
        </main>
    </div>

    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            font-size: 2.5rem;
        }

        .stat-content h3 {
            margin: 0;
            font-size: 1.8rem;
            color: var(--primary-color);
        }

        .stat-content p {
            margin: 0.25rem 0 0 0;
            color: var(--text-color);
            font-size: 0.9rem;
        }

        .section {
            background: white;
            padding: 2rem;
            border-radius: 0.75rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .section h2 {
            margin-top: 0;
        }

        .section ul {
            list-style: none;
            padding: 0;
        }

        .section li {
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
            font-size: 1.1rem;
        }

        .section li:last-child {
            border-bottom: none;
        }
    </style>
</body>
</html>
