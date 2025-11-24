<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../connection.php';
require __DIR__ . '/../mailer.php';
require_once __DIR__ . '/../inc/user_book_config.php';

lp_ensure_user_book_config_table($conn);
$settings = lp_get_default_borrow_settings($conn);
$interval_days = (int) $settings['overdue_email_interval_days'];
$interval_days = $interval_days > 0 ? $interval_days : 3;
$default_due_days = (int) $settings['book_due_date_days'];
$default_due_days = $default_due_days > 0 ? $default_due_days : 7;

// Prepare SQL using day intervals instead of minutes
$sql = "
    SELECT 
        o.borrow_id, 
        o.user_id, 
        o.book_id,
        u.email, 
        u.first_name, 
        b.title, 
        o.date_overdue_detected,
        o.last_email_sent,
        bl.date_borrowed,
        COALESCE(cfg.due_date_days, $default_due_days) AS effective_due_days,
        COALESCE(cfg.overdue_email_interval_days, $interval_days) AS effective_interval_days,
        GREATEST(
            TIMESTAMPDIFF(
                DAY, 
                DATE_ADD(bl.date_borrowed, INTERVAL COALESCE(cfg.due_date_days, $default_due_days) DAY),
                NOW()
            ),
            0
        ) AS days_overdue
    FROM overdue_log o
    JOIN users u ON o.user_id = u.user_id
    JOIN book_inventory b ON o.book_id = b.item_id
    JOIN borrow_log bl ON o.borrow_id = bl.id
    LEFT JOIN user_book_configurations AS cfg 
        ON cfg.user_id = bl.user_id AND cfg.book_id = bl.book_id
    WHERE bl.date_returned IS NULL
      AND bl.status = 'Overdue'
      AND GREATEST(
            TIMESTAMPDIFF(
                DAY, 
                DATE_ADD(bl.date_borrowed, INTERVAL COALESCE(cfg.due_date_days, $default_due_days) DAY),
                NOW()
            ),
            0
        ) >= 1
      AND (
        o.last_email_sent IS NULL
        OR TIMESTAMPDIFF(DAY, o.last_email_sent, NOW()) >= COALESCE(cfg.overdue_email_interval_days, $interval_days)
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

        $effectiveDueDays = (int)($row['effective_due_days'] ?? $default_due_days);

        $bodyHtml = "
            <p>Dear {$toName},</p>
            <p>Our records show that your borrowed book <strong>{$row['title']}</strong> 
            is overdue. It has been <strong>{$timeText}</strong> since the due date ({$effectiveDueDays} day loan).</p>
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
