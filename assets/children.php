<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/db.php';

$message = $_SESSION['children_message'] ?? ''; 
$messageType = $_SESSION['children_message_type'] ?? 'success';
unset($_SESSION['children_message'], $_SESSION['children_message_type']);

$dbError = '';
$editChild = null;
$children = [];
$classes = [];
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? ''), '/');
$stats = [
    'total' => 0,
    'boys' => 0,
    'girls' => 0,
    'present_today' => 0,
];

try {
    $db = db_connect();

    $classes = db_fetch_all($db, 'SELECT class_id, class_name FROM classes ORDER BY class_name');
    $children = db_fetch_all($db, 'SELECT c.child_id, c.full_name, c.gender, c.age, c.guardian_name, c.phone, c.status, c.date_registered, cl.class_name FROM children c LEFT JOIN classes cl ON c.class_id = cl.class_id ORDER BY c.full_name');

    $stats['total'] = count($children);
    $stats['boys'] = count(array_filter($children, static fn ($child) => strtolower((string) ($child['gender'] ?? '')) === 'male'));
    $stats['girls'] = count(array_filter($children, static fn ($child) => strtolower((string) ($child['gender'] ?? '')) === 'female'));
    $stats['present_today'] = (int) db_scalar($db, "SELECT COUNT(*) FROM attendance_records WHERE attendance_date = CURDATE() AND LOWER(status) = 'present'");

    if (isset($_GET['edit']) && ctype_digit((string) $_GET['edit'])) {
        $editChild = db_fetch_one($db, 'SELECT child_id, full_name, gender, age, class_id, guardian_name, phone, status FROM children WHERE child_id = ?', [(int) $_GET['edit']]);
    }
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Children | AGC Bomet Region</title>
    <link rel="icon" type="image/jpeg" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/images/logo.jpg">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/style.css?v=2">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="brand">
                <img src="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/images/mu-logo.jpg" alt="AGC Bomet Region logo" class="brand-logo" onerror="this.onerror=null; this.src='<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/images/logo.jpg';">
                <div>
                    <h1>AGC Bomet Region</h1>
                    <p>Sunday School Dashboard</p>
                </div>
            </div>

            <nav class="sidebar-nav">
                <p class="nav-label">Main</p>
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/dashboard.php">Dashboard</a>
                <a class="nav-item active" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/children.php">Children</a>
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/teachers.php">Teachers</a>
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
                        <h2>Children Records</h2>
                    </div>
                </div>
                <div class="top-nav-links">
                    <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/logout.php">Log Out</a>
                </div>
            </header>

            <section class="page-header">
                <div>
                    <p class="eyebrow">Children</p>
                    <h3>All registered children</h3>
                    <p>Manage classes, guardians, and attendance in one place.</p>
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
                    <h4>Total Children</h4>
                    <span><?php echo number_format($stats['total']); ?></span>
                </article>
                <article class="summary-card">
                    <h4>Boys</h4>
                    <span><?php echo number_format($stats['boys']); ?></span>
                </article>
                <article class="summary-card">
                    <h4>Girls</h4>
                    <span><?php echo number_format($stats['girls']); ?></span>
                </article>
                <article class="summary-card">
                    <h4>Present Today</h4>
                    <span><?php echo number_format($stats['present_today']); ?></span>
                </article>
            </section>

            <section class="table-card">
                <div class="table-header">
                    <h3><?php echo $editChild ? 'Edit Child' : 'Add Child'; ?></h3>
                </div>
                <form action="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/children_handler.php" method="post" class="panel-card">
                    <input type="hidden" name="action" value="<?php echo $editChild ? 'edit' : 'add'; ?>">
                    <?php if ($editChild): ?>
                        <input type="hidden" name="child_id" value="<?php echo (int) $editChild['child_id']; ?>">
                    <?php endif; ?>
                    <div class="children-grid">
                        <div>
                            <label for="full_name">Full name</label>
                            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($editChild['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div>
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender">
                                <option value="" <?php echo (($editChild['gender'] ?? '') === '') ? 'selected' : ''; ?>>Select</option>
                                <option value="Male" <?php echo (($editChild['gender'] ?? '') === 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo (($editChild['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>
                        <div>
                            <label for="age">Age</label>
                            <input type="number" id="age" name="age" min="3" max="18" value="<?php echo htmlspecialchars((string) ($editChild['age'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div>
                            <label for="class_id">Class</label>
                            <select id="class_id" name="class_id">
                                <option value="">None</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo (int) $class['class_id']; ?>" <?php echo (($editChild['class_id'] ?? null) == $class['class_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($class['class_name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="guardian_name">Guardian</label>
                            <input type="text" id="guardian_name" name="guardian_name" value="<?php echo htmlspecialchars($editChild['guardian_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div>
                            <label for="phone">Phone</label>
                            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($editChild['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div>
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="Active" <?php echo (($editChild['status'] ?? 'Active') === 'Active') ? 'selected' : ''; ?>>Active</option>
                                <option value="Present" <?php echo (($editChild['status'] ?? '') === 'Present') ? 'selected' : ''; ?>>Present</option>
                                <option value="Absent" <?php echo (($editChild['status'] ?? '') === 'Absent') ? 'selected' : ''; ?>>Absent</option>
                                <option value="Pending" <?php echo (($editChild['status'] ?? '') === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-top: 1rem;">
                        <button type="submit" class="primary-btn"><?php echo $editChild ? 'Save Changes' : 'Add Child'; ?></button>
                        <?php if ($editChild): ?>
                            <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/children.php" class="text-link">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>

            <section class="table-card">
                <div class="table-header">
                    <h3>Children Details</h3>
                </div>
                <div class="table-wrapper">
                    <table class="children-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Class</th>
                                <th>Guardian</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($children)): ?>
                                <tr>
                                    <td colspan="7">No children found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($children as $child): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($child['full_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($child['age'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($child['class_name'] ?? 'Unassigned'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($child['guardian_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($child['phone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><span class="status <?php echo strtolower((string) ($child['status'] ?? 'active')) === 'active' ? 'active' : 'pending'; ?>"><?php echo htmlspecialchars((string) ($child['status'] ?? 'Active'), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td>
                                            <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/children.php?edit=<?php echo (int) $child['child_id']; ?>" class="text-link">Edit</a>
                                            <form action="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/children_handler.php" method="post" style="display:inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="child_id" value="<?php echo (int) $child['child_id']; ?>">
                                                <button type="submit" class="text-link" onclick="return confirm('Delete this child?')">Delete</button>
                                            </form>
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
