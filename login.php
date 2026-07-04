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
    <title>Login | AGC Sunday School</title>
    <link rel="icon" type="image/jpeg" href="images/logo.jpg">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/style.css?v=3">
</head>
<body>
    <main class="auth-page">
        <section class="auth-card">
            <div class="auth-header">
                <div>
                    <p class="eyebrow">Welcome back</p>
                    <h1>Log in to your account</h1>
                    <p>Enter your Username and password to continue to the Sunday School dashboard.</p>
                </div>
            </div>

            <?php if ($message !== ''): ?>
                <section class="<?php echo $messageType === 'error' ? 'error-banner' : 'success-banner'; ?>">
                    <p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
                </section>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/auth_handler.php" method="post" class="auth-form">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" placeholder="Janecheptoo" required>
                </div>
                <div class="form-group">
                    <label for="mobile_number">Mobile Number</label>
                    <input id="mobile_number" name="mobile_number" type="tel" placeholder="0712345678" required>
                </div>
                <button type="submit" class="primary-btn">Log In</button>
            </form>

            <div class="auth-footer">
                <p>Don’t have an account? <a class="text-link" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/signup.php">Sign up</a></p>
            </div>
        </section>
    </main>
</body>
</html>
