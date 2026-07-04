<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/db.php';

$message = $_SESSION['certificates_message'] ?? ''; 
$messageType = $_SESSION['certificates_message_type'] ?? 'success';
unset($_SESSION['certificates_message'], $_SESSION['certificates_message_type']);

$dbError = '';
$selectedCertificate = null;
$awards = [];
$certificates = [];
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? ''), '/');

try {
    $db = db_connect();
    $awards = db_fetch_all(
        $db,
        'SELECT a.award_id, a.child_id, a.award_date, c.full_name AS child_name, ac.category_name FROM awards a LEFT JOIN children c ON a.child_id = c.child_id LEFT JOIN award_categories ac ON a.category_id = ac.category_id ORDER BY a.award_date DESC, a.award_id DESC'
    );

    $certificates = db_fetch_all(
        $db,
        'SELECT certificate_id, award_id, child_name, award_title, award_date, teacher_signature, church_stamp, status FROM certificates ORDER BY certificate_id DESC'
    );

    if (isset($_GET['edit_certificate']) && ctype_digit((string) $_GET['edit_certificate'])) {
        $selectedCertificate = db_fetch_one(
            $db,
            'SELECT certificate_id, award_id, child_name, award_title, award_date, teacher_signature, church_stamp, status FROM certificates WHERE certificate_id = ?',
            [(int) $_GET['edit_certificate']]
        );
    }
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

$certificateCount = count($certificates);
$awardCount = count($awards);
$awardedChildren = count(array_unique(array_column($awards, 'child_id')));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificates | AGC Bomet Area</title>
    <link rel="icon" type="image/jpeg" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/images/logo.jpg">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/style.css?v=3">
    <style>
        .certificate-container { background: white; border: 3px solid #1f4f8a; border-radius: 8px; padding: 40px; margin: 20px 0; text-align: center; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.1); max-width: 640px; margin-left: auto; margin-right: auto; }
        .certificate-header { font-size: 28px; font-weight: 700; color: #ad0051; margin-bottom: 30px; letter-spacing: 2px; }
        .certificate-body { font-family: Georgia, serif; font-size: 16px; color: #243447; line-height: 2; }
        .certificate-body p { margin: 15px 0; }
        .certificate-child-name { font-size: 24px; font-weight: 700; color: #1f4f8a; margin: 20px 0; border-bottom: 2px solid #1f4f8a; padding-bottom: 10px; }
        .certificate-award { font-size: 18px; font-style: italic; color: #ad0051; margin: 20px 0; }
        .certificate-date { font-size: 14px; color: #6b778d; margin-top: 30px; }
        .certificate-signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 40px; padding-top: 30px; border-top: 1px solid #e7ebf0; }
        .signature-block { text-align: center; }
        .signature-line { border-top: 2px solid #243447; margin-top: 40px; padding-top: 10px; font-size: 12px; color: #6b778d; min-height: 40px; }
        .form-section { background: #f8fafc; border-radius: 8px; padding: 24px; margin: 20px 0; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #243447; font-size: 14px; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #e7ebf0; border-radius: 6px; font-size: 14px; font-family: Arial, sans-serif; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #1f4f8a; box-shadow: 0 0 0 3px rgba(31, 79, 138, 0.1); }
        .button-group { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; margin-top: 20px; }
        .btn-primary, .btn-print, .primary-btn { background: #1f4f8a; color: white; padding: 12px 24px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px; }
        .btn-print { background: #ad0051; }
        .btn-primary:hover, .primary-btn:hover, .btn-print:hover { opacity: 0.95; }
        .table-card { margin-top: 20px; }
        @media print { .form-section, .button-group, .page-header, .top-bar, .sidebar, footer, .table-card { display: none; } .certificate-container { margin: 0; padding: 20px; box-shadow: none; border: 2px solid #1f4f8a; } body { background: white; } }
    </style>
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
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/announcements.php">Announcements</a>
                <p class="nav-label">Management</p>
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/awards.php">Awards</a>
                <a class="nav-item active" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/certificates.php">Certificates</a>
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
                        <h2>Certificate Generation</h2>
                    </div>
                </div>
                <div class="top-nav-links">
                    <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/logout.php">Log Out</a>
                </div>
            </header>
            <section class="page-header">
                <div>
                    <p class="eyebrow">Certificates</p>
                    <h3>Generate Award Certificates</h3>
                    <p>Use existing awards to generate and save printable certificate records.</p>
                </div>
                <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/awards.php" class="text-link">← Back to Awards</a>
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
                <article class="summary-card"><p class="card-label">Certificates Saved</p><p class="card-number"><?php echo number_format($certificateCount); ?></p></article>
                <article class="summary-card"><p class="card-label">Available Awards</p><p class="card-number"><?php echo number_format($awardCount); ?></p></article>
                <article class="summary-card"><p class="card-label">Children Awarded</p><p class="card-number"><?php echo number_format($awardedChildren); ?></p></article>
            </section>
            <section class="children-section">
                <div class="section-heading">
                    <h3><?php echo $selectedCertificate ? 'Edit Certificate' : 'Generate Certificate'; ?></h3>
                    <?php if ($selectedCertificate): ?>
                        <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/certificates.php" class="text-link">Cancel Edit</a>
                    <?php endif; ?>
                </div>
                <form action="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/certificates_handler.php" method="post">
                    <input type="hidden" name="action" value="<?php echo $selectedCertificate ? 'edit_certificate' : 'add_certificate'; ?>">
                    <?php if ($selectedCertificate): ?>
                        <input type="hidden" name="certificate_id" value="<?php echo (int) $selectedCertificate['certificate_id']; ?>">
                    <?php endif; ?>
                    <div class="content-grid two-col">
                        <article class="panel-card">
                            <label for="award_id">Award</label>
                            <select id="award_id" name="award_id" required>
                                <option value="">Select award</option>
                                <?php foreach ($awards as $award): ?>
                                    <option value="<?php echo (int) $award['award_id']; ?>" data-award-date="<?php echo htmlspecialchars($award['award_date'], ENT_QUOTES, 'UTF-8'); ?>" data-child-name="<?php echo htmlspecialchars($award['child_name'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo ((int) ($selectedCertificate['award_id'] ?? 0) === (int) $award['award_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($award['child_name'] . ' — ' . $award['category_name'] . ' (' . date('d M Y', strtotime($award['award_date'])) . ')', ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </article>
                        <article class="panel-card">
                            <label for="award_title">Award Title</label>
                            <input id="award_title" name="award_title" type="text" value="<?php echo htmlspecialchars($selectedCertificate['award_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                        </article>
                    </div>
                    <div class="content-grid two-col">
                        <article class="panel-card">
                            <label for="award_date">Award Date</label>
                            <input id="award_date" name="award_date" type="date" value="<?php echo htmlspecialchars($selectedCertificate['award_date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>" required>
                        </article>
                        <article class="panel-card">
                            <label for="teacher_signature">Teacher Signature</label>
                            <input id="teacher_signature" name="teacher_signature" type="text" value="<?php echo htmlspecialchars($selectedCertificate['teacher_signature'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="e.g., Mrs. Mary Kipchoge">
                        </article>
                    </div>
                    <div class="content-grid two-col">
                        <article class="panel-card">
                            <label for="church_stamp">Church Stamp / Seal</label>
                            <input id="church_stamp" name="church_stamp" type="text" value="<?php echo htmlspecialchars($selectedCertificate['church_stamp'] ?? 'AGC Bomet Area', ENT_QUOTES, 'UTF-8'); ?>">
                        </article>
                        <article class="panel-card">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="Printed" <?php echo (($selectedCertificate['status'] ?? 'Printed') === 'Printed') ? 'selected' : ''; ?>>Printed</option>
                                <option value="Pending" <?php echo (($selectedCertificate['status'] ?? 'Printed') === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="Draft" <?php echo (($selectedCertificate['status'] ?? 'Printed') === 'Draft') ? 'selected' : ''; ?>>Draft</option>
                            </select>
                        </article>
                    </div>
                    <div class="section-heading">
                        <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/certificates.php" class="text-link">Back to list</a>
                        <button type="submit" class="primary-btn"><?php echo $selectedCertificate ? 'Update Certificate' : 'Save Certificate'; ?></button>
                    </div>
                </form>
            </section>
            <section class="certificate-container">
                <div class="certificate-header">✓ Certificate of Achievement</div>
                <div class="certificate-body">
                    <p>This certificate is proudly presented to</p>
                    <div class="certificate-child-name" id="displayChildName"><?php echo htmlspecialchars($selectedCertificate['child_name'] ?? 'Child Name', ENT_QUOTES, 'UTF-8'); ?></div>
                    <p>For outstanding performance in</p>
                    <div class="certificate-award" id="displayAwardCategory"><?php echo htmlspecialchars($selectedCertificate['award_title'] ?? 'Award Title', ENT_QUOTES, 'UTF-8'); ?></div>
                    <p>Awarded on</p>
                    <div style="font-size: 16px; font-weight: 600; color: #1f4f8a;" id="displayAwardDate"><?php echo htmlspecialchars(($selectedCertificate['award_date'] ? date('d M Y', strtotime($selectedCertificate['award_date'])) : 'Award Date'), ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="certificate-signatures">
                        <div class="signature-block"><div class="signature-line" id="displayTeacherSignature"><?php echo htmlspecialchars($selectedCertificate['teacher_signature'] ?: 'Teacher Signature', ENT_QUOTES, 'UTF-8'); ?></div></div>
                        <div class="signature-block"><div class="signature-line" id="displayChurchStamp"><?php echo htmlspecialchars($selectedCertificate['church_stamp'] ?? 'Church Stamp / Seal', ENT_QUOTES, 'UTF-8'); ?></div></div>
                    </div>
                    <div class="certificate-date"><p style="margin: 20px 0 0;">AGC Bomet Area Sunday School</p></div>
                </div>
            </section>
            <section class="table-card">
                <div class="table-header"><h3>Recent Certificates</h3></div>
                <div class="table-wrapper">
                    <table class="children-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Child</th>
                                <th>Award Title</th>
                                <th>Award Date</th>
                                <th>Teacher</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($certificates)): ?>
                                <tr><td colspan="7">No certificates found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($certificates as $certificate): ?>
                                    <tr>
                                        <td><?php echo (int) $certificate['certificate_id']; ?></td>
                                        <td><?php echo htmlspecialchars($certificate['child_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($certificate['award_title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars(date('d M Y', strtotime($certificate['award_date'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($certificate['teacher_signature'] ?: '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><span class="status <?php echo strtolower((string) ($certificate['status'] ?? 'Printed')) === 'printed' ? 'active' : 'pending'; ?>"><?php echo htmlspecialchars($certificate['status'] ?? 'Printed', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td>
                                            <div class="top-nav-links">
                                                <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/certificates.php?edit_certificate=<?php echo (int) $certificate['certificate_id']; ?>" class="text-link">Edit</a>
                                                <form action="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/certificates_handler.php" method="post" style="display:inline;">
                                                    <input type="hidden" name="action" value="delete_certificate">
                                                    <input type="hidden" name="certificate_id" value="<?php echo (int) $certificate['certificate_id']; ?>">
                                                    <button type="submit" class="button text-link" onclick="return confirm('Delete this certificate?');">Delete</button>
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
            <footer class="footer"><p>© 2026 AGC Bomet Area Sunday School. All rights reserved.</p></footer>
        </main>
    </div>
    <script>
        function parseDate(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr + 'T00:00:00');
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            return date.getDate() + ' ' + months[date.getMonth()] + ' ' + date.getFullYear();
        }
        function syncPreview() {
            const awardTitle = document.getElementById('award_title')?.value || 'Award Title';
            const awardDate = document.getElementById('award_date')?.value || '';
            const teacher = document.getElementById('teacher_signature')?.value || 'Teacher Signature';
            const churchStamp = document.getElementById('church_stamp')?.value || 'Church Stamp / Seal';
            document.getElementById('displayAwardCategory').textContent = awardTitle;
            document.getElementById('displayAwardDate').textContent = awardDate ? parseDate(awardDate) : 'Award Date';
            document.getElementById('displayTeacherSignature').textContent = teacher;
            document.getElementById('displayChurchStamp').textContent = churchStamp;
        }
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('#award_title, #award_date, #teacher_signature, #church_stamp').forEach(function (element) {
                element.addEventListener('input', syncPreview);
            });
            syncPreview();
        });
    </script>
</body>
</html>
