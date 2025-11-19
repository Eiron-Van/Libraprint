<?php
require_once __DIR__ . '/../../inc/auth_admin.php';
require '../../connection.php';
require_once __DIR__ . '/../../inc/user_book_config.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        throw new RuntimeException('Invalid request method.');
    }

    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) {
        throw new RuntimeException('Invalid JSON payload.');
    }

    $userId = isset($payload['user_id']) ? trim($payload['user_id']) : '';
    $bookId = isset($payload['book_id']) ? (int) $payload['book_id'] : 0;
    $clear = !empty($payload['clear']);

    if ($userId === '' || $bookId <= 0) {
        throw new RuntimeException('Missing user_id or book_id.');
    }

    lp_ensure_user_book_config_table($conn);

    if ($clear) {
        $stmt = $conn->prepare("DELETE FROM user_book_configurations WHERE user_id = ? AND book_id = ?");
        if (!$stmt) {
            throw new RuntimeException('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("si", $userId, $bookId);
        if (!$stmt->execute()) {
            throw new RuntimeException('Failed to reset configuration: ' . $stmt->error);
        }
        $stmt->close();

        echo json_encode(['success' => true, 'message' => 'Configuration cleared.']);
        exit;
    }

    $hasDue = array_key_exists('due_date_days', $payload);
    $hasInterval = array_key_exists('overdue_email_interval_days', $payload);

    if (!$hasDue && !$hasInterval) {
        throw new RuntimeException('No configuration changes were supplied.');
    }

    $dueDateDays = null;
    if ($hasDue) {
        $value = $payload['due_date_days'];
        if ($value === null || $value === '') {
            $dueDateDays = null;
        } else {
            $intValue = (int) $value;
            if ($intValue < 1 || $intValue > 365) {
                throw new RuntimeException('Due date must be between 1 and 365 days.');
            }
            $dueDateDays = $intValue;
        }
    }

    $intervalDays = null;
    if ($hasInterval) {
        $value = $payload['overdue_email_interval_days'];
        if ($value === null || $value === '') {
            $intervalDays = null;
        } else {
            $intValue = (int) $value;
            if ($intValue < 1 || $intValue > 30) {
                throw new RuntimeException('Email interval must be between 1 and 30 days.');
            }
            $intervalDays = $intValue;
        }
    }

    $stmt = $conn->prepare("
        INSERT INTO user_book_configurations (user_id, book_id, due_date_days, overdue_email_interval_days)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            due_date_days = VALUES(due_date_days),
            overdue_email_interval_days = VALUES(overdue_email_interval_days),
            updated_at = CURRENT_TIMESTAMP
    ");

    if (!$stmt) {
        throw new RuntimeException('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("siii", $userId, $bookId, $dueDateDays, $intervalDays);
    if (!$stmt->execute()) {
        throw new RuntimeException('Failed to save configuration: ' . $stmt->error);
    }
    $stmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'Configuration saved.',
        'data' => [
            'due_date_days' => $dueDateDays,
            'overdue_email_interval_days' => $intervalDays,
        ],
    ]);
} catch (Throwable $e) {
    if (http_response_code() === 200) {
        http_response_code(400);
    }
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
} finally {
    $conn->close();
}

