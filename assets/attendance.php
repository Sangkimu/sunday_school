<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/db.php';

$message = $_SESSION['attendance_message'] ?? ''; 
$messageType = $_SESSION['attendance_message_type'] ?? 'success';
unset($_SESSION['attendance_message'], $_SESSION['attendance_message_type']);

$dbError = '';
$editAttendance = null;
$attendanceRecords = [];
$children = [];
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? ''), '/');
$role = trim((string) ($_SESSION['user']['role'] ?? ''));
$canManageAttendance = can_manage_module('attendance', $role);

try {
    $db = db_connect();
    $attendanceRecords = db_fetch_all($db, 'SELECT ar.attendance_id, ar.child_id, ar.attendance_date, ar.status, ar.remarks, c.full_name, cl.class_name FROM attendance_records ar LEFT JOIN children c ON ar.child_id = c.child_id LEFT JOIN classes cl ON c.class_id = cl.class_id ORDER BY ar.attendance_date DESC, ar.attendance_id DESC');
    $children = db_fetch_all($db, 'SELECT child_id, full_name FROM children ORDER BY full_name');

    if (isset($_GET['edit']) && ctype_digit((string) $_GET['edit'])) {
        $editAttendance = db_fetch_one($db, 'SELECT attendance_id, child_id, attendance_date, status, remarks FROM attendance_records WHERE attendance_id = ?', [(int) $_GET['edit']]);
    }
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

$totalRecords = count($attendanceRecords);
$presentToday = count(array_filter($attendanceRecords, static fn ($record) => strtolower((string) ($record['status'] ?? '')) === 'present'));
$absentToday = count(array_filter($attendanceRecords, static fn ($record) => strtolower((string) ($record['status'] ?? '')) === 'absent'));
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance | AGC Bomet Area</title>
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
                <a class="nav-item active" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/attendance.php">Attendance</a>
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
                        <h2>Attendance Records</h2>
                    </div>
                </div>
                <div class="top-nav-links">
                    <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/logout.php">Log Out</a>
                </div>
            </header>

            <section class="page-header">
                <div>
                    <p class="eyebrow">Attendance</p>
                    <h3>Daily attendance tracking</h3>
                    <p>Record child attendance with class and status details.</p>
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
                    <h4>Total Records</h4>
                    <span><?php echo number_format($totalRecords); ?></span>
                </article>
                <article class="summary-card">
                    <h4>Present</h4>
                    <span><?php echo number_format($presentToday); ?></span>
                </article>
                <article class="summary-card">
                    <h4>Absent</h4>
                    <span><?php echo number_format($absentToday); ?></span>
                </article>
            </section>

            <section class="children-section">
                <div class="section-heading">
                    <h3><?php echo $editAttendance ? 'Edit Attendance Record' : 'Create Attendance Record'; ?></h3>
                    <?php if ($editAttendance): ?>
                        <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/attendance.php" class="text-link">Cancel Edit</a>
                    <?php endif; ?>
                </div>

                <?php if ($canManageAttendance): ?>
                    <form action="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/attendance_handler.php" method="post">
                        <input type="hidden" name="action" value="<?php echo $editAttendance ? 'edit' : 'add'; ?>">
                        <?php if ($editAttendance): ?>
                            <input type="hidden" name="attendance_id" value="<?php echo (int) $editAttendance['attendance_id']; ?>">
                        <?php endif; ?>

                        <div class="content-grid two-col">
                            <article class="panel-card">
                                <label for="child_id">Child</label>
                                <select id="child_id" name="child_id" required>
                                    <option value="">Select child</option>
                                    <?php foreach ($children as $child): ?>
                                        <option value="<?php echo (int) $child['child_id']; ?>" <?php echo (($editAttendance['child_id'] ?? '') == $child['child_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($child['full_name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </article>
                            <article class="panel-card">
                                <label for="attendance_date">Date</label>
                                <input id="attendance_date" name="attendance_date" type="date" value="<?php echo htmlspecialchars($editAttendance['attendance_date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>" required>
                            </article>
                        </div>

                        <div class="content-grid two-col">
                            <article class="panel-card">
                                <label for="status">Status</label>
                                <select id="status" name="status">
                                    <option value="Present" <?php echo (($editAttendance['status'] ?? 'Present') === 'Present') ? 'selected' : ''; ?>>Present</option>
                                    <option value="Absent" <?php echo (($editAttendance['status'] ?? 'Present') === 'Absent') ? 'selected' : ''; ?>>Absent</option>
                                    <option value="Late" <?php echo (($editAttendance['status'] ?? 'Present') === 'Late') ? 'selected' : ''; ?>>Late</option>
                                </select>
                            </article>
                            <article class="panel-card">
                                <label for="remarks">Remarks</label>
                                <textarea id="remarks" name="remarks" rows="4"><?php echo htmlspecialchars($editAttendance['remarks'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </article>
                        </div>

                        <div class="section-heading">
                            <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/attendance.php" class="text-link">Back to list</a>
                            <button type="submit" class="primary-btn"><?php echo $editAttendance ? 'Update Record' : 'Save Record'; ?></button>
                        </div>
                    </form>
                <?php else: ?>
                    <p class="error-banner">Only teachers and coordinators can create or update attendance records.</p>
                <?php endif; ?>
            </section>

            <section class="table-card">
                <div class="table-header">
                    <h3>Attendance Details</h3>
                </div>
                <div class="table-wrapper">
                    <table class="children-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Child</th>
                                <th>Class</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Remarks</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($attendanceRecords)): ?>
                                <tr>
                                    <td colspan="7">No attendance records found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($attendanceRecords as $record): ?>
                                    <tr>
                                        <td><?php echo (int) $record['attendance_id']; ?></td>
                                        <td><?php echo htmlspecialchars($record['full_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($record['class_name'] ?? 'Unassigned', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($record['attendance_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><span class="status <?php echo strtolower((string) ($record['status'] ?? '')) === 'present' ? 'active' : 'pending'; ?>"><?php echo htmlspecialchars($record['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td><?php echo htmlspecialchars($record['remarks'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <?php if ($canManageAttendance): ?>
                                                <div class="top-nav-links">
                                                    <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/attendance.php?edit=<?php echo (int) $record['attendance_id']; ?>" class="text-link">Edit</a>
                                                    <form action="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/attendance_handler.php" method="post" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="attendance_id" value="<?php echo (int) $record['attendance_id']; ?>">
                                                        <button type="submit" class="button text-link" onclick="return confirm('Delete this attendance record?');">Delete</button>
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
        </main>
    </div>
    <script src="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/script.js"></script>
</body>
</html>
