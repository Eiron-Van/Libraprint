<?php
require_once __DIR__ . '/../../inc/auth_admin.php';
include '../../connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barcode = trim($_POST['barcode'] ?? '');

    if (empty($barcode)) {
        echo json_encode(["success" => false, "error" => "No barcode provided"]);
        exit;
    }

    // 1️⃣ Find book by barcode
    $stmt = $conn->prepare("SELECT item_id, title, status FROM book_inventory WHERE barcode = ?");
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

    // 3️⃣ Get the latest borrow record
    $borrowStmt = $conn->prepare("
        SELECT b.id, b.user_id, u.first_name, u.last_name, b.date_borrowed, 
               DATEDIFF(CURDATE(), b.date_borrowed) AS days_borrowed
        FROM borrow_log b
        JOIN users u ON b.user_id = u.user_id
        WHERE b.book_id = ? AND b.status IN ('Borrowed', 'Overdue')
        ORDER BY b.id DESC LIMIT 1
    ");
    $borrowStmt->bind_param("i", $book['item_id']);
    $borrowStmt->execute();
    $borrow = $borrowStmt->get_result()->fetch_assoc();

    if (!$borrow) {
        echo json_encode(["success" => false, "error" => "No active borrow record found"]);
        exit;
    }

    // 4️⃣ Calculate overdue days and penalty
    $daysOverdue = max(0, $borrow['days_borrowed'] - 7);
    $penalty = $daysOverdue * 1; // ₱1 per day

    // 5️⃣ Update book to Available
    $updateBook = $conn->prepare("UPDATE book_inventory SET status = 'Available' WHERE barcode = ?");
    $updateBook->bind_param("s", $barcode);
    $updateBook->execute();

    // 6️⃣ Update borrow_log (mark returned)
    $updateBorrow = $conn->prepare("
        UPDATE borrow_log 
        SET date_returned = NOW(), status = 'Returned'
        WHERE id = ?
    ");
    $updateBorrow->bind_param("i", $borrow['id']);
    $updateBorrow->execute();

    // 7️⃣ Update overdue_log if exists
    $updateOverdue = $conn->prepare("
        UPDATE overdue_log 
        SET status = 'Returned'
        WHERE borrow_id = ?
    ");
    $updateOverdue->bind_param("i", $borrow['id']);
    $updateOverdue->execute();

    // 8️⃣ Return receipt info
    echo json_encode([
        "success" => true,
        "overdue" => $daysOverdue > 0,
        "days_overdue" => $daysOverdue,
        "penalty" => $penalty,
        "book_title" => $book['title'],
        "borrower" => $borrow['first_name'] . " " . $borrow['last_name']
    ]);
}