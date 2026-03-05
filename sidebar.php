<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <h2 style="margin-bottom: 40px; color: var(--primary);">📱 E-Gadget PRO</h2>
    
    <nav style="flex: 1; display: flex; flex-direction: column; gap: 5px;">
        <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
        <a href="dashboard.php" class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>"><span>📊</span> <span>Dashboard</span></a>
        <?php endif; ?>
        
        <a href="pos.php" class="nav-link <?= $current_page == 'pos.php' ? 'active' : '' ?>"><span>🛒</span> <span>Mesin Kasir</span></a>
        <a href="riwayat_penjualan.php" class="nav-link <?= $current_page == 'riwayat_penjualan.php' ? 'active' : '' ?>"><span>📜</span> <span>Riwayat Jual</span></a>
        
        <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
        <a href="laporan.php" class="nav-link <?= $current_page == 'laporan.php' ? 'active' : '' ?>"><span>📃</span> <span>Laporan Penjualan</span></a>
        
        <div class="sidebar-section">
            <p class="sidebar-section-title">Master Data</p>
            <a href="kategori.php" class="nav-link <?= $current_page == 'kategori.php' ? 'active' : '' ?>"><span>📂</span> <span>Kategori</span></a>
            <a href="merk.php" class="nav-link <?= $current_page == 'merk.php' ? 'active' : '' ?>"><span>🏷️</span> <span>Merk/Brand</span></a>
            <a href="tambah.php" class="nav-link <?= $current_page == 'tambah.php' ? 'active' : '' ?>"><span>➕</span> <span>Tambah Produk</span></a>
            <a href="users.php" class="nav-link <?= $current_page == 'users.php' ? 'active' : '' ?>"><span>👥</span> <span>Pengguna</span></a>
        </div>
        
        <div class="sidebar-section">
            <p class="sidebar-section-title">Keamanan</p>
            <a href="logs.php" class="nav-link <?= $current_page == 'logs.php' ? 'active' : '' ?>"><span>🛡️</span> <span>Audit Trail (Log)</span></a>
            <a href="backup.php" class="nav-link"><span>💾</span> <span>Backup Database</span></a>
        </div>
        <?php endif; ?>
    </nav>

    <div class="user-profile">
        <div class="avatar">
            <?php if(!empty($user_data['profil_foto'])): ?>
                <img src="<?= htmlspecialchars($user_data['profil_foto']) ?>" alt="Profile">
            <?php else: ?>
                <span style="color: white;"><?= strtoupper(substr($user_data['username'], 0, 1)) ?></span>
            <?php endif; ?>
        </div>
        <div style="flex: 1;">
            <p style="font-size: 13px; font-weight: 700; color: var(--text-main); margin: 0;"><?= htmlspecialchars($user_data['username']) ?></p>
            <p style="font-size: 10px; color: var(--text-muted); margin: 0; text-transform: capitalize;"><?= htmlspecialchars($_SESSION['user']['role'] ?? 'Kasir') ?></p>
        </div>
        <div style="display: flex; gap: 5px;">
            <a href="profil.php" style="color: var(--primary); text-decoration: none; font-size: 16px; padding: 5px; border-radius: 8px;" title="Profil">👤</a>
            <a href="logout.php" style="color: var(--danger); text-decoration: none; font-size: 16px; padding: 5px; border-radius: 8px;" title="Logout">🚪</a>
        </div>
    </div>
</aside>
