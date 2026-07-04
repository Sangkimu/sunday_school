<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/db.php';

$message = $_SESSION['calendar_message'] ?? ''; 
$messageType = $_SESSION['calendar_message_type'] ?? 'success';
unset($_SESSION['calendar_message'], $_SESSION['calendar_message_type']);

$events = [];
$editEvent = null;
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? ''), '/');

try {
    $db = db_connect();
    $events = db_fetch_all($db, 'SELECT event_id, event_date, title, description FROM calendar_events ORDER BY event_date DESC, event_id DESC');

    if (isset($_GET['edit_event']) && ctype_digit((string) $_GET['edit_event'])) {
        $editEvent = db_fetch_one($db, 'SELECT event_id, event_date, title, description FROM calendar_events WHERE event_id = ?', [(int) $_GET['edit_event']]);
    }

    $upcomingEvent = db_fetch_one($db, 'SELECT event_date, title FROM calendar_events WHERE event_date >= CURDATE() ORDER BY event_date LIMIT 1');
    $totalEvents = count($events);
    $futureEvents = db_fetch_all($db, 'SELECT title FROM calendar_events WHERE event_date >= CURDATE() ORDER BY event_date LIMIT 3');
} catch (Throwable $e) {
    $message = $e->getMessage();
    $messageType = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar of Events | AGC Bomet Area</title>
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
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/memory-verses.php">Memory Verses</a>
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/songs.php">Songs</a>
                <a class="nav-item" href="#">Plays</a>
                <a class="nav-item active" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/calendar.php">Calendar of Events</a>
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
                        <h2>Calendar of Events</h2>
                    </div>
                </div>
                <div class="top-nav-links">
                    <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/logout.php">Log Out</a>
                </div>
            </header>

            <section class="page-header">
                <div>
                    <p class="eyebrow">Planning</p>
                    <h3>Upcoming Sunday School Activities</h3>
                    <p>Manage event dates, titles, and descriptions for the children&apos;s program.</p>
                </div>
                <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/dashboard.php" class="text-link">← Back to Dashboard</a>
            </section>

            <?php if ($message !== ''): ?>
                <section class="<?php echo $messageType === 'error' ? 'error-banner' : 'success-banner'; ?>">
                    <p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
                </section>
            <?php endif; ?>

            <section class="summary-grid">
                <article class="summary-card">
                    <p class="card-label">Total Events</p>
                    <p class="card-number"><?php echo number_format($totalEvents); ?></p>
                </article>
                <article class="summary-card">
                    <p class="card-label">Next Event</p>
                    <p class="card-number"><?php echo $upcomingEvent ? htmlspecialchars(date('j M Y', strtotime($upcomingEvent['event_date'])), ENT_QUOTES, 'UTF-8') : 'N/A'; ?></p>
                </article>
                <article class="summary-card">
                    <p class="card-label">Activity Preview</p>
                    <p class="card-number"><?php echo $upcomingEvent ? htmlspecialchars($upcomingEvent['title'], ENT_QUOTES, 'UTF-8') : 'No upcoming events'; ?></p>
                </article>
            </section>

            <section class="children-section">
                <div class="section-heading">
                    <h3><?php echo $editEvent ? 'Edit Event' : 'Add Event'; ?></h3>
                    <?php if ($editEvent): ?>
                        <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/calendar.php" class="text-link">Cancel Edit</a>
                    <?php endif; ?>
                </div>

                <form action="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/calendar_handler.php" method="post">
                    <input type="hidden" name="action" value="<?php echo $editEvent ? 'edit_event' : 'add_event'; ?>">
                    <?php if ($editEvent): ?>
                        <input type="hidden" name="event_id" value="<?php echo (int) $editEvent['event_id']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="title">Event Title</label>
                        <input id="title" name="title" type="text" required value="<?php echo htmlspecialchars($editEvent['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="event_date">Date</label>
                        <input id="event_date" name="event_date" type="date" required value="<?php echo htmlspecialchars($editEvent['event_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($editEvent['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <div class="section-heading">
                        <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/calendar.php" class="text-link">Back to list</a>
                        <button type="submit" class="primary-btn"><?php echo $editEvent ? 'Update Event' : 'Save Event'; ?></button>
                    </div>
                </form>
            </section>

            <section class="table-card">
                <div class="table-header">
                    <h3>Events Schedule</h3>
                    <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/calendar.php?add=1" class="text-link">Add Event</a>
                </div>
                <div class="table-wrapper">
                    <table class="children-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Event</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($events)): ?>
                                <tr><td colspan="4">No events found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($events as $event): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(date('j M Y', strtotime($event['event_date'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo nl2br(htmlspecialchars($event['description'] ?: '-', ENT_QUOTES, 'UTF-8')); ?></td>
                                        <td>
                                            <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/calendar.php?edit_event=<?php echo (int) $event['event_id']; ?>" class="text-link">Edit</a>
                                            <form action="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/calendar_handler.php" method="post" style="display:inline;">
                                                <input type="hidden" name="action" value="delete_event">
                                                <input type="hidden" name="event_id" value="<?php echo (int) $event['event_id']; ?>">
                                                <button type="submit" class="text-link" onclick="return confirm('Delete this event?');">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <footer class="footer">
                <p>© 2026 AGC Bomet Area Sunday School</p>
            </footer>
        </main>
    </div>
</body>
</html>
