<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../connection.php';
require __DIR__ . '/../mailer.php'; // import your sendEmail() function

// Query overdue books that need notification
$sql = "
    SELECT o.borrow_id, o.user_id, u.email, u.first_name, b.book_title, o.date_overdue_detected
    FROM overdue_log o
    JOIN users u ON o.user_id = u.id
    JOIN book_inventory b ON o.book_id = b.item_id
    JOIN borrow_log bl ON o.borrow_id = bl.id
    WHERE bl.date_returned IS NULL
      AND (
            DATEDIFF(CURDATE(), o.date_overdue_detected) = 0   -- first day overdue
         OR DATEDIFF(CURDATE(), o.date_overdue_detected) % 3 = 0 -- every 3 days
      )
";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $toEmail = $row['email'];
        $toName  = $row['first_name'] ?? ''; // fallback if no name column
        $subject = "Library Notice: Overdue Book Reminder";

        // Calculate how many days since overdue
        $days = (new DateTime($row['date_overdue_detected']))->diff(new DateTime())->days;

        $bodyHtml = "
            <p>Dear {$toName},</p>
            <p>Our records show that your borrowed book <strong>{$row['book_title']}</strong> 
            is overdue. It has been <strong>{$days} day(s)</strong> since the due date.</p>
            <p>Please return the book as soon as possible to avoid further action.</p>
            <p>Thank you,<br>Libraprint</p>
        ";

        $resultSend = sendEmail($toEmail, $toName, $subject, $bodyHtml);

        if ($resultSend['status'] === 'success') {
            echo "Email sent to {$toEmail}<br>";
        } else {
            echo "Failed to send email to {$toEmail}: {$resultSend['message']}<br>";
        }
    }
} else {
    echo "No overdue notifications needed today.<br>";
}

$conn->close();