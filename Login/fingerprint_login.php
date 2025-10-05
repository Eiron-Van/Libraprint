<?php
// Configure session settings for better security and reliability
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // Use HTTPS only
ini_set('session.cookie_samesite', 'Lax');

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

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();

    // Regenerate session ID for security
    session_regenerate_id(true);

    // Return session information for the C# app to handle
    echo json_encode([
        'success' => true,
        'session_id' => session_id(),
        'redirect' => 'https://libraprintlucena.com/User/',
        'message' => 'Login successful'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'User not found']);
}
