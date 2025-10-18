<?php
require '../../connection.php';

$search = trim($_GET['search'] ?? '');

// ✅ Helper Function
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

// ✅ Query
$query = "
    SELECT 
        CONCAT(u.first_name, ' ', u.last_name) AS name,
        l.purpose,
        l.location,
        l.login_time
    FROM login_record AS l
    JOIN users AS u ON l.user_id = u.user_id
";

$params = [];
if ($search !== '') {
    $query .= " WHERE 
        CONCAT(u.first_name, ' ', u.last_name) LIKE ?
        OR l.purpose LIKE ?
        OR l.location LIKE ?
    ";
}

// ✅ Order + Limit
$query .= " ORDER BY l.id DESC LIMIT 100";

$stmt = $conn->prepare($query);

if ($search !== '') {
    $like = "%$search%";
    $stmt->bind_param('sss', $like, $like, $like);
}

$stmt->execute();
$result = $stmt->get_result();
$records = $result->fetch_all(MYSQLI_ASSOC);

// ✅ Display
if (empty($records)) {
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
foreach ($records as $row) {
    echo "
    <div class='grid grid-cols-4 p-2 bg-gray-200 text-center text-gray-600 border-b border-gray-300 hover:bg-gray-100 transition'>
        <div class='flex justify-center items-center col-span-1'>" . highlightTerms($row['name'], $search) . "</div>
        <div class='flex justify-center items-center col-span-1'>" . highlightTerms($row['purpose'], $search) . "</div>
        <div class='flex justify-center items-center col-span-1'>" . highlightTerms($row['location'], $search) . "</div>
        <div class='flex justify-center items-center col-span-1'>" . htmlspecialchars($row['login_time']) . "</div>
    </div>
    ";
}

echo "</div>";