<?php
session_start();
require '../connection.php'; // adjust path

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$barcode = trim($data['barcode'] ?? '');

if (empty($barcode)) {
    echo json_encode(['success' => false, 'message' => 'No barcode provided']);
    exit;
}

$user_id = $_SESSION['user_id'];

$findUser_id = $conn->prepare("SELECT id FROM users WHERE user_id = ?");
$findUser_id->bind_param("s", $user_id);
$findUser_id->execute();
$findUser_id->store_result();



// 1️⃣ Find the book in book_inventory
$findBook = $conn->prepare("SELECT id FROM book_inventory WHERE class_no = ?");
$findBook->bind_param("s", $barcode);
$findBook->execute();
$findBook->store_result();

if ($findBook->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Book not found in inventory']);
    exit;
}

$findBook->bind_result($book_id);
$findBook->fetch();
$findBook->close();

// 2️⃣ Save to book_readings table
$stmt = $conn->prepare("INSERT INTO book_readings (user_id, book_id) VALUES (?, ?)");
$stmt->bind_param("ii", $findUser_id, $book_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$conn->close();