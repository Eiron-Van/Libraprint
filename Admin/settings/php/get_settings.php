<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../../../connection.php';

header('Content-Type: application/json');

try {
    // Query both settings at once
    $sql = "SELECT setting_name, setting_value FROM settings WHERE setting_name IN ('book_due_date_days', 'overdue_email_interval_days')";
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }
    // Defaults (if settings not found)
    $due_date_days = 7;
    $overdue_email_interval_days = 3;
    while ($row = $result->fetch_assoc()) {
        if ($row['setting_name'] === 'book_due_date_days') {
            $due_date_days = (int)$row['setting_value'];
        }
        if ($row['setting_name'] === 'overdue_email_interval_days') {
            $overdue_email_interval_days = (int)$row['setting_value'];
        }
    }
    // Validate
    if ($due_date_days < 1) { throw new Exception("Invalid book due date setting"); }
    if ($overdue_email_interval_days < 1) { throw new Exception("Invalid email interval setting"); }
    echo json_encode([
        'success' => true,
        'due_date_days' => $due_date_days,
        'overdue_email_interval_days' => $overdue_email_interval_days
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
$conn->close();
