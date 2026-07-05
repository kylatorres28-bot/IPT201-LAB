<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - CareerAI</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="nav">
        <a href="index.php" class="logo">🎓 CareerAI</a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="login.php">Login</a>
            <a href="register.php" class="btn-nav-cta">Sign Up</a>
        </div>
    </nav>

    <div class="hero" style="padding: 60px 20px 80px;">
        <h1 style="font-size: 2.8em;">Join CareerAI Today</h1>
        <p>Start your data-driven journey to the perfect tech or corporate career trajectory</p>
    </div>

    <div class="container-sm card-elevated" style="max-width: 520px;">
        <div class="card">
            <h2 style="text-align: center; margin-bottom: 24px; color: var(--primary);">Create Your Student Profile</h2>

            <form action="register_process.php" method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" placeholder="Alex Rivera" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="you@careerai.com" required>
                </div>
                <div class="form-group">
                    <label>Create Password</label>
                    <input type="password" name="password" placeholder="Create a secure password" required>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Education Level</label>
                        <select name="education" required>
                            <option value="High School">High School</option>
                            <option value="Bachelor" selected>Bachelor's Degree</option>
                            <option value="Master">Master's Degree</option>
                            <option value="PhD">PhD</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Primary Specialization</label>
                        <select name="specialization" required>
                            <option value="Computer Science">Computer Science</option>
                            <option value="Data Science">Data Science & AI</option>
                            <option value="Business">Business & Management</option>
                            <option value="Engineering">Engineering</option>
                            <option value="Cyber Security">Cyber Security</option>
                            <option value="Arts">Design & Creative Media</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="submit-btn" style="width: 100%;">Create Account & Launch Dashboard →</button>
            </form>

            <div style="text-align: center; margin-top: 24px; font-size: 0.95em; color: var(--text-muted);">
                Already have an account? <a href="login.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Log In Here</a>
            </div>
        </div>
    </div>
</body>
</html>