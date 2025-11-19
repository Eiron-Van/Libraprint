<?php
require_once __DIR__ . '/../../inc/auth_admin.php';
require '../../connection.php';
require 'helpers.php';
require_once __DIR__ . '/../../inc/user_book_config.php';

lp_ensure_user_book_config_table($conn);
$defaults = lp_get_default_borrow_settings($conn);
$defaultDueDays = (int) $defaults['book_due_date_days'];
$defaultIntervalDays = (int) $defaults['overdue_email_interval_days'];

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// ✅ Build Query
if (!empty($search)) {
    $safe_search = "%" . $search . "%";
    $logs = $conn->prepare("
        SELECT 
            br.id AS borrow_id,
            br.user_id,
            br.book_id,
            CONCAT(u.first_name, ' ', u.last_name) AS name,
            b.title AS book_name,
            br.date_borrowed,
            br.date_returned,
            br.status,
            cfg.due_date_days AS custom_due_date_days,
            cfg.overdue_email_interval_days AS custom_email_interval_days
        FROM borrow_log AS br
        JOIN users AS u ON br.user_id = u.user_id
        JOIN book_inventory AS b ON br.book_id = b.item_id
        LEFT JOIN user_book_configurations AS cfg 
            ON cfg.user_id = br.user_id AND cfg.book_id = br.book_id
        WHERE CONCAT(u.first_name, ' ', u.last_name) LIKE ?
           OR b.title LIKE ?
           OR br.status LIKE ?
           OR br.date_borrowed LIKE ?
           OR br.date_returned LIKE ?
        ORDER BY br.id DESC
    ");
    $logs->bind_param("sssss", $safe_search, $safe_search, $safe_search, $safe_search, $safe_search);
} else {
    $logs = $conn->prepare("
        SELECT 
            br.id AS borrow_id,
            br.user_id,
            br.book_id,
            CONCAT(u.first_name, ' ', u.last_name) AS name,
            b.title AS book_name,
            br.date_borrowed,
            br.date_returned,
            br.status,
            cfg.due_date_days AS custom_due_date_days,
            cfg.overdue_email_interval_days AS custom_email_interval_days
        FROM borrow_log AS br
        JOIN users AS u ON br.user_id = u.user_id
        JOIN book_inventory AS b ON br.book_id = b.item_id
        LEFT JOIN user_book_configurations AS cfg 
            ON cfg.user_id = br.user_id AND cfg.book_id = br.book_id
        ORDER BY br.id DESC
    ");
}

$logs->execute();
$logsResult = $logs->get_result();

// ✅ Display
if ($logsResult->num_rows === 0) {
    echo "<div class='text-center text-gray-400 mt-10'>No borrow records found.</div>";
    exit;
}

echo "
<div class='overflow-auto rounded-lg shadow-lg shadow-gray-500/30 max-h-[62vh]'>
    <div class='grid grid-cols-10 p-2 bg-gray-700 sticky top-0 text-center'>
        <div class='flex justify-center items-center col-span-1'>Name</div>
        <div class='flex justify-center items-center col-span-2'>Book Name</div>
        <div class='flex justify-center items-center col-span-2'>Borrowed Date</div>
        <div class='flex justify-center items-center col-span-2'>Return Date</div>
        <div class='flex justify-center items-center col-span-1'>Duration</div>
        <div class='flex justify-center items-center col-span-1'>Status</div>
        <div class='flex justify-center items-center col-span-1'>Config</div>
    </div>
";

while ($row = $logsResult->fetch_assoc()) {
    // Color-code the status
    $status = ucfirst($row['status']);
    $statusColor = match (strtolower($status)) {
        'returned' => 'text-green-600',
        'borrowed' => 'text-blue-600',
        'overdue' => 'text-red-600',
        default => 'text-gray-600'
    };

    $borrowed = strtotime($row['date_borrowed']);
    $returned = strtotime($row['date_returned']);

    if ($borrowed && $returned) {
        $days = floor(($returned - $borrowed) / 86400);
        $duration = $days > 0 ? "$days day" . ($days > 1 ? "s" : "") : "Less than a day";
    } else {
        $duration = "-";
    }

    $effectiveDue = $row['custom_due_date_days'] !== null ? (int) $row['custom_due_date_days'] : $defaultDueDays;
    $effectiveInterval = $row['custom_email_interval_days'] !== null ? (int) $row['custom_email_interval_days'] : $defaultIntervalDays;
    $configSummary = "Due: {$effectiveDue} day(s); Email interval: {$effectiveInterval} day(s)";
    $hasCustom = $row['custom_due_date_days'] !== null || $row['custom_email_interval_days'] !== null;
    $configBadge = $hasCustom ? "<span class='text-xs font-semibold text-amber-600 bg-amber-100 px-2 py-0.5 rounded-full'>Custom</span>" : "<span class='text-xs text-gray-400'>Default</span>";

    echo "
    <div class='grid grid-cols-10 p-2 bg-gray-200 text-center text-sm text-gray-600 border-b border-gray-300 hover:bg-gray-100 transition'>
        <div class='flex justify-center items-center col-span-1'>" . highlightTerms($row['name'], $search) . "</div>
        <div class='flex justify-center items-center col-span-2'>" . highlightTerms($row['book_name'], $search) . "</div>
        <div class='flex justify-center items-center col-span-2'>" . formatDateTime($row['date_borrowed']) . "</div>
        <div class='flex justify-center items-center col-span-2'>" . formatDateTime($row['date_returned'] ?: '-') . "</div>
        <div class='flex justify-center items-center col-span-1'>" . computeDuration($row['date_borrowed'], $row['date_returned']) . "</div>
        <div class='flex justify-center items-center col-span-1 font-semibold $statusColor'>" . highlightTerms($status, $search) . "</div>
        <div class='flex items-center justify-center gap-2 col-span-1'>
            $configBadge
            <button 
                class='config-btn text-gray-500 hover:text-gray-800 focus:outline-none'
                title='" . htmlspecialchars($configSummary, ENT_QUOTES, 'UTF-8') . "'
                data-user-id='" . htmlspecialchars($row['user_id'], ENT_QUOTES, 'UTF-8') . "'
                data-book-id='" . (int) $row['book_id'] . "'
                data-borrow-id='" . (int) $row['borrow_id'] . "'
                data-user-name='" . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . "'
                data-book-title='" . htmlspecialchars($row['book_name'], ENT_QUOTES, 'UTF-8') . "'
                data-default-due='" . $defaultDueDays . "'
                data-default-interval='" . $defaultIntervalDays . "'
                data-custom-due='" . ($row['custom_due_date_days'] !== null ? (int) $row['custom_due_date_days'] : '') . "'
                data-custom-interval='" . ($row['custom_email_interval_days'] !== null ? (int) $row['custom_email_interval_days'] : '') . "'
            >
                <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 12a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm7.5 0a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm6 0a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z' />
                </svg>
            </button>
        </div>
    </div>
    ";
}

echo "</div>";