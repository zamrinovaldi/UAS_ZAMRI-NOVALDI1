<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
require 'koneksi.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Initialize Cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$user_id = $_SESSION['user']['id_user'];
$user_query = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$user_id'");
$user_data = mysqli_fetch_assoc($user_query);

// Logic Check: Loyalty (10+ purchases)
$loyalty_query = mysqli_query($koneksi, "SELECT COUNT(*) as total_transaksi FROM penjualan WHERE id_user = '$user_id'");
$loyalty_data = mysqli_fetch_assoc($loyalty_query);
$is_loyal = ($loyalty_data['total_transaksi'] >= 10);

$error = "";
$success = "";

// Handle Add to Cart
if (isset($_POST['add_to_cart'])) {
    $id_produk = $_POST['id_produk'];
    $jumlah = (int)$_POST['jumlah'];

    // Check stock
    $check_stock = mysqli_query($koneksi, "SELECT * FROM produk WHERE id_produk = '$id_produk'");
    $p = mysqli_fetch_assoc($check_stock);

    if ($p['stok'] >= $jumlah) {
        if (isset($_SESSION['cart'][$id_produk])) {
            $_SESSION['cart'][$id_produk] += $jumlah;
        } else {
            $_SESSION['cart'][$id_produk] = $jumlah;
        }
        $success = "Produk ditambahkan ke keranjang!";
    } else {
        $error = "Stok tidak mencukupi!";
    }
}

// Handle Remove from Cart
if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    header("Location: pos.php");
    exit;
}

// Handle Checkout
if (isset($_POST['checkout']) && !empty($_SESSION['cart'])) {
    mysqli_begin_transaction($koneksi);
    try {
        $total_harga = 0;
        $loyalty_discount_rate = $is_loyal ? 0.05 : 0; // 5% extra for loyal users
        
        // Calculate Total with Wholesale & Loyalty Logic
        foreach($_SESSION['cart'] as $id => $qty) {
            $p_query = mysqli_query($koneksi, "SELECT harga FROM produk WHERE id_produk = '$id'");
            $p = mysqli_fetch_assoc($p_query);
            
            $unit_price = (float)$p['harga'];
            // Wholesale Logic: buy 5+ get 10% off unit price
            if ($qty >= 5) {
                $unit_price *= 0.9;
            }
            
            $total_harga += ($unit_price * $qty);
        }

        // Apply global loyalty discount if applicable
        $total_harga = $total_harga * (1 - $loyalty_discount_rate);

        // Insert into Penjualan
        $insert_jual = mysqli_query($koneksi, "INSERT INTO penjualan (id_user, total_harga) VALUES ('$user_id', '$total_harga')");
        $id_penjualan = mysqli_insert_id($koneksi);

        // Insert Details and Update Stock
        foreach($_SESSION['cart'] as $id => $qty) {
            $p_query = mysqli_query($koneksi, "SELECT harga, nama_produk FROM produk WHERE id_produk = '$id'");
            $p = mysqli_fetch_assoc($p_query);
            $subtotal = $p['harga'] * $qty;

            mysqli_query($koneksi, "INSERT INTO detail_penjualan (id_penjualan, id_produk, jumlah, subtotal) VALUES ('$id_penjualan', '$id', '$qty', '$subtotal')");
            mysqli_query($koneksi, "UPDATE produk SET stok = stok - $qty WHERE id_produk = '$id'");
        }

        log_activity("Transaksi Baru", "Berhasil melakukan transaksi #$id_penjualan total Rp " . number_format($total_harga, 0, ',', '.'));
        mysqli_commit($koneksi);
        $_SESSION['cart'] = [];
        
        $msg = "Transaksi Berhasil!";
        if ($is_loyal) {
            $msg .= " Bonus Loyalitas: Voucer & Hadiah Alat Tulis Diperoleh! 🎁";
        }
        header("Location: riwayat_penjualan.php?msg=" . urlencode($msg) . "&type=success");
        exit;
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        $error = "Terjadi kesalahan transaksi: " . $e->getMessage();
    } catch (Error $e) {
        mysqli_rollback($koneksi);
        $error = "Terjadi kesalahan sistem: " . $e->getMessage();
    }
}

// Fetch all products for selection
$products = mysqli_query($koneksi, "SELECT produk.*, merk.nama_merk FROM produk JOIN merk ON produk.id_merk = merk.id_merk WHERE stok > 0 ORDER BY nama_produk ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System - E-Gadget PRO</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <style>
        .pos-container { display: grid; grid-template-columns: 1fr 400px; gap: 30px; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
        .cart-card { height: calc(100vh - 200px); display: flex; flex-direction: column; }
        .cart-items { flex: 1; overflow-y: auto; margin: 20px 0; }
        .cart-item { display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding: 10px 0; font-size: 13px; }
        .product-item-card { text-align: center; }
        .product-item-card img { width: 100%; height: 120px; object-fit: cover; border-radius: 10px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="win-bloom-bg"></div>
    <div class="app-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <div>
                    <h1>Mesin Kasir</h1>
                    <p style="color: var(--text-muted); font-size: 14px;">Terminal transaksi cepat untuk pelanggan.</p>
                </div>
                <div style="display: flex; gap: 15px;">
                    <button id="theme-toggle" class="theme-toggle" title="Toggle Dark Mode">🌓</button>
                    <a href="dashboard.php" class="btn-premium" style="background: var(--border-color); color: var(--text-main);">Dashboard</a>
                </div>
            </header>

            <div class="pos-container fade-in">
                <!-- Product Selection -->
                <div class="glass" style="padding: 25px; border-radius: 20px;">
                    <h3 style="margin-bottom: 25px;">Pilih Produk</h3>
                    <div class="product-grid">
                        <?php while($p = mysqli_fetch_assoc($products)): ?>
                        <div class="form-card product-item-card glass" style="padding: 16px; border: 1px solid var(--border-color); border-radius: var(--radius-md);">
                            <?php if(!empty($p['foto_produk']) && file_exists("img/".$p['foto_produk'])): ?>
                                <img src="img/<?= htmlspecialchars($p['foto_produk']) ?>" alt="img" style="border-radius: var(--radius-md);">
                            <?php else: ?>
                                <div style="height: 120px; display:flex; align-items:center; justify-content:center; background:var(--bg-main); border-radius:var(--radius-md); margin-bottom:12px; font-size: 32px;">📱</div>
                            <?php endif; ?>
                            <p style="font-weight: 700; font-size: 14px; margin-bottom: 5px;"><?= htmlspecialchars($p['nama_produk']) ?></p>
                            <p style="color: var(--primary); font-weight: 800; font-size: 13px; margin-bottom: 10px;">Rp <?= number_format($p['harga'], 0, ',', '.') ?></p>
                            <form method="POST">
                                <input type="hidden" name="id_produk" value="<?= $p['id_produk'] ?>">
                                <div style="display: flex; flex-direction: column; gap: 12px; align-items: center;">
                                    <div class="qty-control" style="background: var(--bg-main); border: 1px solid var(--border-color);">
                                        <button type="button" class="qty-btn minus" style="background: transparent; color: var(--text-main);">-</button>
                                        <input type="number" name="jumlah" value="1" min="1" max="<?= $p['stok'] ?>" class="input-premium qty-input" style="color: var(--text-main);" readonly>
                                        <button type="button" class="qty-btn plus" style="background: transparent; color: var(--text-main);">+</button>
                                    </div>
                                    <button type="submit" name="add_to_cart" class="btn-premium btn-primary" style="width: 100%; justify-content: center; height: 40px;">
                                        Tambah
                                    </button>
                                </div>
                                <p style="font-size: 10px; color: var(--text-muted); margin-top: 5px;">Sisa Stok: <?= $p['stok'] ?></p>
                                <?php if($p['stok'] >= 5): ?>
                                    <p style="font-size: 10px; color: var(--primary); font-weight: 800;">🔥 Grosir: Beli 5+ Diskon 10%!</p>
                                <?php endif; ?>
                            </form>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Shopping Cart -->
                <div class="glass cart-card" style="padding: 25px; border-radius: 20px;">
                    <h3>Keranjang Belanja</h3>
                    <div class="cart-items">
                        <?php 
                        $grand_total = 0;
                        $loyalty_discount_rate = $is_loyal ? 0.05 : 0;
                        
                        foreach($_SESSION['cart'] as $id => $qty): 
                            $p_query = mysqli_query($koneksi, "SELECT * FROM produk WHERE id_produk = '$id'");
                            $p = mysqli_fetch_assoc($p_query);
                            
                            $unit_price = (float)$p['harga'];
                            $is_whole = false;
                            if ($qty >= 5) {
                                $unit_price *= 0.9;
                                $is_whole = true;
                            }
                            
                            $subtotal = $unit_price * $qty;
                            $grand_total += $subtotal;
                        ?>
                        <div class="cart-item">
                            <div style="flex: 1;">
                                <p style="font-weight: 700;"><?= htmlspecialchars($p['nama_produk']) ?></p>
                                <p style="font-size: 11px; color: var(--text-muted);">
                                    <?= $qty ?> x Rp <?= number_format($p['harga'], 0, ',', '.') ?>
                                    <?php if($is_whole): ?>
                                        <span style="color: var(--primary); font-weight: 800; font-size: 10px;">(Grosir -10%)</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div style="text-align: right;">
                                <p style="font-weight: 800; color: var(--primary);">Rp <?= number_format($subtotal, 0, ',', '.') ?></p>
                                <a href="pos.php?remove=<?= $id ?>" style="color: var(--danger); font-size: 11px; text-decoration: none;">Hapus</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php 
                        // Apply Loyalty Discount to Grand Total
                        $loyalty_amt = $grand_total * $loyalty_discount_rate;
                        $grand_total -= $loyalty_amt;
                        ?>

                        <?php if($is_loyal && $grand_total > 0): ?>
                            <div style="padding: 10px; background: rgba(76, 201, 240, 0.1); border-radius: 8px; margin-top: 15px; border-left: 3px solid #4cc9f0;">
                                <p style="font-size: 11px; color: #4cc9f0; margin: 0; font-weight: 700;">✨ Diskon Loyalitas (5%) Diterapkan!</p>
                            </div>
                        <?php endif; ?>
                        <?php if(empty($_SESSION['cart'])): ?>
                            <p style="text-align: center; color: var(--text-muted); margin-top: 50px;">Keranjang kosong.</p>
                        <?php endif; ?>
                    </div>

                    <div style="border-top: 1px solid var(--border-color); padding-top: 24px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 24px;">
                            <span style="font-weight: 600; font-size: 16px; color: var(--text-muted);">Total Tagihan</span>
                            <span style="font-weight: 800; font-size: 24px; color: var(--text-main);">Rp <?= number_format($grand_total, 0, ',', '.') ?></span>
                        </div>
                        <form method="POST">
                            <button type="submit" name="checkout" class="btn-premium btn-primary" style="width: 100%; justify-content: center; height: 48px; font-size: 15px; letter-spacing: 0.5px;">BAYAR SEKARANG</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    <script>
    // Qty Control JS
    document.querySelectorAll('.qty-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const container = e.target.closest('.qty-control');
            const input = container.querySelector('.qty-input');
            let val = parseInt(input.value);
            if (btn.classList.contains('plus')) {
                if (val < parseInt(input.max)) input.value = val + 1;
            } else {
                if (val > 1) input.value = val - 1;
            }
        });
    });

    // Theme Toggle Logic
    const tt = document.getElementById('theme-toggle');
    if (tt) {
        const currentTheme = localStorage.getItem('theme') || 'light';
        if (currentTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
        tt.addEventListener('click', () => {
            let theme = document.documentElement.getAttribute('data-theme');
            if (theme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            }
        });
    }
    </script>
</body>
</html>
