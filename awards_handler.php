<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/db.php';

function redirect_back(string $message, string $messageType): void
{
    $_SESSION['awards_message'] = $message;
    $_SESSION['awards_message_type'] = $messageType;

    $scriptName = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';
    $basePath = rtrim(dirname($scriptName), '/');
    $target = ($basePath !== '' && $basePath !== '.') ? $basePath . '/awards.php' : 'awards.php';

    header('Location: ' . $target);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_back('Invalid request.', 'error');
}

$action = $_POST['action'] ?? '';

try {
    $db = db_connect();

    if ($action === 'add_category') {
        $categoryName = trim((string) ($_POST['category_name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $status = trim((string) ($_POST['status'] ?? 'Active'));

        if ($categoryName === '') {
            redirect_back('Category name is required.', 'error');
        }

        $stmt = db_prepare_and_execute(
            $db,
            'INSERT INTO award_categories (category_name, description, status) VALUES (?, ?, ?)',
            [$categoryName, $description, $status]
        );
        $stmt->close();

        redirect_back('Award category added successfully.', 'success');
    }

    if ($action === 'edit_category') {
        $categoryId = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int) $_POST['category_id'] : 0;
        $categoryName = trim((string) ($_POST['category_name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $status = trim((string) ($_POST['status'] ?? 'Active'));

        if ($categoryId <= 0 || $categoryName === '') {
            redirect_back('Invalid category update request.', 'error');
        }

        $stmt = db_prepare_and_execute(
            $db,
            'UPDATE award_categories SET category_name = ?, description = ?, status = ? WHERE category_id = ?',
            [$categoryName, $description, $status, $categoryId]
        );
        $stmt->close();

        redirect_back('Award category updated successfully.', 'success');
    }

    if ($action === 'delete_category') {
        $categoryId = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int) $_POST['category_id'] : 0;

        if ($categoryId <= 0) {
            redirect_back('Invalid category delete request.', 'error');
        }

        $stmt = db_prepare_and_execute($db, 'DELETE FROM award_categories WHERE category_id = ?', [$categoryId]);
        $stmt->close();

        redirect_back('Award category deleted successfully.', 'success');
    }

    if ($action === 'add_award') {
        $childId = isset($_POST['child_id']) && $_POST['child_id'] !== '' ? (int) $_POST['child_id'] : 0;
        $categoryId = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int) $_POST['category_id'] : 0;
        $awardDate = trim((string) ($_POST['award_date'] ?? ''));
        $status = trim((string) ($_POST['status'] ?? 'Presented'));

        if ($childId <= 0 || $categoryId <= 0 || $awardDate === '') {
            redirect_back('Child, category, and date are required.', 'error');
        }

        $stmt = db_prepare_and_execute(
            $db,
            'INSERT INTO awards (child_id, category_id, award_date, status) VALUES (?, ?, ?, ?)',
            [$childId, $categoryId, $awardDate, $status]
        );
        $stmt->close();

        redirect_back('Award issued successfully.', 'success');
    }

    if ($action === 'edit_award') {
        $awardId = isset($_POST['award_id']) && $_POST['award_id'] !== '' ? (int) $_POST['award_id'] : 0;
        $childId = isset($_POST['child_id']) && $_POST['child_id'] !== '' ? (int) $_POST['child_id'] : 0;
        $categoryId = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int) $_POST['category_id'] : 0;
        $awardDate = trim((string) ($_POST['award_date'] ?? ''));
        $status = trim((string) ($_POST['status'] ?? 'Presented'));

        if ($awardId <= 0 || $childId <= 0 || $categoryId <= 0 || $awardDate === '') {
            redirect_back('Invalid award update request.', 'error');
        }

        $stmt = db_prepare_and_execute(
            $db,
            'UPDATE awards SET child_id = ?, category_id = ?, award_date = ?, status = ? WHERE award_id = ?',
            [$childId, $categoryId, $awardDate, $status, $awardId]
        );
        $stmt->close();

        redirect_back('Award updated successfully.', 'success');
    }

    if ($action === 'delete_award') {
        $awardId = isset($_POST['award_id']) && $_POST['award_id'] !== '' ? (int) $_POST['award_id'] : 0;

        if ($awardId <= 0) {
            redirect_back('Invalid award delete request.', 'error');
        }

        $stmt = db_prepare_and_execute($db, 'DELETE FROM awards WHERE award_id = ?', [$awardId]);
        $stmt->close();

        redirect_back('Award deleted successfully.', 'success');
    }

    redirect_back('Unknown action.', 'error');
} catch (Throwable $e) {
    redirect_back('Database error: ' . $e->getMessage(), 'error');
}
