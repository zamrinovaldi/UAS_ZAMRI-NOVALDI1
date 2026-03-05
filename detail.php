<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
require 'koneksi.php';

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$id = mysqli_real_escape_string($koneksi, $_GET['id']);
$query = "SELECT produk.*, kategori.nama_kategori, merk.nama_merk 
          FROM produk 
          JOIN kategori ON produk.id_kategori = kategori.id_kategori 
          JOIN merk ON produk.id_merk = merk.id_merk
          WHERE produk.id_produk = '$id'";
$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) === 0) {
    header("Location: dashboard.php?msg=Produk tidak ditemukan&type=error");
    exit;
}

$row = mysqli_fetch_assoc($result);

// User data for sidebar
$user_id = $_SESSION['user']['id_user'];
$user_query = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$user_id'");
$user_data = mysqli_fetch_assoc($user_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk - <?= htmlspecialchars($row['nama_produk']) ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .detail-card {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 40px;
            align-items: start;
        }
        .product-image-container {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: 20px;
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 400px;
        }
        .product-image-large {
            max-width: 100%;
            max-height: 500px;
            object-fit: contain;
            border-radius: var(--radius-md);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .info-section {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }
        .price-tag {
            font-size: 32px;
            font-weight: 800;
            color: var(--primary);
            margin: 10px 0;
        }
        .spec-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .spec-item {
            background: var(--bg-card);
            padding: 15px;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
        }
        .spec-label {
            font-size: 11px;
            text-transform: uppercase;
            color: var(--text-muted);
            font-weight: 700;
            margin-bottom: 5px;
            display: block;
        }
        .spec-value {
            font-weight: 600;
            font-size: 15px;
        }
    </style>
</head>
<body>
    <div class="win-bloom-bg"></div>
    <div class="app-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <div>
                    <a href="dashboard.php" style="text-decoration: none; color: var(--primary); font-weight: 600; display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                        <span>⬅️</span> Kembali ke Dashboard
                    </a>
                    <h1>Detail Spesifikasi</h1>
                </div>
                <div style="display: flex; gap: 15px;">
                    <button id="theme-toggle" class="theme-toggle" title="Toggle Dark Mode">🌓</button>
                    <a href="edit.php?id=<?= $row['id_produk'] ?>" class="btn-premium btn-primary" style="background: var(--secondary);">Edit Produk</a>
                </div>
            </header>

            <div class="detail-card fade-in">
                <div class="product-image-container">
                    <?php if(!empty($row['foto_produk']) && file_exists("img/".$row['foto_produk'])): ?>
                        <img src="img/<?= htmlspecialchars($row['foto_produk']) ?>" class="product-image-large" alt="Foto Produk">
                    <?php else: ?>
                        <div style="font-size: 100px; opacity: 0.1;">📱</div>
                    <?php endif; ?>
                </div>

                <div class="info-section">
                    <div>
                        <span class="badge badge-success"><?= htmlspecialchars($row['nama_kategori']) ?></span>
                        <h2 style="font-size: 36px; font-weight: 800; margin: 10px 0;"><?= htmlspecialchars($row['nama_produk']) ?></h2>
                        <p style="color: var(--text-muted); font-size: 18px;"><?= htmlspecialchars($row['nama_merk']) ?></p>
                    </div>

                    <div class="price-tag">
                        Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                    </div>

                    <div class="spec-grid">
                        <div class="spec-item">
                            <span class="spec-label">STATUS STOK</span>
                            <?php
                                $stok = (int)$row['stok'];
                                $stok_text = "Tersedia ($stok Unit)";
                                $color = "var(--primary)";
                                if($stok == 0) { $stok_text = "Habis"; $color = "var(--danger)"; }
                                elseif($stok < 5) { $color = "var(--warning)"; }
                            ?>
                            <span class="spec-value" style="color: <?= $color ?>;"><?= $stok_text ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">BRAND / MERK</span>
                            <span class="spec-value"><?= htmlspecialchars($row['nama_merk']) ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">KATEGORI PERANGKAT</span>
                            <span class="spec-value"><?= htmlspecialchars($row['nama_kategori']) ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">ID PRODUK</span>
                            <span class="spec-value">#GADGET-<?= str_pad($row['id_produk'], 4, '0', STR_PAD_LEFT) ?></span>
                        </div>
                    </div>

                    <div class="form-card" style="margin-top: 20px;">
                        <h4 style="margin-bottom: 15px;">Aksi Cepat</h4>
                        <div style="display: flex; gap: 10px;">
                            <button onclick="window.print()" class="btn-premium" style="background: #f3f5f7; color: #323130; flex: 1; justify-content: center;">🖨️ Cetak Label</button>
                            <a href="hapus.php?id=<?= $row['id_produk'] ?>" class="btn-premium btn-danger" style="flex: 1; justify-content: center;" onclick="return confirm('Hapus produk ini?')">🗑️ Hapus</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Dark Mode Logic
        const themeToggle = document.getElementById('theme-toggle');
        const currentTheme = localStorage.getItem('theme') || 'light';
        
        if (currentTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }

        themeToggle.addEventListener('click', () => {
            let theme = document.documentElement.getAttribute('data-theme');
            if (theme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            }
        });
    </script>
</body>
</html>
