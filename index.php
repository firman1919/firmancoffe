<?php
// index.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $table = trim($_POST['table'] ?? '');
    
    if ($name === '') {
        $error = "Mohon isi nama.";
    } elseif (!ctype_digit($table) || intval($table) <= 0) {
        $error = "Nomor meja harus berupa angka positif.";
    } else {
        $_SESSION['customer'] = ['name' => $name, 'table' => $table];
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        header("Location: menu.php");
        exit;
    }
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RK Coffe</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        padding: 20px; /* 🔹 Tambah jarak kiri-kanan dan atas-bawah */
        box-sizing: border-box;
    }

    .card {
        width: 100%;
        max-width: 400px;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        text-align: center;
        animation: fadeIn 0.8s ease-in-out;
    }

    /* 🟤 Logo tengah + animasi */
    .card img.logo {
        display: block;
        margin: 0 auto 1rem auto;
        width: 80px;
        height: auto;
        animation: zoomIn 0.8s ease-in-out;
    }

    .card h4 {
        font-weight: 600;
        color: #4e342e;
    }

    .card p {
        color: #555;
        font-size: 0.95rem;
    }

    input[type="text"],
    input[type="number"] {
        border-radius: 8px;
        padding: 10px;
    }

    button {
        border-radius: 10px;
        width: 100%;
        background-color: #6f4e37;
        border: none;
        padding: 10px;
        color: white;
        font-weight: 600;
        transition: 0.3s;
    }

    button:hover {
        background-color: #54392c;
    }

    a.cart-link {
        display: inline-block;
        margin-top: 12px;
        color: #6f4e37;
        font-weight: 600;
        text-decoration: none;
    }

    a.cart-link:hover {
        text-decoration: underline;
    }

    /* Animasi lembut */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes zoomIn {
        from { transform: scale(0.8); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }

    /* 🔹 Responsif di HP: beri jarak samping lebih luas */
    @media (max-width: 576px) {
        body {
            padding: 25px; /* Tambah jarak di layar kecil */
        }

        .card {
            padding: 1.5rem;
            border-radius: 14px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
        }
    }
</style>
</head>

<body>
    <div class="card">
        <!-- Logo -->
        <img src="foto/logo.png" alt="logo Gamel" class="logo">

        <h4 class="mb-3">Selamat Datang di Kedai RK Coffe</h4>
        <p>Masukkan nama dan nomor meja untuk memulai pemesanan.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" class="mt-3">
            <div class="mb-3">
                <input type="text" name="name" class="form-control" placeholder="Nama Anda" required
                    value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
            </div>

            <div class="mb-3">
                <input type="number" name="table" class="form-control" placeholder="Nomor Meja" min="1" max='15' required
                    value="<?= isset($_POST['table']) ? htmlspecialchars($_POST['table']) : '' ?>">
            </div>

            <button type="submit">Mulai Pesan</button>
        </form>

        <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
            <a href="cart.php" class="cart-link">Lihat Keranjang (<?= count($_SESSION['cart']) ?> item)</a>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>