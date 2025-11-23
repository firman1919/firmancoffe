<?php
// menu_manage.php
session_start();
require_once 'db.php';

// Cek login admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header("Location: admin_login.php");
  exit;
}

// folder upload
$uploadDir = __DIR__ . "/uploads/";
if (!is_dir($uploadDir)) {
  mkdir($uploadDir, 0777, true);
}

$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $category = trim($_POST['category']);
  $name = trim($_POST['name']);
  $description = trim($_POST['description'] ?? '');
  $price_hot = $_POST['price_hot'] === '' ? null : intval($_POST['price_hot']);
  $price_ice = $_POST['price_ice'] === '' ? null : intval($_POST['price_ice']);
  $image = null;

  // Upload gambar jika ada
  if (!empty($_FILES['image']['name'])) {
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid("menu_") . "." . strtolower($ext);
    $target = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
      $image = "uploads/" . $filename;
    }
  }

  if ($action === 'add') {
    $stmt = $mysqli->prepare("INSERT INTO menus (category, name, description, price_hot, price_ice, stock, image, created_at) VALUES (?, ?, ?, ?, ?, 0, ?, NOW())");
    $stmt->bind_param("sssiss", $category, $name, $description, $price_hot, $price_ice, $image);
    $stmt->execute();
    $stmt->close();
    header("Location: menu_manage.php");
    exit;

  } elseif ($action === 'edit' && $id) {
    if ($image) {
      $stmt = $mysqli->prepare("SELECT image FROM menus WHERE id=?");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $res = $stmt->get_result();
      $old = $res->fetch_assoc();
      if ($old && !empty($old['image']) && file_exists(__DIR__ . "/" . $old['image'])) {
        unlink(__DIR__ . "/" . $old['image']);
      }
      $stmt->close();

      $stmt = $mysqli->prepare("UPDATE menus SET category=?, name=?, description=?, price_hot=?, price_ice=?, image=? WHERE id=?");
      $stmt->bind_param("sssissi", $category, $name, $description, $price_hot, $price_ice, $image, $id);
    } else {
      $stmt = $mysqli->prepare("UPDATE menus SET category=?, name=?, description=?, price_hot=?, price_ice=? WHERE id=?");
      $stmt->bind_param("sssisi", $category, $name, $description, $price_hot, $price_ice, $id);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: menu_manage.php");
    exit;
  }
} elseif ($action === 'delete' && $id) {
  $stmt = $mysqli->prepare("SELECT image FROM menus WHERE id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $res = $stmt->get_result();
  $old = $res->fetch_assoc();
  if ($old && !empty($old['image']) && file_exists(__DIR__ . "/" . $old['image'])) {
    unlink(__DIR__ . "/" . $old['image']);
  }
  $stmt->close();

  $stmt = $mysqli->prepare("DELETE FROM menus WHERE id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->close();
  header("Location: menu_manage.php");
  exit;
}

$res = $mysqli->query("SELECT * FROM menus ORDER BY created_at DESC");
$menus = $res->fetch_all(MYSQLI_ASSOC);
$res->close();

$editData = null;
if ($action === 'edit' && $id) {
  $stmt = $mysqli->prepare("SELECT * FROM menus WHERE id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $res = $stmt->get_result();
  $editData = $res->fetch_assoc();
  $stmt->close();
}

if (!function_exists('rupiah')) {
  function rupiah($n) {
    return number_format($n, 0, ',', '.');
  }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Kelola Menu - Admin</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    :root {
      --bg: #f4f5f7;
      --panel: #fff;
      --accent: #2F3640; /* Biru gelap */
      --muted: #6b7280;
    }

    body {
      margin: 0;
      font-family: Inter, "Segoe UI", Arial, sans-serif;
      background: var(--bg);
      color: #222;
    }

    .wrap {
      display: flex;
      min-height: 100vh;
    }

    /* Sidebar kiri tetap (fixed) */
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      width: 220px;
      background: var(--accent);
      color: #fff;
      display: flex;
      flex-direction: column;
      box-shadow: 2px 0 8px rgba(0, 0, 0, 0.2);
      z-index: 1000;
    }

    .sidebar h2 {
      margin: 20px 0;
      text-align: center;
      font-size: 18px;
      font-weight: 600;
    }

    .sidebar a {
      padding: 12px 18px;
      color: #fff;
      text-decoration: none;
      display: block;
      transition: 0.15s;
    }

    .sidebar a:hover {
      background: rgba(255, 255, 255, 0.15);
    }

    .sidebar a.active {
      background: rgba(255, 255, 255, 0.25);
      border-left: 4px solid #fff;
    }

    /* Bagian kanan bergeser agar tidak tertutup sidebar */
    .main {
      flex: 1;
      margin-left: 220px;
      padding: 22px;
      overflow-y: auto;
    }

    .panel {
      background: var(--panel);
      padding: 18px;
      border-radius: 10px;
      box-shadow: 0 4px 14px rgba(9, 30, 66, 0.06);
      margin-bottom: 16px;
    }

    h1 {
      margin: 0 0 12px 0;
      color: var(--accent);
      font-size: 20px;
    }

    label {
      font-weight: 500;
      margin-top: 10px;
      display: block;
    }

    input, textarea {
      padding: 10px;
      margin: 6px 0 16px 0;
      width: 100%;
      border-radius: 8px;
      border: 1px solid #ccc;
    }

    button {
      padding: 12px 20px;
      background: var(--accent);
      color: #fff;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }

    thead th {
      background: #f3f4f6;
      padding: 10px;
      text-align: left;
      color: #111;
      font-weight: 600;
      border-bottom: 1px solid #e6e6e6;
    }

    tbody td {
      padding: 12px;
      border-bottom: 1px solid #f1f1f1;
      vertical-align: top;
    }

    a.cancel-link {
      margin-left: 15px;
      color: var(--accent);
      text-decoration: none;
      font-weight: 600;
    }
  </style>
</head>

<script>
document.addEventListener("DOMContentLoaded", function() {
  const hargaHot = document.getElementById("harga_hot");
  const hargaIce = document.getElementById("harga_ice");

  function formatRupiah(angka) {
    const numberString = angka.replace(/[^,\d]/g, "");
    const split = numberString.split(",");
    const sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    const ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
      const separator = sisa ? "." : "";
      rupiah += separator + ribuan.join(".");
    }

    rupiah = split[1] !== undefined ? rupiah + "," + split[1] : rupiah;
    return rupiah ? "Rp " + rupiah : "";
  }

  function unformatRupiah(rupiah) {
    return rupiah.replace(/[^0-9]/g, "");
  }

  function applyFormat(input) {
    input.addEventListener("input", function() {
      const rawValue = input.value.replace(/[^0-9]/g, "");
      input.value = formatRupiah(rawValue);
    });
  }

  applyFormat(hargaHot);
  applyFormat(hargaIce);

  const form = document.querySelector("form");
  form.addEventListener("submit", function() {
    hargaHot.value = unformatRupiah(hargaHot.value);
    hargaIce.value = unformatRupiah(hargaIce.value);
  });
});
</script>

<body>
  <div class="wrap">
    <div class="sidebar" role="navigation" aria-label="sidebar">
      <h2>Admin</h2>
      <a href="admin_dashboard.php">🏠 Dashboard</a>
      <a href="laporan.php">📊 Laporan</a>
      <a href="menu_manage.php" class="active">📋 Kelola Menu</a>
      <a href="logout.php">🚪 Logout</a>
    </div>

    <div class="main">
      <div class="panel">
        <h1>📋 Kelola Menu</h1>
        <form method="post" enctype="multipart/form-data" action="?action=<?= $editData ? "edit&id=" . $editData['id'] : "add" ?>">
          <label>Kategori</label>
          <input type="text" name="category" value="<?= htmlspecialchars($editData['category'] ?? '') ?>" required>

          <label>Nama Menu</label>
          <input type="text" name="name" value="<?= htmlspecialchars($editData['name'] ?? '') ?>" required>

          <label>Deskripsi</label>
          <textarea name="description" rows="3"><?= htmlspecialchars($editData['description'] ?? '') ?></textarea>

          <label>Harga Hot</label>
          <input type="text" id="harga_hot" name="price_hot" value="<?= htmlspecialchars($editData['price_hot'] ?? '') ?>">

          <label>Harga Ice</label>
          <input type="text" id="harga_ice" name="price_ice" value="<?= htmlspecialchars($editData['price_ice'] ?? '') ?>">

          <label>Upload Gambar</label>
          <input type="file" name="image" accept="image/*">
          <?php if ($editData && $editData['image']): ?>
            <p><img src="<?= htmlspecialchars($editData['image']) ?>" style="max-width:120px;border-radius:8px;border:1px solid #ddd;padding:4px;"></p>
          <?php endif; ?>

          <button type="submit"><?= $editData ? "Update" : "Tambah" ?></button>
          <?php if ($editData): ?><a href="menu_manage.php" class="cancel-link">Batal</a><?php endif; ?>
        </form>
      </div>

      <div class="panel">
        <h1>📑 Daftar Menu</h1>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Kategori</th>
              <th>Nama</th>
              <th>Deskripsi</th>
              <th>Harga Hot</th>
              <th>Harga Ice</th>
              <th>Stok</th>
              <th>Gambar</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($menus) > 0): ?>
              <?php foreach ($menus as $m): ?>
                <tr>
                  <td><?= $m['id'] ?></td>
                  <td><?= htmlspecialchars($m['category']) ?></td>
                  <td><?= htmlspecialchars($m['name']) ?></td>
                  <td><?= htmlspecialchars($m['description'] ?? '') ?></td>
                  <td><?= ($m['price_hot'] && $m['price_hot'] > 0) ? rupiah($m['price_hot']) : "Tidak Ada" ?></td>
                  <td><?= ($m['price_ice'] && $m['price_ice'] > 0) ? rupiah($m['price_ice']) : "Tidak Ada" ?></td>
                  <td><?= $m['stock'] ?></td>
                  <td><?php if ($m['image']): ?><img src="<?= htmlspecialchars($m['image']) ?>" style="max-width:60px;border-radius:8px;"><?php endif; ?></td>
                  <td>
                    <a href="?action=edit&id=<?= $m['id'] ?>" style="color:var(--accent);text-decoration:none;font-weight:500;">Edit</a> |
                    <a href="?action=delete&id=<?= $m['id'] ?>" onclick="return confirm('Hapus menu ini?')" style="color:#d9534f;text-decoration:none;font-weight:500;">Hapus</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="9" style="text-align:center;padding:20px;color:#777;">Belum ada menu</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
