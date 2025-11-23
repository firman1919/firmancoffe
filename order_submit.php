<?php
// order_submit.php
session_start();
require_once 'db.php';

if (!isset($_SESSION['customer']) || empty($_SESSION['cart'])) {
    header("Location: menu.php");
    exit;
}

$customer = $_SESSION['customer'];
$cart     = $_SESSION['cart'];
$payment  = ($_SESSION['payment_method'] ?? 'cash') === 'qris' ? 'qris' : 'cash';
$bukti_qris = $_SESSION['bukti_qris'] ?? null;

if ($payment !== 'qris') {
    $bukti_qris = null;
}

$mysqli->begin_transaction();

try {
    // --- Cek stok gabungan ---
    $grouped = [];
    foreach ($cart as $it) {
        $mid = $it['menu_id'];
        if (!isset($grouped[$mid])) {
            $grouped[$mid] = 0;
        }
        $grouped[$mid] += $it['qty'];
    }

    foreach ($grouped as $mid => $qtyTotal) {
        $stmt = $mysqli->prepare("SELECT name, stock FROM menus WHERE id = ?");
        $stmt->bind_param("i", $mid);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        if (!$row) {
            throw new Exception("Menu tidak ditemukan (ID: $mid).");
        }
        if ((int)$row['stock'] < $qtyTotal) {
            throw new Exception("Stok tidak cukup untuk {$row['name']} (tersisa {$row['stock']}, diminta {$qtyTotal}).");
        }
    }

    // --- Hitung total ---
    $total = 0;
    foreach ($cart as $it) {
        $total += $it['price'] * $it['qty'];
    }

    // --- Simpan order ---
    $stmt = $mysqli->prepare("INSERT INTO orders 
        (customer_name, table_number, total, payment_method, bukti_qris) 
        VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "ssiss",
        $customer['name'],
        $customer['table'],
        $total,
        $payment,
        $bukti_qris
    );
    if (!$stmt->execute()) {
        throw new Exception("Error insert orders: " . $stmt->error);
    }
    $order_id = $stmt->insert_id;
    $stmt->close();

    // --- Simpan order_items + update stok ---
    foreach ($cart as $it) {
        $subtotal = $it['price'] * $it['qty'];
        $note = substr($it['note'] ?? '', 0, 500);

        $stmtItem = $mysqli->prepare("INSERT INTO order_items 
            (order_id, menu_id, menu_name, variant, qty, price, note, subtotal) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtItem->bind_param(
            "iissidss",
            $order_id,          // i
            $it['menu_id'],     // i
            $it['name'],        // s
            $it['variant'],     // s
            $it['qty'],         // i
            $it['price'],       // d
            $note,              // s
            $subtotal           // d
        );
        if (!$stmtItem->execute()) {
            throw new Exception("Error insert order_items: " . $stmtItem->error);
        }
        $stmtItem->close();
    }

    // --- Update stok per menu (gabungan varian) ---
    foreach ($grouped as $mid => $qtyTotal) {
        $stmtStock = $mysqli->prepare("UPDATE menus SET stock = stock - ? WHERE id = ?");
        $stmtStock->bind_param("ii", $qtyTotal, $mid);
        if (!$stmtStock->execute()) {
            throw new Exception("Error update stok: " . $stmtStock->error);
        }
        $stmtStock->close();
    }

    $mysqli->commit();

    // Bersihkan session cart & pembayaran
    unset($_SESSION['cart'], $_SESSION['payment_method'], $_SESSION['bukti_qris']);

    header("Location: order_success.php?id=" . $order_id);
    exit;

} catch (Exception $e) {
    $mysqli->rollback();
    $_SESSION['msg_error'] = "Gagal menyimpan pesanan: " . $e->getMessage();
    header("Location: cart.php");
    exit;
}
