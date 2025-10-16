<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../../connection.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// query
if (!empty($search)) {
    $safe_search = "%" . $search . "%";

    // Get Users Table
    $users = $conn->prepare("
        SELECT *
        FROM users
        WHERE user_id LIKE ?
        OR username LIKE ?
        OR first_name LIKE ?
        OR last_name LIKE ?
        OR gender LIKE ?
        OR address LIKE ?
        Or birthday LIKE ?
        OR contact_number LIKE ?
        OR email LIKE ?
    ");
    $users->bind_param("sssssssss", $safe_search, $safe_search, $safe_search, $safe_search, $safe_search, $safe_search, $safe_search, $safe_search, $safe_search);
    $users->execute();
    $usersResult = $users->get_result();

} else {
    // Get Users Table
    $users = $conn->prepare("SELECT user_id, username, first_name, last_name, gender, address, birthday, contact_number, email FROM users");
    $users->execute();
    $usersResult = $users->get_result();
}

$users->execute();
$usersResult = $users->get_result();
$num_rows = $usersResult->num_rows;

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

// output
echo "<h1 class='pb-6 text-4xl text-white font-semibold text-center'>Users</h1> 
        <div class='overflow-auto rounded-lg shadow-lg shadow-gray-500/30 max-h-[61.8vh]'>
            <!-- Header -->
            <div class='grid grid-cols-12 p-2 bg-gray-700 sticky top-0 text-center'>
                <div class='flex justify-center items-center col-span-1'>User ID</div>
                <div class='flex justify-center items-center col-span-1'>User Name</div>
                <div class='flex justify-center items-center col-span-2'>Full Name</div>
                <div class='flex justify-center items-center col-span-1'>Gender</div>
                <div class='flex justify-center items-center col-span-2'>Address</div>
                <div class='flex justify-center items-center col-span-1'>Birthday</div>
                <div class='flex justify-center items-center col-span-1'>Contact Number</div>
                <div class='flex justify-center items-center col-span-2'>Email</div>
                <div class='flex justify-center items-center col-span-1'>Action</div>
            </div>";

if ($usersResult->num_rows > 0){
    while ($row = $usersResult->fetch_assoc()){
        echo "<div class='grid grid-cols-12 p-2 bg-gray-200 text-xs text-black border-b border-black whitespace-normal break-all'>
                <div class='flex justify-center items-center min-w-0 p-1 col-span-1'>" . highlightTerms($row['user_id'], $search) . "</div>
                <div class='flex justify-center items-center min-w-0 p-1 col-span-1'>" . highlightTerms($row['username'], $search) . "</div>
                <div class='flex justify-center items-center min-w-0 p-1 col-span-2'>" . highlightTerms($row['first_name'] . " " . $row['last_name'], $search) . "</div>
                <div class='flex justify-center items-center min-w-0 p-1 col-span-1'>" . highlightTerms($row['gender'], $search) . "</div>
                <div class='flex justify-center items-center min-w-0 p-1 col-span-2'>" . highlightTerms($row['address'], $search) . "</div>
                <div class='flex justify-center items-center min-w-0 p-1 col-span-1'>" . highlightTerms($row['birthday'], $search) . "</div>
                <div class='flex justify-center items-center min-w-0 p-1 col-span-1'>" . highlightTerms($row['contact_number'], $search) . "</div>
                <div class='flex justify-center items-center min-w-0 p-1 col-span-2'>" . highlightTerms($row['email'], $search) . "</div>
                <div class='flex justify-center items-center'>
                    <button class='bg-red-400 hover:bg-red-500 active:bg-red-600 shadow-lg shadow-red-500/50 px-2 py-1 col-span-1 rounded-lg'>Delete</button>
                </div>
            </div>";
    }
}
echo "</div>";



                
            