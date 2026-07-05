<?php
require_once 'config.php';
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user['name'] = trim($_POST['name'] ?? $user['name']);
    $user['education'] = $_POST['education'] ?? $user['education'] ?? 'Bachelor';
    $user['specialization'] = $_POST['specialization'] ?? $user['specialization'] ?? 'Computer Science';
    $user['cgpa'] = floatval($_POST['cgpa'] ?? $user['cgpa'] ?? 85);
    $user['bio'] = trim($_POST['bio'] ?? '');
    $user['portfolio'] = trim($_POST['portfolio'] ?? '');
    
    $_SESSION['user'] = save_user_account($user);
    $message = 'Profile updated successfully!';
}

// Calculate Profile Completeness
$fields = ['name', 'email', 'education', 'specialization', 'cgpa', 'bio', 'portfolio', 'skills_score', 'interest_profile'];
$filled = 0;
foreach ($fields as $f) {
    if (!empty($user[$f])) $filled++;
}
$completeness = round(($filled / count($fields)) * 100);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - CareerAI</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Top Navigation -->
    <nav class="nav">
        <a href="dashboard_student.php" class="logo">🎓 CareerAI</a>
        <div class="nav-links">
            <a href="dashboard_student.php">Dashboard</a>
            <a href="skill_assessment.php">Skill Assessment</a>
            <a href="interest_survey.php">Interest Survey</a>
            <a href="industry_dashboard.php">Industry Dashboard</a>
            <a href="profile.php" class="active">Profile</a>
            <a href="logout.php" class="btn-secondary" style="padding: 6px 16px;">Logout</a>
        </div>
    </nav>

    <div class="hero" style="padding: 60px 20px 70px;">
        <h1 style="font-size: 2.8em;">Student Profile Management</h1>
        <p>Keep your academic records, skills, and portfolio up to date for precise AI recommendations</p>
    </div>

    <div class="container-sm card-elevated">
        <div class="card">
            <?php if ($message): ?>
            <div class="alert alert-success">✅ <?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div style="margin-bottom: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-weight: 700;">Profile Completeness</span>
                    <span class="badge badge-orange"><?php echo $completeness; ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $completeness; ?>%;"></div>
                </div>
            </div>

            <form action="profile.php" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled style="background: #f1f5f9; cursor: not-allowed;">
                    </div>
                    <div class="form-group">
                        <label>Education Level</label>
                        <select name="education" required>
                            <option value="High School" <?php echo ($user['education'] ?? '') === 'High School' ? 'selected' : ''; ?>>High School</option>
                            <option value="Bachelor" <?php echo ($user['education'] ?? '') === 'Bachelor' ? 'selected' : ''; ?>>Bachelor's Degree</option>
                            <option value="Master" <?php echo ($user['education'] ?? '') === 'Master' ? 'selected' : ''; ?>>Master's Degree</option>
                            <option value="PhD" <?php echo ($user['education'] ?? '') === 'PhD' ? 'selected' : ''; ?>>PhD</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Specialization</label>
                        <select name="specialization" required>
                            <option value="Computer Science" <?php echo ($user['specialization'] ?? '') === 'Computer Science' ? 'selected' : ''; ?>>Computer Science</option>
                            <option value="Data Science" <?php echo ($user['specialization'] ?? '') === 'Data Science' ? 'selected' : ''; ?>>Data Science & AI</option>
                            <option value="Business" <?php echo ($user['specialization'] ?? '') === 'Business' ? 'selected' : ''; ?>>Business & Management</option>
                            <option value="Engineering" <?php echo ($user['specialization'] ?? '') === 'Engineering' ? 'selected' : ''; ?>>Engineering</option>
                            <option value="Cyber Security" <?php echo ($user['specialization'] ?? '') === 'Cyber Security' ? 'selected' : ''; ?>>Cyber Security</option>
                            <option value="Arts" <?php echo ($user['specialization'] ?? '') === 'Arts' ? 'selected' : ''; ?>>Design & Arts</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Current CGPA / Percentage (60 - 100)</label>
                    <input type="number" step="0.1" min="60" max="100" name="cgpa" value="<?php echo htmlspecialchars($user['cgpa'] ?? 85); ?>" required>
                </div>

                <div class="form-group">
                    <label>Portfolio / GitHub URL</label>
                    <input type="text" name="portfolio" placeholder="https://github.com/yourusername" value="<?php echo htmlspecialchars($user['portfolio'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Professional Summary & Target Aspirations</label>
                    <textarea name="bio" rows="4" placeholder="Describe your passion for software, AI, or industry aspirations..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="submit-btn" style="width: 100%;">💾 Save Profile Settings</button>
            </form>
        </div>
    </div>
</body>
</html>
