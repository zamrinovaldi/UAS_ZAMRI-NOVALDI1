<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: pos.php");
    exit;
}
require 'koneksi.php';

$user_id = $_SESSION['user']['id_user'];
$user_query = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$user_id'");
$user_data = mysqli_fetch_assoc($user_query);

// Filter Logic
$where = "WHERE 1=1";
if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $start = mysqli_real_escape_string($koneksi, $_GET['start_date']);
    $end = mysqli_real_escape_string($koneksi, $_GET['end_date']) . ' 23:59:59';
    $where .= " AND p.created_at BETWEEN '$start' AND '$end'";
}

$query = "SELECT dp.*, p.created_at, p.total_harga as grand_total, prod.nama_produk, u.username as kasir
          FROM detail_penjualan dp
          JOIN penjualan p ON dp.id_penjualan = p.id_penjualan
          JOIN produk prod ON dp.id_produk = prod.id_produk
          JOIN users u ON p.id_user = u.id_user
          $where
          ORDER BY p.created_at DESC";

$result = mysqli_query($koneksi, $query);

$total_pendapatan_query = mysqli_query($koneksi, "SELECT SUM(total_harga) as ttl FROM penjualan p $where");
$total_pendapatan = mysqli_fetch_assoc($total_pendapatan_query)['ttl'] ?? 0;

$total_barang_query = mysqli_query($koneksi, "SELECT SUM(dp.jumlah) as ttl FROM detail_penjualan dp JOIN penjualan p ON dp.id_penjualan = p.id_penjualan $where");
$total_barang = mysqli_fetch_assoc($total_barang_query)['ttl'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - E-Gadget PRO</title>
    <link rel="stylesheet" href="style.css">
    <style>
        @media print {
            .sidebar, .top-bar, .filter-form, .btn-premium { display: none !important; }
            .main-content { margin-left: 0 !important; width: 100% !important; }
            body { background: white; }
            .premium-table { border: 1px solid #ccc; }
            .premium-table th { background: #eee !important; color: black !important; }
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
                    <h1>Laporan Penjualan</h1>
                    <p style="color: var(--text-muted); font-size: 14px;">Detail transaksi lengkap untuk Admin/Bos.</p>
                </div>
                <div>
                    <button onclick="window.print()" class="btn-premium" style="background: var(--primary); color: white;">🖨️ Cetak Laporan</button>
                </div>
            </header>

            <form method="GET" class="filter-form form-card fade-in" style="margin-bottom: 32px; display: flex; gap: 16px; align-items: flex-end; padding: 24px;">
                <div style="flex: 1;">
                    <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--text-muted);">Dari Tanggal</label>
                    <input type="date" name="start_date" class="win-input-premium" value="<?= $_GET['start_date'] ?? '' ?>" required style="padding: 10px 16px;">
                </div>
                <div style="flex: 1;">
                    <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--text-muted);">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="win-input-premium" value="<?= $_GET['end_date'] ?? '' ?>" required style="padding: 10px 16px;">
                </div>
                <button type="submit" class="win-btn-action win-btn-save" style="padding: 12px 24px;">Terapkan Filter</button>
                <?php if(!empty($_GET['start_date'])): ?>
                    <a href="laporan.php" class="win-btn-action win-btn-cancel" style="padding: 12px 24px;">Reset</a>
                <?php endif; ?>
            </form>

            <section class="stats-grid" style="margin-bottom: 32px;">
                <div class="stat-card glass" style="background: linear-gradient(135deg, rgba(37,99,235,0.05) 0%, rgba(37,99,235,0.15) 100%); border: 1px solid rgba(37,99,235,0.2);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <span class="stat-label">Total Barang Terjual (Filter)</span>
                        <span style="font-size: 20px; background: rgba(37,99,235,0.2); padding: 8px; border-radius: 8px; color: var(--primary);">📦</span>
                    </div>
                    <span class="stat-value" style="color: var(--primary);"><?= $total_barang ?> Unit</span>
                </div>
                <div class="stat-card glass" style="background: linear-gradient(135deg, rgba(16,185,129,0.05) 0%, rgba(16,185,129,0.15) 100%); border: 1px solid rgba(16,185,129,0.2);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <span class="stat-label">Total Pendapatan Kotor (Filter)</span>
                        <span style="font-size: 20px; background: rgba(16,185,129,0.2); padding: 8px; border-radius: 8px; color: var(--success);">💰</span>
                    </div>
                    <span class="stat-value" style="color: var(--success);">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></span>
                </div>
            </section>

            <div class="glass fade-in" style="padding: 24px;">
                <table class="premium-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Tgl & Waktu</th>
                            <th>Produk</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                            <th>Kasir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td style="color: var(--text-muted); font-size: 13px;"><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
                                <td style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($row['nama_produk']) ?></td>
                                <td><?= $row['jumlah'] ?></td>
                                <td style="font-weight: 600;">Rp <?= number_format($row['subtotal'], 0, ',', '.') ?></td>
                                <td style="color: var(--text-muted); font-size: 13px;"><?= htmlspecialchars($row['kasir']) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted);">Tidak ada transaksi pada periode ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
