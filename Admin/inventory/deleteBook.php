<?php
include '../../connection.php';
$id = $_POST['id'] ?? $_GET['id'];
$sql = "DELETE FROM book_inventory WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
echo json_encode(['success' => true]);
?>
