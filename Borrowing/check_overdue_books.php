<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../connection.php';

// Step 0: Get book due date setting
$sql_settings = "SELECT setting_value FROM settings WHERE setting_name = 'book_due_date_days'";
$result_settings = $conn->query($sql_settings);
if (!$result_settings) {
    die("Error getting settings: " . $conn->error);
}
$row_settings = $result_settings->fetch_assoc();
$due_date_days = $row_settings ? (int)$row_settings['setting_value'] : 7; // fallback to 7
if ($due_date_days < 1) { $due_date_days = 7; }
$due_date_minutes = $due_date_days * 24 * 60;
echo "Current overdue threshold is <strong>$due_date_days day(s)</strong>.<br>";

// Demo: Overdue threshold set to 1 minute instead of 7 days
// AND TIMESTAMPDIFF(MINUTE, date_borrowed, NOW()) > 1

// Step 1: Mark as overdue if borrowed for more than 1 minute and not returned
$sql1 = "
    UPDATE borrow_log
    SET status = 'Overdue'
    WHERE date_returned IS NULL
    AND status != 'Overdue'
    AND TIMESTAMPDIFF(DAY, date_borrowed, NOW()) >= $due_date_days
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
        TIMESTAMPDIFF(MINUTE, b.date_borrowed, NOW()) - 1 AS days_overdue
    FROM borrow_log AS b
    WHERE b.date_returned IS NULL
      AND b.status = 'Overdue'
      AND TIMESTAMPDIFF(DAY, date_borrowed, NOW()) >= $due_date_days
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
// Only insert user_ids that exist in users table to avoid foreign key constraint errors
$sql3 = "
    INSERT INTO delinquent_log (user_id, total_overdue_books, logged_at)
    SELECT 
        b.user_id,
        COUNT(*) AS total_overdue_books,
        NOW()
    FROM borrow_log AS b
    INNER JOIN users AS u ON b.user_id = u.user_id
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

$sql_update_days = "
    UPDATE overdue_log AS o
    JOIN borrow_log AS b ON o.borrow_id = b.id
    SET o.days_overdue = TIMESTAMPDIFF(DAY, b.date_borrowed, NOW())
    WHERE b.date_returned IS NULL
      AND b.status = 'Overdue'
";
if ($conn->query($sql_update_days) === TRUE) {
    $affected4 = $conn->affected_rows;
    echo "Step 1.6 success — $affected4 overdue_log record(s) updated with new days_overdue.<br>";
} else {
    echo "Error Step 1.6: " . $conn->error . "<br>";
}

$conn->close();