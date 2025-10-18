<?php
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
            r.purpose,
            r.date_reserved
        FROM reservation AS r
        JOIN users AS u ON r.user_id = u.user_id
        JOIN book_inventory AS b ON r.item_id = b.item_id
        WHERE CONCAT(u.first_name, ' ', u.last_name) LIKE ?
           OR b.title LIKE ?
           OR r.purpose LIKE ?
           OR r.date_reserved LIKE ?
        ORDER BY r.id DESC
        LIMIT 100
    ");
    $logs->bind_param("ssss", $safe_search, $safe_search, $safe_search, $safe_search);
} else {
    $logs = $conn->prepare("
        SELECT 
            CONCAT(u.first_name, ' ', u.last_name) AS name,
            b.title AS book_name,
            r.purpose,
            r.date_reserved
        FROM reservation AS r
        JOIN users AS u ON r.user_id = u.user_id
        JOIN book_inventory AS b ON r.item_id = b.item_id
        ORDER BY r.id DESC
        LIMIT 100
    ");
}

$logs->execute();
$logsResult = $logs->get_result();

// ✅ Display
if ($logsResult->num_rows === 0) {
    echo "<div class='text-center text-gray-400 mt-10'>No reservation records found.</div>";
    exit;
}

echo "
<div class='overflow-auto rounded-lg shadow-lg shadow-gray-500/30 max-h-[62vh]'>
    <div class='grid grid-cols-4 p-2 bg-gray-700 sticky top-0 text-center'>
        <div class='flex justify-center items-center col-span-1'>Name</div>
        <div class='flex justify-center items-center col-span-1'>Book Name</div>
        <div class='flex justify-center items-center col-span-1'>Purpose</div>
        <div class='flex justify-center items-center col-span-1'>Date Reserved</div>
    </div>
";

while ($row = $logsResult->fetch_assoc()) {
    echo "
    <div class='grid grid-cols-4 p-2 bg-gray-200 text-center text-gray-600 border-b border-gray-300 hover:bg-gray-100 transition'>
        <div class='flex justify-center items-center col-span-1'>" . highlightTerms($row['name'], $search) . "</div>
        <div class='flex justify-center items-center col-span-1'>" . highlightTerms($row['book_name'], $search) . "</div>
        <div class='flex justify-center items-center col-span-1'>" . highlightTerms($row['purpose'], $search) . "</div>
        <div class='flex justify-center items-center col-span-1'>" . formatDateTime($row['date_reserved']) . "</div>
    </div>
    ";
}

echo "</div>";