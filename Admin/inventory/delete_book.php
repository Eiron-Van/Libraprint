<?php
require_once __DIR__ . '../../inc/auth_admin.php';
include '../../connection.php';

$id = $_GET['item_id'] ?? null;
if (!$id) {
    die("Invalid book ID.");
}

$sql = "DELETE FROM book_inventory WHERE item_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: https://libraprintlucena.com/Admin/inventory");
exit;
?>
