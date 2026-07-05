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
$saved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $interests = $_POST['interests'] ?? [];
    if (is_array($interests) && count($interests) > 0) {
        $profile_str = implode(' & ', $interests);
    } else {
        $profile_str = 'Investigative & Realistic';
    }

    $user['interest_profile'] = $profile_str;
    $_SESSION['user'] = save_user_account($user);
    $saved = true;
}

$current_interests = $user['interest_profile'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career Interest Survey - CareerAI</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .interest-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin: 24px 0;
        }
        .interest-tile {
            background: white;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 24px;
            cursor: pointer;
            transition: all 0.25s;
            position: relative;
        }
        .interest-tile:hover {
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }
        .interest-tile input[type="checkbox"] {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 22px;
            height: 22px;
            accent-color: var(--primary);
        }
        .icon { font-size: 36px; margin-bottom: 12px; }
        .ai-banner {
            background: linear-gradient(135deg, #fffaf5, #ffffff);
            border: 2px solid #ff6b00;
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
            <a href="skill_assessment.php">Skill Assessment</a>
            <a href="interest_survey.php" class="active">Interest Survey</a>
            <a href="industry_dashboard.php">Industry Dashboard</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php" class="btn-secondary" style="padding: 6px 16px;">Logout</a>
        </div>
    </nav>

    <div class="hero" style="padding: 50px 20px 60px;">
        <span class="badge badge-orange" style="margin-bottom: 12px; background: rgba(255,255,255,0.15); color: #fff;">📊 RIASEC Psychological Calibration</span>
        <h1 style="font-size: 2.8em;">Student Career Interest Survey</h1>
        <p>Fill out your passions below. When you click Get Recommendation, our AI decides the exact job paths for you</p>
    </div>

    <div class="container card-elevated" style="margin-top: 20px;">
        <?php if ($saved): ?>
        <div class="ai-banner">
            <div>
                <span class="badge badge-emerald" style="margin-bottom: 8px;">✔ Survey Successfully Logged</span>
                <h2 style="font-size: 1.8em; margin: 4px 0;">Vector Updated: <span style="color: var(--primary);"><?php echo htmlspecialchars($user['interest_profile']); ?></span></h2>
                <p style="color: var(--text-muted); margin: 0; max-width: 540px;">
                    Your survey answers are stored! Let our AI classification model decide which careers in our dynamic database fit your unique profile vector.
                </p>
            </div>
            <div>
                <a href="process.php?auto=1" class="btn-primary" style="padding: 16px 36px; font-size: 1.1em; text-decoration: none; display: inline-block; box-shadow: 0 8px 20px rgba(255,107,0,0.25);">
                    ⚡ Let AI Decide My Jobs →
                </a>
            </div>
        </div>
        <?php elseif (!empty($current_interests)): ?>
        <div class="card" style="margin-bottom: 24px; background: #f8fafc; border-left: 4px solid var(--accent-indigo); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <p style="margin: 0; color: var(--text-main);">Current Saved Interest Profile: <strong><?php echo htmlspecialchars($current_interests); ?></strong></p>
            </div>
            <a href="process.php?auto=1" class="btn-secondary" style="border-color: var(--primary); color: var(--primary);">
                ⚡ Run AI Recommendation Now →
            </a>
        </div>
        <?php endif; ?>

        <div class="card">
            <div style="text-align: center; max-width: 650px; margin: 0 auto 30px;">
                <h2 style="margin-bottom: 10px;">Select All Technical Domains That Inspire You</h2>
                <p style="color: var(--text-muted);">Check the interest tiles that align with your work preferences. No options are pre-selected so you can make your own authentic choice.</p>
            </div>

            <form action="interest_survey.php" method="POST">
                <div class="interest-grid">
                    <label class="interest-tile">
                        <input type="checkbox" name="interests[]" value="Investigative" <?php echo str_contains($current_interests, 'Investigative') ? 'checked' : ''; ?>>
                        <div class="icon">🔬</div>
                        <h3>Investigative & AI Research</h3>
                        <p style="font-size: 0.9em; margin-top: 8px;">Developing neural networks, analyzing deep datasets, complex algorithm design, and mathematical modeling.</p>
                    </label>

                    <label class="interest-tile">
                        <input type="checkbox" name="interests[]" value="Realistic" <?php echo str_contains($current_interests, 'Realistic') ? 'checked' : ''; ?>>
                        <div class="icon">☁️</div>
                        <h3>Realistic & Cloud Systems</h3>
                        <p style="font-size: 0.9em; margin-top: 8px;">Architecting cloud server infrastructure, cybersecurity defense, DevOps automation, and distributed networks.</p>
                    </label>

                    <label class="interest-tile">
                        <input type="checkbox" name="interests[]" value="Artistic" <?php echo str_contains($current_interests, 'Artistic') ? 'checked' : ''; ?>>
                        <div class="icon">🎨</div>
                        <h3>Artistic & UI/UX Design</h3>
                        <p style="font-size: 0.9em; margin-top: 8px;">Designing beautiful interactive user experiences, prototyping design systems, and creative digital media.</p>
                    </label>

                    <label class="interest-tile">
                        <input type="checkbox" name="interests[]" value="Enterprising" <?php echo str_contains($current_interests, 'Enterprising') ? 'checked' : ''; ?>>
                        <div class="icon">🚀</div>
                        <h3>Enterprising & Leadership</h3>
                        <p style="font-size: 0.9em; margin-top: 8px;">Leading tech product roadmaps, coordinating agile engineering teams, and driving strategic business growth.</p>
                    </label>

                    <label class="interest-tile">
                        <input type="checkbox" name="interests[]" value="Conventional" <?php echo str_contains($current_interests, 'Conventional') ? 'checked' : ''; ?>>
                        <div class="icon">📊</div>
                        <h3>Conventional & FinTech Data</h3>
                        <p style="font-size: 0.9em; margin-top: 8px;">Structured financial modeling, quantitative blockchain analytics, and enterprise data compliance.</p>
                    </label>

                    <label class="interest-tile">
                        <input type="checkbox" name="interests[]" value="Social" <?php echo str_contains($current_interests, 'Social') ? 'checked' : ''; ?>>
                        <div class="icon">🤝</div>
                        <h3>Social & Human-Centered Tech</h3>
                        <p style="font-size: 0.9em; margin-top: 8px;">Mentoring engineering talent, building accessible edtech tools, and improving community wellness via software.</p>
                    </label>
                </div>

                <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; margin-top: 36px; padding-top: 24px; border-top: 1px solid var(--border-color);">
                    <button type="submit" class="btn-secondary" style="padding: 16px 36px; font-size: 1.05em;">💾 Save Survey Vector</button>
                    <button type="submit" formaction="interest_survey.php?predict=1" class="btn-primary" style="padding: 16px 42px; font-size: 1.05em;">✨ Save Survey & Let AI Decide Jobs →</button>
                </div>
            </form>
        </div>
    </div>
    <?php
    if (isset($_GET['predict']) && $_GET['predict'] == '1' && $saved) {
        echo "<script>window.location.href = 'process.php?auto=1';</script>";
    }
    ?>
</body>
</html>
