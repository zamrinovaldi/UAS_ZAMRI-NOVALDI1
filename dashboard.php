<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
require 'koneksi.php';

// Fetch current user data for profile display
$user_id = $_SESSION['user']['id_user'];
$user_query = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$user_id'");
$user_data = mysqli_fetch_assoc($user_query);

// Handle Search and Filter
$where = "WHERE 1=1";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($koneksi, $_GET['search']);
    $where .= " AND produk.nama_produk LIKE '%$search%'";
}
if (isset($_GET['kategori']) && !empty($_GET['kategori'])) {
    $kat_id = mysqli_real_escape_string($koneksi, $_GET['kategori']);
    $where .= " AND produk.id_kategori = '$kat_id'";
}
if (isset($_GET['merk']) && !empty($_GET['merk'])) {
    $merk_id = mysqli_real_escape_string($koneksi, $_GET['merk']);
    $where .= " AND produk.id_merk = '$merk_id'";
}

// Handle Sorting
$sort_by = "produk.id_produk DESC";
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'price_asc': $sort_by = "produk.harga ASC"; break;
        case 'price_desc': $sort_by = "produk.harga DESC"; break;
        case 'stock_asc': $sort_by = "produk.stok ASC"; break;
        case 'stock_desc': $sort_by = "produk.stok DESC"; break;
        case 'name_asc': $sort_by = "produk.nama_produk ASC"; break;
    }
}

// Handle Quick Filter (Low Stock)
if (isset($_GET['filter']) && $_GET['filter'] == 'low_stock') {
    $where .= " AND produk.stok < 5 AND produk.stok > 0";
}

// Fetch products with filters and sorting
$query = "SELECT produk.*, kategori.nama_kategori, merk.nama_merk 
          FROM produk 
          JOIN kategori ON produk.id_kategori = kategori.id_kategori 
          JOIN merk ON produk.id_merk = merk.id_merk
          $where
          ORDER BY $sort_by";
$result = mysqli_query($koneksi, $query);

// Fetch Stats
$total_produk = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_produk FROM produk"));
$total_kategori = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_kategori FROM kategori"));
$stok_habis = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_produk FROM produk WHERE stok = 0"));
$total_stok_query = mysqli_query($koneksi, "SELECT SUM(stok) as total FROM produk");
$total_stok = mysqli_fetch_assoc($total_stok_query)['total'] ?? 0;

// Fetch Total Sales for Admin (Boss)
$total_barang_terjual = 0;
$total_pendapatan = 0;
if (isset($user_data['role']) && $user_data['role'] === 'admin') {
    $sales_query = mysqli_query($koneksi, "SELECT SUM(jumlah) AS total_barang, SUM(subtotal) AS total_pendapatan FROM detail_penjualan");
    $sales_data = mysqli_fetch_assoc($sales_query);
    $total_barang_terjual = $sales_data['total_barang'] ?? 0;
    $total_pendapatan = $sales_data['total_pendapatan'] ?? 0;
}

// Fetch Chart Data (Product count per Category)
$chart_query = "SELECT kategori.nama_kategori, COUNT(produk.id_produk) as total 
                FROM kategori 
                LEFT JOIN produk ON kategori.id_kategori = produk.id_kategori 
                GROUP BY kategori.id_kategori";
$chart_res = mysqli_query($koneksi, $chart_query);
$chart_labels = [];
$chart_data = [];
while($c = mysqli_fetch_assoc($chart_res)) {
    $chart_labels[] = $c['nama_kategori'];
    $chart_data[] = (int)$c['total'];
}

// Fetch Categories and Brands for filter dropdowns
$kategori_res = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
$merk_res = mysqli_query($koneksi, "SELECT * FROM merk ORDER BY nama_merk ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - E-Gadget System</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* ---- Search Bar Clear Visibility ---- */
        .search-filter-card {
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
            padding: 24px;
            margin-bottom: 32px;
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-soft);
        }
        .search-input-clear {
            width: 100%;
            padding: 12px 16px 12px 48px;
            font-size: 15px;
            font-weight: 500;
            color: var(--text-main);
            background: var(--bg-main);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            outline: none;
            transition: all 0.2s;
        }
        .search-input-clear::placeholder { color: var(--text-muted); }
        .search-input-clear:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .search-wrap {
            flex: 2; min-width: 260px; position: relative;
        }
        .search-wrap .search-icon {
            position: absolute; left: 16px; top: 50%;
            transform: translateY(-50%);
            font-size: 16px; color: var(--text-muted); pointer-events: none;
        }
        /* ---- Product Card Grid ---- */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }
        .product-card {
            background: white;
            border-radius: 20px;
            border: 1px solid rgba(0,0,0,0.07);
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(0,120,212,0.15);
            border-color: rgba(0,120,212,0.2);
        }
        [data-theme="dark"] .product-card {
            background: rgba(255,255,255,0.06);
            border-color: rgba(255,255,255,0.1);
        }
        .product-card-img {
            width: 100%;
            height: 220px;
            object-fit: contain;
            background: #f8fafc;
            padding: 10px;
            display: block;
            transition: transform 0.4s ease;
        }
        .product-card:hover .product-card-img {
            transform: scale(1.05);
        }
        .product-card-img-placeholder {
            width: 100%;
            height: 220px;
            background: linear-gradient(135deg, #e8f4fd 0%, #dbeafe 50%, #ede9fe 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 72px;
        }
        .product-card-body {
            padding: 18px 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .product-card-name {
            font-size: 15px;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.3px;
            line-height: 1.3;
        }
        .product-card-meta {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
        }
        .product-card-price {
            font-size: 18px;
            font-weight: 900;
            color: var(--primary);
            margin-top: 4px;
        }
        .product-card-actions {
            display: flex;
            gap: 8px;
            padding: 15px 20px;
            border-top: 1px solid rgba(0,0,0,0.06);
            background: rgba(0,0,0,0.01);
        }
        .product-card-actions a {
            flex: 1;
            padding: 9px 0;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
            transition: all 0.2s;
        }
        .card-btn-detail { background: rgba(0,120,212,0.1); color: var(--primary); }
        .card-btn-detail:hover { background: var(--primary); color: white; }
        .card-btn-edit { background: rgba(16,124,16,0.1); color: var(--secondary); }
        .card-btn-edit:hover { background: var(--secondary); color: white; }
        .card-btn-del { background: rgba(209,52,56,0.1); color: var(--danger); }
        .card-btn-del:hover { background: var(--danger); color: white; }
        canvas { max-height: 250px; }
        .view-toggle { display: flex; gap: 8px; }
        .view-btn {
            padding: 8px 14px;
            border: 1px solid var(--border-color);
            background: transparent;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.2s;
        }
        .view-btn.active { background: var(--primary); color: white; border-color: var(--primary); }
    </style>
</head>
<body>
    <div id="toast-container"></div>

    <div class="app-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <div>
                    <h1>Inventory Overview</h1>
                    <p style="color: var(--text-muted); font-size: 14px;">Selamat datang kembali ke sistem kontrol pusat.</p>
                </div>
                <div style="display: flex; align-items: center; gap: 20px;">
                    <a href="profil.php" style="display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit; padding: 5px 15px; border-radius: 30px; background: rgba(255,255,255,0.5); border: 1px solid rgba(0,0,0,0.05); transition: all 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.8)'; this.style.transform='translateY(-2px)'" onmouseout="this.style.background='rgba(255,255,255,0.5)'; this.style.transform='translateY(0)'">
                        <div class="avatar" style="width: 35px; height: 35px; margin: 0; border: none; box-shadow: 0 2px 8px rgba(0,120,212,0.2);">
                            <?php if(!empty($user_data['profil_foto'])): ?>
                                <img src="<?= htmlspecialchars($user_data['profil_foto']) ?>" alt="Profile">
                            <?php else: ?>
                                <span style="color: white; font-size: 14px;"><?= strtoupper(substr($user_data['username'], 0, 1)) ?></span>
                            <?php endif; ?>
                        </div>
                        <div style="text-align: left;">
                            <p style="font-size: 13px; font-weight: 700; margin: 0;"><?= htmlspecialchars($user_data['username']) ?></p>
                            <p style="font-size: 10px; color: var(--text-muted); margin: 0;">Online</p>
                        </div>
                    </a>
                    <button id="theme-toggle" class="theme-toggle" title="Toggle Dark Mode" style="margin-right: 10px;">🌓</button>
                    <a href="export.php" class="btn-premium" style="background: #28a745; color: white; margin-right: 10px;">📊 Export CSV</a>
                    <button id="btn-register-bio" class="btn-premium btn-primary" style="background: var(--secondary); margin: 0;">
                        <span>☝️</span> Biometrik
                    </button>
                    <a href="tambah.php" class="btn-premium btn-primary" style="margin: 0;"><span>+</span> Baru</a>
                </div>
            </header>

            <section class="stats-grid">
                <div class="stat-card glass fade-in" style="animation-delay: 0.1s;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <span class="stat-label">Total Produk</span>
                        <span style="font-size: 20px; background: rgba(37,99,235,0.1); padding: 8px; border-radius: 8px; color: var(--primary);">📦</span>
                    </div>
                    <span class="stat-value text-gray-900"><?= $total_produk ?></span>
                </div>
                <div class="stat-card glass fade-in" style="animation-delay: 0.2s;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <span class="stat-label">Kategori</span>
                        <span style="font-size: 20px; background: rgba(37,99,235,0.1); padding: 8px; border-radius: 8px; color: var(--primary);">🗂️</span>
                    </div>
                    <span class="stat-value text-gray-900"><?= $total_kategori ?></span>
                </div>
                <div class="stat-card glass fade-in" style="animation-delay: 0.3s;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <span class="stat-label">Stok Kosong</span>
                        <span style="font-size: 20px; background: rgba(239,68,68,0.1); padding: 8px; border-radius: 8px; color: var(--danger);">⚠️</span>
                    </div>
                    <span class="stat-value" style="color: var(--danger);"><?= $stok_habis ?></span>
                </div>
                <div class="stat-card glass fade-in" style="animation-delay: 0.4s;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <span class="stat-label">Total Unit Stok</span>
                        <span style="font-size: 20px; background: rgba(245,158,11,0.1); padding: 8px; border-radius: 8px; color: var(--warning);">⚡</span>
                    </div>
                    <span class="stat-value" style="color: var(--warning);"><?= $total_stok ?></span>
                </div>
            </section>

            <?php if ($user_data['role'] === 'admin'): ?>
            <section class="stats-grid" style="margin-top: 24px;">
                <div class="stat-card glass fade-in" style="animation-delay: 0.5s; background: linear-gradient(135deg, rgba(37,99,235,0.05) 0%, rgba(37,99,235,0.15) 100%); border: 1px solid rgba(37,99,235,0.2);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <span class="stat-label">Total Barang Terjual</span>
                        <span style="font-size: 20px; background: rgba(37,99,235,0.2); padding: 8px; border-radius: 8px; color: var(--primary);">📈</span>
                    </div>
                    <span class="stat-value" style="color: var(--primary);"><?= $total_barang_terjual ?> Unit</span>
                </div>
                <div class="stat-card glass fade-in" style="animation-delay: 0.6s; background: linear-gradient(135deg, rgba(16,185,129,0.05) 0%, rgba(16,185,129,0.15) 100%); border: 1px solid rgba(16,185,129,0.2);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <span class="stat-label">Total Pendapatan</span>
                        <span style="font-size: 20px; background: rgba(16,185,129,0.2); padding: 8px; border-radius: 8px; color: var(--success);">💰</span>
                    </div>
                    <span class="stat-value" style="color: var(--success);">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></span>
                </div>
            </section>
            <?php endif; ?>

            <div class="chart-card glass fade-in" style="animation-delay: 0.5s;">
                <h3 style="margin-bottom: 20px; font-size: 16px;">Distribusi Gadget Berdasarkan Kategori</h3>
                <canvas id="categoryChart"></canvas>
                <p style="margin-top: 30px; color: #323130; opacity: 0.5; font-size: 12px;">© 2026 E-Gadget System for Windows</p>
            </div>

            <!-- SEARCH & FILTER BAR -->
            <form method="GET" class="search-filter-card glass fade-in" style="animation-delay: 0.6s;">
                <div class="search-wrap">
                    <span class="search-icon">🔍</span>
                    <input type="text" name="search" 
                           class="search-input-clear" 
                           placeholder="Ketik nama produk atau brand..." 
                           value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
                           autocomplete="off" id="searchField">
                </div>
                <div style="flex: 1; min-width: 140px;">
                    <select name="kategori" class="win-input-premium" style="padding: 13px 15px; font-size: 14px;">
                        <option value="">🗂️ Semua Kategori</option>
                        <?php mysqli_data_seek($kategori_res, 0); while($kat = mysqli_fetch_assoc($kategori_res)): ?>
                            <option value="<?= $kat['id_kategori'] ?>" <?= (isset($_GET['kategori']) && $_GET['kategori'] == $kat['id_kategori']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kat['nama_kategori']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div style="flex: 1; min-width: 130px;">
                    <select name="merk" class="win-input-premium" style="padding: 13px 15px; font-size: 14px;">
                        <option value="">🏷️ Semua Merk</option>
                        <?php mysqli_data_seek($merk_res, 0); while($m = mysqli_fetch_assoc($merk_res)): ?>
                            <option value="<?= $m['id_merk'] ?>" <?= (isset($_GET['merk']) && $_GET['merk'] == $m['id_merk']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['nama_merk']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" class="win-btn-action win-btn-save" style="padding: 13px 25px; white-space: nowrap;">Cari Produk</button>
                <div style="display: flex; gap: 8px;">
                    <a href="dashboard.php?filter=low_stock" class="win-btn-action" style="background: rgba(255,193,7,0.15); color: #b45309; border: 1px solid rgba(255,193,7,0.3); padding: 10px 15px; font-size: 12px; white-space: nowrap;">⚠️ Stok Tipis</a>
                    <?php if(isset($_GET['search']) || isset($_GET['kategori']) || isset($_GET['merk']) || isset($_GET['filter'])): ?>
                        <a href="dashboard.php" class="win-btn-action win-btn-cancel" style="padding: 10px 15px; font-size: 12px;">✖ Reset</a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- SORT + VIEW TOGGLE -->
            <div style="display: flex; justify-content: space-between; margin-bottom: 24px; gap: 15px; align-items: center;" class="fade-in">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 13px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Urutkan:</span>
                    <select onchange="location.href='dashboard.php?sort=' + this.value + '<?= isset($_GET['search']) ? '&search='.$_GET['search'] : '' ?><?= isset($_GET['kategori']) ? '&kategori='.$_GET['kategori'] : '' ?>'" class="win-input-premium" style="width: auto; padding: 9px 15px; font-size: 13px;">
                        <option value="id_desc" <?= (!isset($_GET['sort']) || $_GET['sort'] == 'id_desc') ? 'selected' : '' ?>>🆕 Terbaru</option>
                        <option value="name_asc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'name_asc') ? 'selected' : '' ?>>🔤 Nama (A-Z)</option>
                        <option value="price_asc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') ? 'selected' : '' ?>>💰 Harga Termurah</option>
                        <option value="price_desc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'selected' : '' ?>>💎 Harga Termahal</option>
                        <option value="stock_asc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'stock_asc') ? 'selected' : '' ?>>📦 Stok Terendah</option>
                    </select>
                </div>
                <div class="view-toggle">
                    <button class="view-btn active" id="btnGrid" onclick="setView('grid')" title="Grid View">⊞</button>
                    <button class="view-btn" id="btnTable" onclick="setView('table')" title="Table View">☰</button>
                </div>
            </div>

            <!-- PRODUCT CARD GRID VIEW -->
            <div class="product-grid fade-in" id="viewGrid">
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php $rows = []; while($row = mysqli_fetch_assoc($result)) $rows[] = $row; ?>
                    <?php foreach($rows as $row): ?>
                    <?php
                        $stok = (int)$row['stok'];
                        $badge_class = 'badge-success'; $stok_text = '✅ Stok Aman';
                        if($stok == 0) { $badge_class = 'badge-danger'; $stok_text = '❌ Habis'; }
                        elseif($stok < 5) { $badge_class = 'badge-warning'; $stok_text = '⚠️ Hampir Habis'; }
                    ?>
                    <div class="product-card fade-in">
                        <?php if(!empty($row['foto_produk']) && file_exists("img/".$row['foto_produk'])): ?>
                            <img src="img/<?= htmlspecialchars($row['foto_produk']) ?>" class="product-card-img" alt="<?= htmlspecialchars($row['nama_produk']) ?>">
                        <?php else: ?>
                            <div class="product-card-img-placeholder">📱</div>
                        <?php endif; ?>
                        <div class="product-card-body">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px;">
                                <p class="product-card-name"><?= htmlspecialchars($row['nama_produk']) ?></p>
                                <span class="badge <?= $badge_class ?>" style="font-size: 10px; white-space: nowrap;"><?= $row['stok'] ?></span>
                            </div>
                            <p class="product-card-meta"><?= htmlspecialchars($row['nama_merk']) ?> &middot; <?= htmlspecialchars($row['nama_kategori']) ?></p>
                            <p class="product-card-price">Rp <?= number_format($row['harga'], 0, ',', '.') ?></p>
                            <span class="badge <?= $badge_class ?>" style="font-size: 10px; align-self: flex-start;"><?= $stok_text ?></span>
                        </div>
                        <div class="product-card-actions">
                            <a href="detail.php?id=<?= $row['id_produk'] ?>" class="card-btn-detail">👁 Detail</a>
                            <a href="edit.php?id=<?= $row['id_produk'] ?>" class="card-btn-edit">✏️ Edit</a>
                            <a href="hapus.php?id=<?= $row['id_produk'] ?>" class="card-btn-del" onclick="return confirm('Hapus produk ini?')">🗑 Hapus</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 80px; color: var(--text-muted);">
                        <div style="font-size: 60px; margin-bottom: 20px;">🔍</div>
                        <h3 style="margin-bottom: 10px;">Tidak ada produk ditemukan</h3>
                        <p>Coba ubah filter atau kata kunci pencarian.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- TABLE VIEW (hidden by default) -->
            <div id="viewTable" style="display: none;">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>No</th><th>Visual</th><th>Model Gadget</th>
                            <th>Kategori</th><th>Harga & Stok</th>
                            <th style="text-align: right;">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($rows)): ?>
                            <?php $no = 1; foreach($rows as $row): ?>
                            <?php
                                $stok = (int)$row['stok'];
                                $bc = 'badge-success'; $st = 'In Stock';
                                if($stok == 0) { $bc = 'badge-danger'; $st = 'Habis'; }
                                elseif($stok < 5) { $bc = 'badge-warning'; $st = 'Tipis'; }
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <?php if(!empty($row['foto_produk']) && file_exists("img/".$row['foto_produk'])): ?>
                                        <img src="img/<?= htmlspecialchars($row['foto_produk']) ?>" style="width:50px;height:50px;object-fit:cover;border-radius:10px;">
                                    <?php else: ?>
                                        <div style="width:50px;height:50px;background:linear-gradient(135deg,#e8f4fd,#dbeafe);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:22px;">📱</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <p style="font-weight: 700; font-size: 14px;"><?= htmlspecialchars($row['nama_produk']) ?></p>
                                    <span style="font-size: 11px; color: var(--text-muted);"><?= htmlspecialchars($row['nama_merk']) ?></span>
                                </td>
                                <td><span class="win-badge win-badge-id"><?= htmlspecialchars($row['nama_kategori']) ?></span></td>
                                <td>
                                    <p style="font-weight: 700; color: var(--primary); margin-bottom: 4px;">Rp <?= number_format($row['harga'], 0, ',', '.') ?></p>
                                    <span class="badge <?= $bc ?>"><?= $st ?> (<?= $stok ?>)</span>
                                </td>
                                <td style="text-align: right;">
                                    <a href="detail.php?id=<?= $row['id_produk'] ?>" class="win-btn-action win-btn-save" style="padding:6px 12px;font-size:11px;">Detail</a>
                                    <a href="edit.php?id=<?= $row['id_produk'] ?>" class="win-btn-action" style="padding:6px 12px;font-size:11px;background:var(--secondary);color:white;">Edit</a>
                                    <a href="hapus.php?id=<?= $row['id_produk'] ?>" class="win-btn-action win-btn-cancel" style="padding:6px 12px;font-size:11px;color:var(--danger);" onclick="return confirm('Hapus?')">Hapus</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align:center;padding:60px;color:var(--text-muted);">🔍 Tidak ada data</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
    // Toast Utility
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `<span>${type === 'success' ? '✅' : '❌'}</span> <span>${message}</span>`;
        container.appendChild(toast);
        setTimeout(() => toast.classList.add('active'), 100);
        setTimeout(() => {
            toast.classList.remove('active');
            setTimeout(() => {
                toast.remove();
            }, 500);
        }, 4000);
    }

    // Check URL for messages
    window.addEventListener('load', () => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('msg')) {
            const msg = urlParams.get('msg');
            const type = urlParams.get('type') || 'success';
            showToast(msg, type);
            // Clean URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });

    // Chart.js Implementation
    const ctx = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: [{
                label: 'Jumlah Produk',
                data: <?= json_encode($chart_data) ?>,
                backgroundColor: 'rgba(67, 97, 238, 0.5)',
                borderColor: '#4361ee',
                borderWidth: 2,
                borderRadius: 8,
                barThickness: 30
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } },
                x: { grid: { display: false } }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });

    // Theme Toggle Logic
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

    // Biometric Registration
    const btnRegisterBio = document.getElementById('btn-register-bio');
    if (btnRegisterBio) {
        btnRegisterBio.addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                const challenge = new Uint8Array(32);
                window.crypto.getRandomValues(challenge);
                const userId = new Uint8Array(16);
                window.crypto.getRandomValues(userId);

                const publicKey = {
                    challenge: challenge,
                    rp: { id: window.location.hostname, name: "E-Gadget System" },
                    user: {
                        id: userId,
                        name: "<?= $_SESSION['user']['username'] ?>",
                        displayName: "<?= $_SESSION['user']['username'] ?>"
                    },
                    pubKeyCredParams: [{ alg: -7, type: "public-key" }, { alg: -257, type: "public-key" }],
                    authenticatorSelection: { authenticatorAttachment: "platform", userVerification: "required" },
                    timeout: 60000,
                    attestation: "none"
                };

                const credential = await navigator.credentials.create({ publicKey });
                const rawId = btoa(String.fromCharCode.apply(null, new Uint8Array(credential.rawId)));
                
                const response = await fetch('register_biometric.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ credential_id: rawId })
                });
                const result = await response.json();
                
                if (result.status === 'success') {
                    showToast("Sidik Jari berhasil didaftarkan!", "success");
                } else {
                    showToast("Gagal mendaftarkan sidik jari.", "error");
                }
            } catch (err) {
                console.error(err);
                showToast("Biometrik gagal atau tidak didukung.", "error");
            }
        });
    }

    // ---- GRID / TABLE VIEW TOGGLE ----
    function setView(mode) {
        const vGrid = document.getElementById('viewGrid');
        const vTable = document.getElementById('viewTable');
        const btnG = document.getElementById('btnGrid');
        const btnT = document.getElementById('btnTable');
        if (mode === 'grid') {
            vGrid.style.display = 'grid';
            vTable.style.display = 'none';
            btnG.classList.add('active');
            btnT.classList.remove('active');
            localStorage.setItem('dashView', 'grid');
        } else {
            vGrid.style.display = 'none';
            vTable.style.display = 'block';
            btnG.classList.remove('active');
            btnT.classList.add('active');
            localStorage.setItem('dashView', 'table');
        }
    }
    // Restore last view mode
    const savedView = localStorage.getItem('dashView') || 'grid';
    setView(savedView);

    // ---- SEARCH INPUT LIVE HIGHLIGHT ----
    const sf = document.getElementById('searchField');
    if (sf) {
        sf.addEventListener('input', function() {
            if (this.value.trim().length > 0) {
                this.style.borderColor = 'var(--primary)';
                this.style.background = '#fff';
            } else {
                this.style.borderColor = 'rgba(0, 120, 212, 0.2)';
            }
        });
        // Auto-focus search on "/"
        document.addEventListener('keydown', e => {
            if (e.key === '/' && document.activeElement !== sf) {
                e.preventDefault();
                sf.focus();
                sf.select();
            }
        });
    }
    </script>
</body>
</html>

