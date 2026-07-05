<?php
session_start();
$is_logged_in = isset($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Powered Career Recommendation System - CareerAI</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Top Navigation -->
    <nav class="nav">
        <a href="index.php" class="logo">🎓 CareerAI</a>
        <div class="nav-links">
            <?php if ($is_logged_in): ?>
            <a href="dashboard_student.php">My Dashboard</a>
            <a href="skill_assessment.php">Skill Assessment</a>
            <a href="interest_survey.php">Interest Survey</a>
            <a href="industry_dashboard.php">Industry Dashboard</a>
            <a href="logout.php" class="btn-secondary" style="padding: 6px 16px;">Logout</a>
            <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" class="btn-nav-cta">Get Started Free</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero">
        <span class="brain-icon">🧠</span>
        <h1 style="font-size: 3.6em;">Discover Your True Career Calling</h1>
        <p>AI-powered career prediction integrating academic statistics, RIASEC psychological surveys, and real-time industry intelligence</p>
    </div>

    <!-- Main Container -->
    <div class="container card-elevated" style="max-width: 860px;">
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
                <div>
                    <h2 style="font-size: 2em; margin-bottom: 6px;">Instant AI Career Simulator</h2>
                    <p style="color: var(--text-muted); margin: 0;">Enter your academic details below or use sample data to test the ML engine</p>
                </div>
                <button type="button" onclick="fillRandomData()" class="btn-magic">
                    ✨ Fill Random Sample Data
                </button>
            </div>

            <form id="careerForm" action="process.php" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Education Level</label>
                        <select name="education_level" id="education_level" required>
                            <option value="">Select Level</option>
                            <option value="High School">High School</option>
                            <option value="Bachelor">Bachelor's Degree</option>
                            <option value="Master">Master's Degree</option>
                            <option value="PhD">PhD</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Specialization Domain</label>
                        <select name="specialization" id="specialization" required>
                            <option value="">Select Specialization</option>
                            <option value="Computer Science">Computer Science</option>
                            <option value="Data Science">Data Science & AI</option>
                            <option value="Business">Business Administration</option>
                            <option value="Engineering">Engineering</option>
                            <option value="Cyber Security">Cyber Security</option>
                            <option value="Arts">Design & Creative Media</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Core Technical Skillset</label>
                        <select name="skills" id="skills" required>
                            <option value="">Select Primary Skill</option>
                            <option value="Programming">Full-Stack Programming</option>
                            <option value="Data Analysis">Data Analytics & Machine Learning</option>
                            <option value="Cloud/DevOps">Cloud & DevOps Engineering</option>
                            <option value="Design">UI/UX Design Systems</option>
                            <option value="Leadership">Agile Product Leadership</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Industry Certifications</label>
                        <select name="certifications" id="certifications">
                            <option value="None">None</option>
                            <option value="AWS">AWS Solutions Architect</option>
                            <option value="Google">Google Data Analytics</option>
                            <option value="PMP">Project Management Professional (PMP)</option>
                            <option value="CISSP">CISSP Cybersecurity</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Academic Performance (CGPA / Percentage: 60 - 100)</label>
                    <input type="number" step="0.1" name="cgpa" id="cgpa" min="60" max="100" placeholder="e.g. 88.5" required>
                </div>

                <button type="submit" class="submit-btn" style="width: 100%; margin-top: 12px;">
                    🚀 Predict My Career Path & Industry Matches
                </button>
            </form>
        </div>

        <!-- Features Showcase Grid -->
        <div style="margin-top: 50px; text-align: center;">
            <h2 style="font-size: 2.2em; margin-bottom: 30px;">An Advanced Ecosystem for Student Growth</h2>
            
            <div class="grid-3" style="text-align: left;">
                <div class="card" style="padding: 24px;">
                    <span style="font-size: 32px;">🎯</span>
                    <h3 style="margin: 12px 0 8px;">Multi-Stage Skill Assessment</h3>
                    <p style="font-size: 0.9em; color: var(--text-muted);">Interactive quizzes benchmarking coding, statistics, cloud deployment, and system design competencies.</p>
                </div>

                <div class="card" style="padding: 24px;">
                    <span style="font-size: 32px;">🔬</span>
                    <h3 style="margin: 12px 0 8px;">RIASEC Interest Surveys</h3>
                    <p style="font-size: 0.9em; color: var(--text-muted);">Calibrate recommendations with psychological career interest profiles and daily engineering workflow preferences.</p>
                </div>

                <div class="card" style="padding: 24px;">
                    <span style="font-size: 32px;">🏢</span>
                    <h3 style="margin: 12px 0 8px;">Industry Info Dashboard</h3>
                    <p style="font-size: 0.9em; color: var(--text-muted);">Explore live compensation, demand indices, and skill gaps across AI, FinTech, and Cloud ecosystems.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function fillRandomData() {
            const education = document.getElementById('education_level');
            const spec = document.getElementById('specialization');
            const skills = document.getElementById('skills');
            const cert = document.getElementById('certifications');
            const cgpa = document.getElementById('cgpa');

            const edLevels = ['Bachelor', 'Master'];
            const specList = ['Computer Science', 'Data Science', 'Business', 'Engineering', 'Cyber Security'];
            const skillList = ['Programming', 'Data Analysis', 'Cloud/DevOps', 'Design', 'Leadership'];
            const certList = ['AWS', 'Google', 'PMP', 'None'];

            education.value = edLevels[Math.floor(Math.random() * edLevels.length)];
            spec.value = specList[Math.floor(Math.random() * specList.length)];
            skills.value = skillList[Math.floor(Math.random() * skillList.length)];
            cert.value = certList[Math.floor(Math.random() * certList.length)];
            cgpa.value = (Math.random() * 18 + 80).toFixed(1);
        }
    </script>
</body>
</html>