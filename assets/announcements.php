<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/db.php';

$message = $_SESSION['announcements_message'] ?? '';
$messageType = $_SESSION['announcements_message_type'] ?? 'success';
unset($_SESSION['announcements_message'], $_SESSION['announcements_message_type']);

$dbError = '';
$editAnnouncement = null;
$announcements = [];
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? ''), '/');
$role = trim((string) ($_SESSION['user']['role'] ?? ''));
$canManageAnnouncements = can_manage_module('announcements', $role);

try {
    $db = db_connect();
    $announcements = db_fetch_all($db, 'SELECT announcement_id, title, message, announcement_date, audience FROM announcements ORDER BY announcement_date DESC, announcement_id DESC');

    if (isset($_GET['edit']) && ctype_digit((string) $_GET['edit'])) {
        $editAnnouncement = db_fetch_one($db, 'SELECT announcement_id, title, message, announcement_date, audience FROM announcements WHERE announcement_id = ?', [(int) $_GET['edit']]);
    }
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

$allCount = count(array_filter($announcements, static fn ($announcement) => strtolower((string) ($announcement['audience'] ?? '')) === 'all'));
$teachersCount = count(array_filter($announcements, static fn ($announcement) => stripos((string) ($announcement['audience'] ?? ''), 'teacher') !== false));
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements | AGC Bomet Area</title>
    <link rel="icon" type="image/jpeg" href="images/logo.jpg">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/style.css?v=3">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="brand">
                <img src="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/images/mu-logo.jpg" alt="AGC Bomet Area logo" class="brand-logo" onerror="this.onerror=null; this.src='<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/images/logo.jpg';">
                <div>
                    <h1>AGC Bomet Area</h1>
                    <p>Sunday School Dashboard</p>
                </div>
            </div>

            <nav class="sidebar-nav">
                <p class="nav-label">Main</p>
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/dashboard.php">Dashboard</a>
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/children.php">Children</a>
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/teachers.php">Teachers</a>
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/attendance.php">Attendance</a>
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/classes.php">Classes</a>
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/bible-stories.php">Bible Stories</a>
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/memory-verses.php">Memory Verses</a>
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/songs.php">Songs</a>
                <a class="nav-item" href="#">Plays</a>
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/calendar.php">Calendar of Events</a>
                <a class="nav-item active" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/announcements.php">Announcements</a>
                <p class="nav-label">Management</p>
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/awards.php">Awards</a>
                <a class="nav-item" href="#">Reports</a>
                <a class="nav-item" href="#">Settings</a>
                <a class="nav-item" href="#">Profile</a>
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/logout.php">Log Out</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="brand-block">
                    <img src="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/images/mu-logo.jpg" alt="AGC logo" class="top-brand-logo" onerror="this.onerror=null; this.src='<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/images/logo.jpg';">
                    <div>
                        <p class="eyebrow">Sunday School</p>
                        <h2>Announcements</h2>
                    </div>
                </div>
                <div class="top-nav-links">
                    <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/logout.php">Log Out</a>
                </div>
            </header>

            <section class="page-header">
                <div>
                    <p class="eyebrow">Communication</p>
                    <h3>Important Updates and Notices</h3>
                    <p>Manage announcements for children, teachers, or the full church community.</p>
                </div>
                <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/dashboard.php" class="text-link">← Back to Dashboard</a>
            </section>

            <?php if ($dbError): ?>
                <section class="error-banner">
                    <p>Database error: <?php echo htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8'); ?></p>
                </section>
            <?php endif; ?>

            <?php if ($message !== ''): ?>
                <section class="<?php echo $messageType === 'error' ? 'error-banner' : 'success-banner'; ?>">
                    <p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
                </section>
            <?php endif; ?>

            <section class="summary-grid">
                <article class="summary-card">
                    <p class="card-label">Total Announcements</p>
                    <p class="card-number"><?php echo count($announcements); ?></p>
                </article>
                <article class="summary-card">
                    <p class="card-label">For All</p>
                    <p class="card-number"><?php echo $allCount; ?></p>
                </article>
                <article class="summary-card">
                    <p class="card-label">For Teachers</p>
                    <p class="card-number"><?php echo $teachersCount; ?></p>
                </article>
            </section>

            <section class="children-section">
                <div class="section-heading">
                    <h3><?php echo $editAnnouncement ? 'Edit Announcement' : 'Create Announcement'; ?></h3>
                    <?php if ($editAnnouncement): ?>
                        <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/announcements.php" class="text-link">Cancel Edit</a>
                    <?php endif; ?>
                </div>

                <?php if ($canManageAnnouncements): ?>
                    <form action="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/announcements_handler.php" method="post">
                        <input type="hidden" name="action" value="<?php echo $editAnnouncement ? 'edit' : 'add'; ?>">
                        <?php if ($editAnnouncement): ?>
                            <input type="hidden" name="announcement_id" value="<?php echo (int) $editAnnouncement['announcement_id']; ?>">
                        <?php endif; ?>

                        <div class="content-grid two-col">
                            <article class="panel-card">
                                <label for="title">Title</label>
                                <input id="title" name="title" type="text" value="<?php echo htmlspecialchars($editAnnouncement['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                            </article>
                            <article class="panel-card">
                                <label for="announcement_date">Date</label>
                                <input id="announcement_date" name="announcement_date" type="date" value="<?php echo htmlspecialchars($editAnnouncement['announcement_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                            </article>
                        </div>

                        <div class="content-grid two-col">
                            <article class="panel-card">
                                <label for="audience">Audience</label>
                                <select id="audience" name="audience">
                                    <option value="All" <?php echo (($editAnnouncement['audience'] ?? 'All') === 'All') ? 'selected' : ''; ?>>All</option>
                                    <option value="Teachers Only" <?php echo (($editAnnouncement['audience'] ?? 'All') === 'Teachers Only') ? 'selected' : ''; ?>>Teachers Only</option>
                                    <option value="Parents Only" <?php echo (($editAnnouncement['audience'] ?? 'All') === 'Parents Only') ? 'selected' : ''; ?>>Parents Only</option>
                                </select>
                            </article>
                            <article class="panel-card">
                                <label for="message">Message</label>
                                <textarea id="message" name="message" rows="5" required><?php echo htmlspecialchars($editAnnouncement['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </article>
                        </div>

                        <div class="section-heading">
                            <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/announcements.php" class="text-link">Back to list</a>
                            <button type="submit" class="primary-btn"><?php echo $editAnnouncement ? 'Update Announcement' : 'Save Announcement'; ?></button>
                        </div>
                    </form>
                <?php else: ?>
                    <p class="error-banner">Only teachers and coordinators can create or update announcements.</p>
                <?php endif; ?>
            </section>

            <section class="table-card">
                <div class="table-header">
                    <h3>Announcements Database</h3>
                </div>
                <div class="table-wrapper">
                    <table class="children-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Audience</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($announcements)): ?>
                                <tr>
                                    <td colspan="6">No announcements found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($announcements as $announcement): ?>
                                    <tr>
                                        <td><?php echo (int) $announcement['announcement_id']; ?></td>
                                        <td><?php echo htmlspecialchars($announcement['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($announcement['message'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($announcement['announcement_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><span class="status <?php echo stripos((string) $announcement['audience'], 'teacher') !== false ? 'pending' : 'active'; ?>"><?php echo htmlspecialchars($announcement['audience'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td>
                                            <?php if ($canManageAnnouncements): ?>
                                                <div class="top-nav-links">
                                                    <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/announcements.php?edit=<?php echo (int) $announcement['announcement_id']; ?>" class="text-link">Edit</a>
                                                    <form action="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/announcements_handler.php" method="post" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="announcement_id" value="<?php echo (int) $announcement['announcement_id']; ?>">
                                                        <button type="submit" class="button text-link" onclick="return confirm('Delete this announcement?');">Delete</button>
                                                    </form>
                                                </div>
                                            <?php else: ?>
                                                <span>—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="content-grid">
                <article class="panel-card">
                    <h3>Latest Updates</h3>
                    <p>Check this page regularly for the latest announcements and important information from church leadership.</p>
                </article>
                <article class="panel-card">
                    <h3>Contact Us</h3>
                    <p>For questions about announcements, contact the Sunday School coordinator or speak to a teacher.</p>
                </article>
            </section>

            <footer class="footer">
                <p>© 2026 AGC Bomet Area Sunday School. All rights reserved.</p>
            </footer>
        </main>
    </div>

    <script src="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/script.js"></script>
</body>
</html>
