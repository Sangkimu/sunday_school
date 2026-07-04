<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/db.php';

$message = $_SESSION['songs_message'] ?? '';
$messageType = $_SESSION['songs_message_type'] ?? 'success';
unset($_SESSION['songs_message'], $_SESSION['songs_message_type']);

$dbError = '';
$editSong = null;
$songs = [];
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? ''), '/');
$stats = [
    'total' => 0,
    'categories' => 0,
    'audio' => 0,
    'video' => 0,
    'languages' => 0,
];

try {
    $db = db_connect();
    $songs = db_fetch_all($db, 'SELECT song_id, title, category, language, has_audio, has_video FROM songs ORDER BY category, title');

    $stats['total'] = count($songs);
    $stats['categories'] = (int) db_scalar($db, 'SELECT COUNT(DISTINCT category) FROM songs');
    $stats['audio'] = (int) db_scalar($db, 'SELECT COUNT(*) FROM songs WHERE has_audio = 1');
    $stats['video'] = (int) db_scalar($db, 'SELECT COUNT(*) FROM songs WHERE has_video = 1');
    $stats['languages'] = (int) db_scalar($db, 'SELECT COUNT(DISTINCT language) FROM songs');

    if (isset($_GET['edit']) && ctype_digit((string) $_GET['edit'])) {
        $editSong = db_fetch_one($db, 'SELECT song_id, title, category, language, has_audio, has_video FROM songs WHERE song_id = ?', [(int) $_GET['edit']]);
    }
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Songs | AGC Bomet Area</title>
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
                <a class="nav-item active" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/songs.php">Songs</a>
                <a class="nav-item" href="#">Plays</a>
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/calendar.php">Calendar of Events</a>
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/announcements.php">Announcements</a>
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
                        <h2>Songs Management</h2>
                    </div>
                </div>
                <div class="top-nav-links">
                    <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/logout.php">Log Out</a>
                </div>
            </header>

            <section class="page-header">
                <div>
                    <p class="eyebrow">Songs</p>
                    <h3>Manage the song library</h3>
                    <p>Create and edit song records with category, language, audio, and video availability.</p>
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
                    <p class="card-label">Total Songs</p>
                    <p class="card-number"><?php echo number_format($stats['total']); ?></p>
                </article>
                <article class="summary-card">
                    <p class="card-label">Categories</p>
                    <p class="card-number"><?php echo number_format($stats['categories']); ?></p>
                </article>
                <article class="summary-card">
                    <p class="card-label">With Audio</p>
                    <p class="card-number"><?php echo number_format($stats['audio']); ?></p>
                </article>
                <article class="summary-card">
                    <p class="card-label">With Video</p>
                    <p class="card-number"><?php echo number_format($stats['video']); ?></p>
                </article>
            </section>

            <section class="table-card">
                <div class="table-header">
                    <h3><?php echo $editSong ? 'Edit Song' : 'Add New Song'; ?></h3>
                </div>
                <form action="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/songs_handler.php" method="post" class="panel-card">
                    <input type="hidden" name="action" value="<?php echo $editSong ? 'edit' : 'add'; ?>">
                    <?php if ($editSong): ?>
                        <input type="hidden" name="song_id" value="<?php echo (int) $editSong['song_id']; ?>">
                    <?php endif; ?>

                    <div class="content-grid two-col">
                        <article class="panel-card">
                            <label for="title">Title</label>
                            <input id="title" name="title" type="text" value="<?php echo htmlspecialchars($editSong['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                        </article>
                        <article class="panel-card">
                            <label for="category">Category</label>
                            <input id="category" name="category" type="text" value="<?php echo htmlspecialchars($editSong['category'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                        </article>
                    </div>

                    <div class="content-grid two-col">
                        <article class="panel-card">
                            <label for="language">Language</label>
                            <input id="language" name="language" type="text" value="<?php echo htmlspecialchars($editSong['language'] ?? 'English', ENT_QUOTES, 'UTF-8'); ?>" required>
                        </article>
                        <article class="panel-card">
                            <div class="checkbox-group">
                                <label>
                                    <input type="checkbox" name="has_audio" value="1" <?php echo isset($editSong['has_audio']) && $editSong['has_audio'] ? 'checked' : ''; ?>>
                                    Has Audio
                                </label>
                                <label>
                                    <input type="checkbox" name="has_video" value="1" <?php echo isset($editSong['has_video']) && $editSong['has_video'] ? 'checked' : ''; ?>>
                                    Has Video
                                </label>
                            </div>
                        </article>
                    </div>

                    <div class="section-heading">
                        <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/songs.php" class="text-link">Back to list</a>
                        <button type="submit" class="primary-btn"><?php echo $editSong ? 'Update Song' : 'Save Song'; ?></button>
                    </div>
                </form>
            </section>

            <section class="table-card">
                <div class="table-header">
                    <h3>Song Library</h3>
                </div>
                <div class="table-wrapper">
                    <table class="children-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Language</th>
                                <th>Audio</th>
                                <th>Video</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($songs)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 2rem;">No songs found. Add a song to get started.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($songs as $song): ?>
                                    <tr>
                                        <td><?php echo (int) $song['song_id']; ?></td>
                                        <td><?php echo htmlspecialchars($song['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($song['category'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($song['language'] ?? 'English', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo $song['has_audio'] ? '✓' : ''; ?></td>
                                        <td><?php echo $song['has_video'] ? '✓' : ''; ?></td>
                                        <td>
                                            <div class="top-nav-links">
                                                <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/songs.php?edit=<?php echo (int) $song['song_id']; ?>" class="text-link">Edit</a>
                                                <form action="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/songs_handler.php" method="post" style="display:inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="song_id" value="<?php echo (int) $song['song_id']; ?>">
                                                    <button type="submit" class="button text-link" onclick="return confirm('Delete this song?');">Delete</button>
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
