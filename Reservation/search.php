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
$user_id = $_SESSION['user_id'];

// query
if (!empty($search)) {
     $safe_search = "%" . $search . "%";

    // Reserved Books Query
    $reservedBooks = $conn->prepare("
        SELECT bi.item_id, bi.title, bi.author, r.date_reserved, r.purpose
        FROM reservation r
        INNER JOIN book_inventory bi ON r.item_id = bi.item_id
        WHERE r.user_id = ?
        AND (
            bi.title LIKE ?
            OR bi.author LIKE ?
            OR r.date_reserved LIKE ?
            OR r.purpose LIKE ?
        )
    ");
    $reservedBooks->bind_param("sssss", $user_id, $safe_search, $safe_search, $safe_search, $safe_search);
    $reservedBooks->execute();
    $reservedResult = $reservedBooks->get_result();

    // Available Books Query
    $availableBooks = $conn->prepare("
        SELECT title, author
        FROM book_inventory
        WHERE status = 'Available'
        AND (
            title LIKE ?
            OR author LIKE ?
        )
    ");
    $availableBooks->bind_param("ss", $safe_search, $safe_search);
    $availableBooks->execute();
    $availableResult = $availableBooks->get_result();
} else {
    // Reserved Books
    $reservedBooks = $conn->prepare("
        SELECT bi.title, bi.author, r.date_reserved, r.purpose
        FROM reservation r
        INNER JOIN book_inventory bi ON r.item_id = bi.item_id
        WHERE r.user_id = ?
    ");
    $reservedBooks->bind_param("s", $user_id);
    $reservedBooks->execute();
    $reservedResult = $reservedBooks->get_result();

    // Available Books
    $availableResult = $conn->query("
        SELECT title, author
        FROM book_inventory
        WHERE status = 'Available'
    ");
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
        <!-- Reserved Section Label -->
        <div class='bg-blue-600 text-white sticky top-0 z-10 px-6 py-2 font-semibold'>
                Your Reserved Books
        </div>

        <div class='w-full grid grid-cols-7 bg-[#7581a6] text-white uppercase text-sm font-semibold items-center z-9'>
            <div class='px-2 py-3 col-span-3'>Title</div>
            <div class='px-2 py-3 col-span-2'>Author</div>
            <div class='px-2 py-3 col-span-1'>Date Borrowed</div>
            <div class='px-2 py-3 col-span-1'>Purpose</div>
        </div>
        ";

if ($reservedResult->num_rows > 0) {
    while ($row = $reservedResult->fetch_assoc()) {
        echo "<div class='w-full grid grid-cols-7 bg-white text-gray-700 border-b border-gray-200 items-center'>
                <div class='col-span-3 px-2 py-1'>" . highlightTerms($row['title'], $search) . "</div>
                <div class='col-span-2 px-2 py-1'>" . highlightTerms($row['author'], $search) . "</div>
                <div class='col-span-1 px-2 py-1'>" . highlightTerms($row['date_reserved'], $search) . "</div>
                <div class='col-span-1 px-2 py-1'>" . highlightTerms($row['purpose'], $search) . "</div>
            </div>";
    }
}else{
    echo "<div class='text-center py-4 text-gray-400'>You haven't reserved any books yet</div>";
}

echo "  <!-- Available Section Label -->
        <div class='bg-green-500 text-black sticky top-[5rem] z-10 px-6 py-2 font-semibold'>
                Available Books
        </div>

        <div class='w-full grid grid-cols-6 bg-[#7581a6] text-white uppercase text-sm font-semibold items-center z-9'>
            <div class='px-2 py-3 col-span-3'>Title</div>
            <div class='px-2 py-3 col-span-2'>Author</div>
            <div class='px-2 py-3 col-span-1'></div>
        </div>
        ";

    if ($availableResult->num_rows > 0) {
    while ($row = $availableResult->fetch_assoc()) {
    echo "<div class='w-full grid grid-cols-6 bg-white text-gray-700 border-b border-gray-200 items-center'>
            <div class='col-span-3 px-2 py-1'>" . highlightTerms($row['title'], $search) . "</div>
            <div class='col-span-2 px-2 py-1'>" . highlightTerms($row['author'], $search) . "</div>
            <div class='col-span-1 px-2 py-1 justify-center items-center'>
                <button class='bg-[#005f78] hover:bg-[#064358] transition-opacity duration-200 px-2 py-1 rounded text-sm text-white'>Reserve</button>
            </div>
        </div>";
    }
    }else{
        echo "<div class='text-center py-4 text-gray-400'>You haven't reserved any books yet</div>";
    }
echo "</div>";



