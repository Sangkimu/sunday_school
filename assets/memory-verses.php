<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/db.php';

$message = $_SESSION['memory_verses_message'] ?? ''; 
$messageType = $_SESSION['memory_verses_message_type'] ?? 'success';
unset($_SESSION['memory_verses_message'], $_SESSION['memory_verses_message_type']);

$dbError = '';
$editVerse = null;
$verses = [];
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? ''), '/');
$stats = [
    'total' => 0,
    'categories' => 0,
    'recent_week' => '',
];

try {
    $db = db_connect();

    $verses = db_fetch_all($db, 'SELECT verse_id, verse_text, reference, category, date_added FROM memory_verses ORDER BY date_added DESC');

    $stats['total'] = count($verses);
    $stats['categories'] = (int) db_scalar($db, 'SELECT COUNT(DISTINCT category) FROM memory_verses WHERE category IS NOT NULL');
    $latestVerse = db_fetch_one($db, 'SELECT reference FROM memory_verses ORDER BY date_added DESC LIMIT 1');
    $stats['recent_week'] = $latestVerse ? $latestVerse['reference'] : 'None';

    if (isset($_GET['edit']) && ctype_digit((string) $_GET['edit'])) {
        $editVerse = db_fetch_one($db, 'SELECT verse_id, verse_text, reference, category FROM memory_verses WHERE verse_id = ?', [(int) $_GET['edit']]);
    }
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memory Verses | AGC Bomet Area</title>
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
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/bible-stories.php">Bible Stories</a>
                <a class="nav-item active" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/memory-verses.php">Memory Verses</a>
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
                        <h2>Memory Verses</h2>
                    </div>
                </div>
                <div class="top-nav-links">
                    <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/logout.php">Log Out</a>
                </div>
            </header>

            <section class="page-header">
                <div>
                    <p class="eyebrow">Memory Verses</p>
                    <h3>Weekly memory verses for children</h3>
                    <p>Track verses with biblical references, category, and dates.</p>
                </div>
                <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/dashboard.php" class="text-link">Back to Dashboard</a>
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
                    <h4>Total Verses</h4>
                    <span><?php echo number_format($stats['total']); ?></span>
                </article>
                <article class="summary-card">
                    <h4>Recent Verse</h4>
                    <span><?php echo htmlspecialchars($stats['recent_week'], ENT_QUOTES, 'UTF-8'); ?></span>
                </article>
                <article class="summary-card">
                    <h4>Categories</h4>
                    <span><?php echo number_format($stats['categories']); ?></span>
                </article>
                <article class="summary-card">
                    <h4>Last Updated</h4>
                    <span><?php echo !empty($verses) ? htmlspecialchars(date('M d, Y', strtotime($verses[0]['date_added'])), ENT_QUOTES, 'UTF-8') : 'N/A'; ?></span>
                </article>
            </section>

            <section class="table-card">
                <div class="table-header">
                    <h3><?php echo $editVerse ? 'Edit Verse' : 'Add New Verse'; ?></h3>
                </div>
                <form action="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/memory-verses_handler.php" method="post" class="panel-card">
                    <input type="hidden" name="action" value="<?php echo $editVerse ? 'edit' : 'add'; ?>">
                    <?php if ($editVerse): ?>
                        <input type="hidden" name="verse_id" value="<?php echo (int) $editVerse['verse_id']; ?>">
                    <?php endif; ?>
                    <div class="children-grid">
                        <div>
                            <label for="reference">Bible Reference</label>
                            <input type="text" id="reference" name="reference" value="<?php echo htmlspecialchars($editVerse['reference'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required placeholder="e.g., John 3:16">
                        </div>
                        <div>
                            <label for="category">Category</label>
                            <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($editVerse['category'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="e.g., Faith, Love, Trust">
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label for="verse_text">Verse Text</label>
                            <textarea id="verse_text" name="verse_text" required placeholder="Full verse text" style="width: 100%; min-height: 100px; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-family: inherit;"><?php echo htmlspecialchars($editVerse['verse_text'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                    </div>
                    <div style="margin-top: 1rem;">
                        <button type="submit" class="primary-btn"><?php echo $editVerse ? 'Save Changes' : 'Add Verse'; ?></button>
                        <?php if ($editVerse): ?>
                            <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/memory-verses.php" class="text-link">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>

            <section class="table-card">
                <div class="table-header">
                    <h3>Memory Verse Records</h3>
                </div>
                <div class="table-wrapper">
                    <table class="children-table">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Verse Text</th>
                                <th>Category</th>
                                <th>Date Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($verses)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 2rem;">No verses yet. Add one to get started.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($verses as $verse): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($verse['reference'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars(substr($verse['verse_text'], 0, 60) . (strlen($verse['verse_text']) > 60 ? '...' : ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($verse['category'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars(date('M d, Y', strtotime($verse['date_added'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <div style="display: flex; gap: 0.5rem;">
                                                <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/memory-verses.php?edit=<?php echo (int) $verse['verse_id']; ?>" class="text-link">Edit</a>
                                                <form action="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/memory-verses_handler.php" method="post" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="verse_id" value="<?php echo (int) $verse['verse_id']; ?>">
                                                    <button type="submit" class="text-link" style="background: none; border: none; color: inherit; cursor: pointer; padding: 0;" onclick="return confirm('Are you sure?')">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
