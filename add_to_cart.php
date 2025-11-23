<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['customer'])) {
    header("Location: index.php");
    exit;
}

$post = $_POST['items'] ?? [];
if (!isset($_SESSION['cart']))
    $_SESSION['cart'] = [];
$errors = [];

if (empty($post)) {
    $_SESSION['msg_error'] = "Tidak ada item yang dipilih.";
    header("Location: menu.php");
    exit;
}

foreach ($post as $key => $data) {
    $qty = intval($data['qty'] ?? 0);
    if ($qty <= 0)
        continue;

    // key format: "12_hot" atau "12_ice"
    if (preg_match('/^(\d+)_(hot|ice)$/', $key, $m)) {
        $menu_id = (int) $m[1];
        $variant = $m[2];
    } else {
        continue; // skip kalau format key tidak valid
    }

    $note = trim($data['note'] ?? '');

    // ambil menu dari DB
    $stmt = $mysqli->prepare("SELECT id, name, price_hot, price_ice, stock, image FROM menus WHERE id = ?");
    $stmt->bind_param("i", $menu_id);
    $stmt->execute();
    $menu = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$menu) {
        $errors[] = "Menu ID $menu_id tidak ditemukan.";
        continue;
    }

    $price = ($variant === 'hot') ? $menu['price_hot'] : $menu['price_ice'];
    if ($price === null) {
        $errors[] = "Menu '{$menu['name']}' tidak tersedia varian {$variant}.";
        continue;
    }

    if ($qty > intval($menu['stock'])) {
        $errors[] = "Stok tidak cukup untuk '{$menu['name']}'. (stok: {$menu['stock']})";
        continue;
    }

    // tambahkan ke session cart
    $found = false;
    foreach ($_SESSION['cart'] as &$c) {
        if ($c['menu_id'] == $menu['id'] && $c['variant'] === $variant) {
            $c['qty'] += $qty;
            if ($note) {
                $c['note'] = trim($c['note'] ? $c['note'] . "; " . $note : $note);
            }
            $found = true;
            break;
        }
    }
    unset($c);

    if (!$found) {
        $_SESSION['cart'][] = [
            'menu_id' => $menu['id'],
            'name' => $menu['name'],
            'variant' => $variant,
            'price' => $price,
            'qty' => $qty,
            'note' => $note,
            'image' => $menu['image'] ?? ''
        ];
    }
}

if (!empty($errors)) {
    $_SESSION['msg_error'] = implode("<br>", $errors);
} else {
    $_SESSION['msg_success'] = "Item berhasil ditambahkan ke keranjang.";
}

header("Location: cart.php");
exit;
