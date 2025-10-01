<?php
session_start();
include '../connection.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

// Get user_id from POST (sent by Windows app after fingerprint verification)
$user_id = $_POST['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'No user id provided']);
    exit();
}

// Find the user in DB
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // If you require email verification, check it
    if (isset($user['is_verified']) && $user['is_verified'] == 0) {
        echo json_encode(['success' => false, 'message' => 'Please verify your email first.']);
        exit();
    }

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];

    echo json_encode(['success' => true, 'redirect' => 'https://libraprintlucena.com']);
} else {
    echo json_encode(['success' => false, 'message' => 'User not found']);
}
