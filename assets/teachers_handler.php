<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/db.php';

$message = '';
$messageType = 'success';

function redirect_back(string $message, string $messageType): void
{
    $_SESSION['teachers_message'] = $message;
    $_SESSION['teachers_message_type'] = $messageType;

    $scriptName = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';
    $basePath = rtrim(dirname($scriptName), '/');
    $target = ($basePath !== '' && $basePath !== '.') ? $basePath . '/teachers.php' : 'teachers.php';

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
        $role = trim((string) ($_POST['role'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $status = trim((string) ($_POST['status'] ?? 'Active'));

        if ($fullName === '' || $role === '') {
            redirect_back('Teacher name and role are required.', 'error');
        }

        $stmt = db_prepare_and_execute(
            $db,
            'INSERT INTO teachers (full_name, role, phone, email, status) VALUES (?, ?, ?, ?, ?)',
            [$fullName, $role, $phone !== '' ? $phone : null, $email !== '' ? $email : null, $status]
        );
        $stmt->close();

        redirect_back('Teacher added successfully.', 'success');
    }

    if ($action === 'edit') {
        $teacherId = isset($_POST['teacher_id']) && $_POST['teacher_id'] !== '' ? (int) $_POST['teacher_id'] : 0;
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $role = trim((string) ($_POST['role'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $status = trim((string) ($_POST['status'] ?? 'Active'));

        if ($teacherId <= 0 || $fullName === '' || $role === '') {
            redirect_back('Invalid teacher update request.', 'error');
        }

        $stmt = db_prepare_and_execute(
            $db,
            'UPDATE teachers SET full_name = ?, role = ?, phone = ?, email = ?, status = ? WHERE teacher_id = ?',
            [$fullName, $role, $phone !== '' ? $phone : null, $email !== '' ? $email : null, $status, $teacherId]
        );
        $stmt->close();

        redirect_back('Teacher updated successfully.', 'success');
    }

    if ($action === 'delete') {
        $teacherId = isset($_POST['teacher_id']) && $_POST['teacher_id'] !== '' ? (int) $_POST['teacher_id'] : 0;

        if ($teacherId <= 0) {
            redirect_back('Invalid teacher delete request.', 'error');
        }

        $stmt = db_prepare_and_execute($db, 'DELETE FROM teachers WHERE teacher_id = ?', [$teacherId]);
        $stmt->close();

        redirect_back('Teacher deleted successfully.', 'success');
    }

    redirect_back('Unknown action.', 'error');
} catch (Throwable $e) {
    redirect_back('Database error: ' . $e->getMessage(), 'error');
}
