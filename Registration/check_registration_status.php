<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

header('Content-Type: application/json');

// Check if registration is complete (pending_registration is cleared)
if (!isset($_SESSION['pending_registration'])) {
    // Registration is complete
    echo json_encode([
        'registrationComplete' => true,
        'message' => 'Registration successful! Please check your email for verification.'
    ]);
} else {
    // Registration still in progress
    echo json_encode([
        'registrationComplete' => false
    ]);
}
?>

