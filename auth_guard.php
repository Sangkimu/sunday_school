<?php
require_once __DIR__ . '/access_control.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$scriptName = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';
$page = basename($scriptName);
$basePath = rtrim(dirname($scriptName), '/');

if (!isset($_SESSION['user'])) {
    if (is_public_page($page)) {
        return;
    }

    $target = ($basePath !== '' && $basePath !== '.') ? $basePath . '/index.php' : 'index.php';
    header('Location: ' . $target);
    exit;
}

$role = trim((string) ($_SESSION['user']['role'] ?? ''));
if (!can_access_page($page, $role)) {
    $dashboardUrl = ($basePath !== '' && $basePath !== '.') ? $basePath . '/dashboard.php' : 'dashboard.php';
    header('Location: ' . $dashboardUrl);
    exit;
}
