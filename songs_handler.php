<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/db.php';

function redirect_back(string $message, string $messageType): void
{
    $_SESSION['songs_message'] = $message;
    $_SESSION['songs_message_type'] = $messageType;

    $scriptName = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';
    $basePath = rtrim(dirname($scriptName), '/');
    $target = ($basePath !== '' && $basePath !== '.') ? $basePath . '/songs.php' : 'songs.php';

    header('Location: ' . $target);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_back('Invalid request method.', 'error');
}

$action = trim((string) ($_POST['action'] ?? ''));

try {
    $db = db_connect();

    if ($action === 'add') {
        $title = trim((string) ($_POST['title'] ?? ''));
        $category = trim((string) ($_POST['category'] ?? ''));
        $language = trim((string) ($_POST['language'] ?? 'English')) ?: 'English';
        $hasAudio = isset($_POST['has_audio']) ? 1 : 0;
        $hasVideo = isset($_POST['has_video']) ? 1 : 0;

        if ($title === '' || $category === '') {
            redirect_back('Title and category are required.', 'error');
        }

        $stmt = db_prepare_and_execute(
            $db,
            'INSERT INTO songs (title, category, language, has_audio, has_video) VALUES (?, ?, ?, ?, ?)',
            [$title, $category, $language, $hasAudio, $hasVideo]
        );
        $stmt->close();

        redirect_back('Song added successfully.', 'success');
    }

    if ($action === 'edit') {
        $songId = isset($_POST['song_id']) && $_POST['song_id'] !== '' ? (int) $_POST['song_id'] : 0;
        $title = trim((string) ($_POST['title'] ?? ''));
        $category = trim((string) ($_POST['category'] ?? ''));
        $language = trim((string) ($_POST['language'] ?? 'English')) ?: 'English';
        $hasAudio = isset($_POST['has_audio']) ? 1 : 0;
        $hasVideo = isset($_POST['has_video']) ? 1 : 0;

        if ($songId <= 0 || $title === '' || $category === '') {
            redirect_back('Invalid song update request.', 'error');
        }

        $stmt = db_prepare_and_execute(
            $db,
            'UPDATE songs SET title = ?, category = ?, language = ?, has_audio = ?, has_video = ? WHERE song_id = ?',
            [$title, $category, $language, $hasAudio, $hasVideo, $songId]
        );
        $stmt->close();

        redirect_back('Song updated successfully.', 'success');
    }

    if ($action === 'delete') {
        $songId = isset($_POST['song_id']) && $_POST['song_id'] !== '' ? (int) $_POST['song_id'] : 0;

        if ($songId <= 0) {
            redirect_back('Invalid song delete request.', 'error');
        }

        $stmt = db_prepare_and_execute($db, 'DELETE FROM songs WHERE song_id = ?', [$songId]);
        $stmt->close();

        redirect_back('Song deleted successfully.', 'success');
    }

    redirect_back('Unknown action.', 'error');
} catch (Throwable $e) {
    redirect_back('Database error: ' . $e->getMessage(), 'error');
}
