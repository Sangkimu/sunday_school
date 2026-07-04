<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/db.php';

$message = $_SESSION['teachers_message'] ?? ''; 
$messageType = $_SESSION['teachers_message_type'] ?? 'success';
unset($_SESSION['teachers_message'], $_SESSION['teachers_message_type']);

$dbError = '';
$editTeacher = null;
$teachers = [];
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? ''), '/');
$stats = [
    'total' => 0,
    'active' => 0,
    'inactive' => 0,
    'roles_count' => 0,
];

try {
    $db = db_connect();

    $teachers = db_fetch_all($db, 'SELECT teacher_id, full_name, role, phone, email, status FROM teachers ORDER BY full_name');

    $stats['total'] = count($teachers);
    $stats['active'] = count(array_filter($teachers, static fn ($t) => strtolower((string) ($t['status'] ?? '')) === 'active'));
    $stats['inactive'] = $stats['total'] - $stats['active'];
    $stats['roles_count'] = (int) db_scalar($db, 'SELECT COUNT(DISTINCT role) FROM teachers WHERE role IS NOT NULL');

    if (isset($_GET['edit']) && ctype_digit((string) $_GET['edit'])) {
        $editTeacher = db_fetch_one($db, 'SELECT teacher_id, full_name, role, phone, email, status FROM teachers WHERE teacher_id = ?', [(int) $_GET['edit']]);
    }
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teachers | AGC Bomet Area</title>
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
                <a class="nav-item active" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/teachers.php">Teachers</a>
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/attendance.php">Attendance</a>
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/classes.php">Classes</a>
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/bible-stories.php">Bible Stories</a>
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
                        <h2>Teachers Records</h2>
                    </div>
                </div>
                <div class="top-nav-links">
                    <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/logout.php">Log Out</a>
                </div>
            </header>

            <section class="page-header">
                <div>
                    <p class="eyebrow">Teachers</p>
                    <h3>All Sunday school teachers</h3>
                    <p>Track teaching assignments, contact details, and availability.</p>
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
                    <h4>Total Teachers</h4>
                    <span><?php echo number_format($stats['total']); ?></span>
                </article>
                <article class="summary-card">
                    <h4>Active</h4>
                    <span><?php echo number_format($stats['active']); ?></span>
                </article>
                <article class="summary-card">
                    <h4>Inactive</h4>
                    <span><?php echo number_format($stats['inactive']); ?></span>
                </article>
                <article class="summary-card">
                    <h4>Roles</h4>
                    <span><?php echo number_format($stats['roles_count']); ?></span>
                </article>
            </section>

            <section class="table-card">
                <div class="table-header">
                    <h3><?php echo $editTeacher ? 'Edit Teacher' : 'Add New Teacher'; ?></h3>
                </div>
                <form action="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/teachers_handler.php" method="post" class="panel-card">
                    <input type="hidden" name="action" value="<?php echo $editTeacher ? 'edit' : 'add'; ?>">
                    <?php if ($editTeacher): ?>
                        <input type="hidden" name="teacher_id" value="<?php echo (int) $editTeacher['teacher_id']; ?>">
                    <?php endif; ?>
                    <div class="children-grid">
                        <div>
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($editTeacher['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required placeholder="e.g., Pastor Jane">
                        </div>
                        <div>
                            <label for="role">Role</label>
                            <input type="text" id="role" name="role" value="<?php echo htmlspecialchars($editTeacher['role'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required placeholder="e.g., Lead Teacher, Assistant">
                        </div>
                        <div>
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($editTeacher['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="e.g., 0722 111 222">
                        </div>
                        <div>
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($editTeacher['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="e.g., jane@example.com">
                        </div>
                        <div>
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="Active" <?php echo (($editTeacher['status'] ?? 'Active') === 'Active') ? 'selected' : ''; ?>>Active</option>
                                <option value="Inactive" <?php echo (($editTeacher['status'] ?? '') === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                <option value="On Leave" <?php echo (($editTeacher['status'] ?? '') === 'On Leave') ? 'selected' : ''; ?>>On Leave</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-top: 1rem;">
                        <button type="submit" class="primary-btn"><?php echo $editTeacher ? 'Save Changes' : 'Add Teacher'; ?></button>
                        <?php if ($editTeacher): ?>
                            <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/teachers.php" class="text-link">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>

            <section class="table-card">
                <div class="table-header">
                    <h3>Teachers Details</h3>
                </div>
                <div class="table-wrapper">
                    <table class="children-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($teachers)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 2rem;">No teachers yet. Add one to get started.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($teachers as $teacher): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($teacher['full_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($teacher['role'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($teacher['phone'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($teacher['email'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <?php 
                                                $status = $teacher['status'] ?? 'Active';
                                                $statusClass = strtolower($status) === 'active' ? 'active' : 'pending';
                                            ?>
                                            <span class="status <?php echo htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></span>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 0.5rem;">
                                                <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/teachers.php?edit=<?php echo (int) $teacher['teacher_id']; ?>" class="text-link">Edit</a>
                                                <form action="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/teachers_handler.php" method="post" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="teacher_id" value="<?php echo (int) $teacher['teacher_id']; ?>">
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
