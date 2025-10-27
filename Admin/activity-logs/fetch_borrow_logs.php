<?php
require_once __DIR__ . '/../../inc/auth_admin.php';
require '../../connection.php';
require 'helpers.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// ✅ Build Query
if (!empty($search)) {
    $safe_search = "%" . $search . "%";
    $logs = $conn->prepare("
        SELECT 
            CONCAT(u.first_name, ' ', u.last_name) AS name,
            b.title AS book_name,
            br.date_borrowed,
            br.date_returned,
            br.status
        FROM borrow_log AS br
        JOIN users AS u ON br.user_id = u.user_id
        JOIN book_inventory AS b ON br.book_id = b.item_id
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
            CONCAT(u.first_name, ' ', u.last_name) AS name,
            b.title AS book_name,
            br.date_borrowed,
            br.date_returned,
            br.status
        FROM borrow_log AS br
        JOIN users AS u ON br.user_id = u.user_id
        JOIN book_inventory AS b ON br.book_id = b.item_id
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
    <div class='grid grid-cols-9 p-2 bg-gray-700 sticky top-0 text-center'>
        <div class='flex justify-center items-center col-span-1'>Name</div>
        <div class='flex justify-center items-center col-span-2'>Book Name</div>
        <div class='flex justify-center items-center col-span-2'>Borrowed Date</div>
        <div class='flex justify-center items-center col-span-2'>Return Date</div>
        <div class='flex justify-center items-center col-span-1'>Duration</div>
        <div class='flex justify-center items-center col-span-1'>Status</div>
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

    echo "
    <div class='grid grid-cols-9 p-2 bg-gray-200 text-center text-sm text-gray-600 border-b border-gray-300 hover:bg-gray-100 transition'>
        <div class='flex justify-center items-center col-span-1'>" . highlightTerms($row['name'], $search) . "</div>
        <div class='flex justify-center items-center col-span-2'>" . highlightTerms($row['book_name'], $search) . "</div>
        <div class='flex justify-center items-center col-span-2'>" . formatDateTime($row['date_borrowed']) . "</div>
        <div class='flex justify-center items-center col-span-2'>" . formatDateTime($row['date_returned'] ?: '-') . "</div>
        <div class='flex justify-center items-center col-span-1'>" . computeDuration($row['date_borrowed'], $row['date_returned']) . "</div>
        <div class='flex justify-center items-center col-span-1 font-semibold $statusColor'>" . highlightTerms($status, $search) . "</div>
    </div>
    ";
}

echo "</div>";