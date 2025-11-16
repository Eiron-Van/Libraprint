<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../connection.php';
require __DIR__ . '/../mailer.php';

// Step 0: Get overdue email interval from settings
$sql = "SELECT setting_value FROM settings WHERE setting_name = 'overdue_email_interval_days'";
$result = $conn->query($sql);

$interval_days = 3; // default if not set
if ($result && $row = $result->fetch_assoc()) {
    $interval_days = (int)$row['setting_value'];
    if ($interval_days < 1) { $interval_days = 3; }
}

// Prepare SQL using day intervals instead of minutes
$sql = "
    SELECT 
        o.borrow_id, 
        o.user_id, 
        u.email, 
        u.first_name, 
        b.title, 
        o.date_overdue_detected,
        o.last_email_sent,
        bl.date_borrowed,
        TIMESTAMPDIFF(DAY, bl.date_borrowed, NOW()) AS days_overdue
    FROM overdue_log o
    JOIN users u ON o.user_id = u.user_id
    JOIN book_inventory b ON o.book_id = b.item_id
    JOIN borrow_log bl ON o.borrow_id = bl.id
    WHERE bl.date_returned IS NULL
      AND bl.status = 'Overdue'
      AND TIMESTAMPDIFF(DAY, bl.date_borrowed, NOW()) >= 1
      AND (
        o.last_email_sent IS NULL
        OR TIMESTAMPDIFF(DAY, o.last_email_sent, NOW()) >= $interval_days
      )
";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $toEmail = $row['email'];
        $toName  = $row['first_name'] ?? '';
        $subject = "Library Notice: Overdue Book Reminder";

        $daysOverdue = max(0, $row['days_overdue']);

        // Format time display
        $timeText = "$daysOverdue day(s)";

        $bodyHtml = "
            <p>Dear {$toName},</p>
            <p>Our records show that your borrowed book <strong>{$row['title']}</strong> 
            is overdue. It has been <strong>{$timeText}</strong> since the due date.</p>
            <p>Please return the book as soon as possible to avoid further action.</p>
            <p>Thank you,<br>Libraprint</p>
        ";

        $resultSend = sendEmail($toEmail, $toName, $subject, $bodyHtml);

        if ($resultSend['status'] === 'success') {
            $update_stmt = $conn->prepare("UPDATE overdue_log SET last_email_sent = NOW() WHERE borrow_id = ?");
            $update_stmt->bind_param("i", $row['borrow_id']);
            $update_stmt->execute();
            $update_stmt->close();
            echo "Email sent to {$toEmail} at " . date('Y-m-d H:i:s') . "<br>";
        } else {
            echo "Failed to send email to {$toEmail}: {$resultSend['message']}<br>";
        }
    }
} else {
    echo "No overdue notifications needed at this time.<br>";
}

$conn->close();
