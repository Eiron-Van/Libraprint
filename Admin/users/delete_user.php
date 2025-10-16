<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? '';

    if (empty($user_id)) {
        echo json_encode(['success' => false, 'message' => 'No user ID provided.']);
        exit();
    }

    // Before deleting, check if the user exists
    $check = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $check->bind_param("s", $user_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit();
    }

    // Optional: Disable foreign key checks if necessary
    $conn->query("SET FOREIGN_KEY_CHECKS=0");

    $delete = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $delete->bind_param("s", $user_id);

    if ($delete->execute()) {
        echo json_encode(['success' => true, 'message' => 'User deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete user.']);
    }

    // Optional: Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS=1");
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
