<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Handle session ID from URL parameter (for fingerprint login)
if (isset($_GET['PHPSESSID']) && !empty($_GET['PHPSESSID'])) {
    session_id($_GET['PHPSESSID']);
}

session_start();

require '../connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /Login");
    exit();
}


$user_id = $_SESSION['user_id'];


// ✅ 2. Get JSON data
$data = json_decode(file_get_contents("php://input"), true);
$barcode = trim($data['barcode'] ?? '');

if (empty($barcode)) {
    echo json_encode(['success' => false, 'message' => 'No barcode provided']);
    exit;
}

// ✅ 3. Find the user's numeric ID
$findUser = $conn->prepare("SELECT id FROM users WHERE user_id = ?");
$findUser->bind_param("s", $user_id);
$findUser->execute();
$findUser->bind_result($id);
$findUser->fetch();
$findUser->close();

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

// ✅ 4. Find the book in the inventory
$findBook = $conn->prepare("SELECT item_id, title FROM book_inventory WHERE class_no = ?");
$findBook->bind_param("s", $barcode);
$findBook->execute();
$findBook->bind_result($book_id, $book_title);
$findBook->fetch();
$findBook->close();

if (empty($book_id)) {
    echo json_encode(['success' => false, 'message' => 'Book not found in inventory']);
    exit;
}

// ✅ 5. Insert into book_record
$stmt = $conn->prepare("INSERT INTO book_record (user_id, book_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $book_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'title' => $book_title,
        'barcode' => $barcode
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $stmt->error
    ]);
}

// --- Borrow action ---

// 1. Update book status to Checked Out
$update = $conn->prepare("UPDATE book_inventory SET status = 'Checked Out' WHERE item_id = ?");
$update->bind_param("i", $book_id);
$update->execute();

// 2. Remove from reservation (if exists)
$delete = $conn->prepare("DELETE FROM reservation WHERE item_id = ? AND user_id = ?");
$delete->bind_param("ii", $book_id, $user_id);
$delete->execute();

$stmt->close();
$conn->close();