<?php
require_once 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$result_saved = false;
$score = $user['skills_score'] ?? 80;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $q1 = intval($_POST['q1'] ?? 3);
    $q2 = intval($_POST['q2'] ?? 3);
    $q3 = intval($_POST['q3'] ?? 3);
    $q4 = intval($_POST['q4'] ?? 3);
    $q5 = intval($_POST['q5'] ?? 3);

    $total = $q1 + $q2 + $q3 + $q4 + $q5;
    $score = round(($total / 25) * 100);

    $user['skills_score'] = $score;
    $_SESSION['user'] = save_user_account($user);
    $result_saved = true;

    if (isset($_GET['predict']) && $_GET['predict'] == '1') {
        header('Location: process.php?auto=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skill Assessment - CareerAI</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .ai-banner {
            background: linear-gradient(135deg, #f0fdf4, #ffffff);
            border: 2px solid #10b981;
            border-radius: var(--radius-lg);
            padding: 28px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
    </style>
</head>
<body>
    <nav class="nav">
        <a href="dashboard_student.php" class="logo">🎓 CareerAI</a>
        <div class="nav-links">
            <a href="dashboard_student.php">Dashboard</a>
            <a href="skill_assessment.php" class="active">Skill Assessment</a>
            <a href="interest_survey.php">Interest Survey</a>
            <a href="industry_dashboard.php">Industry Dashboard</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php" class="btn-secondary" style="padding: 6px 16px;">Logout</a>
        </div>
    </nav>

    <div class="hero" style="padding: 50px 20px 60px;">
        <span class="badge badge-orange" style="margin-bottom: 12px; background: rgba(255,255,255,0.15); color: #fff;">⚙️ Technical Evaluation Engine</span>
        <h1 style="font-size: 2.8em;">Core Engineering Assessment</h1>
        <p>Complete this evaluation to benchmark your technical mastery. Our AI will use your score to decide suitable jobs</p>
    </div>

    <div class="container-sm card-elevated" style="margin-top: 20px;">
        <?php if ($result_saved): ?>
        <div class="ai-banner">
            <div>
                <span class="badge badge-emerald" style="margin-bottom: 8px;">✔ Evaluation Logged</span>
                <h2 style="font-size: 1.8em; margin: 4px 0;">Competency Index: <span style="color: #10b981;"><?php echo $score; ?>%</span></h2>
                <p style="color: var(--text-muted); margin: 0; max-width: 520px;">
                    Your score has been updated in our system. Let the AI classification model evaluate your skill index against all career roles.
                </p>
            </div>
            <div>
                <a href="process.php?auto=1" class="btn-primary" style="background: var(--secondary-gradient); padding: 16px 36px; font-size: 1.1em; text-decoration: none; display: inline-block; box-shadow: 0 8px 20px rgba(99,102,241,0.25);">
                    ⚡ Let AI Decide My Jobs →
                </a>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h3>Current Competency Index</h3>
                <span class="badge badge-indigo" style="font-size: 1em; padding: 8px 16px;"><?php echo $score; ?>% Mastery</span>
            </div>
            <div class="progress-bar" style="height: 14px; margin-bottom: 30px; background: #e2e8f0; border-radius: 50px; overflow: hidden;">
                <div class="progress-fill" style="width: <?php echo $score; ?>%; background: var(--primary-gradient); height: 100%;"></div>
            </div>

            <form action="skill_assessment.php" method="POST">
                <div class="form-group" style="margin-bottom: 28px;">
                    <label style="font-size: 1.1em; margin-bottom: 12px; display: block; font-weight: 600;">1. Programming & Algorithms: How comfortable are you designing scalable software architectures and optimizing time complexity?</label>
                    <label class="quiz-option"><input type="radio" name="q1" value="5" required> Expert: Can implement advanced algorithms and distributed systems effortlessly</label>
                    <label class="quiz-option"><input type="radio" name="q1" value="4"> Proficient: Confident with data structures, OOP, and modern APIs</label>
                    <label class="quiz-option"><input type="radio" name="q1" value="3"> Moderate: Can build working applications and debug standard code</label>
                    <label class="quiz-option"><input type="radio" name="q1" value="2"> Beginner: Familiar with syntax and basic scripting</label>
                </div>

                <div class="form-group" style="margin-bottom: 28px;">
                    <label style="font-size: 1.1em; margin-bottom: 12px; display: block; font-weight: 600;">2. Data Analytics & AI: How confident are you working with statistical modeling, SQL databases, or ML libraries?</label>
                    <label class="quiz-option"><input type="radio" name="q2" value="5" required> Expert: Experienced with PyTorch/TensorFlow, deep learning, and complex SQL queries</label>
                    <label class="quiz-option"><input type="radio" name="q2" value="4"> Proficient: Can analyze datasets, build regression models, and visualize trends</label>
                    <label class="quiz-option"><input type="radio" name="q2" value="3"> Moderate: Comfortable with Excel, basic SQL, and data summaries</label>
                    <label class="quiz-option"><input type="radio" name="q2" value="2"> Novice: Limited exposure to formal data analytics</label>
                </div>

                <div class="form-group" style="margin-bottom: 28px;">
                    <label style="font-size: 1.1em; margin-bottom: 12px; display: block; font-weight: 600;">3. Cloud Infrastructure & DevOps: How familiar are you with cloud deployments (AWS/GCP/Azure) and CI/CD pipelines?</label>
                    <label class="quiz-option"><input type="radio" name="q3" value="5" required> Expert: Certified cloud architect or daily Kubernetes/Docker container user</label>
                    <label class="quiz-option"><input type="radio" name="q3" value="4"> Proficient: Have deployed production servers and set up automated workflows</label>
                    <label class="quiz-option"><input type="radio" name="q3" value="3"> Moderate: Can host web applications on standard hosting or cloud VMs</label>
                    <label class="quiz-option"><input type="radio" name="q3" value="2"> Beginner: Mostly work on local development environments</label>
                </div>

                <div class="form-group" style="margin-bottom: 28px;">
                    <label style="font-size: 1.1em; margin-bottom: 12px; display: block; font-weight: 600;">4. UI/UX Product Intuition: How well do you understand human-centered design, prototyping, and accessibility?</label>
                    <label class="quiz-option"><input type="radio" name="q4" value="5" required> Expert: Highly skilled in Figma design systems and user research methodologies</label>
                    <label class="quiz-option"><input type="radio" name="q4" value="4"> Proficient: Design clean interfaces that follow responsive web standards</label>
                    <label class="quiz-option"><input type="radio" name="q4" value="3"> Moderate: Focus mostly on function, but appreciate good design</label>
                    <label class="quiz-option"><input type="radio" name="q4" value="2"> Beginner: Rely on UI templates and default browser styles</label>
                </div>

                <div class="form-group" style="margin-bottom: 28px;">
                    <label style="font-size: 1.1em; margin-bottom: 12px; display: block; font-weight: 600;">5. Leadership & Agile Collaboration: How experienced are you in leading engineering sprints and stakeholder communication?</label>
                    <label class="quiz-option"><input type="radio" name="q5" value="5" required> Expert: Regularly mentor engineers and coordinate multi-team milestones</label>
                    <label class="quiz-option"><input type="radio" name="q5" value="4"> Proficient: Active participant in agile ceremonies and code reviews</label>
                    <label class="quiz-option"><input type="radio" name="q5" value="3"> Moderate: Work effectively in small team settings</label>
                    <label class="quiz-option"><input type="radio" name="q5" value="2"> Individual Contributor: Prefer working independently on tasks</label>
                </div>

                <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; margin-top: 36px; padding-top: 24px; border-top: 1px solid var(--border-color);">
                    <button type="submit" class="btn-secondary" style="padding: 16px 36px; font-size: 1.05em;">💾 Save Competency Score</button>
                    <button type="submit" formaction="skill_assessment.php?predict=1" class="btn-primary" style="padding: 16px 42px; font-size: 1.05em;">✨ Save Assessment & Let AI Decide Jobs →</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>