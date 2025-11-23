<?php
// laporan.php (admin) - Laporan Penjualan per Metode (dengan filter tampilan + ekspor excel)
session_start();
require_once 'db.php';

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header("Location: admin_login.php");
  exit;
}

// helper rupiah
if (!function_exists('rupiah')) {
  function rupiah($n) {
    return "Rp " . number_format($n, 0, ',', '.');
  }
}

// Filter waktu: all / day / month / year
$filter = $_GET['filter'] ?? 'all';
$where = "";

if ($filter === 'day') {
  $where = "WHERE DATE(o.created_at) = CURDATE()";
} elseif ($filter === 'month') {
  $where = "WHERE MONTH(o.created_at) = MONTH(CURDATE()) AND YEAR(o.created_at) = YEAR(CURDATE())";
} elseif ($filter === 'year') {
  $where = "WHERE YEAR(o.created_at) = YEAR(CURDATE())";
}

// Filter metode pembayaran: all / qris / cash
$payment = $_GET['payment'] ?? 'all';
if ($payment !== 'all') {
  $where .= ($where ? " AND " : "WHERE ") . "LOWER(o.payment_method) = '" . strtolower($payment) . "'";
}

// Ambil data
$sql = "
  SELECT 
      o.id AS order_id,
      o.customer_name,
      o.total,
      o.created_at,
      o.payment_method,
      oi.menu_name,
      oi.variant,
      oi.qty,
      m.category
  FROM orders o
  JOIN order_items oi ON o.id = oi.order_id
  LEFT JOIN menus m ON oi.menu_id = m.id
  $where
  ORDER BY o.created_at DESC, o.id DESC
";
$stmt = $mysqli->prepare($sql);
if ($stmt === false) {
  echo "Query error: " . htmlspecialchars($mysqli->error);
  exit;
}
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Kelompokkan per order_id
$orders = [];
foreach ($rows as $r) {
  $id = (int)$r['order_id'];
  if (!isset($orders[$id])) {
    $orders[$id] = [
      'id' => $id,
      'customer_name' => $r['customer_name'],
      'total' => (float)$r['total'],
      'created_at' => $r['created_at'],
      'payment_method' => $r['payment_method'],
      'items' => [],
      'categories' => []
    ];
  }
  $orders[$id]['items'][] = [
    'menu_name' => $r['menu_name'],
    'variant' => $r['variant'],
    'qty' => (int)$r['qty']
  ];
  if (!empty($r['category']) && !in_array($r['category'], $orders[$id]['categories'])) {
    $orders[$id]['categories'][] = $r['category'];
  }
}

// Hitung total omzet
$totalOmzet = 0;
foreach ($orders as $o) {
  $totalOmzet += $o['total'];
}

// ==== FITUR EXPORT EXCEL ====
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
  header("Content-Type: application/vnd.ms-excel");
  header("Content-Disposition: attachment; filename=laporan_penjualan_" . $payment . "_" . $filter . ".xls");
  echo "<table border='1'>
    <thead>
      <tr>
        <th>ID</th>
        <th>Nama Pemesan</th>
        <th>Kategori</th>
        <th>Menu Dipesan</th>
        <th>Total</th>
        <th>Pembayaran</th>
        <th>Tanggal</th>
      </tr>
    </thead><tbody>";
  foreach ($orders as $o) {
    echo "<tr>
      <td>{$o['id']}</td>
      <td>" . htmlspecialchars($o['customer_name']) . "</td>
      <td>" . htmlspecialchars(implode(", ", $o['categories'] ?: ['-'])) . "</td>
      <td>";
      foreach ($o['items'] as $it) {
        echo $it['qty'] . "x " . htmlspecialchars($it['menu_name']);
        if ($it['variant']) echo " (" . htmlspecialchars($it['variant']) . ")";
        echo "<br>";
      }
    echo "</td>
      <td>" . rupiah($o['total']) . "</td>
      <td>" . strtoupper($o['payment_method']) . "</td>
      <td>{$o['created_at']}</td>
    </tr>";
  }
  echo "</tbody></table>";
  echo "<p><b>Total Omzet:</b> " . rupiah($totalOmzet) . "</p>";
  exit;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Laporan Penjualan - Admin</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    :root{
      --bg:#f4f5f7;
      --panel:#fff;
      --accent:#2F3640;
      --muted:#6b7280;
      --qris:#059669;
      --cash:#6b7280;
    }
    body{margin:0;font-family:Inter, "Segoe UI",Arial,sans-serif;background:var(--bg);color:#222;overflow:hidden;}
    .wrap{display:flex;min-height:100vh;}
    
    /* ✅ Sidebar fix agar tidak ikut scroll */
    .sidebar{
      width:220px;
      background:var(--accent);
      color:#fff;
      display:flex;
      flex-direction:column;
      box-shadow:2px 0 8px rgba(0,0,0,0.12);
      position:fixed;
      top:0;
      left:0;
      height:100vh;
      overflow-y:auto;
      z-index:10;
    }
    
    .sidebar h2{margin:20px 0;text-align:center;font-size:18px;}
    .sidebar a{padding:12px 18px;color:#fff;text-decoration:none;display:block;transition:0.15s;}
    .sidebar a.active{background:#353b48;border-left:4px solid #fff;}
    
    /* Konten utama di kanan */
    .main{
      flex:1;
      padding:22px;
      margin-left:220px;
      height:100vh;
      overflow-y:auto;
    }

    .panel{background:var(--panel);padding:18px;border-radius:10px;box-shadow:0 4px 14px rgba(9,30,66,0.06);margin-bottom:16px;}
    h1{margin:0 0 6px 0;color:var(--accent);font-size:20px;}
    p.lead{margin:0;color:var(--muted);font-size:13px;}
    .filters{margin:12px 0 18px 0;}
    .filters a{display:inline-block;padding:8px 12px;border-radius:8px;text-decoration:none;margin-right:8px;font-size:14px;}
    .filters a.active{background:var(--accent);color:#fff;}
    .btn-export{float:right;background:var(--accent);color:#fff;padding:8px 14px;border-radius:8px;text-decoration:none;font-size:13px;}
    .btn-export:hover{opacity:0.9;}
    table{width:100%;border-collapse:collapse;font-size:14px;background:transparent;}
    thead th{background:#f3f4f6;padding:10px;text-align:left;color:#111;font-weight:600;border-bottom:1px solid #e6e6e6;}
    tbody td{padding:12px;border-bottom:1px solid #f1f1f1;vertical-align:top;}
    .badge{display:inline-block;padding:6px 8px;border-radius:6px;font-size:13px;color:#fff;}
    .badge.qris{background:var(--qris);}
    .badge.cash{background:var(--cash);}
    .items-list{margin:0;padding-left:18px;font-size:13px;color:#333;}
    .tot-row{display:flex;justify-content:flex-end;margin-top:10px;font-weight:700;font-size:15px;color:var(--accent);}
    .no-data{padding:18px;text-align:center;color:var(--muted);}
    
    @media(max-width:900px){
      .sidebar{display:none;}
      .main{padding:12px;margin-left:0;}
      thead th,tbody td{font-size:13px;padding:8px;}
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="sidebar">
      <h2>Admin</h2>
      <a href="admin_dashboard.php">🏠 Dashboard</a>
      <a href="laporan.php" class="active">📊 Laporan</a>
      <a href="menu_manage.php">📋 Kelola Menu</a>
      <a href="logout.php">🚪 Logout</a>
    </div>

    <div class="main">
      <div class="panel">
        <h1>📊 Laporan Penjualan</h1>
        <p class="lead">Pilih periode dan metode pembayaran untuk melihat laporan.</p>
      </div>

      <div class="panel">
        <a href="?filter=<?= $filter ?>&payment=<?= $payment ?>&export=excel" class="btn-export">⬇ Export Excel</a>

        <!-- Filter waktu -->
        <div class="filters">
          <strong>Periode:</strong>
          <a href="?filter=all&payment=<?= $payment ?>" class="<?= $filter === 'all' ? 'active' : '' ?>">Semua</a>
          <a href="?filter=day&payment=<?= $payment ?>" class="<?= $filter === 'day' ? 'active' : '' ?>">Harian</a>
          <a href="?filter=month&payment=<?= $payment ?>" class="<?= $filter === 'month' ? 'active' : '' ?>">Bulanan</a>
          <a href="?filter=year&payment=<?= $payment ?>" class="<?= $filter === 'year' ? 'active' : '' ?>">Tahunan</a>
        </div>

        <!-- Filter metode -->
        <div class="filters">
          <strong>Metode:</strong>
          <a href="?filter=<?= $filter ?>&payment=all" class="<?= $payment === 'all' ? 'active' : '' ?>">Semua</a>
          <a href="?filter=<?= $filter ?>&payment=qris" class="<?= $payment === 'qris' ? 'active' : '' ?>">QRIS</a>
          <a href="?filter=<?= $filter ?>&payment=cash" class="<?= $payment === 'cash' ? 'active' : '' ?>">Cash</a>
        </div>

        <?php if (count($orders) === 0): ?>
          <div class="no-data">Belum ada transaksi untuk periode ini.</div>
        <?php else: ?>
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Nama Pemesan</th>
                <th>Kategori</th>
                <th>Menu Dipesan</th>
                <th style="text-align:right">Total</th>
                <th>Pembayaran</th>
                <th>Tanggal</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $o): ?>
                <tr>
                  <td><?= $o['id'] ?></td>
                  <td><?= htmlspecialchars($o['customer_name']) ?></td>
                  <td><?= htmlspecialchars(implode(", ", $o['categories'] ?: ['-'])) ?></td>
                  <td>
                    <ul class="items-list">
                      <?php foreach ($o['items'] as $it): ?>
                        <li><?= $it['qty'] ?>x <?= htmlspecialchars($it['menu_name']) ?> <?= $it['variant'] ? "(" . htmlspecialchars($it['variant']) . ")" : "" ?></li>
                      <?php endforeach; ?>
                    </ul>
                  </td>
                  <td style="text-align:right;"><?= rupiah($o['total']) ?></td>
                  <td>
                    <?php if (strtolower($o['payment_method']) === 'qris'): ?>
                      <span class="badge qris">QRIS</span>
                    <?php else: ?>
                      <span class="badge cash">Cash</span>
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($o['created_at']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <div class="tot-row">
            Total Omzet (<?= strtoupper($payment) ?>): <?= rupiah($totalOmzet) ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>
