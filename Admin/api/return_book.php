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
    $stmt = $conn->prepare("SELECT item_id, status, title FROM book_inventory WHERE barcode = ?");
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();

    if (!$book) {
        echo json_encode(["success" => false, "error" => "Book not found"]);
        exit;
    }

    // 2️⃣ Validate book status
    if ($book['status'] !== 'Checked Out' && $book['status'] !== 'Missing' && $book['status'] !== 'Overdue') {
        echo json_encode(["success" => false, "error" => "Book is not checked out, missing, or overdue"]);
        exit;
    }

    // 3️⃣ Get borrow log
    $borrowQuery = $conn->prepare("
        SELECT id, user_id, DATEDIFF(CURDATE(), date_borrowed) AS days_borrowed, status
        FROM borrow_log
        WHERE book_id = ? AND status IN ('Borrowed', 'Overdue')
        ORDER BY id DESC LIMIT 1
    ");
    $borrowQuery->bind_param("i", $book['item_id']);
    $borrowQuery->execute();
    $borrow = $borrowQuery->get_result()->fetch_assoc();

    $days_overdue = 0;
    $fine = 0;

    if ($borrow) {
        $days_overdue = max(0, $borrow['days_borrowed'] - 7);
        $fine = $days_overdue > 0 ? $days_overdue * 1 : 0; // ₱1 per day
    }

    // 4️⃣ Update book to Available
    $updateBook = $conn->prepare("UPDATE book_inventory SET status = 'Available' WHERE barcode = ?");
    $updateBook->bind_param("s", $barcode);
    $updateBook->execute();

    // 5️⃣ Mark borrow log as Returned
    $updateBorrow = $conn->prepare("
        UPDATE borrow_log 
        SET date_returned = NOW(), status = 'Returned'
        WHERE book_id = ? AND status IN ('Borrowed', 'Overdue')
        ORDER BY id DESC LIMIT 1
    ");
    $updateBorrow->bind_param("i", $book['item_id']);
    $updateBorrow->execute();

    // 6️⃣ Mark claim log as returned
    $updateClaim = $conn->prepare("
        UPDATE claim_log 
        SET is_returned = 1 
        WHERE item_id = ? AND is_returned = 0
    ");
    $updateClaim->bind_param("i", $book['item_id']);
    $updateClaim->execute();

    // ✅ Respond with overdue info if any
    echo json_encode([
        "success" => true,
        "book_title" => $book['title'],
        "overdue" => $days_overdue > 0,
        "days_overdue" => $days_overdue,
        "fine" => $fine,
        "borrow_id" => $borrow['id'] ?? null
    ]);
}
