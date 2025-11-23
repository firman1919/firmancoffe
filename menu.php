<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['customer'])) {
    header("Location: index.php");
    exit;
}

$customer = $_SESSION['customer'];

$res = $mysqli->query("SELECT * FROM menus");
$menus = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

$kategori_urutan = [
    'Espresso Base',
    'Manual Brew',
    'Milk Base',
    'Other',
    'Snack',
    'Foods'
];

$grouped = [];
foreach ($kategori_urutan as $k) {
    foreach ($menus as $m) {
        if ($m['category'] === $k) {
            $grouped[$k][] = $m;
        }
    }
}

$cart = $_SESSION['cart'] ?? [];

if (!function_exists('rupiah')) {
    function rupiah($n) {
        return "Rp" . number_format($n, 0, ',', '.');
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Menu - RK Coffe</title>
    <style>
        body {
            margin: 0;
            font-family: "Poppins", Arial, Helvetica, sans-serif;
            background: #fff9f5;
            padding-bottom: 90px;
        }

        /* BAGIAN ATAS */
        .topbar {
            max-width: 1100px;
            margin: 15px auto 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 15px;
        }

        .topbar .info {
            font-weight: bold;
            color: #5a3825;
            font-size: 15px;
        }

        .topbar a {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            background: #aaa;
            color: #fff;
        }

        /* TOMBOL KATEGORI STICKY */
        .filter-btns-wrapper {
            position: sticky;
            top: 0;
            z-index: 999;
            background: #fff9f5;
            border-bottom: 1px solid #ddd;
        }

        .filter-btns {
            display: flex;
            justify-content: center;
            gap: 25px;
            padding: 8px 0;
        }

        .filter-btns button {
            background: none;
            border: none;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            color: #5a3825;
            padding: 8px 10px;
            border-bottom: 2px solid transparent;
        }

        .filter-btns button:hover,
        .filter-btns button.active {
            border-bottom: 2px solid #5a3825;
        }

        h2 {
            color: #5a3825;
            margin: 20px 0 12px;
            text-align: center;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 15px;
            padding: 0 15px;
        }

        .menu-card {
            background: #fff;
            padding: 12px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            text-align: center;
            transition: transform 0.3s;
        }

        .menu-card:hover {
            transform: translateY(-3px);
        }

        .menu-card img, .menu-card .no-image {
            width: 100%;
            height: 130px;
            border-radius: 10px;
            margin-bottom: 8px;
            object-fit: cover;
        }

        .no-image {
            background: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #aaa;
            font-size: 13px;
        }

        .menu-card h3 {
            margin: 0 0 6px;
            color: #5a3825;
            font-size: 16px;
        }

        .menu-desc {
            font-size: 13px;
            color: #555;
            margin-bottom: 6px;
        }

        .stok {
            font-size: 12px;
            color: #888;
            margin-bottom: 6px;
        }

        .variant-block {
            margin-bottom: 8px;
        }

        .variant-block label {
            font-size: 13px;
            color: #333;
            font-weight: 500;
        }

        .qty-control {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 6px 0;
            justify-content: center;
        }

        .qty-btn {
            background: #6b3e2e;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 4px 10px;
            cursor: pointer;
            font-size: 15px;
            font-weight: bold;
        }

        /* CATATAN */
        .note {
            width: 90%;
            margin: 8px auto 0 auto;
            display: block;
            padding: 8px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 13px;
            text-align: center;
        }

        /* TOMBOL KERANJANG */
        .submit-btn {
            position: fixed;
            bottom: 15px;
            right: 15px;
            background: #5a3825;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
            padding: 12px 18px;
            text-align: center;
            z-index: 20;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .submit-btn:hover {
            background: #7a4b32;
        }

        @media (min-width: 768px) {
            .menu-grid {
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            }
        }
    </style>
</head>
<body>
    <!-- BAGIAN ATAS -->
    <div class="topbar">
        <div class="info">
         <?= htmlspecialchars($customer['name']) ?> (Meja <?= htmlspecialchars($customer['table']) ?>)
        </div>
        <a href="index.php">Kembali</a>
    </div>

    <!-- TOMBOL KATEGORI STICKY -->
    <div class="filter-btns-wrapper">
        <div class="filter-btns" id="filterBar">
            <button type="button" class="active" onclick="filterKategori('minuman', this)">Minuman</button>
            <button type="button" onclick="filterKategori('snack', this)">Snack</button>
            <button type="button" onclick="filterKategori('foods', this)">Makanan</button>
        </div>
    </div>

    <!-- DAFTAR MENU -->
    <form method="post" action="cart.php">
        <?php foreach ($grouped as $category => $items): ?>
            <h2 id="<?= strtolower(str_replace(' ', '_', $category)) ?>"><?= htmlspecialchars($category) ?></h2>
            <div class="menu-grid">
                <?php foreach ($items as $m): 
                    $menuId = $m['id'];
                    $qtyHot = $cart[$menuId.'_hot']['qty'] ?? 0;
                    $qtyIce = $cart[$menuId.'_ice']['qty'] ?? 0;
                    $note   = '';
                    foreach ($cart as $ck => $cv) {
                        if ($cv['menu_id'] == $menuId && !empty($cv['note'])) {
                            $note = $cv['note'];
                            break;
                        }
                    }
                ?>
                    <div class="menu-card">
                        <?php if (!empty($m['image'])): ?>
                            <img src="/firmancoffe/<?= htmlspecialchars($m['image']) ?>" alt="<?= htmlspecialchars($m['name']) ?>">
                        <?php else: ?>
                            <div class="no-image">Tidak ada gambar</div>
                        <?php endif; ?>

                        <h3><?= htmlspecialchars($m['name']) ?></h3>
                        <div class="menu-desc"><?= htmlspecialchars($m['description'] ?? 'Menu spesial kami.') ?></div>
                        <div class="stok" data-id="<?= $m['id'] ?>">Stok: <?= intval($m['stock']) ?></div>


                        <?php if ($m['price_hot']): ?>
                            <div class="variant-block">
                                <label>Hot (<?= rupiah($m['price_hot']) ?>)</label>
                                <div class="qty-control">
                                    <button type="button" class="qty-btn" onclick="decreaseQty('<?= $m['id'] ?>_hot')">-</button>
                                    <span id="display_<?= $m['id'] ?>_hot"><?= $qtyHot ?></span>
                                    <button type="button" class="qty-btn" onclick="increaseQty('<?= $m['id'] ?>_hot', <?= $m['stock'] ?>)">+</button>
                                </div>
                                <input type="hidden" id="input_<?= $m['id'] ?>_hot" name="items[<?= $m['id'] ?>_hot][qty]" value="<?= $qtyHot ?>">
                                <input type="hidden" name="items[<?= $m['id'] ?>_hot][menu_id]" value="<?= $m['id'] ?>">
                                <input type="hidden" name="items[<?= $m['id'] ?>_hot][name]" value="<?= htmlspecialchars($m['name']) ?>">
                                <input type="hidden" name="items[<?= $m['id'] ?>_hot][price]" value="<?= $m['price_hot'] ?>">
                                <input type="hidden" name="items[<?= $m['id'] ?>_hot][variant]" value="hot">
                            </div>
                        <?php endif; ?>

                        <?php if ($m['price_ice']): ?>
                            <div class="variant-block">
                                <label>Ice (<?= rupiah($m['price_ice']) ?>)</label>
                                <div class="qty-control">
                                    <button type="button" class="qty-btn" onclick="decreaseQty('<?= $m['id'] ?>_ice')">-</button>
                                    <span id="display_<?= $m['id'] ?>_ice"><?= $qtyIce ?></span>
                                    <button type="button" class="qty-btn" onclick="increaseQty('<?= $m['id'] ?>_ice', <?= $m['stock'] ?>)">+</button>
                                </div>
                                <input type="hidden" id="input_<?= $m['id'] ?>_ice" name="items[<?= $m['id'] ?>_ice][qty]" value="<?= $qtyIce ?>">
                                <input type="hidden" name="items[<?= $m['id'] ?>_ice][menu_id]" value="<?= $m['id'] ?>">
                                <input type="hidden" name="items[<?= $m['id'] ?>_ice][name]" value="<?= htmlspecialchars($m['name']) ?>">
                                <input type="hidden" name="items[<?= $m['id'] ?>_ice][price]" value="<?= $m['price_ice'] ?>">
                                <input type="hidden" name="items[<?= $m['id'] ?>_ice][variant]" value="ice">
                            </div>
                        <?php endif; ?>

                        <input type="text" name="notes[<?= $m['id'] ?>]" class="note" placeholder="Catatan" value="<?= htmlspecialchars($note) ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <button type="submit" class="submit-btn">+ Keranjang</button>
    </form>

   <script>
function increaseQty(id, maxStock) {
    let input = document.getElementById("input_" + id);
    let display = document.getElementById("display_" + id);
    let parts = id.split("_");
    let menuId = parts[0];
    let totalQty = 0;
    let hotInput = document.getElementById("input_" + menuId + "_hot");
    let iceInput = document.getElementById("input_" + menuId + "_ice");
    if (hotInput) totalQty += parseInt(hotInput.value) || 0;
    if (iceInput) totalQty += parseInt(iceInput.value) || 0;

    if (totalQty < maxStock) {
        let value = parseInt(input.value) || 0;
        input.value = value + 1;
        display.textContent = value + 1;

        // Kurangi stok yang ditampilkan
        updateStockDisplay(menuId, -1);
    } else {
        alert("Stok menu ini tinggal " + maxStock + " (total hot + ice).");
    }
}

function decreaseQty(id) {
    let input = document.getElementById("input_" + id);
    let display = document.getElementById("display_" + id);
    let value = parseInt(input.value) || 0;
    let parts = id.split("_");
    let menuId = parts[0];

    if (value > 0) {
        input.value = value - 1;
        display.textContent = value - 1;

        // Tambahkan stok kembali ke tampilan
        updateStockDisplay(menuId, +1);
    }
}

// Fungsi untuk update tampilan stok di elemen
function updateStockDisplay(menuId, change) {
    let stockEl = document.querySelector(`.stok[data-id="${menuId}"]`);
    if (stockEl) {
        let stokText = stockEl.textContent.replace(/[^0-9]/g, "");
        let stokNow = parseInt(stokText) || 0;
        stokNow += change;
        if (stokNow < 0) stokNow = 0;
        stockEl.textContent = "Stok: " + stokNow;
    }
}

function filterKategori(type, btn) {
    document.querySelectorAll('.filter-btns button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    let mapping = {
        "minuman": ["Espresso Base", "Manual Brew", "Milk Base"],
        "snack": ["Snack"],
        "foods": ["Foods"]
    };
    if (mapping[type]) {
        let first = mapping[type][0].toLowerCase().replace(/\s+/g, "_");
        let section = document.getElementById(first);
        if (section) section.scrollIntoView({ behavior: "smooth" });
    }
}
</script>
