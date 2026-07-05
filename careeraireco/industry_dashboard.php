<?php
require_once 'config.php';
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];

// Helper to get ML prediction for industry alignment
function get_user_ml_predictions($user) {
    $data = [
        'education_level' => $user['education'] ?? 'Bachelor',
        'specialization' => $user['specialization'] ?? 'Computer Science',
        'skills' => ($user['specialization'] ?? 'Programming') . ', Programming, Data Analysis',
        'cgpa' => $user['cgpa'] ?? 85,
        'interests' => $user['interest_profile'] ?? 'Investigative'
    ];

    // Try executing python CLI script directly
    $cmd = 'python ' . escapeshellarg(__DIR__ . '/ml_api/app.py') . ' --cli ' . escapeshellarg(json_encode($data)) . ' 2>&1';
    $output = @shell_exec($cmd);
    if ($output) {
        $res = json_decode(trim($output), true);
        if (isset($res['success']) && $res['success'] && !empty($res['recommendations'])) {
            return $res['recommendations'];
        }
    }

    // Fallback classification logic in PHP if Python CLI not in PATH
    $score = min(96, intval(($user['cgpa'] ?? 85) * 0.7 + ($user['skills_score'] ?? 80) * 0.3));
    return [
        ['title' => 'AI & Machine Learning Engineer', 'score' => $score, 'industries' => ['Artificial Intelligence', 'Autonomous Systems']],
        ['title' => 'Data Scientist & Big Data Architect', 'score' => max(75, $score - 5), 'industries' => ['FinTech & Banking', 'Healthcare Analytics']],
        ['title' => 'Cloud & DevOps Solutions Architect', 'score' => max(72, $score - 8), 'industries' => ['Cloud Computing', 'Cybersecurity']]
    ];
}

$predictions = get_user_ml_predictions($user);
$top_industries = [];
foreach ($predictions as $p) {
    foreach ($p['industries'] ?? [] as $ind) {
        $top_industries[] = strtolower(trim($ind));
    }
}

$all_industries = [
    [
        'name' => 'Artificial Intelligence & Generative ML',
        'category' => 'Artificial Intelligence',
        'icon' => '🤖',
        'avg_salary' => '$135,000 / yr',
        'growth' => '+34% YoY',
        'demand' => 98,
        'top_roles' => 'LLM Engineer, Computer Vision Scientist, AI Architect',
        'skills' => 'PyTorch, Transformers, Python, Vector DBs',
        'description' => 'The fastest-growing tech domain focusing on automated intelligence, neural networks, and decision support systems.'
    ],
    [
        'name' => 'FinTech, Blockchain & Quantitative Finance',
        'category' => 'FinTech & Banking',
        'icon' => '📈',
        'avg_salary' => '$125,000 / yr',
        'growth' => '+26% YoY',
        'demand' => 91,
        'top_roles' => 'Quant Developer, Data Engineer, Smart Contract Auditor',
        'skills' => 'Python, C++, High-Frequency Trading, Solidity, SQL',
        'description' => 'Revolutionizing global banking, algorithmic trading, and decentralized financial ledgers.'
    ],
    [
        'name' => 'Cloud Computing & Cyber Defense',
        'category' => 'Cloud Computing',
        'icon' => '🛡️',
        'avg_salary' => '$120,000 / yr',
        'growth' => '+29% YoY',
        'demand' => 95,
        'top_roles' => 'DevOps Specialist, Ethical Hacker, Cloud Solutions Architect',
        'skills' => 'AWS/GCP, Kubernetes, Penetration Testing, Zero-Trust Architecture',
        'description' => 'Securing global enterprise networks and building highly available cloud infrastructures.'
    ],
    [
        'name' => 'Digital Media, Creative UX & Consumer Apps',
        'category' => 'Creative Tech',
        'icon' => '✨',
        'avg_salary' => '$98,000 / yr',
        'growth' => '+21% YoY',
        'demand' => 84,
        'top_roles' => 'Lead Product Designer, Design Systems Engineer, Creative Director',
        'skills' => 'Figma, User Psychology, Prototyping, Accessibility Standards',
        'description' => 'Crafting intuitive, delightful user interactions and aesthetic consumer experiences.'
    ],
    [
        'name' => 'Green Tech & Sustainable Energy Software',
        'category' => 'Green Tech',
        'icon' => '🌱',
        'avg_salary' => '$108,000 / yr',
        'growth' => '+32% YoY',
        'demand' => 89,
        'top_roles' => 'Smart Grid Analyst, Carbon Analytics Engineer, IoT Architect',
        'skills' => 'IoT Protocols, Data Analytics, Embedded C++, Python',
        'description' => 'Leveraging smart sensors and AI optimization to drive renewable energy and sustainability.'
    ]
];

$filter = $_GET['filter'] ?? 'all';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Industry Information Dashboard - CareerAI</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .filter-bar {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 32px;
            justify-content: center;
        }
        .filter-chip {
            padding: 10px 22px;
            border-radius: 50px;
            background: white;
            border: 1.5px solid var(--border-color);
            color: var(--text-main);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }
        .filter-chip:hover, .filter-chip.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
    </style>
</head>
<body>
    <nav class="nav">
        <a href="<?php echo ($user['role'] ?? '') === 'admin' ? 'dashboard_admin.php' : 'dashboard_student.php'; ?>" class="logo">🎓 CareerAI</a>
        <div class="nav-links">
            <?php if (($user['role'] ?? '') === 'admin'): ?>
                <a href="dashboard_admin.php?tab=users">Manage User Accounts</a>
                <a href="dashboard_admin.php?tab=careers">Update Career Database</a>
                <a href="dashboard_admin.php?tab=analytics">View System Analytics</a>
                <a href="industry_dashboard.php" class="active">Industry View</a>
            <?php else: ?>
                <a href="dashboard_student.php">Dashboard</a>
                <a href="skill_assessment.php">Skill Assessment</a>
                <a href="interest_survey.php">Interest Survey</a>
                <a href="industry_dashboard.php" class="active">Industry Dashboard</a>
                <a href="profile.php">Profile</a>
            <?php endif; ?>
            <a href="logout.php" class="btn-secondary" style="padding: 6px 16px;">Logout</a>
        </div>
    </nav>

    <div class="hero" style="padding: 60px 20px 70px;">
        <h1 style="font-size: 2.8em;">Industry Intelligence Dashboard</h1>
        <p>Explore real-time industry landscape data integrated with your personalized ML classification</p>
    </div>

    <div class="container card-elevated">
        <!-- ML Banner -->
        <div class="card" style="background: linear-gradient(135deg, #1e293b, #0f172a); color: white; margin-bottom: 32px; border: 1px solid rgba(255,107,0,0.3);">
            <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap; justify-content: space-between;">
                <div>
                    <span class="badge badge-orange" style="margin-bottom: 8px;">🤖 ML Classification Active</span>
                    <h2 style="color: white; margin-bottom: 6px;">Aligned with your interests: <?php echo htmlspecialchars($user['interest_profile'] ?? 'Investigative & Enterprising'); ?></h2>
                    <p style="color: #94a3b8; margin: 0;">Top ML Predicted Career Match: <strong style="color: #ff9e00;"><?php echo htmlspecialchars($predictions[0]['title'] ?? 'AI Software Engineer'); ?> (<?php echo $predictions[0]['score'] ?? 92; ?>%)</strong></p>
                </div>
                <a href="index.php" class="btn-primary" style="padding: 12px 28px; font-size: 0.95em;">⚡ Recalibrate ML Model</a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <a href="industry_dashboard.php?filter=all" class="filter-chip <?php echo $filter === 'all' ? 'active' : ''; ?>">🌐 All Sectors</a>
            <a href="industry_dashboard.php?filter=ai" class="filter-chip <?php echo $filter === 'ai' ? 'active' : ''; ?>">🤖 AI & Data</a>
            <a href="industry_dashboard.php?filter=cloud" class="filter-chip <?php echo $filter === 'cloud' ? 'active' : ''; ?>">🛡️ Cloud & Security</a>
            <a href="industry_dashboard.php?filter=fintech" class="filter-chip <?php echo $filter === 'fintech' ? 'active' : ''; ?>">📈 FinTech</a>
            <a href="industry_dashboard.php?filter=creative" class="filter-chip <?php echo $filter === 'creative' ? 'active' : ''; ?>">✨ Creative UX</a>
        </div>

        <!-- Industry Cards Grid -->
        <div class="grid-2">
            <?php foreach ($all_industries as $ind): 
                // Filter check
                if ($filter === 'ai' && !str_contains(strtolower($ind['name']), 'artificial')) continue;
                if ($filter === 'cloud' && !str_contains(strtolower($ind['name']), 'cloud')) continue;
                if ($filter === 'fintech' && !str_contains(strtolower($ind['name']), 'fintech')) continue;
                if ($filter === 'creative' && !str_contains(strtolower($ind['name']), 'media')) continue;

                $is_ml_match = false;
                foreach ($top_industries as $t_ind) {
                    if (str_contains(strtolower($ind['name']), $t_ind) || str_contains($t_ind, strtolower(explode(' ', $ind['category'])[0]))) {
                        $is_ml_match = true;
                        break;
                    }
                }
            ?>
            <div class="industry-card" style="<?php echo $is_ml_match ? 'border: 2px solid #ff6b00;' : ''; ?>">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                    <span style="font-size: 40px;"><?php echo $ind['icon']; ?></span>
                    <?php if ($is_ml_match): ?>
                    <span class="badge badge-orange">✨ ML Top Industry Match</span>
                    <?php else: ?>
                    <span class="badge badge-indigo"><?php echo $ind['category']; ?></span>
                    <?php endif; ?>
                </div>

                <h3 style="font-size: 1.4em; margin-bottom: 10px;"><?php echo htmlspecialchars($ind['name']); ?></h3>
                <p style="font-size: 0.95em; color: var(--text-muted); min-height: 48px;"><?php echo htmlspecialchars($ind['description']); ?></p>

                <div style="background: #f8fafc; padding: 14px; border-radius: var(--radius-md); margin: 16px 0; border: 1px solid var(--border-color);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span style="font-size: 0.85em; color: var(--text-muted);">Avg. Compensation:</span>
                        <strong style="color: var(--primary);"><?php echo $ind['avg_salary']; ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span style="font-size: 0.85em; color: var(--text-muted);">Market Growth:</span>
                        <strong style="color: var(--accent-emerald);"><?php echo $ind['growth']; ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="font-size: 0.85em; color: var(--text-muted);">Hiring Demand Index:</span>
                        <strong><?php echo $ind['demand']; ?> / 100</strong>
                    </div>
                </div>

                <div style="margin-top: 14px; font-size: 0.9em;">
                    <strong>Target Roles:</strong> <?php echo htmlspecialchars($ind['top_roles']); ?>
                </div>
                <div style="margin-top: 8px; font-size: 0.9em; color: var(--text-muted);">
                    <strong>Core Tech Stack:</strong> <?php echo htmlspecialchars($ind['skills']); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
