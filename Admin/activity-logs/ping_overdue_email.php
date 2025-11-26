<?php
require_once __DIR__ . '/../../inc/auth_admin.php';
require '../../connection.php';
require '../../mailer.php';
require_once __DIR__ . '/../../inc/user_book_config.php';

header('Content-Type: application/json');

if (!isset($_POST['borrow_id']) || empty($_POST['borrow_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Borrow ID is required']);
    exit;
}

$borrow_id = (int)$_POST['borrow_id'];

lp_ensure_user_book_config_table($conn);
$settings = lp_get_default_borrow_settings($conn);
$default_due_days = (int) $settings['book_due_date_days'];
$default_due_days = $default_due_days > 0 ? $default_due_days : 7;

// Get overdue information directly from borrow_log, bypassing interval check
// This works even if there's no entry in overdue_log yet
// We check if the book is actually overdue based on date calculation, not just status
$sql = "
    SELECT 
        bl.id AS borrow_id, 
        bl.user_id, 
        bl.book_id,
        bl.status,
        bl.date_returned,
        u.email, 
        u.first_name, 
        b.title, 
        bl.date_borrowed,
        COALESCE(cfg.due_date_days, $default_due_days) AS effective_due_days,
        GREATEST(
            TIMESTAMPDIFF(
                DAY, 
                DATE_ADD(bl.date_borrowed, INTERVAL COALESCE(cfg.due_date_days, $default_due_days) DAY),
                NOW()
            ),
            0
        ) AS days_overdue
    FROM borrow_log bl
    JOIN users u ON bl.user_id = u.user_id
    JOIN book_inventory b ON bl.book_id = b.item_id
    LEFT JOIN user_book_configurations AS cfg 
        ON cfg.user_id = bl.user_id AND cfg.book_id = bl.book_id
    WHERE bl.id = ?
      AND bl.date_returned IS NULL
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $borrow_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Borrow record not found or book has already been returned.']);
    $stmt->close();
    $conn->close();
    exit;
}

$row = $result->fetch_assoc();
$stmt->close();

// Calculate due date and days overdue manually for verification
$date_borrowed = new DateTime($row['date_borrowed']);
$effective_due_days = (int)($row['effective_due_days'] ?? $default_due_days);
$due_date = clone $date_borrowed;
$due_date->modify("+{$effective_due_days} days");
$now = new DateTime();

// Calculate days overdue: if due_date is in the past, calculate the difference
if ($due_date < $now) {
    $interval = $now->diff($due_date);
    $days_overdue_calc = (int)$interval->format('%a'); // Total days
    // If due date was in the past, make sure it's positive
    if ($days_overdue_calc < 0) {
        $days_overdue_calc = abs($days_overdue_calc);
    }
} else {
    $days_overdue_calc = 0;
}

// Use the SQL calculated value, but verify with PHP calculation
$days_overdue_sql = (int)$row['days_overdue'];

// Use whichever is higher (more accurate) - sometimes SQL and PHP calculations differ slightly
$days_overdue = max($days_overdue_sql, $days_overdue_calc);

if ($days_overdue < 1) {
    $due_date_str = $due_date->format('Y-m-d H:i:s');
    $now_str = $now->format('Y-m-d H:i:s');
    $borrowed_str = $date_borrowed->format('Y-m-d H:i:s');
    
    echo json_encode([
        'status' => 'error', 
        'message' => "Book is not overdue yet. Days overdue: $days_overdue. Debug info: Borrowed: $borrowed_str, Due date: $due_date_str (loan period: $effective_due_days days), Current time: $now_str"
    ]);
    $conn->close();
    exit;
}

$toEmail = $row['email'];
$toName  = $row['first_name'] ?? '';
$subject = "Library Notice: Overdue Book Reminder";

$daysOverdue = max(0, $row['days_overdue']);
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
    // Update or insert overdue_log entry with last_email_sent timestamp
    // First, check if entry exists
    $check_stmt = $conn->prepare("SELECT borrow_id FROM overdue_log WHERE borrow_id = ?");
    $check_stmt->bind_param("i", $borrow_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_stmt->close();
    
    if ($check_result->num_rows > 0) {
        // Update existing entry
        $update_stmt = $conn->prepare("UPDATE overdue_log SET last_email_sent = NOW() WHERE borrow_id = ?");
        $update_stmt->bind_param("i", $borrow_id);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        // Insert new entry if it doesn't exist
        $days_overdue = (int)$row['days_overdue'];
        $insert_stmt = $conn->prepare("
            INSERT INTO overdue_log (borrow_id, user_id, book_id, date_overdue_detected, days_overdue, last_email_sent, status)
            VALUES (?, ?, ?, NOW(), ?, NOW(), 'unreturned')
        ");
        $insert_stmt->bind_param("iiii", $borrow_id, $row['user_id'], $row['book_id'], $days_overdue);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
    
    echo json_encode([
        'status' => 'success', 
        'message' => "Email sent successfully to {$toEmail}",
        'sent_at' => date('Y-m-d H:i:s')
    ]);
} else {
    echo json_encode([
        'status' => 'error', 
        'message' => "Failed to send email: {$resultSend['message']}"
    ]);
}

$conn->close();

