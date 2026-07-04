<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/db.php';

$message = '';
$messageType = 'success';

function redirect_back(string $message, string $messageType): void
{
    $_SESSION['announcements_message'] = $message;
    $_SESSION['announcements_message_type'] = $messageType;

    $scriptName = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';
    $basePath = rtrim(dirname($scriptName), '/');
    $target = ($basePath !== '' && $basePath !== '.') ? $basePath . '/announcements.php' : 'announcements.php';

    header('Location: ' . $target);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_back('Invalid request.', 'error');
}

$action = $_POST['action'] ?? '';
$role = trim((string) ($_SESSION['user']['role'] ?? ''));

if (!can_manage_module('announcements', $role)) {
    redirect_back('Only teachers and coordinators can modify announcements.', 'error');
}

try {
    $db = db_connect();

    if ($action === 'add') {
        $title = trim((string) ($_POST['title'] ?? ''));
        $messageText = trim((string) ($_POST['message'] ?? ''));
        $announcementDate = trim((string) ($_POST['announcement_date'] ?? ''));
        $audience = trim((string) ($_POST['audience'] ?? 'All'));

        if ($title === '' || $messageText === '' || $announcementDate === '') {
            redirect_back('Title, message, and date are required.', 'error');
        }

        $stmt = db_prepare_and_execute(
            $db,
            'INSERT INTO announcements (title, message, announcement_date, audience) VALUES (?, ?, ?, ?)',
            [$title, $messageText, $announcementDate, $audience]
        );
        $stmt->close();

        redirect_back('Announcement added successfully.', 'success');
    }

    if ($action === 'edit') {
        $announcementId = isset($_POST['announcement_id']) && $_POST['announcement_id'] !== '' ? (int) $_POST['announcement_id'] : 0;
        $title = trim((string) ($_POST['title'] ?? ''));
        $messageText = trim((string) ($_POST['message'] ?? ''));
        $announcementDate = trim((string) ($_POST['announcement_date'] ?? ''));
        $audience = trim((string) ($_POST['audience'] ?? 'All'));

        if ($announcementId <= 0 || $title === '' || $messageText === '' || $announcementDate === '') {
            redirect_back('Invalid announcement update request.', 'error');
        }

        $stmt = db_prepare_and_execute(
            $db,
            'UPDATE announcements SET title = ?, message = ?, announcement_date = ?, audience = ? WHERE announcement_id = ?',
            [$title, $messageText, $announcementDate, $audience, $announcementId]
        );
        $stmt->close();

        redirect_back('Announcement updated successfully.', 'success');
    }

    if ($action === 'delete') {
        $announcementId = isset($_POST['announcement_id']) && $_POST['announcement_id'] !== '' ? (int) $_POST['announcement_id'] : 0;

        if ($announcementId <= 0) {
            redirect_back('Invalid announcement delete request.', 'error');
        }

        $stmt = db_prepare_and_execute($db, 'DELETE FROM announcements WHERE announcement_id = ?', [$announcementId]);
        $stmt->close();

        redirect_back('Announcement deleted successfully.', 'success');
    }

    redirect_back('Unknown action.', 'error');
} catch (Throwable $e) {
    redirect_back('Database error: ' . $e->getMessage(), 'error');
}
