<?php
require '../db.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo "Missing order id";
    exit;
}

$order_id = (int)$_GET['id'];

// Ambil data order
$order = $mysqli->query("SELECT * FROM orders WHERE id=$order_id")->fetch_assoc();
if (!$order) {
    http_response_code(404);
    echo "Order not found";
    exit;
}

// Ambil item pesanan
$items = $mysqli->query("SELECT * FROM order_items WHERE order_id=$order_id")->fetch_all(MYSQLI_ASSOC);
?>

<div style="background:#fff;padding:20px;margin-bottom:20px;border-radius:10px;
            box-shadow:0 2px 8px rgba(0,0,0,0.08);">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;">
    <h2 style="margin:0;color:#2F3640;font-size:18px;">
      <?= htmlspecialchars($order['id']) ?> - <?= htmlspecialchars($order['customer_name']) ?>
    </h2>
    <span style="font-size:13px;color:#888;">
      Waktu: <?= htmlspecialchars($order['created_at'] ?? '-') ?>
    </span>
  </div>
  <p style="margin:8px 0 0;font-size:14px;color:#444;">
    Meja: <strong><?= htmlspecialchars($order['table_number']) ?></strong><br>
    Total: <span style="color:#2F3640;font-weight:bold;"><?= rupiah($order['total']) ?></span><br>
    Metode:
    <?php if ($order['payment_method'] === 'qris'): ?>
      <span style="padding:4px 8px;border-radius:6px;font-size:12px;background:#16a34a;color:#fff;">QRIS</span>
    <?php else: ?>
      <span style="padding:4px 8px;border-radius:6px;font-size:12px;background:#2F3640;color:#fff;">Cash</span>
    <?php endif; ?>
  </p>

  <form method="post" style="margin-top:10px;">
    <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
    <?php
      $status = $order['status'] ?? 'baru';
      $class = "status-baru";
      if ($status === 'diproses') $class = "status-diproses";
      if ($status === 'selesai') $class = "status-selesai";
    ?>
    <select name="status" class="status-select <?= $class ?>" onchange="autoSubmit(this)">
      <option value="baru" <?= $status==='baru'?'selected':''; ?>>Baru</option>
      <option value="diproses" <?= $status==='diproses'?'selected':''; ?>>Diproses</option>
      <option value="selesai" <?= $status==='selesai'?'selected':''; ?>>Selesai</option>
    </select>
    <input type="hidden" name="update_status" value="1">
  </form>

  <?php if ($order['payment_method'] === 'qris' && !empty($order['bukti_qris'])): ?>
    <div style="margin-top:10px;">
      <p style="margin:0;font-size:14px;font-weight:500;">Bukti QRIS:</p>
      <img src="../<?= htmlspecialchars($order['bukti_qris']) ?>" alt="Bukti QRIS"
          style="max-width:160px;display:block;margin-top:6px;border:1px solid #ddd;border-radius:6px;">
    </div>
  <?php endif; ?>

  <table style="width:100%;border-collapse:collapse;margin-top:14px;font-size:14px;">
    <thead>
      <tr>
        <th style="border-bottom:2px solid #ddd;padding:10px;text-align:left;background:#f8f8f8;">Menu</th>
        <th style="border-bottom:2px solid #ddd;padding:10px;text-align:left;background:#f8f8f8;">Varian</th>
        <th style="border-bottom:2px solid #ddd;padding:10px;text-align:center;background:#f8f8f8;">Qty</th>
        <th style="border-bottom:2px solid #ddd;padding:10px;text-align:right;background:#f8f8f8;">Harga</th>
        <th style="border-bottom:2px solid #ddd;padding:10px;text-align:right;background:#f8f8f8;">Subtotal</th>
        <th style="border-bottom:2px solid #ddd;padding:10px;text-align:left;background:#f8f8f8;">Catatan</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $it): ?>
        <tr style="border-bottom:1px solid #eee;">
          <td style="padding:8px;"><?= htmlspecialchars($it['menu_name']) ?></td>
          <td style="padding:8px;"><?= htmlspecialchars($it['variant']) ?></td>
          <td style="padding:8px;text-align:center;"><?= (int) $it['qty'] ?></td>
          <td style="padding:8px;text-align:right;"><?= rupiah($it['price']) ?></td>
          <td style="padding:8px;text-align:right;color:#2F3640;font-weight:500;"><?= rupiah($it['subtotal']) ?></td>
          <td style="padding:8px;"><?= htmlspecialchars($it['note']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
