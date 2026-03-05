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

$query = "SELECT * FROM users ORDER BY id_user DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna - E-Gadget PRO</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="win-bloom-bg"></div>
    <div class="app-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <div>
                    <h1>Manajemen Pengguna</h1>
                    <p style="color: var(--text-muted); font-size: 14px;">Kelola akun admin dan kasir.</p>
                </div>
                <div>
                    <a href="user_tambah.php" class="btn-premium btn-primary"><span>+</span> Tambah Pengguna</a>
                </div>
            </header>

            <div class="glass fade-in" style="padding: 24px; border-radius: var(--radius-lg);">
                <table class="premium-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $row['id_user'] ?></td>
                            <td style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['email'] ?? '-') ?></td>
                            <td>
                                <span class="win-badge <?= $row['role'] == 'admin' ? 'badge-warning' : 'badge-success' ?>">
                                    <?= strtoupper($row['role']) ?>
                                </span>
                            </td>
                            <td style="text-align: right; display: flex; justify-content: flex-end; gap: 8px;">
                                <a href="user_edit.php?id=<?= $row['id_user'] ?>" class="win-btn-action" style="background:var(--secondary);color:white;">Edit</a>
                                <?php if($row['id_user'] != $_SESSION['user']['id_user']): ?>
                                    <a href="user_hapus.php?id=<?= $row['id_user'] ?>" class="win-btn-action win-btn-cancel" style="color:var(--danger);" onclick="return confirm('Yakin hapus pengguna ini?')">Hapus</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
