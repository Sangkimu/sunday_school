<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/db.php';

$message = '';
$messageType = 'success';

function redirect_back(string $message, string $messageType): void
{
    $_SESSION['children_message'] = $message;
    $_SESSION['children_message_type'] = $messageType;

    $scriptName = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';
    $basePath = rtrim(dirname($scriptName), '/');
    $target = ($basePath !== '' && $basePath !== '.') ? $basePath . '/children.php' : 'children.php';

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
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $gender = trim((string) ($_POST['gender'] ?? ''));
        $age = isset($_POST['age']) && $_POST['age'] !== '' ? (int) $_POST['age'] : null;
        $classId = isset($_POST['class_id']) && $_POST['class_id'] !== '' ? (int) $_POST['class_id'] : null;
        $guardianName = trim((string) ($_POST['guardian_name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $status = trim((string) ($_POST['status'] ?? 'Active'));

        if ($fullName === '') {
            redirect_back('Child name is required.', 'error');
        }

        $stmt = db_prepare_and_execute(
            $db,
            'INSERT INTO children (full_name, gender, age, class_id, guardian_name, phone, status, date_registered) VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE())',
            [$fullName, $gender, $age, $classId, $guardianName, $phone, $status]
        );
        $stmt->close();

        redirect_back('Child added successfully.', 'success');
    }

    if ($action === 'edit') {
        $childId = isset($_POST['child_id']) && $_POST['child_id'] !== '' ? (int) $_POST['child_id'] : 0;
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $gender = trim((string) ($_POST['gender'] ?? ''));
        $age = isset($_POST['age']) && $_POST['age'] !== '' ? (int) $_POST['age'] : null;
        $classId = isset($_POST['class_id']) && $_POST['class_id'] !== '' ? (int) $_POST['class_id'] : null;
        $guardianName = trim((string) ($_POST['guardian_name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $status = trim((string) ($_POST['status'] ?? 'Active'));

        if ($childId <= 0 || $fullName === '') {
            redirect_back('Invalid child update request.', 'error');
        }

        $stmt = db_prepare_and_execute(
            $db,
            'UPDATE children SET full_name = ?, gender = ?, age = ?, class_id = ?, guardian_name = ?, phone = ?, status = ? WHERE child_id = ?',
            [$fullName, $gender, $age, $classId, $guardianName, $phone, $status, $childId]
        );
        $stmt->close();

        redirect_back('Child updated successfully.', 'success');
    }

    if ($action === 'delete') {
        $childId = isset($_POST['child_id']) && $_POST['child_id'] !== '' ? (int) $_POST['child_id'] : 0;

        if ($childId <= 0) {
            redirect_back('Invalid child delete request.', 'error');
        }

        $stmt = db_prepare_and_execute($db, 'DELETE FROM children WHERE child_id = ?', [$childId]);
        $stmt->close();

        redirect_back('Child deleted successfully.', 'success');
    }

    redirect_back('Unknown action.', 'error');
} catch (Throwable $e) {
    redirect_back('Database error: ' . $e->getMessage(), 'error');
}
