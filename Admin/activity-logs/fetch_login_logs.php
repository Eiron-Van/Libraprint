<?php
require '../../connection.php';
require 'helpers.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // You can change this number
$offset = ($page - 1) * $limit;

// ✅ Base query
$query = "
    SELECT 
        CONCAT(u.first_name, ' ', u.last_name) AS name,
        l.purpose,
        l.location,
        l.login_time
    FROM login_record AS l
    JOIN users AS u ON l.user_id = u.user_id
";

// ✅ Add search filter if needed
$params = [];
$types = '';
if ($search !== '') {
    $query .= " WHERE CONCAT(u.first_name, ' ', u.last_name) LIKE ? 
                OR l.purpose LIKE ? 
                OR l.location LIKE ? ";
    $params = ["%$search%", "%$search%", "%$search%"];
    $types = 'sss';
}

// ✅ Add order and limit
$query .= " ORDER BY l.id DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

// ✅ Prepare statement
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// ✅ Get total count (for pagination info)
$countQuery = "
    SELECT COUNT(*) AS total
    FROM login_record AS l
    JOIN users AS u ON l.user_id = u.user_id
";
if ($search !== '') {
    $countQuery .= " WHERE CONCAT(u.first_name, ' ', u.last_name) LIKE ? 
                     OR l.purpose LIKE ? 
                     OR l.location LIKE ? ";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->bind_param('sss', "%$search%", "%$search%", "%$search%");
} else {
    $countStmt = $conn->prepare($countQuery);
}
$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);





// ✅ Display
if ($result->num_rows === 0) {
    echo "<div class='text-center text-gray-400 mt-10'>No login records found.</div>";
    exit;
}

echo "
<div class='overflow-auto rounded-lg shadow-lg shadow-gray-500/30 max-h-[56vh]'>
    <!-- Header -->
    <div class='grid grid-cols-4 p-2 bg-gray-700 sticky top-0 text-center'>
        <div class='flex justify-center items-center col-span-1'>Name</div>
        <div class='flex justify-center items-center col-span-1'>Purpose</div>
        <div class='flex justify-center items-center col-span-1'>Location</div>
        <div class='flex justify-center items-center col-span-1'>Login Time</div>
    </div>
";

// ✅ Body
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "
            <div class='grid grid-cols-4 p-2 bg-gray-200 text-center text-sm text-gray-600 border-b border-gray-300 hover:bg-gray-100 transition'>
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

echo "
<div class='flex items-center justify-between border-t border-white/10 px-4 py-3 sm:px-6'>
  <div class='hidden sm:flex sm:flex-1 sm:items-center sm:justify-between'>
    <div>
      <p class='text-sm text-gray-300'>
        Showing
        <span class='font-medium'>" . (($totalRows > 0) ? $offset + 1 : 0) . "</span>
        to
        <span class='font-medium'>" . min($offset + $limit, $totalRows) . "</span>
        of
        <span class='font-medium'>$totalRows</span>
        results
      </p>
    </div>
    <div>
      <nav aria-label='Pagination' class='isolate inline-flex -space-x-px rounded-md'>
";

// ✅ Previous button
if ($page > 1) {
    echo "<a href='#' data-page='" . ($page - 1) . "' class='page-link relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 hover:bg-white/10'>
        <svg viewBox='0 0 20 20' fill='currentColor' class='size-5'><path d='M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z' /></svg>
    </a>";
}

// ✅ Numbered pages
for ($i = 1; $i <= $totalPages; $i++) {
    $active = ($i == $page)
        ? "bg-blue-500/50 shadow-lg shadow-blue-500/30 text-white"
        : "text-gray-200 hover:bg-white/10";
    echo "<a href='#' data-page='$i' class='page-link relative inline-flex items-center px-4 py-2 text-sm font-semibold $active'>$i</a>";
}

// ✅ Next button
if ($page < $totalPages) {
    echo "<a href='#' data-page='" . ($page + 1) . "' class='page-link relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 hover:bg-white/10'>
        <svg viewBox='0 0 20 20' fill='currentColor' class='size-5'><path d='M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z' /></svg>
    </a>";
}

echo "
      </nav>
    </div>
  </div>
</div>
";
