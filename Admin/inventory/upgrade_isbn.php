<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../inc/auth_admin.php';
include '../../connection.php';

set_time_limit(0);

function respondAndExit(string $message, bool $success = false): void {
    $status = $success ? 'success' : 'error';
    echo "<h2>ISBN Upgrade ($status)</h2>";
    echo "<pre>{$message}</pre>";
    echo '<p><a href="/Admin/inventory">Return to Inventory</a></p>';
    exit;
}

function ensureIsbnColumn(mysqli $conn): bool {
    $checkSql = "SELECT COUNT(*) AS cnt
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'book_inventory'
                   AND COLUMN_NAME = 'isbn'";
    $result = $conn->query($checkSql);
    if ($result && ($row = $result->fetch_assoc()) && (int)$row['cnt'] > 0) {
        return true;
    }

    $alterSql = "ALTER TABLE book_inventory
                 ADD COLUMN isbn VARCHAR(20) NULL DEFAULT NULL
                 AFTER title";
    return $conn->query($alterSql) === true;
}

function ensureIsbnConstraints(mysqli $conn): bool {
    $columnSql = "SELECT IS_NULLABLE
                  FROM information_schema.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = 'book_inventory'
                    AND COLUMN_NAME = 'isbn'";
    $columnResult = $conn->query($columnSql);
    $isNullable = 'YES';
    if ($columnResult && ($row = $columnResult->fetch_assoc())) {
        $isNullable = $row['IS_NULLABLE'];
    }

    $indexSql = "SELECT COUNT(*) AS cnt
                 FROM information_schema.STATISTICS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'book_inventory'
                   AND INDEX_NAME = 'isbn_unique'";
    $indexResult = $conn->query($indexSql);
    $hasIndex = false;
    if ($indexResult && ($row = $indexResult->fetch_assoc())) {
        $hasIndex = (int)$row['cnt'] > 0;
    }

    if ($isNullable === 'YES' || !$hasIndex) {
        $alterParts = [];
        if ($isNullable === 'YES') {
            $alterParts[] = "MODIFY isbn VARCHAR(20) NOT NULL";
        }
        if (!$hasIndex) {
            $alterParts[] = "ADD UNIQUE KEY isbn_unique (isbn)";
        }
        if (!empty($alterParts)) {
            $alterSql = "ALTER TABLE book_inventory " . implode(", ", $alterParts);
            return $conn->query($alterSql) === true;
        }
    }

    return true;
}

function generateSampleIsbn(int $itemId): string {
    $id = max(1, $itemId);
    $padded = str_pad((string)$id, 6, '0', STR_PAD_LEFT);
    $group = substr($padded, 0, 3);
    $publisher = substr($padded, 3);
    $check = ($id % 9) + 1;
    return "978-1-$group-$publisher-$check";
}

if (!ensureIsbnColumn($conn)) {
    respondAndExit("Unable to add the isbn column. MySQL said: " . $conn->error);
}

$select = $conn->query("SELECT item_id, isbn FROM book_inventory");
if (!$select) {
    respondAndExit("Unable to fetch inventory rows. MySQL said: " . $conn->error);
}

$checkStmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM book_inventory WHERE isbn = ?");
$updateStmt = $conn->prepare("UPDATE book_inventory SET isbn = ? WHERE item_id = ?");
if (!$checkStmt || !$updateStmt) {
    respondAndExit("Prepared statements failed: " . $conn->error);
}

$updated = 0;
while ($row = $select->fetch_assoc()) {
    $itemId = (int)$row['item_id'];
    $isbn = trim((string)$row['isbn']);
    if ($isbn !== '') {
        continue;
    }

    $newIsbn = generateSampleIsbn($itemId);

    // Guarantee uniqueness in case book_inventory already has matching sample.
    $suffix = 1;
    $candidate = $newIsbn;
    do {
        $checkStmt->bind_param('s', $candidate);
        $checkStmt->execute();
        $countResult = $checkStmt->get_result();
        $countRow = $countResult ? $countResult->fetch_assoc() : ['cnt' => 0];
        if ((int)$countRow['cnt'] === 0) {
            break;
        }
        $suffix++;
        $candidate = $newIsbn . '-' . $suffix;
    } while ($suffix < 50);

    $updateStmt->bind_param('si', $candidate, $itemId);
    if ($updateStmt->execute()) {
        $updated++;
    } else {
        respondAndExit("Failed updating item #$itemId: " . $conn->error);
    }
}

$checkStmt->close();
$updateStmt->close();

if (!ensureIsbnConstraints($conn)) {
    respondAndExit("Rows updated but constraints failed: " . $conn->error);
}

respondAndExit("ISBN column ready.\nRows updated with sample ISBNs: $updated\n\nYou can now remove placeholder UI logic referencing the \"ISBN upgrade\" once you confirm everything looks good.", true);

