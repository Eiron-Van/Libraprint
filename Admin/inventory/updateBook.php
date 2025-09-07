<?php
include '../../connection.php';
$data = json_decode(file_get_contents('php://input'), true);
$sql = "UPDATE book_inventory SET author=?, title=? WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $data['author'], $data['title'], $data['id']);
$stmt->execute();
echo json_encode(['success' => true]);
?>
