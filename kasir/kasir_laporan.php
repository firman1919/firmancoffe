<?php
session_start();
require_once '../db.php';
// filepath: c:\laragon\www\firmancoffe\kasir\kasir_dashboard.php

// Pastikan hanya kasir yang bisa akses
if (!isset($_SESSION['kasir_logged_in']) || $_SESSION['kasir_logged_in'] !== true) {
    header("Location: kasir_login.php");
    exit;
}

// Query hanya untuk hari ini + ambil payment_method
$sql = "
    SELECT 
        o.id AS order_id,
        o.customer_name,
        o.total,
        o.created_at,
        o.payment_method,
        m.category,
        oi.menu_name,
        oi.variant,
        oi.qty
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN menus m ON oi.menu_id = m.id
    WHERE DATE(o.created_at) = CURDATE()
    ORDER BY o.created_at DESC
";

$stmt = $mysqli->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Kelompokkan per order_id
$orders = [];
$total_harian = 0;
foreach ($rows as $r) {
    $id = $r['order_id'];
    if (!isset($orders[$id])) {
        $orders[$id] = [
            'id' => $id,
            'customer_name' => $r['customer_name'],
            'total' => $r['total'],
            'created_at' => $r['created_at'],
            'payment_method' => $r['payment_method'],
            'items' => []
        ];
        $total_harian += $r['total'];
    }
    $orders[$id]['items'][] = [
        'menu_name' => $r['menu_name'],
        'variant' => $r['variant'],
        'qty' => $r['qty'],
        'category' => $r['category']
    ];
}

// deteksi halaman aktif
$currentPage = basename($_SERVER['PHP_SELF']);
function activeClass($file, $currentPage)
{
    return $file === $currentPage ? "background:#353B48;border-left:4px solid #fff;" : "";
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan (Hari Ini)</title>
</head>
<body style="margin:0;font-family:'Segoe UI', Tahoma, sans-serif;background:#F5F6FA;">

    <!-- WRAPPER -->
    <div style="display:flex;min-height:100vh;">

        <!-- SIDEBAR (dibuat fixed agar tidak ikut scroll) -->
        <div style="
            width:220px;
            background:#2F3640;
            color:#fff;
            display:flex;
            flex-direction:column;
            box-shadow:2px 0 8px rgba(0,0,0,0.15);
            position:fixed;
            top:0;
            left:0;
            bottom:0;
        ">
            <h2 style="text-align:center;margin:20px 0;font-size:20px;letter-spacing:1px;">Kasir</h2>
            <a href="kasir.php"
                style="padding:12px 20px;color:#fff;text-decoration:none;display:block;transition:0.3s;<?php echo activeClass('kasir.php', $currentPage); ?>">
                Lihat Pesanan</a>
            <a href="kasir_laporan.php"
                style="padding:12px 20px;color:#fff;text-decoration:none;display:block;transition:0.3s;<?php echo activeClass('kasir_laporan.php', $currentPage); ?>">
                Laporan</a>
            <a href="kasir_kelolastok.php"
                style="padding:12px 20px;color:#fff;text-decoration:none;display:block;transition:0.3s;<?php echo activeClass('kasir_kelolastok.php', $currentPage); ?>">
                Kelola Stok</a>
            <a href="logout.php"
                style="padding:12px 20px;color:#fff;text-decoration:none;display:block;transition:0.3s;">🔓 Logout</a>
        </div>

        <!-- MAIN CONTENT -->
        <div style="flex:1;padding:20px;overflow:auto;margin-left:220px;">
            <h1 style="color:#353B48;margin-bottom:20px;">📊 Laporan Penjualan - Hari Ini</h1>

            <!-- TOMBOL FILTER PAYMENT METHOD -->
            <div style="margin-bottom:15px;text-align:center;">
                <button class="filter-payment-btn active" onclick="filterPayment('all')"
                    style="padding:8px 14px;margin:0 5px;border:none;border-radius:6px;background:#353B48;color:#fff;cursor:pointer;">
                    Semua
                </button>
                <button class="filter-payment-btn" onclick="filterPayment('cash')"
                    style="padding:8px 14px;margin:0 5px;border:none;border-radius:6px;background:#7f8fa6;color:#fff;cursor:pointer;">
                    CASH
                </button>
                <button class="filter-payment-btn" onclick="filterPayment('qris')"
                    style="padding:8px 14px;margin:0 5px;border:none;border-radius:6px;background:#7f8fa6;color:#fff;cursor:pointer;">
                    QRIS
                </button>
            </div>

            <!-- TABEL -->
            <div style="background:#fff;padding:20px;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                <table id="ordersTable" style="width:100%;border-collapse:separate;border-spacing:0 6px;font-size:14px;">
                    <thead>
                        <tr style="background:#353B48;color:#fff;text-align:left;">
                            <th style="padding:12px 10px;border-radius:6px 0 0 6px;">ID</th>
                            <th style="padding:12px 10px;">Nama Pemesan</th>
                            <th style="padding:12px 10px;">Kategori</th>
                            <th style="padding:12px 10px;">Menu Dipesan</th>
                            <th style="padding:12px 10px;">Total</th>
                            <th style="padding:12px 10px;">Pembayaran</th>
                            <th style="padding:12px 10px;border-radius:0 6px 6px 0;">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($orders) > 0): ?>
                            <?php foreach ($orders as $o): ?>
                                <tr class="order-row" data-payment="<?= $o['payment_method'] ?>" style="background:#fafafa;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                                    <td style="padding:10px 12px;border-radius:6px 0 0 6px;"><?= $o['id'] ?></td>
                                    <td style="padding:10px 12px;"><?= htmlspecialchars($o['customer_name']) ?></td>
                                    <td style="padding:10px 12px;">
                                        <?php
                                        $kategori = array_unique(array_map(fn($i) => $i['category'], $o['items']));
                                        echo implode(", ", $kategori);
                                        ?>
                                    </td>
                                    <td style="padding:10px 12px;">
                                        <ul style="margin:0;padding-left:18px;list-style:disc;">
                                            <?php foreach ($o['items'] as $it): ?>
                                                <li>
                                                    <?= $it['qty'] ?>x <?= htmlspecialchars($it['menu_name']) ?>
                                                    (<?= htmlspecialchars($it['variant']) ?>)
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </td>
                                    <td style="padding:10px 12px;color:#2F3640;font-weight:600;">
                                        Rp <?= number_format($o['total'], 0, ',', '.') ?>
                                    </td>
                                    <td style="padding:10px 12px;">
                                        <?php if ($o['payment_method'] === 'qris'): ?>
                                            <span style="background:#00a8ff;color:#fff;padding:4px 8px;border-radius:6px;font-size:12px;">QRIS</span>
                                        <?php else: ?>
                                            <span style="background:#44bd32;color:#fff;padding:4px 8px;border-radius:6px;font-size:12px;">CASH</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:10px 12px;border-radius:0 6px 6px 0;"><?= $o['created_at'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <!-- TOTAL HARIAN -->
                            <tr style="background:#353B48;color:#fff;font-weight:bold;">
                                <td colspan="4" style="padding:12px 10px;text-align:right;">TOTAL PENJUALAN HARI INI</td>
                                <td id="totalHarian" style="padding:12px 10px;">Rp <?= number_format($total_harian, 0, ',', '.') ?></td>
                                <td colspan="2"></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align:center;padding:15px;color:#555;">Belum ada transaksi hari ini</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<script>
// FILTER PAYMENT METHOD
function filterPayment(method) {
    let rows = document.querySelectorAll('.order-row');
    let total = 0;

    rows.forEach(row => {
        let pay = row.getAttribute('data-payment');
        if (method === 'all' || pay === method) {
            row.style.display = '';
            let totalCell = row.querySelector('td:nth-child(5)');
            total += parseInt(totalCell.textContent.replace(/[^0-9]/g, ''));
        } else {
            row.style.display = 'none';
        }
    });

    document.getElementById('totalHarian').textContent = 'Rp ' + total.toLocaleString('id-ID');
    document.querySelectorAll('.filter-payment-btn').forEach(btn => {
        btn.classList.remove('active');
        btn.style.background = '#7f8fa6';
    });
    event.currentTarget.style.background = '#353B48';
}
</script>
</body>
</html>
