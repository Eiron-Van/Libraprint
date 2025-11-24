<?php
require_once __DIR__ . '/../../inc/auth_admin.php';
require '../../connection.php';
require_once __DIR__ . '/../../inc/user_book_config.php';
require_once __DIR__ . '/../../mailer.php';

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
    $defaults = lp_get_default_borrow_settings($conn);
    $defaultDueDays = (int) $defaults['book_due_date_days'];

    $existingDue = null;
    $existingStmt = $conn->prepare("SELECT due_date_days FROM user_book_configurations WHERE user_id = ? AND book_id = ? LIMIT 1");
    if ($existingStmt) {
        $existingStmt->bind_param("si", $userId, $bookId);
        $existingStmt->execute();
        $existingStmt->bind_result($existingDueRaw);
        if ($existingStmt->fetch()) {
            $existingDue = $existingDueRaw !== null ? (int) $existingDueRaw : null;
        }
        $existingStmt->close();
    }

    $notificationPlan = [
        'should_send' => false,
        'new_due_days' => null,
    ];

    $dueDateDays = null;
    $intervalDays = null;
    $responseMessage = 'Configuration saved.';

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
        $responseMessage = 'Configuration cleared.';
        $previousEffective = $existingDue !== null ? $existingDue : $defaultDueDays;
        if ($previousEffective !== $defaultDueDays) {
            $notificationPlan = [
                'should_send' => true,
                'new_due_days' => $defaultDueDays,
            ];
        }
    } else {
        $hasDue = array_key_exists('due_date_days', $payload);
        $hasInterval = array_key_exists('overdue_email_interval_days', $payload);

        if (!$hasDue && !$hasInterval) {
            throw new RuntimeException('No configuration changes were supplied.');
        }

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

        if ($hasDue) {
            $newDue = $dueDateDays !== null ? $dueDateDays : $defaultDueDays;
            $previousEffective = $existingDue !== null ? $existingDue : $defaultDueDays;
            if ($newDue !== $previousEffective) {
                $notificationPlan = [
                    'should_send' => true,
                    'new_due_days' => $newDue,
                ];
            }
        }
    }

    $notificationOutcome = ['sent' => false];
    if ($notificationPlan['should_send']) {
        $userStmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE user_id = ? LIMIT 1");
        if ($userStmt) {
            $userStmt->bind_param("s", $userId);
            $userStmt->execute();
            $userStmt->bind_result($firstName, $lastName, $email);
            $hasUser = $userStmt->fetch();
            $userStmt->close();
        } else {
            throw new RuntimeException('Prepare failed when fetching user: ' . $conn->error);
        }

        $bookStmt = $conn->prepare("SELECT title FROM book_inventory WHERE item_id = ? LIMIT 1");
        if ($bookStmt) {
            $bookStmt->bind_param("i", $bookId);
            $bookStmt->execute();
            $bookStmt->bind_result($bookTitle);
            $hasBook = $bookStmt->fetch();
            $bookStmt->close();
        } else {
            throw new RuntimeException('Prepare failed when fetching book: ' . $conn->error);
        }

        if ($hasUser && $hasBook && !empty($email)) {
            $displayName = trim($firstName . ' ' . $lastName);
            $subject = "Updated overdue days for {$bookTitle}";
            $newDue = (int) $notificationPlan['new_due_days'];
            $bodyHtml = "
                <p>Dear {$displayName},</p>
                <p>The library has updated the overdue threshold for <strong>{$bookTitle}</strong>.</p>
                <p><strong>New overdue days:</strong> {$newDue} day(s)</p>
                <p>Please make sure to return the book on or before the updated due window to avoid penalties.</p>
                <p>Thank you,<br>Libraprint</p>
            ";
            $emailResult = sendEmail($email, $displayName, $subject, $bodyHtml);
            $notificationOutcome['sent'] = $emailResult['status'] === 'success';
            if (!$notificationOutcome['sent']) {
                $notificationOutcome['error'] = $emailResult['message'] ?? 'Unable to send email';
            }
        } else {
            if (!$hasUser) {
                $notificationOutcome['error'] = 'Borrower record not found.';
            } elseif (!$hasBook) {
                $notificationOutcome['error'] = 'Book record not found.';
            } else {
                $notificationOutcome['error'] = 'Borrower email missing.';
            }
        }
    }

    echo json_encode([
        'success' => true,
        'message' => $responseMessage,
        'data' => [
            'due_date_days' => $dueDateDays,
            'overdue_email_interval_days' => $intervalDays,
        ],
        'notification' => $notificationPlan + $notificationOutcome,
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

