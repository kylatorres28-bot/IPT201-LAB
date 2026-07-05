<?php
require_once 'config.php';
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$skills_score = $user['skills_score'] ?? 80;
$interest_profile = $user['interest_profile'] ?? 'Investigative & Enterprising';
$top_career = $user['top_career'] ?? 'AI & Software Engineer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - CareerAI</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="nav">
        <a href="dashboard_student.php" class="logo">🎓 CareerAI</a>
        <div class="nav-links">
            <a href="dashboard_student.php" class="active">Dashboard</a>
            <a href="skill_assessment.php">Skill Assessment</a>
            <a href="interest_survey.php">Interest Survey</a>
            <a href="industry_dashboard.php">Industry Dashboard</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php" class="btn-secondary" style="padding: 6px 16px;">Logout</a>
        </div>
    </nav>

    <div class="hero" style="padding: 70px 20px 80px;">
        <span class="badge badge-orange" style="margin-bottom: 12px; background: rgba(255,255,255,0.15); color: #fff;">👋 Welcome Back, Student Portal</span>
        <h1 style="font-size: 3em;"><?php echo htmlspecialchars($user['name']); ?></h1>
        <p><?php echo htmlspecialchars($user['education'] ?? 'Bachelor'); ?> in <?php echo htmlspecialchars($user['specialization'] ?? 'Computer Science'); ?> • CGPA: <?php echo htmlspecialchars($user['cgpa'] ?? 85); ?></p>
    </div>

    <div class="container card-elevated">
        <!-- 4 Stats Cards -->
        <div class="grid-2" style="margin-bottom: 32px;">
            <div class="card" style="padding: 24px;">
                <span class="badge badge-orange" style="margin-bottom: 8px;">Technical Score</span>
                <h3 style="font-size: 2.2em; color: var(--primary);"><?php echo $skills_score; ?>%</h3>
                <p style="font-size: 0.9em; color: var(--text-muted); margin: 6px 0 12px;">Based on your latest 5-part engineering assessment.</p>
                <a href="skill_assessment.php" style="color: var(--primary); font-weight: 600; font-size: 0.9em; text-decoration: none;">Retake Assessment →</a>
            </div>

            <div class="card" style="padding: 24px;">
                <span class="badge badge-indigo" style="margin-bottom: 8px;">Dominant Interests</span>
                <h3 style="font-size: 1.4em; color: var(--text-main); margin: 8px 0;"><?php echo htmlspecialchars($interest_profile); ?></h3>
                <p style="font-size: 0.9em; color: var(--text-muted); margin: 6px 0 12px;">RIASEC & technology career vector.</p>
                <a href="interest_survey.php" style="color: var(--accent-indigo); font-weight: 600; font-size: 0.9em; text-decoration: none;">Update Survey →</a>
            </div>

            <div class="card" style="padding: 24px;">
                <span class="badge badge-cyan" style="margin-bottom: 8px;">Top ML Career Match</span>
                <h3 style="font-size: 1.5em; color: var(--accent-cyan); margin: 8px 0;"><?php echo htmlspecialchars($top_career); ?></h3>
                <p style="font-size: 0.9em; color: var(--text-muted); margin: 6px 0 12px;">Predicted by our weighted multi-feature classification engine.</p>
                <a href="process.php?auto=1" style="color: var(--accent-cyan); font-weight: 600; font-size: 0.9em; text-decoration: none;">Run ML Prediction →</a>
            </div>

            <div class="card" style="padding: 24px;">
                <span class="badge badge-emerald" style="margin-bottom: 8px;">Industry Alignment</span>
                <h3 style="font-size: 1.5em; color: var(--accent-emerald); margin: 8px 0;">Live Industry Insights</h3>
                <p style="font-size: 0.9em; color: var(--text-muted); margin: 6px 0 12px;">Explore salaries, demand, and skill gaps in your target sectors.</p>
                <a href="industry_dashboard.php" style="color: var(--accent-emerald); font-weight: 600; font-size: 0.9em; text-decoration: none;">Explore Dashboard →</a>
            </div>
        </div>

        <h2 style="margin-bottom: 20px;">Quick Action Hub</h2>
        <div class="grid-2">
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <h3 style="font-size: 1.4em; margin-bottom: 8px;">⚡ Generate AI Career Roadmap</h3>
                    <p style="color: var(--text-muted);">Run your updated profile through our Python classification model to get 4 ranked career trajectories and skill gap recommendations.</p>
                </div>
                <a href="process.php?auto=1" class="btn-primary" style="margin-top: 20px; text-align: center;">Analyze My Career Path →</a>
            </div>

            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <h3 style="font-size: 1.4em; margin-bottom: 8px;">🏢 Industry Information Dashboard</h3>
                    <p style="color: var(--text-muted);">See real-time compensation metrics, hiring indexes, and top companies across AI, Cloud, FinTech, and Green Tech.</p>
                </div>
                <a href="industry_dashboard.php" class="btn-secondary" style="margin-top: 20px; justify-content: center;">Explore Industries →</a>
            </div>
        </div>
    </div>
</body>
</html>