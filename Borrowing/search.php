<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../connection.php';

session_start();
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$user_id = '904NTPSVFPHNP8FQ9UGM';

// query
if (!empty($search)) {
    $safe_search = "%" . $search . "%";

    // Reserved Books Query
    $reservedBooks = $conn->prepare("
        SELECT bi.item_id, bi.title, bi.author, bi.status, r.purpose
        FROM reservation r
        INNER JOIN book_inventory bi ON r.item_id = bi.item_id
        WHERE r.user_id = ?
        AND (
            bi.title LIKE ?
            OR bi.author LIKE ?
            OR bi.status LIKE ?
            OR r.purpose LIKE ?
        )
    ");
    $reservedBooks->bind_param("sssss", $user_id, $safe_search, $safe_search, $safe_search, $safe_search);
    $reservedBooks->execute();
    $reservedResult = $reservedBooks->get_result();

    // Available Books Query
    $availableBooks = $conn->prepare("
        SELECT item_id, title, author, status
        FROM book_inventory
        WHERE status = 'Available'
        AND (
            title LIKE ?
            OR author LIKE ?
            OR status LIKE ?
        )
    ");
    $availableBooks->bind_param("sss", $safe_search, $safe_search, $safe_search);
    $availableBooks->execute();
    $availableResult = $availableBooks->get_result();

} else {
    // Reserved Books
    $reservedBooks = $conn->prepare("
        SELECT bi.item_id, bi.title, bi.author, bi.status, r.purpose
        FROM reservation r
        INNER JOIN book_inventory bi ON r.item_id = bi.item_id
        WHERE r.user_id = ?
    ");
    $reservedBooks->bind_param("s", $user_id);
    $reservedBooks->execute();
    $reservedResult = $reservedBooks->get_result();

    // Available Books
    $availableResult = $conn->query("
        SELECT item_id, title, author, status
        FROM book_inventory
        WHERE status = 'Available'
    ");
}

$num_rowsR = $reservedResult->num_rows;
$num_rowsA = $availableResult->num_rows;

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

//output

echo "<div class='overflow-y-auto max-h-[70vh] rounded-xl bg-white text-sm text-gray-800'>";
echo "<!-- Header Row -->";
echo "<div class='grid grid-cols-8 gap-2 bg-gray-800 text-white sticky top-0 z-10 px-6 py-3 font-semibold'>
        <div class='col-span-4'>Title</div>
        <div class='col-span-2'>Author</div>
        <div class='col-span-2 text-center'>Status</div>
      </div>
      
      <!-- Reserved Section Label -->
      <div class='bg-blue-600 text-white sticky top-[2.75rem] z-10 px-6 py-2 font-semibold'>
            Your Reserved Books
      </div>";

if ($reservedResult->num_rows > 0) {
    while ($row = $reservedResult->fetch_assoc()) {
    echo "<div class='grid grid-cols-8 gap-2 border-b border-gray-200 bg-blue-100 hover:bg-blue-200 px-6 py-3 items-center'>
            <div class='col-span-4'>" . highlightTerms($row['title'], $search) . "</div>
            <div class='col-span-2'>" . highlightTerms($row['author'], $search) . "</div>
            <div class='col-span-2 text-center'>" . highlightTerms($row['status'], $search) . " (" . highlightTerms($row['purpose'], $search) . ")</div>
          </div>";
    }
} else {
    echo "<div class='text-center py-4 text-gray-400'>No reserved books at the moment.</div>";
}

echo "<!-- Available Section Label -->
      <div class='bg-gray-500 text-white sticky top-[5rem] z-10 px-6 py-2 font-semibold'>
            Available Books
      </div>";

if ($availableResult->num_rows > 0) {
    while ($row = $availableResult->fetch_assoc()) {
    echo "<div class='grid grid-cols-8 gap-2 border-b border-gray-200 bg-gray-100 hover:bg-gray-200 px-6 py-3 items-center'>
            <div class='col-span-4'>" . highlightTerms($row['title'], $search) . "</div>
            <div class='col-span-2'>" . highlightTerms($row['author'], $search) . "</div>
            <div class='col-span-2 text-center'>" . highlightTerms($row['status'], $search) . "</div>
          </div>";
    }
} else {
    echo "<div class='text-center py-4 text-gray-400'>No available books at the moment.</div>";
}

echo "</div>";





