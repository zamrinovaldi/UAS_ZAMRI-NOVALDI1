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

$error = "";
$success = "";

if (isset($_POST['simpan'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $role = mysqli_real_escape_string($koneksi, $_POST['role']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($koneksi, "SELECT id_user FROM users WHERE username='$username'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Username sudah digunakan!";
    } else {
        $q = "INSERT INTO users (username, password, email, role) VALUES ('$username', '$password', '$email', '$role')";
        if (mysqli_query($koneksi, $q)) {
            log_activity("Tambah User", "Menambahkan user $username ($role)");
            $success = "Pengguna berhasil ditambahkan!";
        } else {
            $error = "Gagal: " . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pengguna</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="win-bloom-bg"></div>
    <div class="app-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <div>
                    <h1>Tambah Pengguna</h1>
                    <p style="color: var(--text-muted); font-size: 14px;">Buat akun admin atau kasir baru.</p>
                </div>
                <div>
                    <a href="users.php" class="win-btn-action" style="padding: 10px 20px;">⬅ Kembali</a>
                </div>
            </header>

            <div class="form-card glass fade-in">
                <?php if($error): ?><div style="color:red; margin-bottom:15px;"><?= $error ?></div><?php endif; ?>
                <?php if($success): ?><div style="color:green; margin-bottom:15px;"><?= $success ?></div><?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="input-premium" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="input-premium" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="input-premium">
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" class="input-premium" required>
                            <option value="kasir">Kasir</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <button type="submit" name="simpan" class="btn-premium btn-primary" style="margin-top:20px;">Simpan Pengguna</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
