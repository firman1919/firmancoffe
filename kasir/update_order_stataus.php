<?php
require 'koneksi.php';

if (isset($_POST['id']) && isset($_POST['status'])) {
    $id = intval($_POST['id']);
    $status = $conn->real_escape_string($_POST['status']);

    $allowed = ['baru', 'diproses', 'selesai'];
    if (in_array($status, $allowed)) {
        $conn->query("UPDATE orders SET status='$status' WHERE id=$id");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Status tidak valid']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Data tidak lengkap']);
}
?>
