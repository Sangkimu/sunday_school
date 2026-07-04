<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/db.php';

$dbError = '';
$stats = [
    'children' => 0,
    'new_registrations' => 0,
    'attendance_today' => 0,
    'upcoming_class' => 'Primary 3',
    'teachers' => 0,
    'events' => 0,
];
$themeVerse = '“Children obey your parents in the Lord, for this is right.”';
$upcomingEvents = [];
$announcements = [];
$memoryVerses = [];

try {
    $db = db_connect();

    $stats['children'] = (int) db_scalar($db, 'SELECT COUNT(*) FROM children');
    $stats['new_registrations'] = (int) db_scalar($db, 'SELECT COUNT(*) FROM children WHERE date_registered >= CURDATE() - INTERVAL 30 DAY');
    $stats['attendance_today'] = (int) db_scalar($db, "SELECT COUNT(*) FROM attendance_records WHERE attendance_date = CURDATE() AND LOWER(status) = 'present'");
    $stats['upcoming_class'] = (string) db_scalar($db, 'SELECT class_name FROM classes ORDER BY class_id LIMIT 1') ?: 'Primary 3';
    $stats['teachers'] = (int) db_scalar($db, 'SELECT COUNT(*) FROM teachers');
    $stats['events'] = (int) db_scalar($db, 'SELECT COUNT(*) FROM calendar_events WHERE event_date >= CURDATE()');

    $themeVerseRow = db_fetch_one($db, 'SELECT verse_text FROM memory_verses ORDER BY date_added DESC LIMIT 1');
    if ($themeVerseRow !== false && !empty($themeVerseRow['verse_text'])) {
        $themeVerse = $themeVerseRow['verse_text'];
    }

    $upcomingEvents = db_fetch_all($db, 'SELECT title FROM calendar_events WHERE event_date >= CURDATE() ORDER BY event_date LIMIT 3');
    $announcements = db_fetch_all($db, 'SELECT title FROM announcements ORDER BY announcement_date DESC LIMIT 3');
    $memoryVerses = db_fetch_all($db, 'SELECT reference FROM memory_verses ORDER BY date_added DESC LIMIT 2');
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

$role = trim((string) ($_SESSION['user']['role'] ?? ''));
$currentPage = basename($_SERVER['PHP_SELF'] ?? 'dashboard.php');

$sidebarLinks = [
    ['label' => 'Dashboard', 'href' => 'dashboard.php', 'show' => true],
    ['label' => 'Children', 'href' => 'children.php', 'show' => can_access_page('children.php', $role)],
    ['label' => 'Teachers', 'href' => 'teachers.php', 'show' => can_access_page('teachers.php', $role)],
    ['label' => 'Attendance', 'href' => 'attendance.php', 'show' => can_access_page('attendance.php', $role)],
    ['label' => 'Classes', 'href' => 'classes.php', 'show' => can_access_page('classes.php', $role)],
    ['label' => 'Bible Stories', 'href' => 'bible-stories.php', 'show' => can_access_page('bible-stories.php', $role)],
    ['label' => 'Memory Verses', 'href' => 'memory-verses.php', 'show' => can_access_page('memory-verses.php', $role)],
    ['label' => 'Songs', 'href' => 'songs.php', 'show' => can_access_page('songs.php', $role)],
    ['label' => 'Calendar of Events', 'href' => 'calendar.php', 'show' => true],
    ['label' => 'Announcements', 'href' => 'announcements.php', 'show' => true],
    ['label' => 'Awards', 'href' => 'awards.php', 'show' => can_access_page('awards.php', $role)],
];

$newEntryLinks = [];
if (can_access_page('children.php', $role)) {
    $newEntryLinks[] = ['label' => 'Add Child', 'href' => 'children.php'];
}
if (can_access_page('teachers.php', $role)) {
    $newEntryLinks[] = ['label' => 'Add Teacher', 'href' => 'teachers.php'];
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AGC Bomet Regio Dashboard</title>
    <link rel="icon" type="image/jpeg" href="images/logo.jpg">
    <link rel="stylesheet" href="style.css">
    <style>
        .new-entry { position: relative; display: inline-block; }
        .new-entry-menu { position: absolute; right: 0; top: calc(100% + 8px); background: #ffffff; border: 1px solid #ddd; box-shadow: 0 6px 20px rgba(0,0,0,0.08); padding: 6px; border-radius: 6px; min-width: 160px; z-index: 60; }
        .new-entry-menu a { display: block; padding: 8px 12px; color: #111; text-decoration: none; }
        .new-entry-menu a:hover, .new-entry-menu a:focus { background: #f4f4f6; outline: none; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="brand">
                <img src="images/mu-logo.jpg" alt="AGC Bomet Region logo" class="brand-logo" onerror="this.onerror=null; this.src='images/logo.jpg';">
                <div>
                    <h1>AGC Bomet Region</h1>
                    <p>Sunday School Dashboard</p>
                </div>
            </div>

            <nav class="sidebar-nav">
                <p class="nav-label">Main</p>
                <?php foreach ($sidebarLinks as $link): ?>
                    <?php if (!$link['show']) continue; ?>
                    <a class="nav-item<?= $currentPage === $link['href'] ? ' active' : '' ?>" href="<?= htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?= htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php endforeach; ?>
                <a class="nav-item" href="logout.php">Log Out</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="brand-block">
                    <img src="images/mu-logo.jpg" alt="AGC logo" class="top-brand-logo" onerror="this.onerror=null; this.src='images/logo.jpg';">
                    <div>
                        <p class="eyebrow">Sunday School</p>
                        <h2>AGC Bomet Region</h2>
                    </div>
                </div>
                <div class="top-nav-links">
                    <a href="logout.php">Log Out</a>
                </div>
            </header>

            <section class="welcome-banner">
                <div>
                    <p class="eyebrow">Welcome</p>
                    <h3>Welcome Sang</h3>
                    <p>Everything you need for Sunday School is organized in one place.</p>
                </div>
                <?php if (!empty($newEntryLinks)): ?>
                    <div class="new-entry">
                        <button id="newEntryBtn" class="primary-btn" aria-haspopup="true" aria-expanded="false" aria-controls="newEntryMenu" aria-label="Add new entry">+ New Entry</button>
                        <div id="newEntryMenu" class="new-entry-menu" role="menu" hidden>
                            <?php foreach ($newEntryLinks as $entryLink): ?>
                                <a role="menuitem" href="<?= htmlspecialchars($entryLink['href'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?= htmlspecialchars($entryLink['label'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </section>

            <?php if ($dbError): ?>
                <section class="error-banner">
                    <p>Database error: <?php echo htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8'); ?></p>
                </section>
            <?php endif; ?>

            <section class="children-section">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Children</p>
                        <h3>Children Management</h3>
                    </div>
                    <a href="children.php" class="text-link">View all</a>
                </div>
                <div class="children-grid">
                    <article class="children-card">
                        <h4>Registered Children</h4>
                        <p class="value"><?php echo number_format($stats['children']); ?></p>
                        <p>Active members this term</p>
                    </article>
                    <article class="children-card">
                        <h4>New Registrations</h4>
                        <p class="value"><?php echo number_format($stats['new_registrations']); ?></p>
                        <p>This month</p>
                    </article>
                    <article class="children-card">
                        <h4>Attendance Today</h4>
                        <p class="value"><?php echo number_format($stats['attendance_today']); ?></p>
                        <p>Present children</p>
                    </article>
                    <article class="children-card">
                        <h4>Upcoming Class</h4>
                        <p class="value"><?php echo htmlspecialchars($stats['upcoming_class'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p>Next session</p>
                    </article>
                </div>
            </section>

            <section class="stats-grid">
                <article class="stat-card">
                    <h4>Children</h4>
                    <span><?php echo number_format($stats['children']); ?></span>
                </article>
                <article class="stat-card">
                    <h4>Teachers</h4>
                    <span><?php echo number_format($stats['teachers']); ?></span>
                </article>
                <article class="stat-card">
                    <h4>Attendance</h4>
                    <span><?php echo number_format($stats['attendance_today']); ?></span>
                </article>
                <article class="stat-card">
                    <h4>Events</h4>
                    <span><?php echo number_format($stats['events']); ?></span>
                </article>
            </section>

            <section class="content-grid">
                <article class="panel-card">
                    <h3>Today's Theme Verse</h3>
                    <p><?php echo htmlspecialchars($themeVerse, ENT_QUOTES, 'UTF-8'); ?></p>
                </article>
                <article class="panel-card">
                    <h3>Upcoming Events</h3>
                    <ul>
                        <?php foreach ($upcomingEvents as $event): ?>
                            <li><?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                        <?php if (empty($upcomingEvents)): ?>
                            <li>No upcoming events scheduled.</li>
                        <?php endif; ?>
                    </ul>
                </article>
                <article class="panel-card">
                    <h3>Announcements</h3>
                    <ul>
                        <?php foreach ($announcements as $announcement): ?>
                            <li><?php echo htmlspecialchars($announcement['title'], ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                        <?php if (empty($announcements)): ?>
                            <li>No announcements yet.</li>
                        <?php endif; ?>
                    </ul>
                </article>
                <article class="panel-card">
                    <h3>Calendar</h3>
                    <p>Track weekly lessons, special events, and important dates.</p>
                </article>
            </section>

            <section class="content-grid two-col">
                <article class="panel-card">
                    <h3>Bible Stories</h3>
                    <p>David and Goliath</p>
                    <p>Noah’s Ark</p>
                </article>
                <article class="panel-card">
                    <h3>Memory Verses</h3>
                    <?php if (!empty($memoryVerses)): ?>
                        <?php foreach ($memoryVerses as $verse): ?>
                            <p><?php echo htmlspecialchars($verse['reference'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Psalm 119:11</p>
                        <p>Proverbs 3:5</p>
                    <?php endif; ?>
                </article>
                <article class="panel-card">
                    <h3>Songs</h3>
                    <p>Jesus Loves Me</p>
                    <p>This Little Light of Mine</p>
                </article>
                <article class="panel-card">
                    <h3>Plays</h3>
                    <p>Daniel in the Lions’ Den</p>
                    <p>The Good Samaritan</p>
                </article>
            </section>

            <section class="gallery-card">
                <div class="gallery-header">
                    <h3>Gallery</h3>
                    <a href="#">View all</a>
                </div>
                <div class="gallery-strip">
                    <div class="gallery-item">
                        <img src="images/kids.jpg" alt="Children in Sunday school">
                        <p>Sunday School</p>
                    </div>
                    <div class="gallery-item">
                        <img src="images/kids.jpg" alt="Children in Sunday school">
                        <p>Memory Verse Time</p>
                    </div>
                    <div class="gallery-item">
                        <img src="images/kids.jpg" alt="Children in Sunday school">
                        <p>Music and Praise</p>
                    </div>
                </div>
            </section>

            <footer class="footer">
                <p>© 2026 AGC Bomet Region Sunday School</p>
            </footer>
        </main>
    </div>

    <script src="script.js"></script>
    <script>
        (function(){
            var btn = document.getElementById('newEntryBtn');
            var menu = document.getElementById('newEntryMenu');
            if (!btn || !menu) return;

            btn.addEventListener('click', function(e){
                var expanded = btn.getAttribute('aria-expanded') === 'true';
                btn.setAttribute('aria-expanded', String(!expanded));
                menu.hidden = expanded;
                if (!expanded) {
                    var first = menu.querySelector('a'); if (first) first.focus();
                }
            });

            document.addEventListener('click', function(e){
                if (!btn.contains(e.target) && !menu.contains(e.target)) {
                    btn.setAttribute('aria-expanded','false');
                    menu.hidden = true;
                }
            });

            document.addEventListener('keydown', function(e){
                if (e.key === 'Escape') { btn.setAttribute('aria-expanded','false'); menu.hidden = true; btn.focus(); }
            });
        })();
    </script>
</body>
</html>
