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


$conn->close();