<?php
require_once __DIR__ . '/../../inc/auth_admin.php';
require '../../connection.php';
require 'helpers.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($search)) {
    $safe_search = "%$search%";
    $logs = $conn->prepare("
        SELECT 
            o.borrow_id,
            CONCAT(u.first_name, ' ', u.last_name) AS name,
            b.title AS book_name,
            o.date_overdue_detected,
            o.days_overdue,
            o.status,
            bl.date_returned,
            bl.status AS borrow_status
        FROM overdue_log AS o
        JOIN users AS u ON o.user_id = u.user_id
        JOIN book_inventory AS b ON o.book_id = b.item_id
        JOIN borrow_log AS bl ON o.borrow_id = bl.id
        WHERE CONCAT(u.first_name, ' ', u.last_name) LIKE ?
           OR b.title LIKE ?
           OR o.status LIKE ?
        ORDER BY o.date_overdue_detected DESC
    ");
    $logs->bind_param("sss", $safe_search, $safe_search, $safe_search);
} else {
    $logs = $conn->prepare("
        SELECT 
            o.borrow_id,
            CONCAT(u.first_name, ' ', u.last_name) AS name,
            b.title AS book_name,
            o.date_overdue_detected,
            o.days_overdue,
            o.status,
            bl.date_returned,
            bl.status AS borrow_status
        FROM overdue_log AS o
        JOIN users AS u ON o.user_id = u.user_id
        JOIN book_inventory AS b ON o.book_id = b.item_id
        JOIN borrow_log AS bl ON o.borrow_id = bl.id
        ORDER BY o.date_overdue_detected DESC
    ");
}

$logs->execute();
$logsResult = $logs->get_result();

if ($logsResult->num_rows === 0) {
    echo "<div class='text-center text-gray-400 mt-10'>No overdue records found.</div>";
    exit;
}

echo "
<div class='overflow-auto rounded-lg shadow-lg shadow-gray-500/30 max-h-[62vh]'>
    <div class='grid grid-cols-6 p-2 bg-gray-700 sticky top-0 text-center'>
        <div class='flex justify-center items-center col-span-1'>Name</div>
        <div class='flex justify-center items-center col-span-1'>Book Name</div>
        <div class='flex justify-center items-center col-span-1'>Detected Date</div>
        <div class='flex justify-center items-center col-span-1'>Days Overdue</div>
        <div class='flex justify-center items-center col-span-1'>Status</div>
        <div class='flex justify-center items-center col-span-1'>Action</div>
    </div>
";

while ($row = $logsResult->fetch_assoc()) {
    $statusColor = match (strtolower($row['status'])) {
        'unreturned' => 'text-red-600',
        'returned' => 'text-green-600',
        default => 'text-gray-600'
    };
    
    $borrow_id = (int)$row['borrow_id'];
    // Show ping button only if book is still overdue and not returned
    $isUnreturned = strtolower($row['status']) === 'unreturned' 
        && $row['date_returned'] === null 
        && strtolower($row['borrow_status']) === 'overdue';

    echo "
    <div class='grid grid-cols-6 p-2 bg-gray-200 text-center text-gray-600 border-b border-gray-300 hover:bg-gray-100 transition'>
        <div class='flex justify-center items-center col-span-1'>" . highlightTerms($row['name'], $search) . "</div>
        <div class='flex justify-center items-center col-span-1'>" . highlightTerms($row['book_name'], $search) . "</div>
        <div class='flex justify-center items-center col-span-1'>" . formatDateTime($row['date_overdue_detected']) . "</div>
        <div class='flex justify-center items-center col-span-1'>" . htmlspecialchars($row['days_overdue']) . " day" . ($row['days_overdue'] > 1 ? "s" : "") . "</div>
        <div class='flex justify-center items-center col-span-1 font-semibold $statusColor'>" . htmlspecialchars($row['status']) . "</div>
        <div class='flex justify-center items-center col-span-1'>
            " . ($isUnreturned ? "<button class='ping-btn bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition' data-borrow-id='$borrow_id'>Ping</button>" : "<span class='text-gray-400'>-</span>") . "
        </div>
    </div>
    ";
}

echo "</div>";