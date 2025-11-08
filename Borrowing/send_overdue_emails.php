<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../connection.php';
require __DIR__ . '/../mailer.php'; // import your sendEmail() function

// Add last_email_sent column to overdue_log if it doesn't exist (for demo: tracking email intervals)
// Check if column exists before adding it
$check_column = $conn->query("SHOW COLUMNS FROM overdue_log LIKE 'last_email_sent'");
if ($check_column->num_rows == 0) {
    $alter_sql = "ALTER TABLE overdue_log ADD COLUMN last_email_sent DATETIME NULL";
    if (!$conn->query($alter_sql)) {
        echo "Warning: Could not add last_email_sent column: " . $conn->error . "<br>";
    }
}

// Query overdue books that need notification
// Send email when overdue is detected, then every 2 minutes
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
        TIMESTAMPDIFF(MINUTE, bl.date_borrowed, NOW()) AS minutes_overdue
    FROM overdue_log o
    JOIN users u ON o.user_id = u.user_id
    JOIN book_inventory b ON o.book_id = b.item_id
    JOIN borrow_log bl ON o.borrow_id = bl.id
    WHERE bl.date_returned IS NULL
      AND bl.status = 'Overdue'
      AND TIMESTAMPDIFF(MINUTE, bl.date_borrowed, NOW()) > 1
      AND (
        -- Send email if overdue is just detected (no previous email sent)
        o.last_email_sent IS NULL
        -- OR send email every 2 minutes since last email
        OR TIMESTAMPDIFF(MINUTE, o.last_email_sent, NOW()) >= 2
      )
";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $toEmail = $row['email'];
        $toName  = $row['first_name'] ?? ''; // fallback if no name column
        $subject = "Library Notice: Overdue Book Reminder";

        // Calculate how many minutes since overdue (demo: using minutes instead of days)
        $minutesOverdue = max(0, $row['minutes_overdue'] - 1);
        
        // Format time display
        if ($minutesOverdue < 60) {
            $timeText = "{$minutesOverdue} minute(s)";
        } else {
            $hours = floor($minutesOverdue / 60);
            $minutes = $minutesOverdue % 60;
            $timeText = "{$hours} hour(s) and {$minutes} minute(s)";
        }

        $bodyHtml = "
            <p>Dear {$toName},</p>
            <p>Our records show that your borrowed book <strong>{$row['title']}</strong> 
            is overdue. It has been <strong>{$timeText}</strong> since the due date.</p>
            <p>Please return the book as soon as possible to avoid further action.</p>
            <p>Thank you,<br>Libraprint</p>
        ";

        $resultSend = sendEmail($toEmail, $toName, $subject, $bodyHtml);

        if ($resultSend['status'] === 'success') {
            // Update last_email_sent timestamp
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