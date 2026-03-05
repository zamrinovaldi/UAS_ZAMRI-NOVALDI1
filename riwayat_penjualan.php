<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
require 'koneksi.php';

$user_id = $_SESSION['user']['id_user'];
$user_query = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$user_id'");
$user_data = mysqli_fetch_assoc($user_query);

// Fetch Transactions
$query = "SELECT penjualan.*, users.username FROM penjualan JOIN users ON penjualan.id_user = users.id_user ORDER BY id_penjualan DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Penjualan - E-Gadget PRO</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="win-bloom-bg"></div>
    <div id="toast-container"></div>
    <div class="app-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <div>
                    <h1>Riwayat Penjualan</h1>
                    <p style="color: var(--text-muted); font-size: 14px;">Daftar transaksi yang telah berhasil dilakukan.</p>
                </div>
                <div style="display: flex; gap: 15px;">
                    <button id="theme-toggle" class="theme-toggle" title="Toggle Dark Mode">🌓</button>
                    <a href="pos.php" class="btn-premium btn-primary">Buka Kasir</a>
                </div>
            </header>

            <?php if(isset($_GET['msg'])): ?>
            <div class="glass fade-in" style="padding: 15px 25px; border-radius: 15px; margin-bottom: 30px; border-left: 5px solid <?= $_GET['type'] == 'success' ? '#4cc9f0' : 'var(--danger)' ?>;">
                <p style="font-weight: 700; margin: 0; color: <?= $_GET['type'] == 'success' ? '#4cc9f0' : 'var(--danger)' ?>;">
                    <?= htmlspecialchars($_GET['msg']) ?>
                </p>
            </div>
            <?php endif; ?>

            <div class="fade-in">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>No Transaksi</th>
                            <th>Tanggal</th>
                            <th>Kasir</th>
                            <th>Total Harga</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr class="staggered-item">
                            <td style="font-weight: 700;">#TRX-<?= str_pad($row['id_penjualan'], 5, '0', STR_PAD_LEFT) ?></td>
                            <td><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td style="font-weight: 800; color: var(--primary);">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                            <td style="text-align: right;">
                                <button class="btn-premium" style="background: var(--secondary); color: white; padding: 5px 15px; font-size: 12px;" onclick="alert('Fitur Cetak Struk akan segera hadir!')">🖨️ Cetak</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if(mysqli_num_rows($result) == 0): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 100px 0; color: var(--text-muted);">
                                <div style="font-size: 40px; margin-bottom: 15px;">📜</div>
                                <p style="font-weight: 700;">Belum ada riwayat penjualan.</p>
                                <p style="font-size: 13px;">Silakan lakukan transaksi di menu POS Terlebih dahulu.</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <script>
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
