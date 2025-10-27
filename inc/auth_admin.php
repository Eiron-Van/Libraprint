<?php
// inc/auth_admin.php
// Purpose: Restrict access to admin-only pages and endpoints

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- SECURITY: Check if user is logged in ---
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header("Location: /Login");
    exit();
}

// --- SECURITY: Check if user is an admin ---
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Optional: log unauthorized attempts
    $logDir = __DIR__ . '/../logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    $logFile = $logDir . '/unauthorized_access.log';
    $logMessage = sprintf(
        "[%s] Unauthorized access by user_id=%s (role=%s) on %s from IP %s\n",
        date('Y-m-d H:i:s'),
        $_SESSION['user_id'] ?? 'N/A',
        $_SESSION['role'] ?? 'N/A',
        $_SERVER['REQUEST_URI'],
        $_SERVER['REMOTE_ADDR']
    );
    file_put_contents($logFile, $logMessage, FILE_APPEND);

    // Redirect to no-permission page
    header("Location: /no-permission.php");
    exit();
}


