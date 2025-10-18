<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../connection.php';

// Step 1: Mark as overdue if borrowed for more than 7 days and not returned
$conn->query("
    UPDATE borrow_log
    SET status = 'Overdue'
    WHERE date_returned IS NULL
    AND status != 'Overdue'
    AND DATEDIFF(CURDATE(), date_borrowed) > 7
");

// Step 2: Mark corresponding books as missing
$conn->query("
    UPDATE book_inventory
    SET status = 'Missing'
    WHERE item_id IN (
        SELECT book_id
        FROM borrow_log
        WHERE date_returned IS NULL
        AND status = 'Overdue'
    )
");

$conn->close();