<?php
// order_success.php
session_start();
require_once 'db.php'; // rupiah() sudah ada di sini

$id = intval($_GET['id'] ?? 0);
if (!$id) {
  echo "Order ID tidak valid.";
  exit;
}

// ambil order
$stmt = $mysqli->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$order = $res->fetch_assoc();
$stmt->close();

if (!$order) {
  echo "Order tidak ditemukan.";
  exit;
}

// ambil items
$stmt = $mysqli->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$items = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Struk Pembayaran - SAGA Cafe</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #fafafa;
      padding: 25px;
      color: #333;
    }
    .container {
      max-width: 500px;
      margin: auto;
      background: #fff;
      padding: 25px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      border-radius: 10px;
      text-align: center;
      animation: fadeIn 0.5s ease-in-out;
    }
    h1 {
      color: #5a3b2e;
      margin-bottom: 10px;
    }
    .info {
      text-align: left;
      margin-top: 15px;
      font-size: 14px;
    }
    .info p {
      margin: 5px 0;
    }
    .line {
      border-top: 1px dashed #ccc;
      margin: 20px 0;
    }
    ul {
      text-align: left;
      list-style: none;
      padding: 0;
    }
    ul li {
      margin-bottom: 8px;
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      font-size: 14px;
      flex-wrap: wrap;
    }
    .variant, .note {
      display: block;
      font-size: 12px;
      color: #777;
      margin-left: 5px;
    }
    .total {
      font-size: 1.1em;
      font-weight: bold;
      margin-top: 15px;
      text-align: right;
    }
    .footer {
      margin-top: 20px;
      font-size: 13px;
      color: #666;
      text-align: center;
    }
    .btn-home {
      display: inline-block;
      margin-top: 20px;
      padding: 10px 25px;
      background-color: #6f4e37;
      color: white;
      text-decoration: none;
      border-radius: 6px;
      font-weight: bold;
      transition: background 0.3s ease;
    }
    .btn-home:hover {
      background-color: #8b5e3b;
    }

    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(10px);}
      to {opacity: 1; transform: translateY(0);}
    }

    @media print {
      body { background: #fff; padding: 0; }
      .btn-home { display: none; }
      .container { box-shadow: none; }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Struk Pembayaran</h1>

    <div class="info">
      <p><strong>Nama:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
      <p><strong>Meja:</strong> <?= htmlspecialchars($order['table_number']) ?></p>
      <p><strong>Tanggal:</strong> <?= date("d M Y H:i", strtotime($order['created_at'])) ?></p>
      <p><strong>Metode Pembayaran:</strong> <?= strtoupper($order['payment_method']) ?></p>
    </div>

    <div class="line"></div>

    <h3 style="text-align:left;color:#5a3b2e;">Detail Pesanan</h3>
    <ul>
      <?php foreach ($items as $it): ?>
        <li>
          <div>
            <?= htmlspecialchars($it['menu_name']) ?> x<?= intval($it['qty']) ?>
            <?php if (!empty($it['variant'])): ?>
              <span class="variant">(<?= htmlspecialchars($it['variant']) ?>)</span>
            <?php endif; ?>
            <?php if (!empty($it['note'])): ?>
              <span class="note">* <?= htmlspecialchars($it['note']) ?></span>
            <?php endif; ?>
          </div>
          <div><?= rupiah($it['subtotal']) ?></div>
        </li>
      <?php endforeach; ?>
    </ul>

    <div class="line"></div>

    <p class="total">Total: <?= rupiah($order['total']) ?></p>

    <div class="footer">
      Terima kasih telah memesan di <strong>RK Coffe</strong> <br> simpan struk ini untuk pemabyaran di kasir
    </div>

    <a class="btn-home" href="index.php">← Kembali ke Halaman Awal</a>
  </div>
</body>
</html>