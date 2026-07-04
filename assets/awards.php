<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/db.php';

$message = $_SESSION['awards_message'] ?? ''; 
$messageType = $_SESSION['awards_message_type'] ?? 'success';
unset($_SESSION['awards_message'], $_SESSION['awards_message_type']);

$dbError = '';
$editCategory = null;
$editAward = null;
$categories = [];
$awards = [];
$children = [];
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? ''), '/');

try {
    $db = db_connect();
    $categories = db_fetch_all($db, 'SELECT category_id, category_name, description, status FROM award_categories ORDER BY category_name');
    $children = db_fetch_all($db, 'SELECT child_id, full_name FROM children ORDER BY full_name');
    $awards = db_fetch_all($db, 'SELECT a.award_id, a.child_id, a.category_id, a.award_date, a.status, c.full_name, ac.category_name FROM awards a LEFT JOIN children c ON a.child_id = c.child_id LEFT JOIN award_categories ac ON a.category_id = ac.category_id ORDER BY a.award_date DESC, a.award_id DESC');

    if (isset($_GET['edit_category']) && ctype_digit((string) $_GET['edit_category'])) {
        $editCategory = db_fetch_one($db, 'SELECT category_id, category_name, description, status FROM award_categories WHERE category_id = ?', [(int) $_GET['edit_category']]);
    }

    if (isset($_GET['edit_award']) && ctype_digit((string) $_GET['edit_award'])) {
        $editAward = db_fetch_one($db, 'SELECT award_id, child_id, category_id, award_date, status FROM awards WHERE award_id = ?', [(int) $_GET['edit_award']]);
    }
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

$awardCount = count($awards);
$categoryCount = count($categories);
$childrenAwarded = count(array_unique(array_column($awards, 'child_id')));
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Awards | AGC Bomet Area</title>
    <link rel="icon" type="image/jpeg" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/images/logo.jpg">
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
                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/announcements.php">Announcements</a>
                <p class="nav-label">Management</p>
                <a class="nav-item active" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/awards.php">Awards</a>
                <a class="nav-item" href="#">Reports</a>
                <a class="nav-item" href="#">Settings</a>
                <a class="nav-item" href="#">Profile</a>                <a class="nav-item" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/logout.php">Log Out</a>            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="brand-block">
                    <img src="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/images/mu-logo.jpg" alt="AGC logo" class="top-brand-logo" onerror="this.onerror=null; this.src='<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/images/logo.jpg';">
                    <div>
                        <p class="eyebrow">Sunday School</p>
                        <h2>🏆 Awards Management</h2>
                    </div>
                </div>                <div class="top-nav-links">
                    <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/logout.php">Log Out</a>
                </div>            </header>

            <section class="page-header">
                <div>
                    <p class="eyebrow">Recognition</p>
                    <h3>Celebrate Student Excellence</h3>
                    <p>Manage award categories and issue recognition to children.</p>
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
                    <p class="card-label">Total Awards</p>
                    <p class="card-number"><?php echo number_format($awardCount); ?></p>
                </article>
                <article class="summary-card">
                    <p class="card-label">Award Categories</p>
                    <p class="card-number"><?php echo number_format($categoryCount); ?></p>
                </article>
                <article class="summary-card">
                    <p class="card-label">Children Awarded</p>
                    <p class="card-number"><?php echo number_format($childrenAwarded); ?></p>
                </article>
            </section>

            <section class="children-section">
                <div class="section-heading">
                    <h3><?php echo $editCategory ? 'Edit Award Category' : 'Create Award Category'; ?></h3>
                    <?php if ($editCategory): ?>
                        <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/awards.php" class="text-link">Cancel Edit</a>
                    <?php endif; ?>
                </div>
                <form action="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/awards_handler.php" method="post">
                    <input type="hidden" name="action" value="<?php echo $editCategory ? 'edit_category' : 'add_category'; ?>">
                    <?php if ($editCategory): ?>
                        <input type="hidden" name="category_id" value="<?php echo (int) $editCategory['category_id']; ?>">
                    <?php endif; ?>
                    <div class="content-grid two-col">
                        <article class="panel-card">
                            <label for="category_name">Category Name</label>
                            <input id="category_name" name="category_name" type="text" value="<?php echo htmlspecialchars($editCategory['category_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                        </article>
                        <article class="panel-card">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="Active" <?php echo (($editCategory['status'] ?? 'Active') === 'Active') ? 'selected' : ''; ?>>Active</option>
                                <option value="Inactive" <?php echo (($editCategory['status'] ?? 'Active') === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </article>
                    </div>
                    <article class="panel-card">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($editCategory['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </article>
                    <div class="section-heading">
                        <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/awards.php" class="text-link">Back to list</a>
                        <button type="submit" class="primary-btn"><?php echo $editCategory ? 'Update Category' : 'Save Category'; ?></button>
                    </div>
                </form>
            </section>

            <section class="table-card">
                <div class="table-header">
                    <h3>Award Categories</h3>
                </div>
                <div class="table-wrapper">
                    <table class="children-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($categories)): ?>
                                <tr><td colspan="5">No categories found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td><?php echo (int) $category['category_id']; ?></td>
                                        <td><?php echo htmlspecialchars($category['category_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($category['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><span class="status <?php echo strtolower((string) ($category['status'] ?? 'Active')) === 'active' ? 'active' : 'pending'; ?>"><?php echo htmlspecialchars($category['status'] ?? 'Active', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td>
                                            <div class="top-nav-links">
                                                <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/awards.php?edit_category=<?php echo (int) $category['category_id']; ?>" class="text-link">Edit</a>
                                                <form action="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/awards_handler.php" method="post" style="display:inline;">
                                                    <input type="hidden" name="action" value="delete_category">
                                                    <input type="hidden" name="category_id" value="<?php echo (int) $category['category_id']; ?>">
                                                    <button type="submit" class="button text-link" onclick="return confirm('Delete this category?');">Delete</button>
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

            <section class="children-section">
                <div class="section-heading">
                    <h3><?php echo $editAward ? 'Edit Award' : 'Issue Award'; ?></h3>
                    <?php if ($editAward): ?>
                        <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/awards.php" class="text-link">Cancel Edit</a>
                    <?php endif; ?>
                </div>
                <form action="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/awards_handler.php" method="post">
                    <input type="hidden" name="action" value="<?php echo $editAward ? 'edit_award' : 'add_award'; ?>">
                    <?php if ($editAward): ?>
                        <input type="hidden" name="award_id" value="<?php echo (int) $editAward['award_id']; ?>">
                    <?php endif; ?>
                    <div class="content-grid two-col">
                        <article class="panel-card">
                            <label for="child_id">Child</label>
                            <select id="child_id" name="child_id" required>
                                <option value="">Select child</option>
                                <?php foreach ($children as $child): ?>
                                    <option value="<?php echo (int) $child['child_id']; ?>" <?php echo (($editAward['child_id'] ?? '') == $child['child_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($child['full_name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </article>
                        <article class="panel-card">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">Select category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo (int) $category['category_id']; ?>" <?php echo (($editAward['category_id'] ?? '') == $category['category_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['category_name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </article>
                    </div>
                    <div class="content-grid two-col">
                        <article class="panel-card">
                            <label for="award_date">Award Date</label>
                            <input id="award_date" name="award_date" type="date" value="<?php echo htmlspecialchars($editAward['award_date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>" required>
                        </article>
                        <article class="panel-card">
                            <label for="award_status">Status</label>
                            <select id="award_status" name="status">
                                <option value="Presented" <?php echo (($editAward['status'] ?? 'Presented') === 'Presented') ? 'selected' : ''; ?>>Presented</option>
                                <option value="Pending" <?php echo (($editAward['status'] ?? 'Presented') === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                            </select>
                        </article>
                    </div>
                    <div class="section-heading">
                        <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/awards.php" class="text-link">Back to list</a>
                        <button type="submit" class="primary-btn"><?php echo $editAward ? 'Update Award' : 'Save Award'; ?></button>
                    </div>
                </form>
            </section>

            <section class="table-card">
                <div class="table-header">
                    <h3>Child Awards</h3>
                </div>
                <div class="table-wrapper">
                    <table class="children-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Child Name</th>
                                <th>Award Category</th>
                                <th>Date Awarded</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($awards)): ?>
                                <tr><td colspan="6">No award records found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($awards as $award): ?>
                                    <tr>
                                        <td><?php echo (int) $award['award_id']; ?></td>
                                        <td><?php echo htmlspecialchars($award['full_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($award['category_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($award['award_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><span class="status <?php echo strtolower((string) ($award['status'] ?? 'Presented')) === 'presented' ? 'active' : 'pending'; ?>"><?php echo htmlspecialchars($award['status'] ?? 'Presented', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td>
                                            <div class="top-nav-links">
                                                <a href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/awards.php?edit_award=<?php echo (int) $award['award_id']; ?>" class="text-link">Edit</a>
                                                <form action="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/awards_handler.php" method="post" style="display:inline;">
                                                    <input type="hidden" name="action" value="delete_award">
                                                    <input type="hidden" name="award_id" value="<?php echo (int) $award['award_id']; ?>">
                                                    <button type="submit" class="button text-link" onclick="return confirm('Delete this award?');">Delete</button>
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

            <footer class="footer">
                <p>© 2026 AGC Bomet Area Sunday School. All rights reserved.</p>
            </footer>
        </main>
    </div>

    <script src="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/script.js"></script>
</body>
</html>
