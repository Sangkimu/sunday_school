<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/db.php';

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? ''), '/');
$action = $_POST['action'] ?? '';
$message = '';
$messageType = 'success';

try {
    $db = db_connect();

    if ($action === 'add_story' || $action === 'edit_story') {
        $title = trim((string) ($_POST['title'] ?? ''));
        $theme = trim((string) ($_POST['theme'] ?? ''));
        $lessonDate = trim((string) ($_POST['lesson_date'] ?? '')) ?: null;
        $summary = trim((string) ($_POST['summary'] ?? ''));

        if ($title === '') {
            throw new InvalidArgumentException('Please provide a story title.');
        }

        if ($action === 'add_story') {
            db_prepare_and_execute($db, 'INSERT INTO bible_stories (title, theme, lesson_date, summary) VALUES (?, ?, ?, ?)', [$title, $theme, $lessonDate, $summary]);
            $message = 'Story added successfully.';
        } else {
            $storyId = filter_input(INPUT_POST, 'story_id', FILTER_VALIDATE_INT);
            if ($storyId === false || $storyId <= 0) {
                throw new InvalidArgumentException('Invalid story id.');
            }
            db_prepare_and_execute($db, 'UPDATE bible_stories SET title = ?, theme = ?, lesson_date = ?, summary = ? WHERE story_id = ?', [$title, $theme, $lessonDate, $summary, $storyId]);
            $message = 'Story updated successfully.';
        }
    } elseif ($action === 'delete_story') {
        $storyId = filter_input(INPUT_POST, 'story_id', FILTER_VALIDATE_INT);
        if ($storyId === false || $storyId <= 0) {
            throw new InvalidArgumentException('Invalid story id.');
        }
        db_prepare_and_execute($db, 'DELETE FROM bible_stories WHERE story_id = ?', [$storyId]);
        $message = 'Story deleted.';
    } else {
        throw new InvalidArgumentException('Invalid action.');
    }
} catch (Throwable $e) {
    $messageType = 'error';
    $message = $e->getMessage();
}

$_SESSION['bible_stories_message'] = $message;
$_SESSION['bible_stories_message_type'] = $messageType;
header('Location: ' . $basePath . '/bible-stories.php');
exit;
