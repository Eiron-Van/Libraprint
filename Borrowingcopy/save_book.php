<?php
session_start();
require '../connection.php'; // adjust path

// ✅ Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_identifier = $_SESSION['user_id']; // this is your users.user_id (string)
$data = json_decode(file_get_contents("php://input"), true);
$barcode = trim($data['barcode'] ?? '');

// ✅ Validate input
if (empty($barcode)) {
    echo json_encode(['success' => false, 'message' => 'No barcode provided']);
    exit;
}

// ✅ 1. Find actual user database id
$findUser = $conn->prepare("SELECT id FROM users WHERE user_id = ?");
$findUser->bind_param("s", $user_identifier);
$findUser->execute();
$findUser->bind_result($user_id);
$findUser->fetch();
$findUser->close();

if (empty($user_id)) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

// ✅ 2. Find the book in book_inventory
$findBook = $conn->prepare("SELECT id FROM book_inventory WHERE class_no = ?");
$findBook->bind_param("s", $barcode);
$findBook->execute();
$findBook->bind_result($book_id);
$findBook->fetch();
$findBook->close();

if (empty($book_id)) {
    echo json_encode(['success' => false, 'message' => 'Book not found in inventory']);
    exit;
}

// ✅ 3. Save to book_readings table
$stmt = $conn->prepare("INSERT INTO book_readings (user_id, book_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $book_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
