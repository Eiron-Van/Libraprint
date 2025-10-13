<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../../connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barcode = trim($_POST['barcode'] ?? '');

    if (empty($barcode)) {
        echo json_encode(["success" => false, "error" => "No barcode provided"]);
        exit;
    }

    // 1️⃣ Find the book
    $stmt = $conn->prepare("SELECT item_id, status FROM book_inventory WHERE barcode = ?");
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();

    if (!$book) {
        echo json_encode(["success" => false, "error" => "Book not found"]);
        exit;
    }

    // 2️⃣ Check if book is checked out or missing
    if ($book['status'] !== 'checked out' && $book['status'] !== 'missing') {
        echo json_encode(["success" => false, "error" => "Book already available"]);
        exit;
    }

    // 3️⃣ Update book status to available
    $update = $conn->prepare("UPDATE book_inventory SET status = 'available' WHERE barcode = ?");
    $update->bind_param("s", $barcode);
    $update->execute();

    // 4️⃣ Update borrow_log if needed
    $log = $conn->prepare("UPDATE borrow_log SET date_returned = NOW(), status = 'Returned' 
                           WHERE book_id = ? AND status = 'Borrowed'");
    $log->bind_param("i", $book['item_id']);
    $log->execute();

    // 5️⃣ Update claim_log if needed
    $claim = $conn->prepare("UPDATE claim_log SET is_returned = 1 
                             WHERE item_id = ? AND is_returned = 0");
    $claim->bind_param("i", $book['item_id']);
    $claim->execute();

    echo json_encode(["success" => true]);
}

