<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '../../../connection.php';  // Adjust path to your connection.php

header('Content-Type: application/json');

try {
    // Check if POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method. POST required.");
    }
    
    // Get the JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['book_due_date'])) {
        throw new Exception("book_due_date parameter is required");
    }
    
    $due_date = (int)$data['book_due_date'];
    
    // Validate the input
    if ($due_date < 1) {
        throw new Exception("Due date must be at least 1 day");
    }
    
    if ($due_date > 365) {
        throw new Exception("Due date cannot exceed 365 days");
    }
    
    // Update or insert the setting
    $sql = "
        INSERT INTO settings (setting_name, setting_value)
        VALUES ('book_due_date_days', ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $due_date);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Book due date updated to $due_date day(s)",
        'due_date_days' => $due_date
    ]);
    
    $stmt->close();
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();