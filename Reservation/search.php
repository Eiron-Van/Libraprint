<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle session ID from URL parameter (for fingerprint login)
if (isset($_GET['PHPSESSID']) && !empty($_GET['PHPSESSID'])) {
    session_id($_GET['PHPSESSID']);
}

session_start();

require '../connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /Login");
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// query
if (!empty($search)) {
    $safe_search = $conn->real_escape_string($search);
    $sql = "SELECT * FROM book_inventory 
            WHERE status = 'Available'
            AND (
                title LIKE '%$safe_search%' 
                OR author LIKE '%$safe_search%')";
} else {
    $sql = "SELECT * FROM book_inventory
            WHERE status = 'Available'";
}
$result = $conn->query($sql);
if (!$result) {
    die("Invalid query: " . $conn->error);
}

// helper
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

echo "<div class='max-h-[50vh] overflow-y-auto rounded-xl shadow-md border border-gray-700 bg-white'>
        <div class='w-full grid grid-cols-7 bg-[#7581a6] text-white uppercase text-sm font-semibold items-center sticky top-0 z-10'>
            <div class='px-4 py-3 col-span-3'>Title</div>
            <div class='px-4 py-3 col-span-1'>Author</div>
            <div class='px-4 py-3 col-span-2 text-center'>Date Borrowed</div>
            <div class='px-4 py-3 col-span-1'></div>
        </div>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='w-full grid grid-cols-7 bg-white text-gray-700 border-b border-gray-200 items-center'>
                <div class='col-span-3 px-2 py-1'>" . highlightTerms($row['title'], $search) . "</div>
                <div class='col-span-1 px-2 py-1'>" . highlightTerms($row['author'], $search) . "</div>
                <div class='col-span-2 px-2 py-1'></div>
                <div class='col-span-1 px-2 py-1 justify-center items-center'>
                    <button class='bg-[#005f78] hover:bg-[#064358] transition-opacity duration-200 px-2 py-1 rounded text-sm text-white'>Reserve</button>
                </div>
            </div>";
    }
}else{
    echo "<div class='text-center py-4 text-gray-400'>No available books at the moment.</div>";
}

echo "</div>";