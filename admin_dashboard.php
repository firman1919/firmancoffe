<?php
// admin_dashboard.php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

if (!function_exists('rupiah')) {
    function rupiah($n) {
        return "Rp " . number_format($n, 0, ',', '.');
    }
}

// ---------- Data ----------
$year = date('Y');

// summary
$totals = [
  'today' => 0,
  'month' => 0,
  'year'  => 0,
];
$res = $mysqli->query("SELECT COALESCE(SUM(total),0) AS s FROM orders WHERE DATE(created_at)=CURDATE()");
$totals['today'] = (float)$res->fetch_assoc()['s'];

$res = $mysqli->query("SELECT COALESCE(SUM(total),0) AS s FROM orders WHERE YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())");
$totals['month'] = (float)$res->fetch_assoc()['s'];

$res = $mysqli->query("SELECT COALESCE(SUM(total),0) AS s FROM orders WHERE YEAR(created_at)=YEAR(CURDATE())");
$totals['year'] = (float)$res->fetch_assoc()['s'];

// kategori
$sql = "SELECT COALESCE(m.category,'Lainnya') AS category, SUM(oi.qty) AS total_qty
        FROM order_items oi
        LEFT JOIN menus m ON oi.menu_id=m.id
        GROUP BY category
        ORDER BY total_qty DESC";
$res = $mysqli->query($sql);
$cat_labels = $cat_values = [];
while ($res && $r=$res->fetch_assoc()) {
  $cat_labels[]=$r['category'];
  $cat_values[]=(int)$r['total_qty'];
}

// monthly
$sql = "SELECT MONTH(created_at) AS m, COALESCE(SUM(total),0) AS sum_total
        FROM orders
        WHERE YEAR(created_at)=$year
        GROUP BY MONTH(created_at)";
$res = $mysqli->query($sql);
$monthly = array_fill(1,12,0);
while ($res && $r=$res->fetch_assoc()) {
  $monthly[(int)$r['m']] = (float)$r['sum_total'];
}
$month_labels = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
$month_values = [];
for ($i=1;$i<=12;$i++) $month_values[]=$monthly[$i];

// top menu
$sql="SELECT oi.menu_name,SUM(oi.qty) AS total_qty FROM order_items oi GROUP BY oi.menu_name ORDER BY total_qty DESC LIMIT 5";
$res=$mysqli->query($sql);
$top_menus = $res ? $res->fetch_all(MYSQLI_ASSOC):[];

// encode
$cat_labels_js=json_encode($cat_labels);
$cat_values_js=json_encode($cat_values);
$month_labels_js=json_encode($month_labels);
$month_values_js=json_encode($month_values);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Admin Dashboard</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
:root{
  --bg:#f4f5f7;
  --panel:#fff;
  --accent:#2F3640;
  --muted:#6b7280;
}
body{margin:0;font-family:Inter,Segoe UI,Arial,sans-serif;background:var(--bg);color:#222;}
.wrap{display:flex;min-height:100vh;}

/* 🔧 Sidebar Fixed */
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
  bottom:0;
  z-index:10;
}
.sidebar h2{margin:20px 0;text-align:center;font-size:18px;}
.sidebar a{padding:12px 18px;color:#fff;text-decoration:none;display:block;transition:0.15s;}
.sidebar a.active{background:#353b48;border-left:4px solid #fff;}

/* 🔧 Tambah padding kiri agar konten tidak ketutup sidebar */
.main{
  flex:1;
  padding:22px;
  margin-left:220px;
}

.panel{background:var(--panel);padding:18px;border-radius:10px;box-shadow:0 4px 14px rgba(9,30,66,0.06);margin-bottom:16px;}
h1{margin:0 0 6px 0;color:var(--accent);font-size:20px;}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:16px;}
.card{background:var(--panel);padding:16px;border-radius:10px;box-shadow:0 6px 18px rgba(20,20,20,0.06);}
.card h3{margin:0 0 8px;font-size:14px;color:var(--muted);}
.big{font-size:20px;font-weight:700;color:var(--accent);}
.charts{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
canvas{width:100%!important;height:320px!important;}
.top-list{margin-top:12px;}
.top-list li{padding:6px 0;border-bottom:1px dashed #eee;font-size:14px;}

@media(max-width:900px){
  .sidebar{display:none;}
  .main{padding:12px;margin-left:0;}
  .grid{grid-template-columns:1fr;}
  .charts{grid-template-columns:1fr;}
}
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="wrap">
  <div class="sidebar">
    <h2>Admin</h2>
    <a href="admin_dashboard.php" class="active">🏠 Dashboard</a>
    <a href="laporan.php">📊 Laporan</a>
    <a href="menu_manage.php">📋 Kelola Menu</a>
    <a href="logout.php" >🚪 Logout</a>
  </div>

  <div class="main">
    <div class="panel">
      <h1>📊 Dashboard Admin</h1>
      <p class="lead" style="color:var(--muted);font-size:13px;">Ringkasan penjualan & grafik tahun <?= $year ?></p>
    </div>

    <div class="grid">
      <div class="card"><h3>Penjualan Hari Ini</h3><div class="big"><?= rupiah($totals['today']) ?></div></div>
      <div class="card"><h3>Penjualan Bulan Ini</h3><div class="big"><?= rupiah($totals['month']) ?></div></div>
      <div class="card"><h3>Penjualan Tahun Ini</h3><div class="big"><?= rupiah($totals['year']) ?></div></div>
    </div>

    <div class="charts">
      <div class="card">
        <h3>Penjualan per Kategori (qty)</h3>
        <canvas id="catChart"></canvas>
      </div>
      <div class="card">
        <h3>Penjualan per Bulan (Rp)</h3>
        <canvas id="monthChart"></canvas>
        <div style="margin-top:14px;">
          <h3 style="margin-bottom:8px;">Top 5 Menu</h3>
          <ul class="top-list">
          <?php if (!$top_menus): ?><li class="muted">Belum ada penjualan</li><?php endif; ?>
          <?php foreach($top_menus as $tm): ?>
            <li><strong><?= htmlspecialchars($tm['menu_name']) ?></strong>
              <span style="float:right;color:var(--muted);"><?= intval($tm['total_qty']) ?> pcs</span></li>
          <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
new Chart(document.getElementById('catChart'),{
  type:'pie',
  data:{labels:<?= $cat_labels_js ?>,datasets:[{data:<?= $cat_values_js ?>,
  backgroundColor:['#6366f1','#10b981','#f59e0b','#ef4444','#06b6d4','#f97316','#8b5cf6','#84cc16'],borderWidth:0}]},
  options:{plugins:{legend:{position:'bottom'}}}
});
new Chart(document.getElementById('monthChart'),{
  type:'bar',
  data:{labels:<?= $month_labels_js ?>,datasets:[{label:'Pendapatan (Rp)',data:<?= $month_values_js ?>,
  backgroundColor:'#2F3640',borderRadius:6,barThickness:28}]},
  options:{scales:{y:{ticks:{callback:v=>v>=1000?(v/1000)+'k':v}}},plugins:{legend:{display:false}}}
});
</script>
</body>
</html>
