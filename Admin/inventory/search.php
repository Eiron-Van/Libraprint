<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../../connection.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// query
if (!empty($search)) {
    $safe_search = $conn->real_escape_string($search);
    $sql = "SELECT * FROM book_inventory 
            WHERE author LIKE '%$safe_search%' 
            OR title LIKE '%$safe_search%' 
            OR property_no LIKE '%$safe_search%' 
            OR unit LIKE '%$safe_search%' 
            OR unit_value LIKE '%$safe_search%' 
            OR accession_no LIKE '%$safe_search%' 
            OR class_no LIKE '%$safe_search%' 
            OR date_acquired LIKE '%$safe_search%' 
            OR remarks LIKE '%$safe_search%' 
            OR status LIKE '%$safe_search%'";
} else {
    $sql = "SELECT * FROM book_inventory";
}
$result = $conn->query($sql);
if (!$result) {
    die("Invalid query: " . $conn->error);
}
$num_rows = $result->num_rows;

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

// output table
echo "<p class='m-2'><strong>$num_rows</strong> results for '" . htmlspecialchars($search) . "'</p>";
echo "<table class='w-full'>";
echo "<thead class='bg-[#7581a6] text-gray-50 sticky top-0 z-[8]'>
        <tr>
          <th class='p-3 text-sm font-semibold tracking-wide text-left w-25'>Author</th>
          <th class='p-3 text-sm font-semibold tracking-wide text-left'>Title</th>
          <th class='p-3 text-sm font-semibold tracking-wide text-left w-28'>Property No.</th>
          <th class='p-3 text-sm font-semibold tracking-wide text-left w-5'>Unit</th>
          <th class='p-3 text-sm font-semibold tracking-wide text-left w-25'>Unit Value</th>
          <th class='p-3 text-sm font-semibold tracking-wide text-left w-30'>Accession No.</th>
          <th class='p-3 text-sm font-semibold tracking-wide text-left w-23'>Class No.</th>
          <th class='p-3 text-sm font-semibold tracking-wide text-left w-30'>Date Acquired</th>
          <th class='p-3 text-sm font-semibold tracking-wide text-left w-10'>Remarks</th>
          <th class='p-3 text-sm font-semibold tracking-wide text-center w-15'>Status</th>
          <th class='p-3 text-sm font-semibold tracking-wide text-left w-35'></th>
        </tr>
      </thead>
      <tbody class='divide-y divide-[#5a6480]'>";

$row_class = true;
while ($row = $result->fetch_assoc()) {
    $bg_color = $row_class ? 'bg-white text-gray-700' : 'bg-[#8f9ecc] text-gray-700';
    $row_class = !$row_class;

    $status_class = '';
    switch (strtolower($row['status'])) {
        case 'available': $status_class = 'bg-green-300 px-2 py-1 rounded font-semibold'; break;
        case 'checked out': $status_class = 'bg-yellow-300 px-2 py-1 rounded font-semibold'; break;
        case 'missing': $status_class = 'bg-red-300 px-2 py-1 rounded font-semibold'; break;
        case 'reserved': $status_class = 'bg-blue-300 px-2 py-1 rounded font-semibold'; break;
        default: $status_class = 'text-gray-600'; break;
    }

    echo "<tr class='$bg_color'>
      <td class='p-3 text-sm whitespace-nowrap'>" . highlightTerms($row['author'], $search) . "</td>
      <td class='p-3 text-sm whitespace-nowrap'>" . highlightTerms($row['title'], $search) . "</td>
      <td class='p-3 text-sm whitespace-nowrap'>" . highlightTerms($row['property_no'], $search) . "</td>
      <td class='p-3 text-sm whitespace-nowrap'>" . highlightTerms($row['unit'], $search) . "</td>
      <td class='p-3 text-sm whitespace-nowrap text-center'>" . highlightTerms($row['unit_value'], $search) . "</td>
      <td class='p-3 text-sm whitespace-nowrap text-center'>" . highlightTerms($row['accession_no'], $search) . "</td>
      <td class='p-3 text-sm whitespace-nowrap text-center'>" . highlightTerms($row['class_no'], $search) . "</td>
      <td class='p-3 text-sm whitespace-nowrap text-center'>" . highlightTerms($row['date_acquired'], $search) . "</td>
      <td class='p-3 text-sm whitespace-nowrap text-center'>" . highlightTerms($row['remarks'], $search) . "</td>
      <td class='p-3 text-sm whitespace-nowrap text-center'><span class='$status_class'>" . highlightTerms($row['status'], $search) . "</span></td>
      <td class='p-3'>
        <a href='edit_book.php?item_id=" . $row['item_id'] . "' class='bg-green-300 px-2 py-1 rounded-2xl inline-block'>Edit</a>
        <a href='delete_book.php?item_id=" . $row['item_id'] . "' onclick='return confirm(\"Delete this book?\");' class='bg-red-300 px-2 py-1 rounded-2xl inline-block'>Delete</a>
      </td>
    </tr>";
}
echo "</tbody></table>";
?>