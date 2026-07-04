<?php
session_start();
$loggedIn = isset($_SESSION['user']);
$userName = $loggedIn ? $_SESSION['user']['name'] : '';
$message = $_SESSION['auth_message'] ?? '';
$messageType = $_SESSION['auth_message_type'] ?? 'success';
unset($_SESSION['auth_message'], $_SESSION['auth_message_type']);
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? ''), '/');
$iconPath = $basePath !== '' ? $basePath . '/images/logo.jpg' : 'images/logo.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AGC Sunday School</title>
    <link rel="icon" type="image/jpeg" href="<?php echo htmlspecialchars($iconPath, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="shortcut icon" type="image/jpeg" href="<?php echo htmlspecialchars($iconPath, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/style.css?v=3">
</head>
<body>
    <main class="auth-page">
        <section class="auth-card">
            <div class="auth-header">
                <p class="eyebrow"><?php echo $loggedIn ? 'Welcome back' : 'Welcome'; ?></p>
                <h1><?php echo $loggedIn ? 'Access your dashboard' : 'Sign in to continue'; ?></h1>
                <p><?php echo $loggedIn ? 'You are signed in as ' . htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') . '.' : 'Use your username and mobile number to sign in and access the AGC Sunday School dashboard.'; ?></p>
            </div>

            <?php if ($message !== ''): ?>
                <section class="<?php echo $messageType === 'error' ? 'error-banner' : 'success-banner'; ?>">
                    <p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
                </section>
            <?php endif; ?>

            <?php if ($loggedIn): ?>
                <div class="auth-form" style="gap: 12px;">
                    <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/dashboard.php" class="primary-btn" style="text-align: center; display: inline-block;">Go to Dashboard</a>
                    <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/logout.php" class="text-link" style="display: inline-block;">Log Out</a>
                </div>
            <?php else: ?>
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
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
