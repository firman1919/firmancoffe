<?php
// kasir.php
session_start();
require_once '../db.php';

// 🔒 Cek login
if (!isset($_SESSION['kasir_logged_in']) || $_SESSION['kasir_logged_in'] !== true) {
  header("Location: kasir_login.php");
  exit;
}

$kasirName = $_SESSION['kasir_username'] ?? 'Kasir';

// deteksi halaman aktif
$currentPage = basename($_SERVER['PHP_SELF']);
function activeClass($file, $currentPage)
{
  return $file === $currentPage ? "background:#353B48;border-left:4px solid #fff;" : "";
}

// helper rupiah
if (!function_exists('rupiah')) {
  function rupiah($angka)
  {
    return "Rp " . number_format($angka, 0, ',', '.');
  }
}

// ✅ Update status pesanan
if (isset($_POST['update_status'])) {
  $order_id = (int)$_POST['order_id'];
  $new_status = $_POST['status'] ?? 'baru';

  $stmt = $mysqli->prepare("UPDATE orders SET status=? WHERE id=?");
  if ($stmt) {
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    $stmt->close();
  }
  header("Location: kasir.php");
  exit;
}

// Ambil semua pesanan
$sql = "SELECT * FROM orders ORDER BY id DESC";
$res = $mysqli->query($sql);
$orders = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$lastOrderId = !empty($orders) ? max(array_column($orders, 'id')) : 0;
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <title>Kasir - Lihat Pesanan</title>
  <style>
    .status-select {
      padding: 6px 12px;
      border-radius: 6px;
      border: none;
      font-size: 13px;
      font-weight: 600;
      color: #fff;
      cursor: pointer;
    }
    .status-baru { background: #999; }
    .status-diproses { background: #f59e0b; }
    .status-selesai { background: #16a34a; }

    /* 🔔 Notifikasi toast */
    #toast {
      visibility: hidden;
      min-width: 250px;
      margin-left: -125px;
      background-color: #333;
      color: #fff;
      text-align: center;
      border-radius: 8px;
      padding: 16px;
      position: fixed;
      z-index: 1000;
      left: 50%;
      bottom: 30px;
      font-size: 14px;
    }
    #toast.show {
      visibility: visible;
      animation: fadein 0.5s, fadeout 0.5s 2.5s;
    }
    @keyframes fadein { from {bottom:0; opacity:0;} to {bottom:30px; opacity:1;} }
    @keyframes fadeout { from {bottom:30px; opacity:1;} to {bottom:0; opacity:0;} }

    /* 🎟️ Thermal Print Mode */
    @media print {
      body * {
        visibility: hidden;
      }
      .print-area, .print-area * {
        visibility: visible;
      }
      .print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 80mm; /* lebar thermal printer */
        font-family: monospace;
        font-size: 12px;
      }
      .no-print { display: none !important; }
    }

    .print-btn {
      background: #2F3640;
      color: #fff;
      border: none;
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 13px;
      cursor: pointer;
      margin-left: 8px;
    }
    .print-btn:hover { background: #353b48; }
  </style>

  <script>
    function autoSubmit(selectEl) {
      selectEl.form.submit();
    }

    // 🔧 Cetak Thermal
    function printOrder(orderId) {
      const printArea = document.querySelector(`#order-${orderId} .print-area`);
      if (!printArea) {
        alert("Gagal menemukan area cetak.");
        return;
      }
      const newWin = window.open('', '', 'width=400,height=600');
      newWin.document.write(`
        <html>
        <head>
          <title>Cetak Struk</title>
          <style>
            body { font-family: monospace; font-size: 12px; }
            h2 { text-align:center; margin-bottom:4px; }
            .line { border-bottom:1px dashed #000; margin:4px 0; }
            table { width:100%; border-collapse:collapse; }
            td { padding:2px 0; }
          </style>
        </head>
        <body>
          ${printArea.innerHTML}
          <script>window.print();window.close();<\/script>
        </body>
        </html>
      `);
      newWin.document.close();
    }
  </script>
</head>

<body style="margin:0;font-family:'Segoe UI',Arial,sans-serif;display:flex;height:100vh;background:#ECEFF1;">

  <!-- ✅ SIDEBAR -->
  <div style="width:220px;background:#2F3640;color:#fff;display:flex;flex-direction:column;box-shadow:2px 0 8px rgba(0,0,0,0.15);">
    <h2 style="text-align:center;margin:20px 0;font-size:20px;letter-spacing:1px;">Kasir</h2>

    <a href="kasir.php" style="padding:12px 20px;color:#fff;text-decoration:none;display:block;<?php echo activeClass('kasir.php', $currentPage); ?>">Lihat Pesanan</a>
    <a href="kasir_laporan.php" style="padding:12px 20px;color:#fff;text-decoration:none;display:block;<?php echo activeClass('kasir_laporan.php', $currentPage); ?>">Laporan</a>
    <a href="kasir_kelolastok.php" style="padding:12px 20px;color:#fff;text-decoration:none;display:block;<?php echo activeClass('kasir_kelolastok.php', $currentPage); ?>">Kelola Stok</a>
    <a href="logout.php" style="padding:12px 20px;color:#fff;text-decoration:none;display:block;">🔓 Logout</a>
  </div>

  <!-- ✅ MAIN CONTENT -->
  <div style="flex:1;padding:25px;overflow:auto;">
    <div style="background:#fff;padding:20px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.08);margin-bottom:20px;">
      <h1 style="margin:0;color:#2F3640;font-size:22px;">📋 Daftar Pesanan Masuk</h1>
      <p style="margin:5px 0 0;color:#555;font-size:14px;">Semua pesanan terbaru RK Coffee</p>
    </div>

    <div id="ordersContainer">
      <?php if (empty($orders)): ?>
        <div style="padding:20px;background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
          <p style="margin:0;color:#777;font-size:14px;">Belum ada pesanan.</p>
        </div>
      <?php else: ?>
        <?php foreach ($orders as $order): ?>
          <?php
            $order_id = (int) $order['id'];
            $stmt = $mysqli->prepare("SELECT * FROM order_items WHERE order_id=?");
            $items = [];
            if ($stmt) {
              $stmt->bind_param("i", $order_id);
              $stmt->execute();
              $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
              $stmt->close();
            }
          ?>
          <div class="order-card" id="order-<?= $order['id'] ?>"
               style="background:#fff;padding:20px;margin-bottom:20px;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.08);">

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

            <!-- STATUS + PRINT -->
            <div style="display:flex;align-items:center;gap:8px;margin-top:10px;">
              <form method="post" style="margin:0;">
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
              <button class="print-btn" onclick="printOrder(<?= $order['id'] ?>)">🖨️ Print</button>
            </div>

            <?php if ($order['payment_method'] === 'qris' && !empty($order['bukti_qris'])): ?>
              <div style="margin-top:10px;">
                <p style="margin:0;font-size:14px;font-weight:500;">Bukti QRIS:</p>
                <img src="../<?= htmlspecialchars($order['bukti_qris']) ?>" alt="Bukti QRIS"
                     style="max-width:160px;display:block;margin-top:6px;border:1px solid #ddd;border-radius:6px;">
              </div>
            <?php endif; ?>

            <div class="print-area" style="display:none;">
              <h2>RK Coffee</h2>
              <div class="line"></div>
              <p>No. Order: <?= $order['id'] ?><br>
              Meja: <?= htmlspecialchars($order['table_number']) ?><br>
              Kasir: <?= htmlspecialchars($kasirName) ?><br>
              Metode: <?= strtoupper($order['payment_method']) ?><br>
              Waktu: <?= htmlspecialchars($order['created_at']) ?></p>
              <div class="line"></div>
              <table>
                <?php foreach ($items as $it): ?>
                  <tr>
                    <td><?= htmlspecialchars($it['menu_name']) ?></td>
                    <td style="text-align:right;"><?= $it['qty'] ?>x</td>
                    <td style="text-align:right;"><?= rupiah($it['subtotal']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </table>
              <div class="line"></div>
              <p style="text-align:right;">Total: <strong><?= rupiah($order['total']) ?></strong></p>
              <p style="text-align:center;">Terima kasih 🙏<br>RK Coffee</p>
            </div>

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
                    <td style="padding:8px;text-align:center;"><?= (int)$it['qty'] ?></td>
                    <td style="padding:8px;text-align:right;"><?= rupiah($it['price']) ?></td>
                    <td style="padding:8px;text-align:right;color:#2F3640;font-weight:500;"><?= rupiah($it['subtotal']) ?></td>
                    <td style="padding:8px;"><?= htmlspecialchars($it['note']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- 🔔 Suara notifikasi -->
  <audio id="notifSound">
    <source src="notif.mp3" type="audio/mpeg">
  </audio>

  <!-- 🔔 Toast popup -->
  <div id="toast">Pesanan baru masuk!</div>

<script>
let lastId = <?= (int)$lastOrderId ?>;

function showToast(msg) {
  const x = document.getElementById("toast");
  x.textContent = msg;
  x.className = "show";
  setTimeout(() => { x.className = x.className.replace("show", ""); }, 3000);
}

function checkOrders() {
  fetch("check_new_orders.php?last_id=" + lastId)
    .then(res => res.json())
    .then(data => {
      if (data.length > 0) {
        data.forEach(order => {
          fetch("get_order_detail.php?id=" + order.id)
            .then(r => r.text())
            .then(html => {
              const container = document.getElementById("ordersContainer");
              const temp = document.createElement("div");
              temp.innerHTML = html.trim();
              const newOrder = temp.firstChild;
              newOrder.id = "order-" + order.id;
              container.prepend(newOrder);
              const audio = document.getElementById("notifSound");
              audio.currentTime = 0;
              audio.play().catch(() => {});
              showToast("Pesanan baru dari meja " + order.table_number);
              lastId = Math.max(lastId, order.id);
            });
        });
      }
    })
    .catch(err => console.error("Error checkOrders:", err));
}

setInterval(checkOrders, 5000);

// 🔧 Aktifkan audio setelah interaksi pertama
document.addEventListener('click', function enableAudio() {
  const audio = document.getElementById('notifSound');
  audio.play().catch(() => {});
  document.removeEventListener('click', enableAudio);
});
</script>
</body>
</html>
