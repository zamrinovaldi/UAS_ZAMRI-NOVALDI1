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

$error = "";

// Handle Add Merk
if (isset($_POST['tambah_merk'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_merk']);
    $query = "INSERT INTO merk (nama_merk) VALUES ('$nama')";
    if (mysqli_query($koneksi, $query)) {
        log_activity("Tambah Merk", "Menambahkan merk baru: $nama");
        header("Location: merk.php?msg=Merk berhasil ditambahkan!&type=success");
    } else {
        $error = "Gagal menambah merk.";
    }
    exit;
}

// Handle Delete Merk
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $get_name = mysqli_query($koneksi, "SELECT nama_merk FROM merk WHERE id_merk = '$id'");
    $name = mysqli_fetch_assoc($get_name)['nama_merk'];
    
    $query = "DELETE FROM merk WHERE id_merk = '$id'";
    if (mysqli_query($koneksi, $query)) {
        log_activity("Hapus Merk", "Menghapus merk: $name");
        header("Location: merk.php?msg=Merk berhasil dihapus!&type=success");
    } else {
        header("Location: merk.php?msg=Gagal menghapus merk.&type=error");
    }
    exit;
}

$where = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($koneksi, $_GET['search']);
    $where = " WHERE nama_merk LIKE '%$search%'";
}
$query = "SELECT * FROM merk $where ORDER BY id_merk DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Merk - E-Gadget</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .page-header { margin-bottom: 40px; }
        .control-panel { 
            display: grid; 
            grid-template-columns: 1fr 2fr; 
            gap: 30px; 
            align-items: start;
        }
    </style>
</head>
<body>
    <div class="win-bloom-bg"></div>
    <div class="app-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <div class="page-header fade-in">
                    <h1 style="font-weight: 800; letter-spacing: -1.5px; font-size: 32px;">Kelola Merk</h1>
                    <p style="color: var(--text-muted); font-size: 15px;">Manajemen brand gadget dari berbagai vendor.</p>
                </div>
                <div class="theme-toggle" id="theme-toggle">🌓</div>
            </header>

            <div class="control-panel fade-in">
                <!-- Add Form -->
                <div class="glass" style="padding: 35px; border-radius: 24px;">
                    <h3 style="margin-bottom: 25px; font-size: 18px; font-weight: 700;">Tambah Merk</h3>
                    <form method="POST">
                        <div class="win-form-group">
                            <label class="win-label">Nama Merk / Brand</label>
                            <input type="text" name="nama_merk" class="win-input-premium" placeholder="Contoh: Apple, Samsung, Xiaomi..." required>
                        </div>
                        <button type="submit" name="tambah_merk" class="win-btn-action win-btn-save" style="width: 100%; justify-content: center;">
                            <span>💾</span> Simpan Merk
                        </button>
                    </form>
                </div>

                <!-- List Table -->
                <div class="glass" style="padding: 35px; border-radius: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                        <h3 style="margin: 0; font-size: 18px; font-weight: 700;">Daftar Merk Beredar</h3>
                        <form action="" method="GET" style="display: flex; gap: 10px;">
                            <input type="text" name="search" class="win-input-premium" style="padding: 8px 15px; width: 200px; font-size: 13px;" placeholder="Cari merk..." value="<?= $_GET['search'] ?? '' ?>">
                            <button type="submit" class="win-btn-action win-btn-save" style="padding: 8px 15px;">🔍</button>
                        </form>
                    </div>

                    <div style="overflow-x: auto;">
                        <table class="premium-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Merk</th>
                                    <th style="text-align: right;">Opsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><span class="win-badge win-badge-id">#<?= $row['id_merk'] ?></span></td>
                                    <td style="font-weight: 700;"><?= htmlspecialchars($row['nama_merk']) ?></td>
                                    <td style="text-align: right;">
                                        <a href="merk.php?hapus=<?= $row['id_merk'] ?>" 
                                           class="win-btn-action win-btn-cancel" 
                                           style="padding: 6px 12px; font-size: 11px; color: var(--danger);"
                                           onclick="return confirm('Hapus merk ini?')">
                                            <span>🗑️</span> Hapus
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
    // Theme Toggle Logic
    const themeToggle = document.getElementById('theme-toggle');
    if (!themeToggle) {
        const headerRight = document.querySelector('.top-bar > div:last-child');
        const btn = document.createElement('button');
        btn.id = 'theme-toggle';
        btn.className = 'theme-toggle';
        btn.innerHTML = '🌓';
        btn.style.marginRight = '10px';
        headerRight.insertBefore(btn, headerRight.firstChild);
    }
    
    const tt = document.getElementById('theme-toggle');
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
            setTimeout(() => toast.remove(), 500);
        }, 4000);
    }

    // Check URL for messages
    window.addEventListener('load', () => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('msg')) {
            showToast(urlParams.get('msg'), urlParams.get('type') || 'success');
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });
    </script>
</body>
</html>
