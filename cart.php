<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['customer'])) {
  header("Location: index.php");
  exit;
}

$customer = $_SESSION['customer'] ?? [];

// fungsi rupiah
if (!function_exists('rupiah')) {
  function rupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
  }
}

// Ambil stok semua menu dari database
function getMenuStocks($mysqli) {
  $stocks = [];
  $res = $mysqli->query("SELECT id, stock, name FROM menus");
  while ($row = $res->fetch_assoc()) {
    $stocks[$row['id']] = [
      'stock' => (int)$row['stock'],
      'name'  => $row['name'],
    ];
  }
  return $stocks;
}

$menuStocks = getMenuStocks($mysqli);

// Fungsi validasi stok gabungan
function validateCart(&$items, $menuStocks) {
  $grouped = [];
  foreach ($items as $key => &$it) {
    $mid = $it['menu_id'];
    $grouped[$mid][$key] = &$it;
  }

  foreach ($grouped as $mid => &$variants) {
    $max = $menuStocks[$mid]['stock'] ?? 0;
    $total = 0;
    foreach ($variants as &$v) {
      $total += $v['qty'];
    }

    if ($total > $max) {
      $factor = $max / $total;
      foreach ($variants as &$v) {
        $v['qty'] = floor($v['qty'] * $factor);
      }
    }
  }
}

// Tambah item baru dari menu.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['items']) && !isset($_POST['ajax_update']) && !isset($_POST['clear'])) {
  $menuNotes = $_POST['notes'] ?? [];

  foreach ($_POST['items'] as $key => $data) {
    $qty = intval($data['qty'] ?? 0);
    if ($qty > 0) {
      $menuId = intval($data['menu_id']);
      $note = trim($menuNotes[$menuId] ?? '');
      $_SESSION['cart'][$key] = [
        'menu_id' => $menuId,
        'name'    => $data['name'],
        'variant' => $data['variant'],
        'price'   => intval($data['price']),
        'qty'     => $qty,
        'note'    => $note,
      ];
    }
  }

  if (!empty($_SESSION['cart'])) {
    validateCart($_SESSION['cart'], $menuStocks);
  }

  header("Location: cart.php");
  exit;
}

// Update via AJAX (real-time)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_update'])) {
  foreach ($_POST['qty'] as $k => $v) {
    $v = intval($v);
    if ($v <= 0) {
      unset($_SESSION['cart'][$k]);
    } else {
      $_SESSION['cart'][$k]['qty'] = $v;
      if (isset($_POST['note'][$k])) {
        $_SESSION['cart'][$k]['note'] = trim($_POST['note'][$k]);
      }
    }
  }

  if (!empty($_SESSION['cart'])) {
    validateCart($_SESSION['cart'], $menuStocks);
  }

  $total = 0;
  foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['qty'];
  }

  echo json_encode([
    'success' => true,
    'total' => $total,
    'formattedTotal' => rupiah($total)
  ]);
  exit;
}

// Clear keranjang manual
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear'])) {
  $_SESSION['cart'] = [];
  $_SESSION['msg_success'] = "Keranjang dikosongkan.";
  header("Location: cart.php");
  exit;
}

$cart = $_SESSION['cart'] ?? [];
$total = 0;
foreach ($cart as $item) {
  $total += $item['price'] * $item['qty'];
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Keranjang - RK Coffee</title>
  <style>
    body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background: #f9f5f0; margin: 0; padding: 0; }
    .container { max-width: 820px; margin: 20px auto; padding: 0 15px; }
    .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); padding: 20px; }
    h2 { text-align: center; margin-bottom: 10px; color: #4b2e2e; }
    p.customer { text-align: center; font-size: 14px; color: #555; margin-bottom: 18px; }

    /* Table */
    .table-responsive { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    table th { background: #f3e6d8; padding: 10px; font-weight: 600; color: #4b2e2e; font-size: 14px; text-align: center; }
    table td { padding: 10px; border-bottom: 1px solid #eee; text-align: center; font-size: 13px; }
    input[type="number"] { width: 60px; padding: 4px; border: 1px solid #ccc; border-radius: 6px; text-align: center; font-size: 13px; }
    input[type="text"] { width: 100%; padding: 5px; border: 1px solid #ccc; border-radius: 6px; font-size: 13px; }

    .total-row td { font-weight: bold; font-size: 14px; color: #4b2e2e; }

    .actions { text-align: center; margin-top: 15px; display: flex; flex-wrap: wrap; justify-content: center; gap: 5px; }
    .btn { background: #7b4f2c; color: #fff; padding: 8px 12px; border: none; border-radius: 6px; font-size: 13px; cursor: pointer; text-decoration: none; transition: 0.3s; }
    .btn:hover { background: #5e3b20; }

    /* Messages */
    .msg { margin-bottom: 12px; padding: 10px 15px; border-radius: 8px; font-size: 14px; }
    .msg.error { background: #ffe5e5; color: #a00; }
    .msg.success { background: #e6ffe6; color: #0a0; }

    @media (max-width: 600px) {
      table th, table td { font-size: 12px; padding: 8px; }
      input[type="number"] { width: 50px; }
      input[type="text"] { font-size: 12px; }
      .actions { flex-direction: row; flex-wrap: wrap; justify-content: center; gap: 5px; }
      .btn { font-size: 12px; padding: 6px 10px; }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <h2>Keranjang Pesanan</h2>
      <p class="customer">Nama: <?= htmlspecialchars($customer['name'] ?? '-') ?> — Meja: <?= htmlspecialchars($customer['table'] ?? '-') ?></p>

      <?php if (!empty($_SESSION['msg_error'])): ?>
        <div class="msg error"><?= $_SESSION['msg_error']; unset($_SESSION['msg_error']); ?></div>
      <?php endif; ?>
      <?php if (!empty($_SESSION['msg_success'])): ?>
        <div class="msg success"><?= $_SESSION['msg_success']; unset($_SESSION['msg_success']); ?></div>
      <?php endif; ?>

      <?php if (empty($cart)): ?>
        <p style="text-align:center; color:#555;">Keranjang kosong. <a href="menu.php" style="color:#7b4f2c;">Kembali ke menu</a></p>
      <?php else: ?>
        <form method="post" id="cart-form">
          <div class="table-responsive">
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Menu</th>
                  <th>Varian</th>
                  <th>Harga</th>
                  <th>Qty</th>
                  <th>Catatan</th>
                </tr>
              </thead>
              <tbody>
                <?php $no = 1; foreach ($cart as $k => $it): ?>
                  <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($it['name']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($it['variant'])) ?></td>
                    <td><?= rupiah($it['price']) ?></td>
                    <td><input type="number" name="qty[<?= $k ?>]" value="<?= intval($it['qty']) ?>" min="0"></td>
                    <td><input type="text" name="note[<?= $k ?>]" value="<?= htmlspecialchars($it['note']) ?>"></td>
                  </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                  <td colspan="4" style="text-align:right">Total:</td>
                  <td colspan="2" id="cart-total"><?= rupiah($total) ?></td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="actions">
            <button type="submit" name="clear" class="btn">Kosongkan</button>
            <a href="menu.php" class="btn">Tambah Lagi</a>
            <a href="checkout.php" class="btn">Checkout</a>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>

<script>
document.querySelectorAll('input[name^="qty"], input[name^="note"]').forEach(el => {
  el.addEventListener('change', function() {
    let form = document.getElementById('cart-form');
    let formData = new FormData(form);
    formData.append('ajax_update', '1');

    fetch('cart.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        document.getElementById('cart-total').textContent = data.formattedTotal;
        location.reload();
      }
    });
  });
});
</script>
</body>
</html>
