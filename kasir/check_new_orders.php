<?php
require '../db.php';

$lastId = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

$stmt = $mysqli->prepare("SELECT id, table_number FROM orders WHERE id>? AND status='baru' ORDER BY id DESC");
$stmt->bind_param("i",$lastId);
$stmt->execute();
$res = $stmt->get_result();

$orders=[];
while($row=$res->fetch_assoc()) $orders[]=$row;

$stmt->close();

header('Content-Type: application/json');
echo json_encode($orders);
