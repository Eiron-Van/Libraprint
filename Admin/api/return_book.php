<?php
require_once __DIR__ . '/../../inc/auth_admin.php';
include '../../connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barcode = trim($_POST['barcode'] ?? '');
    $condition = trim($_POST['condition'] ?? 'Good Condition');

    if (empty($barcode)) {
        echo json_encode(["success" => false, "error" => "No barcode provided"]);
        exit;
    }

    // Validate condition value
    if ($condition !== 'Good Condition' && $condition !== 'Worn Out Condition') {
        $condition = 'Good Condition'; // Default to Good Condition if invalid
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

    // 3️⃣ Try to get the latest borrow record (if exists)
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

    // 4️⃣ Calculate overdue days & penalty only if borrow record exists
    $daysOverdue = 0;
    $penalty = 0;
    $borrowerName = "No borrower record";

    if ($borrow) {
        $daysOverdue = max(0, $borrow['days_borrowed'] - 7);
        $penalty = $daysOverdue * 1;
        $borrowerName = $borrow['first_name'] . " " . $borrow['last_name'];

        // Update borrow_log (mark returned)
        $updateBorrow = $conn->prepare("
            UPDATE borrow_log 
            SET date_returned = NOW(), status = 'Returned'
            WHERE id = ?
        ");
        $updateBorrow->bind_param("i", $borrow['id']);
        $updateBorrow->execute();

        // Update overdue_log if exists
        $updateOverdue = $conn->prepare("
            UPDATE overdue_log 
            SET status = 'Returned'
            WHERE borrow_id = ?
        ");
        $updateOverdue->bind_param("i", $borrow['id']);
        $updateOverdue->execute();
    }

    // 5️⃣ Always update the book to 'Available' and set book condition
    $updateBook = $conn->prepare("UPDATE book_inventory SET status = 'Available', book_conditioned = ? WHERE barcode = ?");
    $updateBook->bind_param("ss", $condition, $barcode);
    $updateBook->execute();

    // 6️⃣ Return response
    echo json_encode([
        "success" => true,
        "overdue" => $daysOverdue > 0,
        "days_overdue" => $daysOverdue,
        "penalty" => $penalty,
        "book_title" => $book['title'],
        "borrower" => $borrowerName
    ]);
}