<?php
session_start();
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CareerAI</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="nav">
        <a href="index.php" class="logo">🎓 CareerAI</a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="register.php">Sign Up</a>
            <a href="login.php" class="btn-nav-cta">Login</a>
        </div>
    </nav>

    <div class="hero" style="padding: 60px 20px 80px;">
        <span class="brain-icon" style="font-size: 70px;">🧠</span>
        <h1 style="font-size: 2.8em;">Welcome Back to CareerAI</h1>
        <p>Access your personalized ML career recommendations, skill assessments, and industry intelligence</p>
    </div>

    <div class="container-sm card-elevated" style="max-width: 480px;">
        <div class="card">
            <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <h2 style="text-align: center; margin-bottom: 24px; color: var(--primary);">Account Portal Login</h2>

            <!-- Quick Demo Buttons -->
            <div style="display: flex; gap: 10px; margin-bottom: 24px;">
                <button type="button" onclick="fillDemo('student@careerai.com', 'demo123')" class="btn-secondary" style="flex: 1; justify-content: center; font-size: 0.85em; padding: 10px;">
                    🎓 Student Demo
                </button>
                <button type="button" onclick="fillDemo('admin@careerai.com', 'admin123')" class="btn-secondary" style="flex: 1; justify-content: center; font-size: 0.85em; padding: 10px;">
                    ⚙️ Admin Demo
                </button>
            </div>

            <form action="login_process.php" method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="text" name="username" id="username" placeholder="student@careerai.com" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" id="password" placeholder="••••••••" required>
                </div>

                <button type="submit" class="submit-btn" style="width: 100%;">Secure Login →</button>
            </form>

            <div style="text-align: center; margin-top: 24px; font-size: 0.95em; color: var(--text-muted);">
                Don't have an account yet? <a href="register.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Create Free Account</a>
            </div>
        </div>
    </div>

    <script>
        function fillDemo(email, pass) {
            document.getElementById('username').value = email;
            document.getElementById('password').value = pass;
        }
        if (window.location.search.includes('demo=student')) {
            fillDemo('student@careerai.com', 'demo123');
        } else if (window.location.search.includes('demo=admin')) {
            fillDemo('admin@careerai.com', 'admin123');
        }
    </script>
</body>
</html>