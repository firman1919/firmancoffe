<?php
session_start();
require_once '../db.php';

// Pastikan kasir login
if (!isset($_SESSION['kasir_logged_in']) || $_SESSION['kasir_logged_in'] !== true) {
    header("Location: kasir_login.php");
    exit;
}

// Proses update stock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['stock'])) {
    $id = intval($_POST['id']);
    $stock = intval($_POST['stock']);
    $stmt = $mysqli->prepare("UPDATE menus SET stock=? WHERE id=?");
    $stmt->bind_param("ii", $stock, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Ambil data menu, urutkan per kategori
$result = $mysqli->query("SELECT * FROM menus ORDER BY category ASC, name ASC");
$menu = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $menu[$row['category']][] = $row;
    }
}

// deteksi halaman aktif
$currentPage = basename($_SERVER['PHP_SELF']);
function activeClass($file, $currentPage)
{
    return $file === $currentPage ? "background:#353B48;border-left:4px solid #fff;" : "";
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kelola Stok | Kasir</title>
</head>

<body style="margin:0;font-family:'Segoe UI',Arial,sans-serif;background:#F5F6FA;">

    <!-- SIDEBAR -->
    <div
        style="width:220px;background:#2F3640;color:#fff;display:flex;flex-direction:column;box-shadow:2px 0 8px rgba(0,0,0,0.15);position:fixed;top:0;left:0;height:100vh;overflow-y:auto;">
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
            style="padding:12px 20px;color:#fff;text-decoration:none;display:block;transition:0.3s;">🔓
            Logout</a>
    </div>

    <!-- CONTENT -->
    <div style="margin-left:220px;padding:30px;min-height:100vh;">
        <div
            style="background:#fff;padding:20px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.08);margin-bottom:20px;">
            <h1 style="margin:0;color:#353B48;font-size:24px;">📦 Kelola Stok Menu</h1>
            <p style="margin:5px 0 0;color:#555;">Update stok menu dengan cepat dan mudah</p>
        </div>

        <?php if ($menu): ?>
            <?php foreach ($menu as $kategori => $items): ?>
                <div
                    style="background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:25px;overflow:hidden;">
                    <h2 style="background:#353B48;color:#fff;padding:12px 20px;margin:0;font-size:18px;">
                        <?= htmlspecialchars($kategori) ?>
                    </h2>
                    <table cellpadding="12" cellspacing="0" style="width:100%;border-collapse:collapse;font-size:14px;">
                        <thead style="background:#eee;color:#333;text-align:left;">
                            <tr>
                                <th style="width:40%;">Nama Menu</th>
                                <th style="width:30%;">Stock</th>
                                <th style="width:30%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $m): ?>
                                <tr style="border-bottom:1px solid #ddd;transition:background 0.3s;"
                                    onmouseover="this.style.background='#f9f9f9'" onmouseout="this.style.background='transparent'">
                                    <td><?= htmlspecialchars($m['name']) ?></td>
                                    <td>
                                        <form method="post" style="margin:0;display:flex;align-items:center;">
                                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                            <input type="number" name="stock" value="<?= $m['stock'] ?>" min="0"
                                                style="width:70px;padding:6px 8px;border:1px solid #ccc;border-radius:5px;">
                                            <button type="submit"
                                                style="padding:6px 10px;margin-left:8px;background:#2F3640;color:#fff;border:none;border-radius:5px;cursor:pointer;transition:0.3s;">💾
                                            </button>
                                        </form>
                                    </td>
                                    <td><span style="color:#353B48;font-weight:bold;">Ubah Stok</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="padding:20px;background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                <p style="margin:0;color:#777;">Belum ada menu.</p>
            </div>
        <?php endif; ?>
    </div>

</body>

</html>
