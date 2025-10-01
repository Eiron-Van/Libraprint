<?php
session_start();
include '../connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$user_id = $_POST['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'No user id provided']);
    exit();
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (isset($user['is_verified']) && $user['is_verified'] == 0) {
        echo json_encode(['success' => false, 'message' => 'Please verify your email first.']);
        exit();
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];

    // ✅ Return the session ID in redirect URL
    echo json_encode([
        'success' => true,
        'redirect' => 'https://libraprintlucena.com/?PHPSESSID=' . session_id()
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'User not found']);
}
