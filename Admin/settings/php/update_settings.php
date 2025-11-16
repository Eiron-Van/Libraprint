<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../../../connection.php';

header('Content-Type: application/json');

try {
    // Check if POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method. POST required.");
    }
    
    // Get the JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    $resp = ['success' => true];

    // --- Book Due Date ---
    if (isset($data['book_due_date'])) {
        $due_date = (int)$data['book_due_date'];
        if ($due_date < 1) {
            throw new Exception("Due date must be at least 1 day");
        }
        if ($due_date > 365) {
            throw new Exception("Due date cannot exceed 365 days");
        }
        $sql = "
            INSERT INTO settings (setting_name, setting_value)
            VALUES ('book_due_date_days', ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed for book_due_date: " . $conn->error);
        }
        $stmt->bind_param("i", $due_date);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed for book_due_date: " . $stmt->error);
        }
        $resp['due_date_days'] = $due_date;
        $resp['message'] = "Book due date updated to $due_date day(s)";
        $stmt->close();
    }
    // --- Overdue Email Interval ---
    if (isset($data['overdue_email_interval'])) {
        $interval = (int)$data['overdue_email_interval'];
        if ($interval < 1) {
            throw new Exception("Overdue email interval must be at least 1 day");
        }
        if ($interval > 30) {
            throw new Exception("Overdue email interval cannot exceed 30 days");
        }
        $sql = "
            INSERT INTO settings (setting_name, setting_value)
            VALUES ('overdue_email_interval_days', ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed for email interval: " . $conn->error);
        }
        $stmt->bind_param("i", $interval);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed for email interval: " . $stmt->error);
        }
        $resp['overdue_email_interval_days'] = $interval;
        $resp['interval_message'] = "Overdue email interval set to $interval day(s)";
        $stmt->close();
    }
    // If nothing was sent
    if (!isset($data['book_due_date']) && !isset($data['overdue_email_interval'])) {
        throw new Exception("No valid setting parameter provided.");
    }
    echo json_encode($resp);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
