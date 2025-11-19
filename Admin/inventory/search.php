<?php
require_once __DIR__ . '/../../inc/auth_admin.php';
include '../../connection.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

function columnExists(mysqli $conn, string $table, string $column): bool {
    $sql = "SELECT COUNT(*) AS count 
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = ? 
              AND COLUMN_NAME = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : ['count' => 0];
    $stmt->close();
    return (int)($row['count'] ?? 0) > 0;
}

$isbnEnabled = columnExists($conn, 'book_inventory', 'isbn');

// query
if (!empty($search)) {
    $safe_search = $conn->real_escape_string($search);
    $conditions = [
        "author LIKE '%$safe_search%'",
        "title LIKE '%$safe_search%'",
        "genre LIKE '%$safe_search%'",
        "property_no LIKE '%$safe_search%'",
        "unit LIKE '%$safe_search%'",
        "unit_value LIKE '%$safe_search%'",
        "accession_no LIKE '%$safe_search%'",
        "class_no LIKE '%$safe_search%'",
        "date_acquired LIKE '%$safe_search%'",
        "remarks LIKE '%$safe_search%'",
        "status LIKE '%$safe_search%'",
        "barcode LIKE '%$safe_search%'",
        "book_condition LIKE '%$safe_search%'",
        "location LIKE '%$safe_search%'"
    ];
    if ($isbnEnabled) {
        $conditions[] = "isbn LIKE '%$safe_search%'";
    }
    $sql = "SELECT * FROM book_inventory WHERE " . implode(' OR ', $conditions);
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

// helper function to generate condition dot
function getConditionDot($condition) {
    if (empty($condition) || $condition === null) {
        $condition = 'Good Condition'; // Default
    }
    
    $condition = trim($condition);
    $conditionLower = strtolower($condition);
    // Check if condition is "good condition" (case insensitive)
    $isGood = ($conditionLower === 'good condition');
    $dotColor = $isGood ? 'bg-green-500' : 'bg-red-500';
    $conditionText = htmlspecialchars($condition);
    
    return "<span class='condition-dot-container relative inline-block mr-2'>
                <span class='condition-dot $dotColor w-3 h-3 rounded-full inline-block' 
                      data-condition='$conditionText'></span>
                <span class='condition-tooltip'>
                    $conditionText
                </span>
            </span>";
}

// helper function to generate location icon
function getLocationIcon($location) {
    if (empty($location) || $location === null) {
        $location = 'Shelved'; // Default
    }
    
    $location = trim($location);
    $locationLower = strtolower($location);
    // Determine icon based on location
    $icon = ($locationLower === 'archived') ? '📦' : '📚';
    $locationText = htmlspecialchars($location);
    
    return "<span class='location-icon-container relative inline-block mr-2'>
                <span class='location-icon text-base cursor-pointer' 
                      data-location='$locationText'
                      style='font-size: 16px; line-height: 1;'>$icon</span>
                <span class='location-tooltip'>
                    $locationText
                </span>
            </span>";
}

function generateSampleIsbnFromId(int $itemId): string {
    $id = max(1, $itemId);
    $padded = str_pad((string)$id, 6, '0', STR_PAD_LEFT);
    $group = substr($padded, 0, 3);
    $publisher = substr($padded, 3);
    $check = ($id % 9) + 1;
    return "978-1-$group-$publisher-$check";
}

// output table
$warningHtml = '';

echo "<div id='results-count'>
        <strong>$num_rows</strong> results for '" . htmlspecialchars($search) . "'
        $warningHtml
      </div>";

echo "<div class='overflow-auto rounded-lg shadow text-white'>";
echo "<table class='inventory-table'>";
echo "<thead class='bg-[#7581a6] text-gray-50 sticky top-0 z-[8]'>
        <tr>
          <th class='p-3 text-sm font-semibold tracking-wide text-left'>Title / Author</th>
          <th class='p-3 text-sm font-semibold tracking-wide text-left w-40'>ISBN</th>
          <th class='p-3 text-sm font-semibold tracking-wide text-center w-32'>Status</th>
          <th class='p-3 text-sm font-semibold tracking-wide text-center w-32'>Barcode</th>
          <th class='p-3 text-sm font-semibold tracking-wide text-center w-32'>Actions</th>
        </tr>
      </thead>
      <tbody class='divide-y divide-[#5a6480]'>";

$row_class = true;
while ($row = $result->fetch_assoc()) {
    $bg_color = $row_class ? 'bg-white text-gray-700' : 'bg-[#e3e7f5] text-gray-700';
    $row_class = !$row_class;

    $status_class = '';
    switch (strtolower($row['status'])) {
        case 'available': $status_class = 'bg-green-300 px-2 py-1 rounded font-semibold'; break;
        case 'checked out': $status_class = 'bg-yellow-300 px-2 py-1 rounded font-semibold'; break;
        case 'missing': $status_class = 'bg-red-300 px-2 py-1 rounded font-semibold'; break;
        case 'reserved': $status_class = 'bg-blue-300 px-2 py-1 rounded font-semibold'; break;
        default: $status_class = 'text-gray-600'; break;
    }

    // Get condition dot and location icon
    $condition = $row['book_condition'] ?? null;
    $conditionDot = getConditionDot($condition);
    $location = $row['location'] ?? null;
    $locationIcon = getLocationIcon($location);
    if ($isbnEnabled) {
        $isbnValue = trim((string)($row['isbn'] ?? ''));
        if ($isbnValue !== '') {
            $isbnDisplay = highlightTerms($isbnValue, $search);
        } else {
        }
    } else {
    }

    $detailPairs = [
        'Genre' => $row['genre'] ?? '',
        'Property No.' => $row['property_no'] ?? '',
        'Unit' => $row['unit'] ?? '',
        'Unit Value' => $row['unit_value'] ?? '',
        'Accession No.' => $row['accession_no'] ?? '',
        'Class No.' => $row['class_no'] ?? '',
        'Date Acquired' => $row['date_acquired'] ?? '',
        'Remarks' => $row['remarks'] ?? '',
        'Location' => $row['location'] ?? '',
        'Condition' => $row['book_condition'] ?? ''
    ];

    $detailsHtml = '';
    foreach ($detailPairs as $label => $value) {
        $valueText = $value !== '' ? highlightTerms($value, $search) : '<span class="text-gray-400">—</span>';
        $detailsHtml .= "<div>
            <div class='details-label'>$label</div>
            <div class='details-value'>$valueText</div>
        </div>";
    }

    $detailsId = 'details-' . (int)$row['item_id'];
    
    $authorDisplay = trim((string)$row['author']) !== ''
        ? "by " . highlightTerms($row['author'], $search)
        : "<span class='text-gray-400'>Author unknown</span>";
    echo "<tr class='$bg_color'>
      <td class='p-3 text-xs whitespace-nowrap'>
        <div class='flex flex-col gap-1'>
            <div class='flex items-center gap-2 text-sm font-semibold'>" . $conditionDot . $locationIcon . "<span>" . highlightTerms($row['title'], $search) . "</span></div>
            <div class='text-gray-600 text-xs'>$authorDisplay</div>
        </div>
      </td>
      <td class='p-3 text-xs whitespace-nowrap'>$isbnDisplay</td>
      <td class='p-3 text-xs whitespace-nowrap text-center'><span class='$status_class'>" . highlightTerms($row['status'], $search) . "</span></td>
      <td class='p-3 text-xs whitespace-nowrap text-center'>" . highlightTerms($row['barcode'], $search) . "</td>
      <td class='p-3 text-xs whitespace-nowrap text-center'>
        <div class='flex flex-col gap-1 items-center'>
            <button type='button' class='toggle-details' data-target='$detailsId'>View other details</button>
            <div class='flex gap-1'>
                <a href='edit_book.php?item_id=" . $row['item_id'] . "' class='bg-green-300 px-2 py-1 rounded-2xl text-xs text-gray-900'>Edit</a>
                <a href='delete_book.php?item_id=" . $row['item_id'] . "' onclick='return confirm(\"Delete this book?\");' class='bg-red-300 px-2 py-1 rounded-2xl text-xs text-gray-900'>Delete</a>
            </div>
        </div>
      </td>
    </tr>
    <tr class='extra-details-row' id='$detailsId'>
        <td colspan='5' class='p-4 text-gray-800'>
            <div class='details-grid'>
                $detailsHtml
            </div>
        </td>
    </tr>";
}
echo "</tbody></table></div>";