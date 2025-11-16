<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../../../connection.php';

header('Content-Type: application/json');

try {
    // Get the book due date setting
    $sql = "SELECT setting_value FROM settings WHERE setting_name = 'book_due_date_days'";
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }
    
    $row = $result->fetch_assoc();
    $due_date_days = $row ? (int)$row['setting_value'] : 7;
    
    // Validate
    if ($due_date_days < 1) {
        throw new Exception("Invalid due date setting");
    }
    
    echo json_encode([
        'success' => true,
        'due_date_days' => $due_date_days
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();