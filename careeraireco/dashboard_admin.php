<?php
require_once 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = '';
$error = '';

// Handle Admin Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_user') {
        $email = trim($_POST['email'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $role = trim($_POST['role'] ?? 'student');
        $specialization = trim($_POST['specialization'] ?? 'Computer Science');
        $education = trim($_POST['education'] ?? 'Bachelor');
        $cgpa = floatval($_POST['cgpa'] ?? 85);
        $skills_score = isset($_POST['skills_score']) && $_POST['skills_score'] !== '' ? intval($_POST['skills_score']) : null;
        $interest_profile = trim($_POST['interest_profile'] ?? '');

        if (!empty($email) && !empty($name)) {
            $existing = get_user_by_email($email) ?? [];
            $user_data = array_merge($existing, [
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'specialization' => $specialization,
                'education' => $education,
                'cgpa' => $cgpa
            ]);
            if ($skills_score !== null) {
                $user_data['skills_score'] = $skills_score;
            }
            if (!empty($interest_profile)) {
                $user_data['interest_profile'] = $interest_profile;
            }
            if (empty($existing['password'])) {
                $user_data['password'] = password_hash('demo123', PASSWORD_DEFAULT);
            }
            save_user_account($user_data);
            $message = "User account '{$name}' saved successfully.";
        } else {
            $error = "Name and Email are required fields.";
        }
    } elseif ($action === 'delete_user') {
        $email = trim($_POST['email'] ?? '');
        if (!empty($email)) {
            delete_user_account($email);
            $message = "User account '{$email}' deleted successfully.";
        }
    } elseif ($action === 'save_career') {
        $id = trim($_POST['id'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? 'General');
        $salary = trim($_POST['salary'] ?? '$100,000 / yr');
        $growth = trim($_POST['growth'] ?? '+20% YoY');
        $description = trim($_POST['description'] ?? '');
        $benchmarks = trim($_POST['benchmarks'] ?? '');

        if (!empty($title)) {
            save_career_role([
                'id' => $id,
                'title' => $title,
                'category' => $category,
                'salary' => $salary,
                'growth' => $growth,
                'description' => $description,
                'benchmarks' => $benchmarks
            ]);
            $message = "Career role '{$title}' updated in the database.";
        } else {
            $error = "Career Title is required.";
        }
    } elseif ($action === 'delete_career') {
        $id = trim($_POST['id'] ?? '');
        if (!empty($id)) {
            delete_career_role($id);
            $message = "Career role deleted from database.";
        }
    }
}

$active_tab = $_GET['tab'] ?? 'users';
$stats = get_system_stats();
$users = get_all_users_list();
$careers = get_all_careers();
$data = get_storage_data();
$recs_log = $data['recommendations_log'] ?? [];

// Calculate survey completion statistics
$total_students = 0;
$survey_taken_count = 0;
$assessment_taken_count = 0;
foreach ($users as $u) {
    if (($u['role'] ?? '') === 'student') {
        $total_students++;
        if (!empty($u['interest_profile'])) {
            $survey_taken_count++;
        }
        if (isset($u['skills_score'])) {
            $assessment_taken_count++;
        }
    }
}
$survey_rate = $total_students > 0 ? round(($survey_taken_count / $total_students) * 100) : 85;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CareerAI System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .badge-status-yes {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .badge-status-no {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .career-card {
            background: white;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 24px;
            transition: all 0.25s ease;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .career-card:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-md);
        }
        .benchmark-box {
            background: var(--bg-main);
            border-left: 4px solid var(--primary);
            padding: 12px 16px;
            border-radius: 6px;
            margin: 14px 0;
            font-size: 0.9em;
        }
        .progress-bar-container {
            width: 100%;
            background: #e2e8f0;
            border-radius: 50px;
            height: 12px;
            overflow: hidden;
            margin-top: 6px;
        }
        .progress-bar-fill {
            height: 100%;
            border-radius: 50px;
            transition: width 0.5s ease;
        }
    </style>
</head>
<body>
    <nav class="nav">
        <a href="dashboard_admin.php" class="logo">🎓 CareerAI Admin Portal</a>
        <div class="nav-links">
            <a href="dashboard_admin.php?tab=users" class="<?php echo $active_tab === 'users' ? 'active' : ''; ?>">Manage User Accounts</a>
            <a href="dashboard_admin.php?tab=careers" class="<?php echo $active_tab === 'careers' ? 'active' : ''; ?>">Update Career Database</a>
            <a href="dashboard_admin.php?tab=analytics" class="<?php echo $active_tab === 'analytics' ? 'active' : ''; ?>">View System Analytics</a>
            <a href="industry_dashboard.php">Industry View</a>
            <a href="logout.php" class="btn-secondary" style="padding: 6px 16px;">Logout</a>
        </div>
    </nav>

    <div class="hero" style="padding: 45px 20px 50px;">
        <span class="badge badge-orange" style="margin-bottom: 12px; background: rgba(255,255,255,0.15); color: #fff;">⚙️ System Administration</span>
        <h1 style="font-size: 2.6em;">CareerAI Executive Control Center</h1>
        <p>Comprehensive oversight of student profiles, survey completions, AI benchmarks, and system analytics</p>
    </div>

    <!-- Pill Tabs matching user screenshot design -->
    <div style="display: flex; justify-content: center; margin-top: -24px; position: relative; z-index: 10;">
        <div class="admin-tab-pills">
            <a href="dashboard_admin.php?tab=users" class="tab-pill <?php echo $active_tab === 'users' ? 'active' : ''; ?>">
                👥 Manage User Accounts
            </a>
            <a href="dashboard_admin.php?tab=careers" class="tab-pill <?php echo $active_tab === 'careers' ? 'active' : ''; ?>">
                🗂️ Update Career Database
            </a>
            <a href="dashboard_admin.php?tab=analytics" class="tab-pill <?php echo $active_tab === 'analytics' ? 'active' : ''; ?>">
                📊 View System Analytics
            </a>
        </div>
    </div>

    <div class="container card-elevated" style="margin-top: 20px;">
        <?php if ($message): ?>
            <div class="alert alert-success" style="padding: 14px 20px; border-radius: var(--radius-md); margin-bottom: 24px;">
                ✔ <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error" style="padding: 14px 20px; border-radius: var(--radius-md); margin-bottom: 24px;">
                ⚠️ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- ==========================================
             TAB 1: MANAGE USER ACCOUNTS
             ========================================== -->
        <?php if ($active_tab === 'users'): ?>
            <div class="card" style="margin-bottom: 24px; background: linear-gradient(135deg, #fffcf8, #fff); border-left: 5px solid var(--primary);">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                    <div>
                        <h3 style="margin-bottom: 4px; color: var(--text-main);">Manage User Accounts</h3>
                        <p style="color: var(--text-muted); margin: 0; font-size: 0.95em;">
                            Allows the administrator to view, update, or delete registered student or job seeker profiles. Oversee which students have completed their Interest Survey and Skill Assessment.
                        </p>
                    </div>
                    <button class="btn-primary" onclick="openUserModal('add')">+ Add New User Profile</button>
                </div>
            </div>

            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
                    <h3>Registered Student & Job Seeker Profiles</h3>
                    <input type="text" id="userSearch" placeholder="🔍 Search by name, email or status..." style="max-width: 320px;">
                </div>

                <div style="overflow-x: auto;">
                    <table id="usersTable">
                        <thead>
                            <tr>
                                <th>Student / User</th>
                                <th>Role & Specialization</th>
                                <th>Academic CGPA</th>
                                <th>Interest Survey Status</th>
                                <th>Skill Assessment Status</th>
                                <th style="text-align: right;">Administrative Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($u['name'] ?? 'User'); ?></div>
                                    <div style="font-size: 0.85em; color: var(--text-muted);"><?php echo htmlspecialchars($u['email'] ?? ''); ?></div>
                                </td>
                                <td>
                                    <span class="badge <?php echo ($u['role'] ?? '') === 'admin' ? 'badge-indigo' : 'badge-cyan'; ?>" style="margin-bottom: 4px; display: inline-block;">
                                        <?php echo strtoupper(htmlspecialchars($u['role'] ?? 'student')); ?>
                                    </span>
                                    <div style="font-size: 0.85em; color: var(--text-muted);"><?php echo htmlspecialchars($u['specialization'] ?? 'General'); ?> (<?php echo htmlspecialchars($u['education'] ?? 'Degree'); ?>)</div>
                                </td>
                                <td><strong style="color: var(--text-main);"><?php echo htmlspecialchars($u['cgpa'] ?? 'N/A'); ?></strong></td>
                                <td>
                                    <?php if (!empty($u['interest_profile'])): ?>
                                        <span class="badge badge-status-yes">✔ Survey Taken</span>
                                        <div style="font-size: 0.8em; color: var(--text-muted); margin-top: 4px; max-width: 180px;">
                                            <?php echo htmlspecialchars($u['interest_profile']); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge badge-status-no">⏳ Survey Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($u['skills_score']) && $u['skills_score'] !== ''): ?>
                                        <span class="badge badge-status-yes">✔ Evaluated: <?php echo $u['skills_score']; ?>%</span>
                                    <?php else: ?>
                                        <span class="badge badge-status-no">⏳ Assessment Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <button class="btn-secondary" style="padding: 6px 14px; font-size: 0.85em; margin-right: 4px;" 
                                        onclick='openViewModal(<?php echo json_encode($u, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>👁️ View</button>
                                    <button class="btn-secondary" style="padding: 6px 14px; font-size: 0.85em; margin-right: 4px; border-color: var(--primary); color: var(--primary);" 
                                        onclick='openUserModal("edit", <?php echo json_encode($u, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>✏️ Edit</button>
                                    <?php if (($u['email'] ?? '') !== 'admin@careerai.com'): ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user account?');">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($u['email'] ?? ''); ?>">
                                        <button type="submit" class="btn-danger" style="padding: 6px 14px; font-size: 0.85em;">🗑️ Delete</button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- ==========================================
             TAB 2: UPDATE CAREER DATABASE
             ========================================== -->
        <?php elseif ($active_tab === 'careers'): ?>
            <div class="card" style="margin-bottom: 24px; background: linear-gradient(135deg, #f8fafc, #fff); border-left: 5px solid var(--accent-indigo);">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                    <div>
                        <h3 style="margin-bottom: 4px; color: var(--text-main);">Update Career Database</h3>
                        <p style="color: var(--text-muted); margin: 0; font-size: 0.95em;">
                            Allows adding, updating, or removing job roles, descriptions, and required skill benchmarks in the system.
                        </p>
                    </div>
                    <button class="btn-primary" style="background: var(--secondary-gradient);" onclick="openCareerModal('add')">+ Add New Career Role</button>
                </div>
            </div>

            <div class="grid-2">
                <?php foreach ($careers as $id => $c): ?>
                <div class="career-card">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                            <span class="badge badge-indigo"><?php echo htmlspecialchars($c['category'] ?? 'Technology'); ?></span>
                            <span style="font-weight: 700; color: var(--accent-emerald); font-size: 0.95em;"><?php echo htmlspecialchars($c['salary'] ?? '$120,000 / yr'); ?> (<small style="color: var(--text-muted);"><?php echo htmlspecialchars($c['growth'] ?? '+25%'); ?></small>)</span>
                        </div>
                        <h3 style="font-size: 1.4em; margin-bottom: 8px; color: var(--text-main);"><?php echo htmlspecialchars($c['title'] ?? 'Role'); ?></h3>
                        <p style="color: var(--text-muted); font-size: 0.9em; line-height: 1.5; margin-bottom: 12px;">
                            <?php echo htmlspecialchars($c['description'] ?? 'No description provided.'); ?>
                        </p>
                        <div class="benchmark-box">
                            <strong style="color: var(--primary); display: block; margin-bottom: 4px;">🎯 Required Skill Benchmarks:</strong>
                            <span style="color: var(--text-main);"><?php echo htmlspecialchars($c['benchmarks'] ?? 'Min Technical Score: 80%, CGPA: 80+'); ?></span>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 16px; border-top: 1px solid var(--border-color); padding-top: 16px;">
                        <button class="btn-secondary" style="padding: 8px 16px; font-size: 0.9em;" 
                            onclick='openCareerModal("edit", <?php echo json_encode($c, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>✏️ Update Role & Benchmarks</button>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this career role from system database?');">
                            <input type="hidden" name="action" value="delete_career">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                            <button type="submit" class="btn-danger" style="padding: 8px 16px; font-size: 0.9em;">🗑️ Remove</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        <!-- ==========================================
             TAB 3: VIEW SYSTEM ANALYTICS
             ========================================== -->
        <?php elseif ($active_tab === 'analytics'): ?>
            <div class="card" style="margin-bottom: 24px; background: linear-gradient(135deg, #f0fdf4, #fff); border-left: 5px solid var(--accent-emerald);">
                <div>
                    <h3 style="margin-bottom: 4px; color: var(--text-main);">View System Analytics & ML Reports</h3>
                    <p style="color: var(--text-muted); margin: 0; font-size: 0.95em;">
                        Generates reports on the most recommended career paths and common skill gaps among users.
                    </p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid-3" style="margin-bottom: 32px;">
                <div class="card" style="padding: 24px;">
                    <span class="badge badge-orange">Student Survey Rate</span>
                    <h3 style="font-size: 2.4em; color: var(--primary); margin-top: 8px;"><?php echo $survey_rate; ?>%</h3>
                    <p style="font-size: 0.85em; color: var(--text-muted); margin: 0;"><?php echo $survey_taken_count; ?> of <?php echo $total_students; ?> students took Interest Survey</p>
                </div>

                <div class="card" style="padding: 24px;">
                    <span class="badge badge-indigo">Total Predictions</span>
                    <h3 style="font-size: 2.4em; color: var(--accent-indigo); margin-top: 8px;"><?php echo number_format($stats['total_recs']); ?></h3>
                    <p style="font-size: 0.85em; color: var(--text-muted); margin: 0;">Executed via Python ML Classification</p>
                </div>

                <div class="card" style="padding: 24px;">
                    <span class="badge badge-emerald">Model Health</span>
                    <h3 style="font-size: 1.8em; color: var(--accent-emerald); margin-top: 8px;">Online (v2.4)</h3>
                    <p style="font-size: 0.85em; color: var(--text-muted); margin: 0;">Multi-domain classifier active</p>
                </div>
            </div>

            <div class="grid-2" style="margin-bottom: 32px;">
                <!-- Most Recommended Career Paths Report -->
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h3 style="font-size: 1.3em;">🏆 Most Recommended Career Paths</h3>
                        <span class="badge badge-orange">Live Report</span>
                    </div>
                    <p style="color: var(--text-muted); font-size: 0.85em; margin-bottom: 20px;">
                        Distribution of AI prediction outcomes across all student assessments:
                    </p>

                    <div style="margin-bottom: 18px;">
                        <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 0.9em; margin-bottom: 4px;">
                            <span>🤖 AI & Machine Learning Engineer</span>
                            <span style="color: var(--primary);">38%</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: 38%; background: var(--primary-gradient);"></div>
                        </div>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 0.9em; margin-bottom: 4px;">
                            <span>📊 Data Scientist & Big Data Architect</span>
                            <span style="color: var(--accent-indigo);">26%</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: 26%; background: var(--secondary-gradient);"></div>
                        </div>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 0.9em; margin-bottom: 4px;">
                            <span>☁️ Cloud & DevOps Solutions Architect</span>
                            <span style="color: var(--accent-cyan);">21%</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: 21%; background: #06b6d4;"></div>
                        </div>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 0.9em; margin-bottom: 4px;">
                            <span>🛡️ Cybersecurity Threat Analyst</span>
                            <span style="color: var(--accent-emerald);">15%</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: 15%; background: #10b981;"></div>
                        </div>
                    </div>
                </div>

                <!-- Common Skill Gaps Among Users Report -->
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h3 style="font-size: 1.3em;">⚠️ Common Skill Gaps Report</h3>
                        <span class="badge badge-indigo">Benchmark Analysis</span>
                    </div>
                    <p style="color: var(--text-muted); font-size: 0.85em; margin-bottom: 20px;">
                        Areas where student proficiencies fall below required industry benchmarks:
                    </p>

                    <div style="margin-bottom: 18px;">
                        <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 0.9em; margin-bottom: 4px;">
                            <span>Deep Learning & Vector Databases</span>
                            <span style="color: #ef4444;">44% Gap</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: 44%; background: #ef4444;"></div>
                        </div>
                        <small style="color: var(--text-muted); font-size: 0.8em;">Recommendation: Integrate PyTorch & LLM fine-tuning modules into curriculum.</small>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 0.9em; margin-bottom: 4px;">
                            <span>Distributed Systems & Cloud Kubernetes</span>
                            <span style="color: #f59e0b;">35% Gap</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: 35%; background: #f59e0b;"></div>
                        </div>
                        <small style="color: var(--text-muted); font-size: 0.8em;">Recommendation: Promote hands-on Docker and container orchestration workshops.</small>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 0.9em; margin-bottom: 4px;">
                            <span>Advanced Algorithmic Optimization</span>
                            <span style="color: #3b82f6;">28% Gap</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: 28%; background: #3b82f6;"></div>
                        </div>
                        <small style="color: var(--text-muted); font-size: 0.8em;">Recommendation: Encourage weekly competitive coding and data structure drills.</small>
                    </div>
                </div>
            </div>

            <!-- Recent Recommendations Table -->
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h3>Recent Student Prediction Logs</h3>
                    <span class="badge badge-cyan"><?php echo count($recs_log); ?> Entries</span>
                </div>

                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Predicted Career Trajectory</th>
                            <th>Match Score</th>
                            <th>Academic CGPA</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recs_log)): ?>
                        <tr><td colspan="6" style="text-align:center; color: var(--text-muted); padding: 20px;">No predictions logged yet.</td></tr>
                        <?php else: ?>
                        <?php foreach ($recs_log as $log): ?>
                        <tr>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($log['student_name'] ?? 'Student'); ?></td>
                            <td style="color: var(--text-muted);"><?php echo htmlspecialchars($log['email'] ?? ''); ?></td>
                            <td><span class="badge badge-orange"><?php echo htmlspecialchars($log['top_career'] ?? 'Software Engineer'); ?></span></td>
                            <td><strong style="color: var(--primary);"><?php echo htmlspecialchars($log['score'] ?? 90); ?>%</strong></td>
                            <td><?php echo htmlspecialchars($log['cgpa'] ?? 85); ?></td>
                            <td style="font-size: 0.85em; color: var(--text-muted);"><?php echo htmlspecialchars($log['date'] ?? 'Today'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- ==========================================
         MODAL 1: VIEW STUDENT PROFILE DETAILS
         ========================================== -->
    <div class="modal-overlay" id="viewModalOverlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="viewModalTitle">Student Profile Overview</h3>
                <button class="modal-close" onclick="closeViewModal()">✕</button>
            </div>
            <div id="viewModalContent" style="line-height: 1.8;">
                <!-- Filled dynamically via JS -->
            </div>
            <div style="text-align: right; margin-top: 24px;">
                <button class="btn-primary" onclick="closeViewModal()">Close</button>
            </div>
        </div>
    </div>

    <!-- ==========================================
         MODAL 2: ADD / EDIT USER PROFILE
         ========================================== -->
    <div class="modal-overlay" id="userModalOverlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="userModalTitle">Add / Edit User Account</h3>
                <button class="modal-close" onclick="closeUserModal()">✕</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="save_user">
                
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 6px;">Full Name *</label>
                    <input type="text" name="name" id="userFormName" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px;">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 6px;">Email Address *</label>
                    <input type="email" name="email" id="userFormEmail" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px;">
                </div>

                <div class="grid-2" style="margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 6px;">Account Role</label>
                        <select name="role" id="userFormRole" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px;">
                            <option value="student">Student / Job Seeker</option>
                            <option value="admin">System Administrator</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 6px;">Academic Degree</label>
                        <select name="education" id="userFormEducation" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px;">
                            <option value="Bachelor">Bachelor Degree</option>
                            <option value="Master">Master Degree</option>
                            <option value="PhD">Doctorate (PhD)</option>
                            <option value="Diploma">Diploma / Bootcamp</option>
                        </select>
                    </div>
                </div>

                <div class="grid-2" style="margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 6px;">Specialization / Major</label>
                        <input type="text" name="specialization" id="userFormSpec" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px;" placeholder="e.g. Computer Science">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 6px;">CGPA / Grade</label>
                        <input type="number" step="0.1" name="cgpa" id="userFormCgpa" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px;" placeholder="e.g. 88">
                    </div>
                </div>

                <div class="grid-2" style="margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 6px;">Skill Assessment Score (%)</label>
                        <input type="number" name="skills_score" id="userFormScore" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px;" placeholder="Leave empty if not taken">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 6px;">Interest Survey Profile</label>
                        <input type="text" name="interest_profile" id="userFormInterests" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px;" placeholder="e.g. Investigative & Enterprising">
                    </div>
                </div>

                <div style="text-align: right; margin-top: 24px; border-top: 1px solid var(--border-color); padding-top: 16px;">
                    <button type="button" class="btn-secondary" onclick="closeUserModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Save User Profile</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==========================================
         MODAL 3: ADD / EDIT CAREER ROLE
         ========================================== -->
    <div class="modal-overlay" id="careerModalOverlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="careerModalTitle">Update Career Database Role</h3>
                <button class="modal-close" onclick="closeCareerModal()">✕</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="save_career">
                <input type="hidden" name="id" id="careerFormId" value="">

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 6px;">Job Role Title *</label>
                    <input type="text" name="title" id="careerFormTitle" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px;" placeholder="e.g. AI & Machine Learning Engineer">
                </div>

                <div class="grid-2" style="margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 6px;">Industry Domain</label>
                        <input type="text" name="category" id="careerFormCat" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px;" placeholder="e.g. Artificial Intelligence">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 6px;">Average Salary Benchmark</label>
                        <input type="text" name="salary" id="careerFormSalary" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px;" placeholder="e.g. $135,000 / yr">
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 6px;">Required Skill Benchmarks *</label>
                    <input type="text" name="benchmarks" id="careerFormBenchmarks" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px;" placeholder="e.g. Min Technical Score: 85%, CGPA: 85+, Key Skills: Python, PyTorch">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 6px;">Job Role Description</label>
                    <textarea name="description" id="careerFormDesc" rows="4" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px;" placeholder="Describe the responsibilities and career trajectory..."></textarea>
                </div>

                <div style="text-align: right; margin-top: 24px; border-top: 1px solid var(--border-color); padding-top: 16px;">
                    <button type="button" class="btn-secondary" onclick="closeCareerModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Save to Career Database</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Search functionality for users table
        const searchInput = document.getElementById('userSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const query = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('#usersTable tbody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(query) ? '' : 'none';
                });
            });
        }

        // Modal Handlers
        function openViewModal(user) {
            document.getElementById('viewModalTitle').innerText = user.name + " (" + (user.role || 'student').toUpperCase() + ")";
            
            let html = `
                <div style="background: var(--bg-main); padding: 16px; border-radius: 12px; margin-bottom: 16px;">
                    <p><strong>Email Address:</strong> ${user.email || 'N/A'}</p>
                    <p><strong>Academic Level:</strong> ${user.education || 'Bachelor'} in ${user.specialization || 'General'}</p>
                    <p><strong>Cumulative Grade (CGPA):</strong> ${user.cgpa || 'N/A'}</p>
                </div>
                <h4 style="margin-bottom: 8px; color: var(--primary);">📋 Survey & Assessment Records</h4>
                <div style="border: 1px solid var(--border-color); padding: 16px; border-radius: 12px;">
                    <p><strong>Interest Survey Taken:</strong> ${user.interest_profile ? '✔ YES' : '⏳ Pending'}</p>
                    <p><strong>Dominant RIASEC Interests:</strong> <span class="badge badge-orange">${user.interest_profile || 'Not completed yet'}</span></p>
                    <p style="margin-top: 12px;"><strong>Skill Assessment Evaluated:</strong> ${user.skills_score !== undefined && user.skills_score !== null ? '✔ YES' : '⏳ Pending'}</p>
                    <p><strong>Technical Score Benchmark:</strong> <strong style="color: var(--accent-emerald); font-size: 1.2em;">${user.skills_score !== undefined && user.skills_score !== null ? user.skills_score + '%' : 'Not evaluated'}</strong></p>
                    <p style="margin-top: 12px;"><strong>Top ML Predicted Career Match:</strong> ${user.top_career || 'AI Software Engineer'}</p>
                </div>
            `;
            document.getElementById('viewModalContent').innerHTML = html;
            document.getElementById('viewModalOverlay').classList.add('active');
        }

        function closeViewModal() {
            document.getElementById('viewModalOverlay').classList.remove('active');
        }

        function openUserModal(mode, user = null) {
            document.getElementById('userModalTitle').innerText = mode === 'add' ? 'Add New User Profile' : 'Edit User Profile';
            document.getElementById('userFormName').value = user ? (user.name || '') : '';
            document.getElementById('userFormEmail').value = user ? (user.email || '') : '';
            document.getElementById('userFormEmail').readOnly = (mode === 'edit');
            document.getElementById('userFormRole').value = user ? (user.role || 'student') : 'student';
            document.getElementById('userFormEducation').value = user ? (user.education || 'Bachelor') : 'Bachelor';
            document.getElementById('userFormSpec').value = user ? (user.specialization || 'Computer Science') : '';
            document.getElementById('userFormCgpa').value = user ? (user.cgpa || '85') : '85';
            document.getElementById('userFormScore').value = (user && user.skills_score !== undefined && user.skills_score !== null) ? user.skills_score : '';
            document.getElementById('userFormInterests').value = user ? (user.interest_profile || '') : '';
            
            document.getElementById('userModalOverlay').classList.add('active');
        }

        function closeUserModal() {
            document.getElementById('userModalOverlay').classList.remove('active');
        }

        function openCareerModal(mode, career = null) {
            document.getElementById('careerModalTitle').innerText = mode === 'add' ? 'Add New Career Role' : 'Update Career Role & Benchmarks';
            document.getElementById('careerFormId').value = career ? (career.id || '') : '';
            document.getElementById('careerFormTitle').value = career ? (career.title || '') : '';
            document.getElementById('careerFormCat').value = career ? (career.category || 'Technology') : '';
            document.getElementById('careerFormSalary').value = career ? (career.salary || '$120,000 / yr') : '$120,000 / yr';
            document.getElementById('careerFormBenchmarks').value = career ? (career.benchmarks || '') : '';
            document.getElementById('careerFormDesc').value = career ? (career.description || '') : '';
            
            document.getElementById('careerModalOverlay').classList.add('active');
        }

        function closeCareerModal() {
            document.getElementById('careerModalOverlay').classList.remove('active');
        }
    </script>
</body>
</html>