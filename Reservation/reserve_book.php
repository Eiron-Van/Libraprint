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
$title = $data['title'] ?? null;
$purpose = isset($data['purpose']) ? strtolower($data['purpose']) : null;
$user_id = $_SESSION['user_id'];

if (!$title || !$purpose) {
    echo json_encode(["success" => false, "message" => "Missing data."]);
    exit;
}

$allowedPurposes = ['read', 'borrow'];
if (!in_array($purpose, $allowedPurposes, true)) {
    echo json_encode(["success" => false, "message" => "Invalid reservation purpose."]);
    exit;
}

// // ✅ Check if the book is available
// $check = $conn->prepare("SELECT status FROM book_inventory WHERE item_id = ?");
// $check->bind_param("i", $item_id);
// $check->execute();
// $result = $check->get_result();

// if ($result->num_rows == 0) {
//     echo json_encode(["success" => false, "message" => "Book not found."]);
//     exit;
// }

// $row = $result->fetch_assoc();
// if ($row['status'] !== 'Available') {
//     echo json_encode(["success" => false, "message" => "Book is not available."]);
//     exit;
// }

// Find one available copy based on the title
$remarksRestriction = $purpose === 'borrow' ? "AND (remarks IS NULL OR remarks = '' OR UPPER(remarks) <> 'R')" : "";
$sql = "
    SELECT item_id 
    FROM book_inventory
    WHERE title = ? 
      AND status = 'Available'
      $remarksRestriction
    ORDER BY item_id ASC
    LIMIT 1
";
$find = $conn->prepare($sql);
$find->bind_param("s", $title);
$find->execute();
$itemResult = $find->get_result();

if ($itemResult->num_rows == 0) {
    $message = "No available copies for this title.";
    if ($purpose === 'borrow') {
        $message = "No borrowable copies available for this title. It may be restricted to in-library use.";
    }
    echo json_encode(["success" => false, "message" => $message]);
    exit;
}

$item = $itemResult->fetch_assoc();
$item_id = $item['item_id'];  // <-- This is the specific copy that will be reserved


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