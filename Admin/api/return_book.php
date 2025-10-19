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

    // 1️⃣ Find the book by barcode
    $stmt = $conn->prepare("SELECT item_id, status FROM book_inventory WHERE barcode = ?");
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();

    if (!$book) {
        echo json_encode(["success" => false, "error" => "Book not found"]);
        exit;
    }

    // 2️⃣ Validate book status
    if ($book['status'] !== 'Checked Out' && $book['status'] !== 'Missing') {
        echo json_encode(["success" => false, "error" => "Book is not checked out or missing"]);
        exit;
    }

    // 3️⃣ Update book_inventory to Available
    $updateBook = $conn->prepare("UPDATE book_inventory SET status = 'Available' WHERE barcode = ?");
    $updateBook->bind_param("s", $barcode);
    $updateBook->execute();

    // 4️⃣ Update borrow_log (mark as Returned)
    $updateBorrow = $conn->prepare("
        UPDATE borrow_log 
        SET date_returned = NOW(), status = 'Returned'
        WHERE book_id = ? AND status IN ('Borrowed', 'Overdue')
        ORDER BY id DESC LIMIT 1
    ");
    $updateBorrow->bind_param("i", $book['item_id']);
    $updateBorrow->execute();

    // 5️⃣ Update claim_log (mark as returned)
    $updateClaim = $conn->prepare("
        UPDATE claim_log 
        SET is_returned = 1 
        WHERE item_id = ? AND is_returned = 0
    ");
    $updateClaim->bind_param("i", $book['item_id']);
    $updateClaim->execute();

    // 6️⃣ Update overdue_log (if exists)
    $updateOverdue = $conn->prepare("
        UPDATE overdue_log AS o
        JOIN borrow_log AS b ON o.borrow_id = b.id
        SET o.status = 'Returned'
        WHERE o.book_id = ? AND b.status = 'Returned'
    ");
    $updateOverdue->bind_param("i", $book['item_id']);
    $updateOverdue->execute();

    echo json_encode(["success" => true]);
}