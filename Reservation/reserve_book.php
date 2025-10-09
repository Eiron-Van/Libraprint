<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

$data = json_decode(file_get_contents("php://input"), true);
$item_id = $data['item_id'] ?? null;
$purpose = $data['purpose'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$item_id || !$purpose) {
    echo json_encode(["success" => false, "message" => "Missing data."]);
    exit;
}

// ✅ Check if the book is available
$check = $conn->prepare("SELECT status FROM book_inventory WHERE item_id = ?");
$check->bind_param("i", $item_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Book not found."]);
    exit;
}

$row = $result->fetch_assoc();
if ($row['status'] !== 'Available') {
    echo json_encode(["success" => false, "message" => "Book is not available."]);
    exit;
}

// ✅ Reserve the book
$conn->begin_transaction();
try {
    $insert = $conn->prepare("INSERT INTO reservation (item_id, user_id, date_reserved, purpose) VALUES (?, ?, NOW(), ?)");
    $insert->bind_param("iss", $item_id, $user_id, $purpose);
    $insert->execute();

    $update = $conn->prepare("UPDATE book_inventory SET status='Reserved' WHERE item_id=?");
    $update->bind_param("i", $item_id);
    $update->execute();

    $conn->commit();
    echo json_encode(["success" => true, "message" => "Book reserved successfully."]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}