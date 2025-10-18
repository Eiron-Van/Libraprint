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
            r.read_date
        FROM book_record AS r
        JOIN users AS u ON r.user_id = u.id
        JOIN book_inventory AS b ON r.book_id = b.item_id
        WHERE CONCAT(u.first_name, ' ', u.last_name) LIKE ?
           OR b.title LIKE ?
           OR r.read_date LIKE ?
        ORDER BY r.id DESC
        LIMIT 100
    ");
    $logs->bind_param("sss", $safe_search, $safe_search, $safe_search);
} else {
    $logs = $conn->prepare("
        SELECT 
            CONCAT(u.first_name, ' ', u.last_name) AS name,
            b.title AS book_name,
            r.read_date
        FROM book_record AS r
        JOIN users AS u ON r.user_id = u.id
        JOIN book_inventory AS b ON r.book_id = b.item_id
        ORDER BY r.id DESC
        LIMIT 100
    ");
}

$logs->execute();
$logsResult = $logs->get_result();

// ✅ Display
if ($logsResult->num_rows === 0) {
    echo "<div class='text-center text-gray-400 mt-10'>No read logs found.</div>";
    exit;
}

echo "
<div class='overflow-auto rounded-lg shadow-lg shadow-gray-500/30 max-h-[62vh]'>
    <div class='grid grid-cols-3 p-2 bg-gray-700 sticky top-0 text-center'>
        <div class='flex justify-center items-center col-span-1'>Name</div>
        <div class='flex justify-center items-center col-span-1'>Book Name</div>
        <div class='flex justify-center items-center col-span-1'>Read Date</div>
    </div>
";

while ($row = $logsResult->fetch_assoc()) {
    echo "
    <div class='grid grid-cols-3 p-2 bg-gray-200 text-center text-gray-600 border-b border-gray-300 hover:bg-gray-100 transition'>
        <div class='flex justify-center items-center col-span-1'>" . highlightTerms($row['name'], $search) . "</div>
        <div class='flex justify-center items-center col-span-1'>" . highlightTerms($row['book_name'], $search) . "</div>
        <div class='flex justify-center items-center col-span-1'>" . formatDateTime($row['read_date']) . "</div>
    </div>
    ";
}

echo "</div>";
