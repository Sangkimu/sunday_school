<?php
session_start();
require_once __DIR__ . '/db.php';

if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$message = $_SESSION['auth_message'] ?? '';
$messageType = $_SESSION['auth_message_type'] ?? 'success';
unset($_SESSION['auth_message'], $_SESSION['auth_message_type']);

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? ''), '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | AGC Sunday School</title>
    <link rel="icon" type="image/jpeg" href="images/logo.jpg">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/style.css?v=3">
</head>
<body>
    <main class="auth-page">
        <section class="auth-card">
            <div class="auth-header">
                <div>
                    <p class="eyebrow">Create account</p>
                    <h1>Sign up for Sunday School</h1>
                    <p>Register your account to access the AGC Bomet Area dashboard and manage activities.</p>
                </div>
            </div>

            <?php if ($message !== ''): ?>
                <section class="<?php echo $messageType === 'error' ? 'error-banner' : 'success-banner'; ?>">
                    <p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
                </section>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/auth_handler.php" method="post" class="auth-form">
                <input type="hidden" name="action" value="signup">
                <div class="auth-grid">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input id="first_name" name="first_name" type="text" placeholder="Jane" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input id="last_name" name="last_name" type="text" placeholder="Cheptoo" required>
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input id="username" name="username" type="text" placeholder="Janecheptoo" required>
                    </div>
                    <div class="form-group">
                        <label for="mobile_number">Mobile Number</label>
                        <input id="mobile_number" name="mobile_number" type="tel" placeholder="0712345678" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="local_church">Local Church</label>
                    <input id="local_church" name="local_church" type="text" placeholder="AGC Bomet" required>
                </div>
                <div class="form-group">
                    <label for="district_church">District Church</label>
                    <input id="district_church" name="district_church" type="text" placeholder="Tenwek District" required>
                </div>
                <div class="form-group">
                    <label for="area_church">Area Church</label>
                    <input id="area_church" name="area_church" type="text" placeholder="Bomet Area" required>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="Parent/Guardian">Parent/Guardian</option>
                        <option value="Teacher">Teacher</option>
                        <option value="Secretary/Clerk">Secretary/Clerk</option>
                        <option value="Sunday School Coordinator">Sunday School Coordinator</option>
                        <option value="Guest/Visitor">Guest/Visitor</option>
                        <option value="Super Administrator">Super Administrator</option>
                    </select>
                </div>
                <button type="submit" class="primary-btn">Create Account</button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a class="text-link" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/login.php">Log in</a></p>
            </div>
        </section>
    </main>
</body>
</html>
