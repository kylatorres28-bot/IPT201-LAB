<?php
require_once 'config.php';
if (!isset($_SESSION['recommendations'])) {
    header('Location: index.php');
    exit;
}

$recs = $_SESSION['recommendations'];
$user_data = $_SESSION['user_data'] ?? [];
$is_logged_in = isset($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personalized AI Career Recommendations - CareerAI</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="nav">
        <a href="index.php" class="logo">🎓 CareerAI</a>
        <div class="nav-links">
            <?php if ($is_logged_in): ?>
            <a href="dashboard_student.php">Dashboard</a>
            <a href="skill_assessment.php">Skill Assessment</a>
            <a href="interest_survey.php">Interest Survey</a>
            <a href="industry_dashboard.php">Industry Dashboard</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php" class="btn-secondary" style="padding: 6px 16px;">Logout</a>
            <?php else: ?>
            <a href="index.php">New Test</a>
            <a href="login.php">Login</a>
            <a href="register.php" class="btn-nav-cta">Sign Up Free</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="hero" style="padding: 60px 20px 70px;">
        <span class="badge badge-orange" style="margin-bottom: 12px; background: rgba(255,255,255,0.15); color: #fff;">✨ ML Classification Complete</span>
        <h1 style="font-size: 2.8em;">Your AI Career Roadmap</h1>
        <p>Profile Analysis: <?php echo htmlspecialchars($user_data['education_level'] ?? 'Degree'); ?> in <?php echo htmlspecialchars($user_data['specialization'] ?? 'Specialization'); ?> (CGPA: <?php echo htmlspecialchars($user_data['cgpa'] ?? 85); ?>)</p>
    </div>

    <div class="container card-elevated">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
            <h2>Top Ranked Career Trajectories</h2>
            <div style="display: flex; gap: 12px;">
                <a href="industry_dashboard.php" class="btn-primary" style="padding: 12px 24px; font-size: 0.95em;">🏢 Explore Aligned Industries</a>
                <a href="index.php" class="btn-secondary" style="padding: 12px 24px;">🔄 Test Another Profile</a>
            </div>
        </div>

        <?php foreach ($recs as $index => $rec): 
            $is_top = ($index === 0);
        ?>
        <div class="rec-card" style="<?php echo $is_top ? 'border: 2px solid #ff6b00; background: linear-gradient(to right, #ffffff, #fffaf5);' : ''; ?>">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 16px;">
                <div>
                    <?php if ($is_top): ?>
                    <span class="badge badge-orange" style="margin-bottom: 8px;">👑 #1 Highest Compatibility Match</span>
                    <?php else: ?>
                    <span class="badge badge-indigo" style="margin-bottom: 8px;">Rank #<?php echo ($index + 1); ?> Match</span>
                    <?php endif; ?>
                    <h3 style="font-size: 1.8em; color: var(--text-main);"><?php echo htmlspecialchars($rec['title']); ?></h3>
                </div>
                <div style="text-align: right;">
                    <div class="score-badge"><?php echo $rec['score']; ?>%</div>
                    <span style="font-size: 0.82em; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Alignment Score</span>
                </div>
            </div>

            <p style="font-size: 1.05em; color: var(--text-muted); margin-bottom: 20px;"><?php echo htmlspecialchars($rec['description'] ?? ''); ?></p>

            <div class="grid-3" style="margin: 20px 0; background: rgba(255,255,255,0.8); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                <div>
                    <span style="font-size: 0.82em; color: var(--text-muted); display: block;">Est. Salary Range</span>
                    <strong style="color: var(--primary); font-size: 1.1em;"><?php echo htmlspecialchars($rec['salary_range'] ?? '$100k - $160k'); ?></strong>
                </div>
                <div>
                    <span style="font-size: 0.82em; color: var(--text-muted); display: block;">Market Growth</span>
                    <strong style="color: var(--accent-emerald); font-size: 1.1em;"><?php echo htmlspecialchars($rec['growth_rate'] ?? '+25% YoY'); ?></strong>
                </div>
                <div>
                    <span style="font-size: 0.82em; color: var(--text-muted); display: block;">Primary Industries</span>
                    <strong style="font-size: 0.95em;"><?php echo implode(', ', $rec['industries'] ?? ['Tech']); ?></strong>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                <div>
                    <span style="font-size: 0.88em; color: var(--text-muted);">Key Competencies:</span>
                    <?php foreach ($rec['required_skills'] ?? [] as $sk): ?>
                    <span class="badge badge-cyan" style="margin-left: 4px;"><?php echo htmlspecialchars($sk); ?></span>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($rec['missing_skills'])): ?>
                <div style="font-size: 0.88em;">
                    <span style="color: #dc2626; font-weight: 600;">⚠️ Skill Gap Focus:</span> <?php echo htmlspecialchars(implode(', ', $rec['missing_skills'])); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <div style="text-align: center; margin-top: 40px;">
            <?php if ($is_logged_in): ?>
            <a href="dashboard_student.php" class="btn-primary" style="padding: 16px 40px; font-size: 1.1em;">Return to Student Dashboard →</a>
            <?php else: ?>
            <p style="margin-bottom: 16px; font-size: 1.1em;">Want to track your skills and save these ML recommendations to a live profile?</p>
            <a href="register.php" class="btn-primary" style="padding: 16px 40px; font-size: 1.1em;">Create Free Student Account →</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>