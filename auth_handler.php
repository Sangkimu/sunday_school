<?php
session_start();
require_once __DIR__ . '/db.php';

function redirect(string $target, string $message, string $messageType): void
{
    $_SESSION['auth_message'] = $message;
    $_SESSION['auth_message_type'] = $messageType;
    header('Location: ' . $target);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php', 'Invalid request method.', 'error');
}

$action = trim((string) ($_POST['action'] ?? ''));
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? ''), '/');
$loginUrl = ($basePath !== '' && $basePath !== '.') ? $basePath . '/login.php' : 'login.php';
$signupUrl = ($basePath !== '' && $basePath !== '.') ? $basePath . '/signup.php' : 'signup.php';

try {
    $db = db_connect();

    if ($action === 'signup') {
        $firstName = trim((string) ($_POST['first_name'] ?? ''));
        $lastName = trim((string) ($_POST['last_name'] ?? ''));
        $username = trim((string) ($_POST['username'] ?? ''));
        $mobileNumber = trim((string) ($_POST['mobile_number'] ?? ''));
        $localChurch = trim((string) ($_POST['local_church'] ?? ''));
        $districtChurch = trim((string) ($_POST['district_church'] ?? ''));
        $areaChurch = trim((string) ($_POST['area_church'] ?? ''));
        $role = trim((string) ($_POST['role'] ?? 'Parent/Guardian'));

        if ($firstName === '' || $lastName === '' || $username === '' || $mobileNumber === '' || $localChurch === '' || $districtChurch === '' || $areaChurch === '' || $role === '') {
            redirect($signupUrl, 'All fields are required.', 'error');
        }

        $existing = db_fetch_one($db, 'SELECT user_id FROM users WHERE username = ? OR mobile = ? LIMIT 1', [$username, $mobileNumber]);
        if ($existing !== false) {
            redirect($signupUrl, 'Username or mobile number already registered.', 'error');
        }

        $stmt = db_prepare_and_execute(
            $db,
            'INSERT INTO users (first_name, last_name, username, mobile, local_church, district_church, area_church, role, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())',
            [$firstName, $lastName, $username, $mobileNumber, $localChurch, $districtChurch, $areaChurch, $role]
        );
        $stmt->close();

        redirect($loginUrl, 'Account created successfully. Please log in.', 'success');
    }

    if ($action === 'login') {
        $username = trim((string) ($_POST['username'] ?? ''));
        $mobileNumber = trim((string) ($_POST['mobile_number'] ?? ''));

        if ($username === '' || $mobileNumber === '') {
            redirect($loginUrl, 'Username and mobile number are required.', 'error');
        }

        $user = db_fetch_one($db, 'SELECT user_id, first_name, last_name, username, mobile, role FROM users WHERE username = ? AND mobile = ? LIMIT 1', [$username, $mobileNumber]);
        if ($user === false) {
            redirect($loginUrl, 'Invalid username or mobile number.', 'error');
        }

        $role = trim((string) ($user['role'] ?? 'Parent/Guardian'));
        if (strtolower($role) === 'user') {
            $role = 'Parent/Guardian';
        }

        $_SESSION['user'] = [
            'id' => (int) $user['user_id'],
            'name' => trim(((string) $user['first_name']) . ' ' . ((string) $user['last_name'])),
            'username' => $user['username'],
            'mobile' => $user['mobile'],
            'role' => $role,
        ];

        $dashboardUrl = ($basePath !== '' && $basePath !== '.') ? $basePath . '/dashboard.php' : 'dashboard.php';
        header('Location: ' . $dashboardUrl);
        exit;
    }

    redirect($loginUrl, 'Unknown authentication action.', 'error');
} catch (Throwable $e) {
    redirect($loginUrl, 'Server error: ' . $e->getMessage(), 'error');
}
