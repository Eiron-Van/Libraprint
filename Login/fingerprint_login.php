<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');


session_start();
include '../connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
 
    // Find the user
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($user['is_verified'] == 0) {
            echo json_encode(['success' => false, 'message' => 'Please verify your email first.']);
            exit();
        }

        // Create session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];

        echo json_encode(['success' => true, 'redirect' => 'https://libraprintlucena.com']);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
}
