<?php
require '../../connection.php';

$type = $_GET['type'] ?? 'login'; // default type
$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10; // show 10 per page
$offset = ($page - 1) * $limit;

// Helper: highlight search terms
function highlightTerms($text, $search) {
    if ($search === '' || $text === '') return htmlspecialchars($text);
    $pattern = '/' . preg_quote($search, '/') . '/i';
    return preg_replace($pattern, '<mark class="search-highlight">$0</mark>', htmlspecialchars($text));
}

// Choose table based on log type
switch ($type) {
    case 'login':
        $table = "login_record l JOIN users u ON l.user_id = u.user_id";
        $columns = "CONCAT(u.first_name, ' ', u.last_name) AS name, l.purpose, l.location, l.login_time AS date";
        $where = "CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR l.purpose LIKE ? OR l.location LIKE ?";
        break;
    case 'read':
        $table = "read_log r JOIN users u ON r.user_id = u.user_id JOIN book_inventory b ON r.book_id = b.item_id";
        $columns = "CONCAT(u.first_name, ' ', u.last_name) AS name, b.title AS book_name, r.read_date AS date";
        $where = "CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR b.title LIKE ?";
        break;
    case 'reservation':
        $table = "reservation_log rl JOIN users u ON rl.user_id = u.user_id JOIN book_inventory b ON rl.book_id = b.item_id";
        $columns = "CONCAT(u.first_name, ' ', u.last_name) AS name, b.title AS book_name, rl.date_reserved AS date";
        $where = "CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR b.title LIKE ?";
        break;
    case 'claim':
        $table = "claim_log c JOIN users u ON c.user_id = u.user_id JOIN book_inventory b ON c.item_id = b.item_id";
        $columns = "CONCAT(u.first_name, ' ', u.last_name) AS name, b.title AS book_name, c.claim_date AS date";
        $where = "CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR b.title LIKE ?";
        break;
    case 'borrow':
        $table = "borrow_log bl JOIN users u ON bl.user_id = u.user_id JOIN book_inventory b ON bl.book_id = b.item_id";
        $columns = "CONCAT(u.first_name, ' ', u.last_name) AS name, b.title AS book_name, bl.date_borrowed AS date";
        $where = "CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR b.title LIKE ?";
        break;
    case 'overdue':
        $table = "borrow_log bl JOIN users u ON bl.user_id = u.user_id JOIN book_inventory b ON bl.book_id = b.item_id";
        $columns = "CONCAT(u.first_name, ' ', u.last_name) AS name, b.title AS book_name, bl.date_borrowed AS date";
        $where = "(CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR b.title LIKE ?) AND bl.status = 'Overdue'";
        break;
    default:
        die("Invalid type");
}

// Count total
$count_sql = "SELECT COUNT(*) AS total FROM $table";
if ($search !== '') $count_sql .= " WHERE $where";
$stmt = $conn->prepare($count_sql);

if ($search !== '') {
    $searchTerm = "%$search%";
    if (substr_count($where, '?') === 3)
        $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    else
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
}
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// Fetch logs
$query = "SELECT $columns FROM $table";
if ($search !== '') $query .= " WHERE $where";
$query .= " ORDER BY date DESC LIMIT $limit OFFSET $offset";

$stmt = $conn->prepare($query);
if ($search !== '') {
    if (substr_count($where, '?') === 3)
        $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    else
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='text-center text-gray-400 mt-10'>No records found.</div>";
    exit;
}

// Output table
echo "<div class='overflow-auto rounded-lg shadow-lg max-h-[62vh]'>
        <div class='grid grid-cols-3 p-2 bg-gray-700 sticky top-0 text-center'>
            <div>Name</div><div>Book / Purpose</div><div>Date</div>
        </div>";
while ($row = $result->fetch_assoc()) {
    echo "<div class='grid grid-cols-3 p-2 bg-gray-200 text-center text-gray-600 border-b border-gray-300'>
            <div>" . highlightTerms($row['name'], $search) . "</div>
            <div>" . highlightTerms($row[array_key_exists('book_name', $row) ? 'book_name' : 'purpose'], $search) . "</div>
            <div>" . date("F j, Y - g:i A", strtotime($row['date'])) . "</div>
          </div>";
}
echo "</div>";

// Pagination info
$totalPages = ceil($total / $limit);
echo "<div class='text-gray-300 text-sm text-center mt-2'>
        Showing <span class='font-medium'>" . (($offset + 1)) . "</span> to 
        <span class='font-medium'>" . min($offset + $limit, $total) . "</span> 
        of <span class='font-medium'>$total</span> results
      </div>
      <div class='flex justify-center space-x-2 mt-3'>";
for ($i = 1; $i <= $totalPages; $i++) {
    $active = $i === $page ? "bg-blue-500/50 shadow-lg shadow-blue-500/30 text-white" : "text-gray-300 hover:bg-white/5";
    echo "<button class='page-btn px-3 py-1 rounded-md border border-gray-700 $active' data-page='$i'>$i</button>";
}
echo "</div>";