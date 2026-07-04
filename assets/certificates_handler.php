<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/db.php';

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? ''), '/');
$action = $_POST['action'] ?? '';
$messageType = 'success';
$message = '';

try {
    $db = db_connect();

    if ($action === 'add_certificate' || $action === 'edit_certificate') {
        $awardId = filter_input(INPUT_POST, 'award_id', FILTER_VALIDATE_INT);
        $awardTitle = trim((string) ($_POST['award_title'] ?? ''));
        $awardDate = trim((string) ($_POST['award_date'] ?? ''));
        $teacherSignature = trim((string) ($_POST['teacher_signature'] ?? ''));
        $churchStamp = trim((string) ($_POST['church_stamp'] ?? '')) ?: 'AGC Bomet Area';
        $status = trim((string) ($_POST['status'] ?? 'Printed')) ?: 'Printed';

        if ($awardId === false || $awardId <= 0) {
            throw new InvalidArgumentException('Please select an existing award.');
        }

        if ($awardTitle === '') {
            throw new InvalidArgumentException('Please enter the award title.');
        }

        if ($awardDate === '') {
            throw new InvalidArgumentException('Please provide the award date.');
        }

        $childName = db_scalar($db, 'SELECT c.full_name FROM awards a JOIN children c ON a.child_id = c.child_id WHERE a.award_id = ? LIMIT 1', [$awardId]);
        if ($childName === null) {
            throw new RuntimeException('Could not find the selected award child.');
        }

        if ($action === 'add_certificate') {
            db_prepare_and_execute(
                $db,
                'INSERT INTO certificates (award_id, child_name, award_title, award_date, teacher_signature, church_stamp, status) VALUES (?, ?, ?, ?, ?, ?, ?)',
                [$awardId, $childName, $awardTitle, $awardDate, $teacherSignature, $churchStamp, $status]
            );
            $message = 'Certificate saved successfully.';
        } else {
            $certificateId = filter_input(INPUT_POST, 'certificate_id', FILTER_VALIDATE_INT);
            if ($certificateId === false || $certificateId <= 0) {
                throw new InvalidArgumentException('Invalid certificate ID.');
            }
            db_prepare_and_execute(
                $db,
                'UPDATE certificates SET award_id = ?, child_name = ?, award_title = ?, award_date = ?, teacher_signature = ?, church_stamp = ?, status = ? WHERE certificate_id = ?',
                [$awardId, $childName, $awardTitle, $awardDate, $teacherSignature, $churchStamp, $status, $certificateId]
            );
            $message = 'Certificate updated successfully.';
        }
    } elseif ($action === 'delete_certificate') {
        $certificateId = filter_input(INPUT_POST, 'certificate_id', FILTER_VALIDATE_INT);
        if ($certificateId === false || $certificateId <= 0) {
            throw new InvalidArgumentException('Invalid certificate ID.');
        }
        db_prepare_and_execute($db, 'DELETE FROM certificates WHERE certificate_id = ?', [$certificateId]);
        $message = 'Certificate deleted successfully.';
    } else {
        throw new InvalidArgumentException('Invalid action.');
    }
} catch (Throwable $e) {
    $messageType = 'error';
    $message = $e->getMessage();
}

$_SESSION['certificates_message'] = $message;
$_SESSION['certificates_message_type'] = $messageType;
header('Location: ' . $basePath . '/certificates.php');
exit;
