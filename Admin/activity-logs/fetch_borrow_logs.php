<?php
require '../../connection.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';


// ✅ Helper to format MySQL datetime into readable form
function formatDateTime($datetime) {
    if (empty($datetime) || $datetime === "0000-00-00 00:00:00") return '-';
    $timestamp = strtotime($datetime);
    return date("F j, Y - g:i A", $timestamp);
}

// ✅ Helper to make duration readable
function formatDuration($duration) {
    if (empty($duration)) return '-';
    
    // Try to detect if it's stored as days, hours, or a time difference
    if (is_numeric($duration)) {
        return $duration . " day" . ($duration > 1 ? "s" : "");
    }

    // If format looks like "HH:MM:SS"
    if (preg_match("/^(\d+):(\d+):(\d+)$/", $duration, $matches)) {
        [$full, $h, $m, $s] = $matches;
        $parts = [];
        if ($h > 0) $parts[] = "$h hour" . ($h > 1 ? "s" : "");
        if ($m > 0) $parts[] = "$m minute" . ($m > 1 ? "s" : "");
        if ($s > 0) $parts[] = "$s second" . ($s > 1 ? "s" : "");
        return implode(', ', $parts);
    }

    // If format looks like "P3D" or "3 days"
    return htmlspecialchars($duration);
}


// ✅ Highlight Helper
function highlightTerms(string $text, string $search): string {
    if ($search === '' || $text === '') return htmlspecialchars($text);
    $words = preg_split('/\s+/', trim($search));
    $words = array_filter($words);
    array_unshift($words, $search);
    $words = array_unique($words);
    usort($words, fn($a,$b) => mb_strlen($b) - mb_strlen($a));
    $escaped = array_map(fn($w) => preg_quote($w, '/'), $words);
    $pattern = '/(' . implode('|', $escaped) . ')/iu';
    $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    $out = '';
    foreach ($parts as $part) {
        if ($part === '') continue;
        if (preg_match($pattern, $part)) {
            $out .= '<mark class="search-highlight">' . htmlspecialchars($part) . '</mark>';
        } else {
            $out .= htmlspecialchars($part);
        }
    }
    return $out;
}

// ✅ Build Query
if (!empty($search)) {
    $safe_search = "%" . $search . "%";
    $logs = $conn->prepare("
        SELECT 
            CONCAT(u.first_name, ' ', u.last_name) AS name,
            b.title AS book_name,
            br.date_borrowed,
            br.date_returned,
            br.date_duration,
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
        LIMIT 100
    ");
    $logs->bind_param("sssss", $safe_search, $safe_search, $safe_search, $safe_search, $safe_search);
} else {
    $logs = $conn->prepare("
        SELECT 
            CONCAT(u.first_name, ' ', u.last_name) AS name,
            b.title AS book_name,
            br.date_borrowed,
            br.date_returned,
            br.date_duration,
            br.status
        FROM borrow_log AS br
        JOIN users AS u ON br.user_id = u.user_id
        JOIN book_inventory AS b ON br.book_id = b.item_id
        ORDER BY br.id DESC
        LIMIT 100
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

    echo "
    <div class='grid grid-cols-9 p-2 bg-gray-200 text-center text-gray-600 border-b border-gray-300 hover:bg-gray-100 transition'>
        <div class='flex justify-center items-center col-span-1'>" . highlightTerms($row['name'], $search) . "</div>
        <div class='flex justify-center items-center col-span-2'>" . highlightTerms($row['book_name'], $search) . "</div>
        <div class='flex justify-center items-center col-span-2'>" . formatDateTime($row['date_borrowed']) . "</div>
        <div class='flex justify-center items-center col-span-2'>" . formatDateTime($row['date_returned'] ?: '-') . "</div>
        <div class='flex justify-center items-center col-span-1'>" . formatDateTime($row['date_duration'] ?: '-') . "</div>
        <div class='flex justify-center items-center col-span-1 font-semibold $statusColor'>" . highlightTerms($status, $search) . "</div>
    </div>
    ";
}

echo "</div>";