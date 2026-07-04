<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/db.php';

function redirect_back(string $message, string $messageType): void
{
    $_SESSION['attendance_message'] = $message;
    $_SESSION['attendance_message_type'] = $messageType;

    $scriptName = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';
    $basePath = rtrim(dirname($scriptName), '/');
    $target = ($basePath !== '' && $basePath !== '.') ? $basePath . '/attendance.php' : 'attendance.php';

    header('Location: ' . $target);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_back('Invalid request.', 'error');
}

$action = $_POST['action'] ?? '';
$role = trim((string) ($_SESSION['user']['role'] ?? ''));

if (!can_manage_module('attendance', $role)) {
    redirect_back('Only teachers and coordinators can modify attendance records.', 'error');
}

try {
    $db = db_connect();

    if ($action === 'add') {
        $childId = isset($_POST['child_id']) && $_POST['child_id'] !== '' ? (int) $_POST['child_id'] : 0;
        $attendanceDate = trim((string) ($_POST['attendance_date'] ?? ''));
        $status = trim((string) ($_POST['status'] ?? 'Present'));
        $remarks = trim((string) ($_POST['remarks'] ?? ''));

        if ($childId <= 0 || $attendanceDate === '') {
            redirect_back('Child and date are required.', 'error');
        }

        $stmt = db_prepare_and_execute(
            $db,
            'INSERT INTO attendance_records (child_id, attendance_date, status, remarks) VALUES (?, ?, ?, ?)',
            [$childId, $attendanceDate, $status, $remarks]
        );
        $stmt->close();

        redirect_back('Attendance record added successfully.', 'success');
    }

    if ($action === 'edit') {
        $attendanceId = isset($_POST['attendance_id']) && $_POST['attendance_id'] !== '' ? (int) $_POST['attendance_id'] : 0;
        $childId = isset($_POST['child_id']) && $_POST['child_id'] !== '' ? (int) $_POST['child_id'] : 0;
        $attendanceDate = trim((string) ($_POST['attendance_date'] ?? ''));
        $status = trim((string) ($_POST['status'] ?? 'Present'));
        $remarks = trim((string) ($_POST['remarks'] ?? ''));

        if ($attendanceId <= 0 || $childId <= 0 || $attendanceDate === '') {
            redirect_back('Invalid attendance update request.', 'error');
        }

        $stmt = db_prepare_and_execute(
            $db,
            'UPDATE attendance_records SET child_id = ?, attendance_date = ?, status = ?, remarks = ? WHERE attendance_id = ?',
            [$childId, $attendanceDate, $status, $remarks, $attendanceId]
        );
        $stmt->close();

        redirect_back('Attendance record updated successfully.', 'success');
    }

    if ($action === 'delete') {
        $attendanceId = isset($_POST['attendance_id']) && $_POST['attendance_id'] !== '' ? (int) $_POST['attendance_id'] : 0;

        if ($attendanceId <= 0) {
            redirect_back('Invalid attendance delete request.', 'error');
        }

        $stmt = db_prepare_and_execute($db, 'DELETE FROM attendance_records WHERE attendance_id = ?', [$attendanceId]);
        $stmt->close();

        redirect_back('Attendance record deleted successfully.', 'success');
    }

    redirect_back('Unknown action.', 'error');
} catch (Throwable $e) {
    redirect_back('Database error: ' . $e->getMessage(), 'error');
}
