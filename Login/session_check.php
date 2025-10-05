<?php
// Handle session ID from URL parameter (for fingerprint login)
if (isset($_GET['PHPSESSID']) && !empty($_GET['PHPSESSID'])) {
    session_id($_GET['PHPSESSID']);
}

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /Login");
    exit();
}