<?php
error_reporting(E_ALL); //temporary
ini_set('display_errors', 1); //temporary

header('Content-Type: application/json');

session_start();
require '../connection.php'; // adjust path if needed

// ✅ 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Session contains user_id (string), not numeric id
$user_identifier = $_SESSION['user_id'];

// ✅ 2. Get JSON data
$data = json_decode(file_get_contents("php://input"), true);
$barcode = trim($data['barcode'] ?? '');

if (empty($barcode)) {
    echo json_encode(['success' => false, 'message' => 'No barcode provided']);
    exit;
}

// ✅ 3. Find the user's numeric ID
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

// ✅ 4. Find the book in the inventory
$findBook = $conn->prepare("SELECT item_id FROM book_inventory WHERE class_no = ?");
$findBook->bind_param("s", $barcode);
$findBook->execute();
$findBook->bind_result($book_id);
$findBook->fetch();
$findBook->close();

if (empty($book_id)) {
    echo json_encode(['success' => false, 'message' => 'Book not found in inventory']);
    exit;
}

// ✅ 5. Insert into book_readings
$stmt = $conn->prepare("INSERT INTO book_record (user_id, book_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $book_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

// Fetch book title for display
$titleQuery = $conn->prepare("SELECT title FROM book_inventory WHERE book_id = ?");
$titleQuery->bind_param("i", $book_id);
$titleQuery->execute();
$titleQuery->bind_result($book_title);
$titleQuery->fetch();
$titleQuery->close();

echo json_encode([
  'success' => true,
  'title' => $book_title,
  'barcode' => $barcode
]);

$stmt->close();
$conn->close();
