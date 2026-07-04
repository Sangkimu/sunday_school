<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/db.php';

$message = '';
$messageType = 'success';

function redirect_back(string $message, string $messageType): void
{
    $_SESSION['classes_message'] = $message;
    $_SESSION['classes_message_type'] = $messageType;

    $scriptName = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';
    $basePath = rtrim(dirname($scriptName), '/');
    $target = ($basePath !== '' && $basePath !== '.') ? $basePath . '/classes.php' : 'classes.php';

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
        $className = trim((string) ($_POST['class_name'] ?? ''));
        $level = trim((string) ($_POST['level'] ?? ''));
        $teacherName = trim((string) ($_POST['teacher_name'] ?? ''));
        $capacity = isset($_POST['capacity']) && $_POST['capacity'] !== '' ? (int) $_POST['capacity'] : 30;
        $schedule = trim((string) ($_POST['schedule'] ?? ''));

        if ($className === '') {
            redirect_back('Class name is required.', 'error');
        }

        $stmt = db_prepare_and_execute(
            $db,
            'INSERT INTO classes (class_name, level, teacher_name, capacity, schedule) VALUES (?, ?, ?, ?, ?)',
            [$className, $level, $teacherName, $capacity, $schedule]
        );
        $stmt->close();

        redirect_back('Class added successfully.', 'success');
    }

    if ($action === 'edit') {
        $classId = isset($_POST['class_id']) && $_POST['class_id'] !== '' ? (int) $_POST['class_id'] : 0;
        $className = trim((string) ($_POST['class_name'] ?? ''));
        $level = trim((string) ($_POST['level'] ?? ''));
        $teacherName = trim((string) ($_POST['teacher_name'] ?? ''));
        $capacity = isset($_POST['capacity']) && $_POST['capacity'] !== '' ? (int) $_POST['capacity'] : 30;
        $schedule = trim((string) ($_POST['schedule'] ?? ''));

        if ($classId <= 0 || $className === '') {
            redirect_back('Invalid class update request.', 'error');
        }

        $stmt = db_prepare_and_execute(
            $db,
            'UPDATE classes SET class_name = ?, level = ?, teacher_name = ?, capacity = ?, schedule = ? WHERE class_id = ?',
            [$className, $level, $teacherName, $capacity, $schedule, $classId]
        );
        $stmt->close();

        redirect_back('Class updated successfully.', 'success');
    }

    if ($action === 'delete') {
        $classId = isset($_POST['class_id']) && $_POST['class_id'] !== '' ? (int) $_POST['class_id'] : 0;

        if ($classId <= 0) {
            redirect_back('Invalid class delete request.', 'error');
        }

        $stmt = db_prepare_and_execute($db, 'DELETE FROM classes WHERE class_id = ?', [$classId]);
        $stmt->close();

        redirect_back('Class deleted successfully.', 'success');
    }

    redirect_back('Unknown action.', 'error');
} catch (Throwable $e) {
    redirect_back('Database error: ' . $e->getMessage(), 'error');
}
