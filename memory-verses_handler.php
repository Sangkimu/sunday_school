<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/db.php';

$message = '';
$messageType = 'success';

function redirect_back(string $message, string $messageType): void
{
    $_SESSION['memory_verses_message'] = $message;
    $_SESSION['memory_verses_message_type'] = $messageType;

    $scriptName = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';
    $basePath = rtrim(dirname($scriptName), '/');
    $target = ($basePath !== '' && $basePath !== '.') ? $basePath . '/memory-verses.php' : 'memory-verses.php';

    header('Location: ' . $target);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_back('Invalid request.', 'error');
}

$action = $_POST['action'] ?? '';

try {
    $db = db_connect();

    if ($action === 'add') {
        $reference = trim((string) ($_POST['reference'] ?? ''));
        $verseText = trim((string) ($_POST['verse_text'] ?? ''));
        $category = trim((string) ($_POST['category'] ?? ''));

        if ($reference === '' || $verseText === '') {
            redirect_back('Bible reference and verse text are required.', 'error');
        }

        $stmt = db_prepare_and_execute(
            $db,
            'INSERT INTO memory_verses (verse_text, reference, category, date_added) VALUES (?, ?, ?, CURDATE())',
            [$verseText, $reference, $category !== '' ? $category : null]
        );
        $stmt->close();

        redirect_back('Verse added successfully.', 'success');
    }

    if ($action === 'edit') {
        $verseId = isset($_POST['verse_id']) && $_POST['verse_id'] !== '' ? (int) $_POST['verse_id'] : 0;
        $reference = trim((string) ($_POST['reference'] ?? ''));
        $verseText = trim((string) ($_POST['verse_text'] ?? ''));
        $category = trim((string) ($_POST['category'] ?? ''));

        if ($verseId <= 0 || $reference === '' || $verseText === '') {
            redirect_back('Invalid verse update request.', 'error');
        }

        $stmt = db_prepare_and_execute(
            $db,
            'UPDATE memory_verses SET verse_text = ?, reference = ?, category = ? WHERE verse_id = ?',
            [$verseText, $reference, $category !== '' ? $category : null, $verseId]
        );
        $stmt->close();

        redirect_back('Verse updated successfully.', 'success');
    }

    if ($action === 'delete') {
        $verseId = isset($_POST['verse_id']) && $_POST['verse_id'] !== '' ? (int) $_POST['verse_id'] : 0;

        if ($verseId <= 0) {
            redirect_back('Invalid verse delete request.', 'error');
        }

        $stmt = db_prepare_and_execute($db, 'DELETE FROM memory_verses WHERE verse_id = ?', [$verseId]);
        $stmt->close();

        redirect_back('Verse deleted successfully.', 'success');
    }

    redirect_back('Unknown action.', 'error');
} catch (Throwable $e) {
    redirect_back('Database error: ' . $e->getMessage(), 'error');
}
