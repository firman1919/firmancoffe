<?php
// checkout.php
session_start();
require_once 'db.php';

if (!isset($_SESSION['customer']) || empty($_SESSION['cart'])) {
  header("Location: menu.php");
  exit;
}

$customer = $_SESSION['customer'];
$cart = $_SESSION['cart'];

// total
$total = 0;
foreach ($cart as $it) {
  $total += $it['price'] * $it['qty'];
}

// pastikan folder upload ada
$uploadDir = __DIR__ . "/uploads/";
if (!is_dir($uploadDir)) {
  mkdir($uploadDir, 0777, true);
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $method = $_POST['payment'] ?? '';
  if ($method === '') {
    $error = "Silakan pilih metode pembayaran.";
  } else {
    $_SESSION['payment_method'] = $method;
    if ($method === 'qris') {
      if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowed)) {
          $error = "Format file harus JPG atau PNG.";
        } elseif ($_FILES['bukti']['size'] > 2 * 1024 * 1024) {
          $error = "Ukuran file maksimal 2MB.";
        } else {
          $fname = "qris_" . time() . "." . $ext;
          $target = $uploadDir . $fname;
          if (move_uploaded_file($_FILES['bukti']['tmp_name'], $target)) {
            $_SESSION['bukti_qris'] = "uploads/" . $fname;
            header("Location: order_submit.php");
            exit;
          } else {
            $error = "Gagal upload bukti pembayaran.";
          }
        }
      } else {
        $error = "Silakan upload bukti pembayaran QRIS.";
      }
    } else {
      header("Location: order_submit.php");
      exit;
    }
  }
}
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <title>Checkout - RK Coffee</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    :root {
      --bg: #fbf6f2;
      --card: #fff;
      --accent: #6b422b;
      --muted: #8b8b8b;
      --danger: #e75b4b;
      --grey: #bdbdbd;
    }

    body {
      margin: 0;
      font-family: "Helvetica Neue", Arial, sans-serif;
      background: var(--bg);
      color: #333;
    }

    .container {
      max-width: 480px;
      margin: 28px auto;
      padding: 18px;
    }

    .card {
      background: var(--card);
      border-radius: 14px;
      padding: 28px;
      box-shadow: 0 6px 20px rgba(40, 30, 20, 0.06);
    }

    h1 {
      margin: 0 0 18px 0;
      text-align: center;
      font-size: 28px;
      color: var(--accent);
      font-weight: 700;
    }

    .meta {
      font-size: 14px;
      color: var(--muted);
      text-align: center;
      margin-bottom: 18px;
    }

    .list {
      list-style: none;
      padding: 0;
      margin: 0 0 14px 0;
    }

    .list li {
      padding: 12px 6px;
      border-bottom: 1px dashed #e6e6e6;
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 12px;
      font-size: 15px;
    }

    .list li .left {
      flex: 1 1 auto;
    }

    .list li .right {
      white-space: nowrap;
      text-align: right;
      min-width: 110px;
      color: #444;
      font-weight: 600;
    }

    .variant {
      display: block;
      font-size: 13px;
      color: var(--muted);
      margin-top: 4px;
    }

    .note-display {
      margin-top: 6px;
      font-size: 13px;
      color: #555;
      font-style: italic;
    }

    .total-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 16px;
      padding-top: 12px;
      border-top: 2px solid #f1f1f1;
      font-weight: 700;
      font-size: 18px;
    }

    .section {
      margin-top: 18px;
    }

    label.section-title {
      font-weight: 700;
      display: block;
      margin-bottom: 8px;
      color: #434343;
    }

    select,
    input[type="file"] {
      width: 100%;
      padding: 12px;
      border-radius: 8px;
      border: 1px solid #e6e6e6;
      font-size: 15px;
      background: #fff;
    }

    #qris-box {
      margin-top: 12px;
      display: none;
      text-align: center;
    }

    #qris-box img.qris {
      max-width: 200px;
      border-radius: 8px;
      border: 1px solid #eee;
      display: block;
      margin: 8px auto;
    }

    .btn {
      display: block;
      width: 100%;
      padding: 12px;
      border-radius: 10px;
      border: 0;
      font-size: 16px;
      cursor: pointer;
      margin-top: 12px;
    }

    .btn--primary {
      background: var(--accent);
      color: #fff;
    }

    .btn--danger {
      background: var(--danger);
      color: #fff;
    }

    .btn--muted {
      background: var(--grey);
      color: #fff;
    }

    a.btn-link {
      display: block;
      text-align: center;
      text-decoration: none;
      padding: 12px;
      border-radius: 10px;
      margin-top: 10px;
      color: #fff;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="card">
      <h1>Checkout</h1>
      <p class="meta"><strong>Nama:</strong> <?= htmlspecialchars($customer['name']) ?> &nbsp;|&nbsp; <strong>Meja:</strong> <?= htmlspecialchars($customer['table']) ?></p>

      <?php if ($error): ?>
        <p style="color: #c0392b; font-weight:600; text-align:center;"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data" id="checkoutForm">
        <ul class="list">
          <?php foreach ($cart as $k => $it):
            $sub = $it['price'] * $it['qty']; ?>
            <li>
              <div class="left">
                <?= htmlspecialchars($it['name']) ?>
                <?php if (!empty($it['variant'])): ?>
                  <span class="variant"><?= htmlspecialchars(ucfirst($it['variant'])) ?></span>
                <?php endif; ?>
                <?php if (!empty($it['note'])): ?>
                  <div class="note-display">Catatan: <?= htmlspecialchars($it['note']) ?></div>
                <?php endif; ?>
              </div>
              <div class="right">
                x <?= intval($it['qty']) ?><br>
                <?= rupiah($sub) ?>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>

        <div class="total-row">
          <div>Total:</div>
          <div><?= rupiah($total) ?></div>
        </div>

        <div class="section">
          <label class="section-title">Metode Pembayaran:</label>
          <?php $selectedPayment = $_POST['payment'] ?? $_SESSION['payment_method'] ?? ''; ?>
          <select name="payment" id="payment" required>
            <option value="">-- Pilih --</option>
            <option value="cash" <?= $selectedPayment === 'cash' ? 'selected' : '' ?>>Tunai</option>
            <option value="qris" <?= $selectedPayment === 'qris' ? 'selected' : '' ?>>QRIS</option>
          </select>

          <div id="qris-box">
            <p>Silakan scan QRIS di bawah ini lalu upload bukti:</p>
            <img class="qris" src="images/1.png" alt="QRIS">
            <input type="file" name="bukti" id="bukti" accept="image/*">
            <div id="preview"></div>
          </div>
        </div>

        <button type="submit" class="btn btn--primary">✅ Selesai</button>

        <a href="cart.php" class="btn-link" style="background:var(--danger)">🔁 Reset Pesanan</a>
        <a href="menu.php" class="btn-link" style="background:var(--grey)">⬅ Kembali ke Menu</a>
      </form>
    </div>
  </div>

  <script>
    const sel = document.getElementById('payment');
    const box = document.getElementById('qris-box');
    const form = document.getElementById('checkoutForm');
    const bukti = document.getElementById('bukti');

    function toggle() {
      box.style.display = sel.value === 'qris' ? 'block' : 'none';
    }
    sel.addEventListener('change', toggle);
    toggle();

    form.addEventListener('submit', function (e) {
      if (sel.value === '') {
        e.preventDefault();
        alert("Silakan pilih metode pembayaran terlebih dahulu.");
        return false;
      }
      if (sel.value === 'qris' && bukti.files.length === 0) {
        e.preventDefault();
        alert("Silakan upload bukti pembayaran QRIS sebelum melanjutkan.");
        return false;
      }
    });

    // preview file
    function previewFile(e) {
      const file = e.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = function (ev) {
        document.getElementById('preview').innerHTML =
          '<img src="' + ev.target.result + '" style="max-width:180px; margin-top:10px; border:1px solid #eee; border-radius:8px;">';
      };
      reader.readAsDataURL(file);
    }
    bukti?.addEventListener('change', previewFile);
  </script>
</body>
</html>
