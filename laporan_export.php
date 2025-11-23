<?php
// laporan_export.php
session_start();
require_once 'db.php';

// pastikan admin login
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

// ambil filter
$filter = $_GET['filter'] ?? 'all';
$where = "";

if ($filter === 'day') {
    $where = "WHERE DATE(o.created_at) = CURDATE()";
} elseif ($filter === 'month') {
    $where = "WHERE MONTH(o.created_at) = MONTH(CURDATE()) AND YEAR(o.created_at) = YEAR(CURDATE())";
} elseif ($filter === 'year') {
    $where = "WHERE YEAR(o.created_at) = YEAR(CURDATE())";
}

// ambil data
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

$res = $mysqli->query($sql);
if (!$res) {
    die("Query error: " . $mysqli->error);
}
$rows = $res->fetch_all(MYSQLI_ASSOC);

// atur header untuk download Excel
$filename = "laporan_penjualan_" . date("Y-m-d_H-i-s") . ".xls";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// buat tabel Excel
echo "<table border='1'>";
echo "<thead>
<tr style='background:#ddd;'>
    <th>ID Order</th>
    <th>Nama Pemesan</th>
    <th>Kategori</th>
    <th>Menu Dipesan</th>
    <th>Total</th>
    <th>Pembayaran</th>
    <th>Tanggal</th>
</tr>
</thead><tbody>";

if (count($rows) > 0) {
    // kelompokkan per order
    $orders = [];
    foreach ($rows as $r) {
        $id = $r['order_id'];
        if (!isset($orders[$id])) {
            $orders[$id] = [
                'id' => $id,
                'customer_name' => $r['customer_name'],
                'total' => $r['total'],
                'created_at' => $r['created_at'],
                'payment_method' => $r['payment_method'],
                'items' => [],
                'categories' => []
            ];
        }
        $orders[$id]['items'][] = $r['qty']."x ".$r['menu_name'].($r['variant'] ? " (".$r['variant'].")" : "");
        if (!empty($r['category']) && !in_array($r['category'], $orders[$id]['categories'])) {
            $orders[$id]['categories'][] = $r['category'];
        }
    }

    $totalOmzet = 0;
    foreach ($orders as $o) {
        $totalOmzet += $o['total'];
        echo "<tr>";
        echo "<td>".$o['id']."</td>";
        echo "<td>".htmlspecialchars($o['customer_name'])."</td>";
        echo "<td>".htmlspecialchars(implode(", ", $o['categories'] ?: ['-']))."</td>";
        echo "<td>".htmlspecialchars(implode("; ", $o['items']))."</td>";
        echo "<td>".rupiah($o['total'])."</td>";
        echo "<td>".strtoupper($o['payment_method'])."</td>";
        echo "<td>".$o['created_at']."</td>";
        echo "</tr>";
    }

    // baris total omzet
    echo "<tr style='font-weight:bold;background:#f3f3f3;'>";
    echo "<td colspan='4' align='right'>Total Omzet (Periode)</td>";
    echo "<td>".rupiah($totalOmzet)."</td>";
    echo "<td colspan='2'></td>";
    echo "</tr>";
} else {
    echo "<tr><td colspan='7' align='center'>Tidak ada data</td></tr>";
}
echo "</tbody></table>";
exit;
