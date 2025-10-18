<?php
require '../../connection.php';
require 'helpers.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// query
if (!empty($search)) {
    $safe_search = "%" . $search . "%";

    // Search query
    $logs = $conn->prepare("
        SELECT 
            CONCAT(u.first_name, ' ', u.last_name) AS name,
            l.purpose,
            l.location,
            l.login_time
        FROM login_record AS l
        JOIN users AS u ON l.user_id = u.id
        WHERE CONCAT(u.first_name, ' ', u.last_name) LIKE ?
           OR l.purpose LIKE ?
           OR l.location LIKE ?
        ORDER BY l.id DESC
        LIMIT 100
    ");

    $logs->bind_param("sss", $safe_search, $safe_search, $safe_search);

} else {
    // Default: show all (latest 100)
    $logs = $conn->prepare("
        SELECT 
            CONCAT(u.first_name, ' ', u.last_name) AS name,
            l.purpose,
            l.location,
            l.login_time
        FROM login_record AS l
        JOIN users AS u ON l.user_id = u.id
        ORDER BY l.id DESC
        LIMIT 100
    ");
}

$logs->execute();
$logsResult = $logs->get_result();





// ✅ Display
if ($logsResult->num_rows === 0) {
    echo "<div class='text-center text-gray-400 mt-10'>No login records found.</div>";
    exit;
}

echo "
<div class='overflow-auto rounded-lg shadow-lg shadow-gray-500/30 max-h-[62vh]'>
    <!-- Header -->
    <div class='grid grid-cols-4 p-2 bg-gray-700 sticky top-0 text-center'>
        <div class='flex justify-center items-center col-span-1'>Name</div>
        <div class='flex justify-center items-center col-span-1'>Purpose</div>
        <div class='flex justify-center items-center col-span-1'>Location</div>
        <div class='flex justify-center items-center col-span-1'>Login Time</div>
    </div>
";

// ✅ Body
if ($logsResult->num_rows > 0) {
    while ($row = $logsResult->fetch_assoc()) {
        echo "
            <div class='grid grid-cols-4 p-2 bg-gray-200 text-center text-gray-600 border-b border-gray-300 hover:bg-gray-100 transition'>
                <div class='flex justify-center items-center col-span-1'>" . highlightTerms($row['name'], $search) . "</div>
                <div class='flex justify-center items-center col-span-1'>" . highlightTerms($row['purpose'], $search) . "</div>
                <div class='flex justify-center items-center col-span-1'>" . highlightTerms($row['location'], $search) . "</div>
                <div class='flex justify-center items-center col-span-1'>" . formatDateTime($row['login_time']) . "</div>
            </div>
        ";
    }
}else{
    echo "
    <div class='grid grid-cols-4 p-2 bg-gray-200 text-center text-gray-600 border-b border-gray-300 hover:bg-gray-100 transition'>
        <div class='flex justify-center items-center col-span-4'>No results found.</div>
    </div>
    ";
}

echo "</div>";