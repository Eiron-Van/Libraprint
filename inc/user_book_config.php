<?php
/**
 * Shared helpers for per-user/book borrowing configuration.
 */

if (!function_exists('lp_get_default_borrow_settings')) {
    function lp_get_default_borrow_settings(mysqli $conn): array
    {
        $defaults = [
            'book_due_date_days' => 7,
            'overdue_email_interval_days' => 3,
        ];

        $sql = "
            SELECT setting_name, setting_value
            FROM settings
            WHERE setting_name IN ('book_due_date_days', 'overdue_email_interval_days')
        ";

        if ($result = $conn->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $name = $row['setting_name'];
                $value = (int) $row['setting_value'];
                if ($name === 'book_due_date_days' && $value > 0) {
                    $defaults['book_due_date_days'] = $value;
                } elseif ($name === 'overdue_email_interval_days' && $value > 0) {
                    $defaults['overdue_email_interval_days'] = $value;
                }
            }
            $result->close();
        }

        if ($defaults['book_due_date_days'] < 1) {
            $defaults['book_due_date_days'] = 7;
        }
        if ($defaults['overdue_email_interval_days'] < 1) {
            $defaults['overdue_email_interval_days'] = 3;
        }

        return $defaults;
    }
}

if (!function_exists('lp_ensure_user_book_config_table')) {
    function lp_ensure_user_book_config_table(mysqli $conn): void
    {
        static $ensured = false;
        if ($ensured) {
            return;
        }

        $sql = "
            CREATE TABLE IF NOT EXISTS user_book_configurations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id VARCHAR(64) NOT NULL,
                book_id INT NOT NULL,
                due_date_days INT DEFAULT NULL,
                overdue_email_interval_days INT DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_user_book (user_id, book_id),
                KEY idx_book (book_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        if (!$conn->query($sql)) {
            throw new RuntimeException('Unable to ensure user_book_configurations table: ' . $conn->error);
        }

        $ensured = true;
    }
}

if (!function_exists('lp_get_user_book_config')) {
    function lp_get_user_book_config(mysqli $conn, string $userId, int $bookId): array
    {
        lp_ensure_user_book_config_table($conn);

        $stmt = $conn->prepare("
            SELECT due_date_days, overdue_email_interval_days
            FROM user_book_configurations
            WHERE user_id = ? AND book_id = ?
            LIMIT 1
        ");

        if (!$stmt) {
            throw new RuntimeException('Prepare failed when fetching user/book configuration: ' . $conn->error);
        }

        $stmt->bind_param("si", $userId, $bookId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if (!$row) {
            return [
                'due_date_days' => null,
                'overdue_email_interval_days' => null,
            ];
        }

        return [
            'due_date_days' => $row['due_date_days'] !== null ? (int) $row['due_date_days'] : null,
            'overdue_email_interval_days' => $row['overdue_email_interval_days'] !== null ? (int) $row['overdue_email_interval_days'] : null,
        ];
    }
}

