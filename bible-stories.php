<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/db.php';

$message = $_SESSION['bible_stories_message'] ?? ''; 
$messageType = $_SESSION['bible_stories_message_type'] ?? 'success';
unset($_SESSION['bible_stories_message'], $_SESSION['bible_stories_message_type']);

$stories = [];
$editStory = null;
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? ''), '/');

try {
    $db = db_connect();
    $stories = db_fetch_all($db, 'SELECT story_id, title, theme, lesson_date, summary FROM bible_stories ORDER BY lesson_date DESC, story_id DESC');

    if (isset($_GET['edit_story']) && ctype_digit((string) $_GET['edit_story'])) {
        $editStory = db_fetch_one($db, 'SELECT story_id, title, theme, lesson_date, summary FROM bible_stories WHERE story_id = ?', [(int) $_GET['edit_story']]);
    }
} catch (Throwable $e) {
    $message = $e->getMessage();
    $messageType = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bible Stories | AGC Bomet Area</title>
    <link rel="icon" type="image/jpeg" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/images/logo.jpg">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/style.css?v=2">
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
                <a class="nav-item active" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/bible-stories.php">Bible Stories</a>
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/memory-verses.php">Memory Verses</a>
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/songs.php">Songs</a>
                <a class="nav-item" href="#">Plays</a>
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/calendar.php">Calendar of Events</a>
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/announcements.php">Announcements</a>
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
                        <h2>Bible Stories</h2>
                    </div>
                </div>
                <div class="top-nav-links">
                    <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/logout.php">Log Out</a>
                </div>
            </header>

            <section class="page-header">
                <div>
                    <p class="eyebrow">Bible Stories</p>
                    <h3>Lesson planning and story details</h3>
                    <p>Store story outlines, teaching objectives, prayers, activities, and memory verses.</p>
                </div>
                <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/dashboard.php" class="text-link">Back to Dashboard</a>
            </section>

            <?php if ($message !== ''): ?>
                <section class="<?php echo $messageType === 'error' ? 'error-banner' : 'success-banner'; ?>">
                    <p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
                </section>
            <?php endif; ?>

            <section class="children-section">
                <div class="section-heading">
                    <h3><?php echo $editStory ? 'Edit Story' : 'Add Story'; ?></h3>
                    <?php if ($editStory): ?>
                        <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/bible-stories.php" class="text-link">Cancel Edit</a>
                    <?php endif; ?>
                </div>

                <form action="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/bible-stories_handler.php" method="post">
                    <input type="hidden" name="action" value="<?php echo $editStory ? 'edit_story' : 'add_story'; ?>">
                    <?php if ($editStory): ?>
                        <input type="hidden" name="story_id" value="<?php echo (int) $editStory['story_id']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="title">Title</label>
                        <input id="title" name="title" type="text" required value="<?php echo htmlspecialchars($editStory['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="theme">Theme</label>
                        <input id="theme" name="theme" type="text" value="<?php echo htmlspecialchars($editStory['theme'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="lesson_date">Lesson Date</label>
                        <input id="lesson_date" name="lesson_date" type="date" value="<?php echo htmlspecialchars($editStory['lesson_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="summary">Summary / Outline</label>
                        <textarea id="summary" name="summary" rows="6"><?php echo htmlspecialchars($editStory['summary'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <div class="section-heading">
                        <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/bible-stories.php" class="text-link">Back to list</a>
                        <button type="submit" class="primary-btn"><?php echo $editStory ? 'Update Story' : 'Save Story'; ?></button>
                    </div>
                </form>
            </section>

            <section class="table-card">
                <div class="table-header">
                    <h3>Bible Story Records</h3>
                    <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/bible-stories.php?add=1" class="text-link">Add Story</a>
                </div>
                <div class="table-wrapper">
                    <table class="children-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Theme</th>
                                <th>Lesson Date</th>
                                <th>Summary</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($stories)): ?>
                                <tr><td colspan="5">No stories found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($stories as $s): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($s['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($s['theme'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo $s['lesson_date'] ? htmlspecialchars(date('d M Y', strtotime($s['lesson_date'])), ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                        <td><?php echo nl2br(htmlspecialchars(substr($s['summary'] ?? '-', 0, 120), ENT_QUOTES, 'UTF-8')); ?></td>
                                        <td>
                                            <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/bible-stories.php?edit_story=<?php echo (int) $s['story_id']; ?>" class="text-link">Edit</a>
                                            <form action="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/bible-stories_handler.php" method="post" style="display:inline;">
                                                <input type="hidden" name="action" value="delete_story">
                                                <input type="hidden" name="story_id" value="<?php echo (int) $s['story_id']; ?>">
                                                <button type="submit" class="text-link" onclick="return confirm('Delete this story?');">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <footer class="footer"><p>© AGC Bomet Area Sunday School</p></footer>
        </main>
    </div>
</body>
</html>
