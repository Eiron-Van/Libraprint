<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
 
session_start();
require '../connection.php'; // adjust path
 
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$barcode = trim($data['barcode'] ?? '');

if (empty($barcode)) {
    echo json_encode(['success' => false, 'message' => 'No barcode provided']);
    exit;
}

$user_id = $_SESSION['id'];

$stmt = $conn->prepare("INSERT INTO book_readings (user_id, book_barcode) VALUES (?, ?)");
$stmt->bind_param("is", $user_id, $barcode);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$conn->close();
