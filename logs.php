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

// Fetch Logs
$query = "SELECT activity_logs.*, users.username FROM activity_logs JOIN users ON activity_logs.id_user = users.id_user ORDER BY created_at DESC LIMIT 100";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Trail - E-Gadget PRO</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .log-item { display: flex; gap: 20px; align-items: center; padding: 15px; border-bottom: 1px solid var(--border-color); }
        .log-time { font-size: 11px; color: var(--text-muted); width: 120px; }
        .log-user { font-weight: 700; width: 100px; }
        .log-action { background: var(--primary); color: white; padding: 2px 8px; border-radius: 4px; font-size: 10px; text-transform: uppercase; font-weight: 800; }
    </style>
</head>
<body>
    <div class="win-bloom-bg"></div>
    <div class="app-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <div>
                    <h1>Audit Trail (System Logs)</h1>
                    <p style="color: var(--text-muted); font-size: 14px;">Log aktivitas sistem untuk keamanan dan pemantauan.</p>
                </div>
                <div style="display: flex; gap: 15px;">
                    <button id="theme-toggle" class="theme-toggle" title="Toggle Dark Mode">🌓</button>
                    <a href="dashboard.php" class="btn-premium btn-primary">Kembali</a>
                </div>
            </header>

            <div class="glass fade-in" style="padding: 20px; border-radius: 20px;">
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="log-item">
                    <span class="log-time"><?= date('d/m/Y H:i:s', strtotime($row['created_at'])) ?></span>
                    <span class="log-user"><?= htmlspecialchars($row['username']) ?></span>
                    <span class="log-action"><?= htmlspecialchars($row['aksi']) ?></span>
                    <span style="flex: 1; font-size: 14px;"><?= htmlspecialchars($row['keterangan']) ?></span>
                </div>
                <?php endwhile; ?>
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
