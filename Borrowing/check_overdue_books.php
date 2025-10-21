<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../connection.php';


// Step 1: Mark as overdue if borrowed for more than 7 days and not returned
$sql1 = "
    UPDATE borrow_log
    SET status = 'Overdue'
    WHERE date_returned IS NULL
    AND status != 'Overdue'
    AND DATEDIFF(CURDATE(), date_borrowed) > 7
";
if ($conn->query($sql1) === TRUE) {
    $affected1 = $conn->affected_rows;
    echo "Step 1 success — $affected1 borrow_log record(s) marked as Overdue.<br>";
} else {
    echo "Error Step 1: " . $conn->error . "<br>";
}

// Step 1.5: Insert new overdue records into overdue_log if not already logged
$sql_insert = "
    INSERT INTO overdue_log (borrow_id, user_id, book_id, date_overdue_detected, days_overdue)
    SELECT 
        b.id AS borrow_id,
        b.user_id,
        b.book_id,
        NOW() AS date_overdue_detected,
        DATEDIFF(CURDATE(), b.date_borrowed) - 7 AS days_overdue
    FROM borrow_log AS b
    WHERE b.date_returned IS NULL
      AND b.status = 'Overdue'
      AND DATEDIFF(CURDATE(), b.date_borrowed) > 7
      AND b.id NOT IN (SELECT borrow_id FROM overdue_log)
";


if ($conn->query($sql_insert) === TRUE) {
    $affected3 = $conn->affected_rows;
    echo "Step 1.5 success — $affected3 new overdue_log record(s) added.<br>";
} else {
    echo "Error Step 1.5: " . $conn->error . "<br>";
}


// Step 2: Mark corresponding books as Missing
$sql2 = "
    UPDATE book_inventory
    JOIN (
        SELECT book_id
        FROM borrow_log
        WHERE date_returned IS NULL
        AND status = 'Overdue'
    ) AS overdue_books
    ON book_inventory.item_id = overdue_books.book_id
    SET book_inventory.status = 'Missing'
";
if ($conn->query($sql2) === TRUE) {
    $affected2 = $conn->affected_rows;
    echo "Step 2 success — $affected2 book_inventory record(s) marked as Missing.<br>";
} else {
    echo "Error Step 2: " . $conn->error . "<br>";
}

// Step 3: Log delinquent borrowers (those with at least 1 overdue book)
$sql3 = "
    INSERT INTO delinquent_log (user_id, total_overdue_books, logged_at)
    SELECT 
        b.user_id,
        COUNT(*) AS total_overdue_books,
        NOW()
    FROM borrow_log AS b
    WHERE b.status = 'Overdue'
    GROUP BY b.user_id
    HAVING COUNT(*) >= 1
    ON DUPLICATE KEY UPDATE 
        total_overdue_books = VALUES(total_overdue_books),
        logged_at = NOW()
";
if ($conn->query($sql3) === TRUE) {
    echo "Step 3 success — delinquent borrowers logged.<br>";
} else {
    echo "Error Step 3: " . $conn->error . "<br>";
}


$conn->close();