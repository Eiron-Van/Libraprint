<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../../inc/auth_admin.php';
include '../../connection.php';

$id = $_GET['item_id'] ?? null;
if (!$id) {
    echo "<script>
        alert('Invalid book ID.');
        window.location.href='https://libraprintlucena.com/Admin/inventory';
    </script>";
    exit;
}

// Step 1: Check if this book is referenced in book_record
$check = $conn->prepare("SELECT COUNT(*) FROM book_record WHERE book_id=?");
$check->bind_param("i", $id);
$check->execute();
$check->bind_result($count);
$check->fetch();
$check->close();

// Step 2: If related records exist, show notification and redirect
if ($count > 0) {
    echo "<script>
        alert('⚠️ Cannot delete this book because it has related records in the book_record table.');
        window.location.href='https://libraprintlucena.com/Admin/inventory';
    </script>";
    exit;
}

// Step 3: If no references, proceed with deletion
$sql = "DELETE FROM book_inventory WHERE item_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

echo "<script>
    alert('✅ Book successfully deleted.');
    window.location.href='https://libraprintlucena.com/Admin/inventory';
</script>";
exit;