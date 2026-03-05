<?php
session_start();
require 'koneksi.php';

$error = "";
$success = "";
$step = 1; // 1: Username, 2: Security Question, 3: Reset Password
$user_id = null;
$question = "";

if (isset($_POST['find_user'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $query = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($koneksi, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (!empty($user['security_question'])) {
            $_SESSION['reset_user_id'] = $user['id_user'];
            $question = $user['security_question'];
            $step = 2;
        } else {
            $error = "Akun ini belum mengatur pertanyaan keamanan. Silakan hubungi admin sistem.";
        }
    } else {
        $error = "Username tidak ditemukan.";
    }
}

if (isset($_POST['verify_answer'])) {
    $id = $_SESSION['reset_user_id'];
    $answer = mysqli_real_escape_string($koneksi, $_POST['answer']);
    
    $query = "SELECT * FROM users WHERE id_user='$id'";
    $result = mysqli_query($koneksi, $query);
    $user = mysqli_fetch_assoc($result);
    
    if (strtolower($answer) === strtolower($user['security_answer'])) {
        $step = 3;
    } else {
        $step = 2;
        $question = $user['security_question'];
        $error = "Jawaban keamanan salah.";
    }
}

if (isset($_POST['reset_password'])) {
    $id = $_SESSION['reset_user_id'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];
    
    if ($new_pass === $confirm_pass) {
        $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
        $query = "UPDATE users SET password='$hashed_pass' WHERE id_user='$id'";
        if (mysqli_query($koneksi, $query)) {
            $success = "Password berhasil direset! Silakan login kembali.";
            unset($_SESSION['reset_user_id']);
            $step = 4; // Done
        } else {
            $error = "Gagal mengupdate password.";
            $step = 3;
        }
    } else {
        $error = "Konfirmasi password tidak cocok.";
        $step = 3;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - E-Gadget</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="win-bloom-bg"></div>
    
    <div class="win-login-container fade-in">
        <div class="win-avatar-ring">
            <div class="win-avatar">🛡️</div>
        </div>
        
        <div class="win-glass-card">
            <h2 style="margin-bottom: 5px;">Pemulihan Akun</h2>
            <p style="margin-bottom: 30px; font-size: 14px; opacity: 0.8;">Atur ulang akses keamanan Anda</p>

            <?php if($error): ?>
                <div style="background: rgba(247, 37, 133, 0.2); color: #fff; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; border-left: 4px solid var(--danger); text-align: left;">
                    ⚠️ <?= $error ?>
                </div>
            <?php endif; ?>

            <?php if($success): ?>
                <div style="background: rgba(0, 188, 242, 0.2); color: #fff; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; border-left: 4px solid var(--primary); text-align: left;">
                    ✅ <?= $success ?>
                </div>
            <?php endif; ?>

            <?php if ($step == 1): ?>
                <form method="POST">
                    <p style="font-size: 13px; margin-bottom: 15px; opacity: 0.8;">Masukkan username Anda untuk memulai pemulihan.</p>
                    <input type="text" name="username" class="win-input" placeholder="Username" required>
                    <button type="submit" name="find_user" class="win-btn-primary">Cari Akun</button>
                </form>
            <?php elseif ($step == 2): ?>
                <form method="POST">
                    <p style="font-size: 13px; margin-bottom: 15px; opacity: 0.8;">Pertanyaan Keamanan:</p>
                    <p style="font-weight: 600; margin-bottom: 20px;"><?= $question ?></p>
                    <input type="text" name="answer" class="win-input" placeholder="Jawaban Anda" required autofocus>
                    <button type="submit" name="verify_answer" class="win-btn-primary">Verifikasi Jawaban</button>
                </form>
            <?php elseif ($step == 3): ?>
                <form method="POST">
                    <p style="font-size: 13px; margin-bottom: 15px; opacity: 0.8;">Verifikasi berhasil! Silakan buat password baru.</p>
                    <input type="password" name="new_password" class="win-input" placeholder="Password Baru" required>
                    <input type="password" name="confirm_password" class="win-input" placeholder="Konfirmasi Password Baru" required>
                    <button type="submit" name="reset_password" class="win-btn-primary">Reset Password</button>
                </form>
            <?php elseif ($step == 4): ?>
                <div style="margin-top: 20px;">
                    <a href="login.php" class="win-btn-primary" style="text-decoration: none; display: flex; align-items: center; justify-content: center;">Kembali ke Login</a>
                </div>
            <?php endif; ?>

            <?php if ($step < 4): ?>
                <div style="margin-top: 20px; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 15px;">
                    <a href="login.php" style="font-size: 13px; color: var(--primary); text-decoration: none; opacity: 0.7;">Batal dan Kembali</a>
                </div>
            <?php endif; ?>
        </div>
        
        <p style="margin-top: 30px; color: #323130; opacity: 0.5; font-size: 12px;">© 2026 E-Gadget System - Secure Recovery</p>
    </div>
</body>
</html>
