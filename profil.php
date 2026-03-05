<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
require 'koneksi.php';

$error = "";
$success = "";
$user_id = $_SESSION['user']['id_user'];

// Handle Profile Photo Upload
if (isset($_POST['upload_photo'])) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
    $new_filename = "user_" . $user_id . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    $uploadOk = 1;

    // Check if image file is a actual image
    $check = getimagesize($_FILES["photo"]["tmp_name"]);
    if($check === false) {
        $error = "File bukan gambar.";
        $uploadOk = 0;
    }

    // Check file size (max 2MB)
    if ($_FILES["photo"]["size"] > 2000000) {
        $error = "File terlalu besar (Maskimal 2MB).";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg" && $file_extension != "gif" ) {
        $error = "Hanya format JPG, JPEG, PNG & GIF yang diperbolehkan.";
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            // Update database
            $update_photo = "UPDATE users SET profil_foto = '$target_file' WHERE id_user = '$user_id'";
            if (mysqli_query($koneksi, $update_photo)) {
                $success = "Foto profil berhasil diperbarui!";
            } else {
                $error = "Gagal memperbarui database foto.";
            }
        } else {
            $error = "Maaf, terjadi kesalahan saat mengunggah file.";
        }
    }
}

// Fetch current user data
$query = "SELECT * FROM users WHERE id_user = '$user_id'";
$result = mysqli_query($koneksi, $query);
$user_data = mysqli_fetch_assoc($result);

// Handle Password Change
if (isset($_POST['change_password'])) {
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if (password_verify($old_pass, $user_data['password'])) {
        if ($new_pass === $confirm_pass) {
            $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password = '$hashed_pass' WHERE id_user = '$user_id'";
            if (mysqli_query($koneksi, $update_query)) {
                $success = "Password berhasil diperbarui!";
            } else {
                $error = "Terjadi kesalahan saat memperbarui password.";
            }
        } else {
            $error = "Konfirmasi password baru tidak cocok.";
        }
    } else {
        $error = "Password lama salah.";
    }
}

// Handle Personal Info Update
if (isset($_POST['update_profile'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    
    // Check if username already exists for other users
    $check_user = mysqli_query($koneksi, "SELECT id_user FROM users WHERE username = '$username' AND id_user != '$user_id'");
    if (mysqli_num_rows($check_user) > 0) {
        $error = "Username sudah digunakan oleh akun lain.";
    } else {
        $update_query = "UPDATE users SET username = '$username', nama_lengkap = '$nama', email = '$email' WHERE id_user = '$user_id'";
        if (mysqli_query($koneksi, $update_query)) {
            $success = "Profil berhasil diperbarui!";
            $_SESSION['user']['username'] = $username; // Update session
            // Refresh data
            $result = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$user_id'");
            $user_data = mysqli_fetch_assoc($result);
        } else {
            $error = "Gagal memperbarui profil.";
        }
    }
}

// Handle Security Question Update
if (isset($_POST['update_security'])) {
    $question = mysqli_real_escape_string($koneksi, $_POST['security_question']);
    $answer = mysqli_real_escape_string($koneksi, $_POST['security_answer']);
    
    $update_query = "UPDATE users SET security_question = '$question', security_answer = '$answer' WHERE id_user = '$user_id'";
    if (mysqli_query($koneksi, $update_query)) {
        $success = "Informasi pemulihan akun berhasil diperbarui!";
        // Refresh data
        $result = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$user_id'");
        $user_data = mysqli_fetch_assoc($result);
    } else {
        $error = "Gagal memperbarui informasi pemulihan.";
    }
}

// Handle Privacy Update
if (isset($_POST['update_privacy'])) {
    $mode = isset($_POST['private_mode']) ? 1 : 0;
    $update_query = "UPDATE users SET private_mode = '$mode' WHERE id_user = '$user_id'";
    if (mysqli_query($koneksi, $update_query)) {
        $success = "Pengaturan privasi diperbarui!";
        $result = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$user_id'");
        $user_data = mysqli_fetch_assoc($result);
    } else {
        $error = "Gagal memperbarui privasi.";
    }
}
$has_bio = !empty($user_data['credential_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna - E-Gadget</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .nav-link {
            padding: 12px 15px;
            color: #605e5c;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            border-radius: var(--radius-md);
            transition: all 0.3s;
            margin-bottom: 5px;
        }
        .nav-link:hover, .nav-link.active {
            background: var(--primary);
            color: white;
        }
        .profile-container {
            display: flex;
            flex-direction: column;
            gap: 25px;
            max-width: 1000px;
            margin: 0 auto;
            width: 95%;
        }
        .photo-upload-overlay {
            position: absolute;
            bottom: 0;
            right: 0;
            background: var(--primary);
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            border: 3px solid white;
            transition: all 0.3s;
        }
        .photo-upload-overlay:hover {
            transform: scale(1.1);
            background: var(--secondary);
        }
        .win-avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        .profile-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 30px;
            text-align: center;
            box-shadow: var(--shadow-soft);
        }
        .profile-avatar-large {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: white;
            margin: 0 auto 20px;
        }
        .bio-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            margin-top: 15px;
        }
        .bio-status-registered {
            background: rgba(0, 188, 242, 0.1);
            color: #0078d4;
        }
        .bio-status-unregistered {
            background: rgba(209, 52, 56, 0.1);
            color: #d13438;
        }
    </style>
</head>
<body>
    <div class="win-bloom-bg"></div>
    <div id="toast-container"></div>
    
    <div class="app-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <div class="fade-in" style="max-width: 1000px; margin: 0 auto; width: 95%;">
                    <h1 style="font-weight: 700; letter-spacing: -1px;">Profil & Keamanan</h1>
                    <p style="color: var(--text-muted); font-size: 14px;">Atur identitas digital dan kontrol sesi Anda.</p>
                </div>
            </header>

            <div class="profile-container">
                <?php if($success): ?>
                    <div class="win-glass-card fade-in" style="background: rgba(40, 167, 69, 0.1); border: 1px solid rgba(40, 167, 69, 0.2); padding: 15px; border-radius: 12px; color: #155724; margin-bottom: 20px;">
                        <?= $success ?>
                    </div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="win-glass-card fade-in" style="background: rgba(220, 53, 69, 0.1); border: 1px solid rgba(220, 53, 69, 0.2); padding: 15px; border-radius: 12px; color: #721c24; margin-bottom: 20px;">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <!-- Single Monolithic Profile Card -->
                <div class="win-glass-card fade-in" style="max-width: 100%; border-radius: 24px; text-align: left; padding: 50px;">
                    <!-- Section 1: Identity & Session -->
                    <div style="display: flex; gap: 40px; align-items: center; flex-wrap: wrap; margin-bottom: 50px;">
                        <div style="position: relative;">
                            <div class="win-avatar-ring" style="margin: 0; width: 100px; height: 100px;">
                                <?php if(!empty($user_data['profil_foto'])): ?>
                                    <img src="<?= htmlspecialchars($user_data['profil_foto']) ?>" class="win-avatar-img" alt="Profile">
                                <?php else: ?>
                                    <div class="win-avatar" style="font-size: 36px;"><?= strtoupper(substr($user_data['username'], 0, 1)) ?></div>
                                <?php endif; ?>
                            </div>
                            <form action="" method="POST" enctype="multipart/form-data" id="photoForm">
                                <label for="photoInput" class="photo-upload-overlay">
                                    <span>📷</span>
                                </label>
                                <input type="file" id="photoInput" name="photo" style="display: none;" onchange="document.getElementById('photoForm').submit();">
                                <input type="hidden" name="upload_photo" value="1">
                            </form>
                        </div>
                        <div style="flex: 1; min-width: 250px;">
                            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 8px;">
                                <h2 style="margin: 0; font-size: 28px; font-weight: 800;"><?= htmlspecialchars($user_data['username']) ?></h2>
                                <a href="#edit-form" style="text-decoration: none; font-size: 14px; background: rgba(0,0,0,0.05); padding: 5px 12px; border-radius: 20px; color: var(--primary); font-weight: 600; display: flex; align-items: center; gap: 5px; border: 1px solid rgba(0,0,0,0.05); transition: all 0.3s;" onmouseover="this.style.background='rgba(0,0,0,0.1)'" onmouseout="this.style.background='rgba(0,0,0,0.05)'">
                                    <span>✏️</span> Edit Nama
                                </a>
                            </div>
                            <p style="color: var(--text-muted); font-size: 15px; margin-bottom: 0;">Administrator System • Akun Terverifikasi</p>
                        </div>
                        <div>
                            <a href="logout.php" style="color: var(--danger); font-size: 13px; text-decoration: none; font-weight: 700; padding: 10px 20px; border-radius: 10px; border: 1px solid rgba(209, 52, 56, 0.2); background: rgba(209, 52, 56, 0.05); display: flex; align-items: center; gap: 8px;">
                                <span>🚪</span> Logout
                            </a>
                        </div>
                    </div>

                    <div id="edit-form" style="display: grid; grid-template-columns: 1fr 1fr; gap: 60px; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 50px;">
                        <!-- Left Column: Personal Info & Privacy -->
                        <div style="display: flex; flex-direction: column; gap: 45px;">
                            <div>
                                <h4 style="margin-bottom: 25px; font-size: 14px; color: var(--primary); letter-spacing: 1.5px; font-weight: 800;">INFORMASI PROFIL</h4>
                                <form method="POST" style="display: flex; flex-direction: column; gap: 20px;">
                                    <div class="input-group">
                                        <label style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 10px; display: block;">USERNAME AKUN</label>
                                        <input type="text" name="username" class="win-input" style="background: rgba(255,255,255,0.7); padding: 14px;" value="<?= htmlspecialchars($user_data['username']) ?>" placeholder="Username login" required>
                                    </div>
                                    <div class="input-group">
                                        <label style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 10px; display: block;">NAMA LENGKAP</label>
                                        <input type="text" name="nama_lengkap" class="win-input" style="background: rgba(255,255,255,0.7); padding: 14px;" value="<?= htmlspecialchars($user_data['nama_lengkap'] ?? '') ?>" placeholder="Masukkan nama lengkap">
                                    </div>
                                    <div class="input-group">
                                        <label style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 10px; display: block;">ALAMAT EMAIL</label>
                                        <input type="email" name="email" class="win-input" style="background: rgba(255,255,255,0.7); padding: 14px;" value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" placeholder="email@contoh.com">
                                    </div>
                                    <button type="submit" name="update_profile" class="win-btn-primary" style="height: 45px; font-size: 14px; width: 160px; background: var(--primary);">Simpan Perubahan</button>
                                </form>
                            </div>

                            <div style="background: rgba(0,0,0,0.03); padding: 25px; border-radius: 20px; border: 1px solid rgba(0,0,0,0.05);">
                                <h4 style="margin-bottom: 20px; font-size: 14px; color: var(--text-main); font-weight: 800;">PENGATURAN PRIVASI</h4>
                                <form method="POST" style="display: flex; align-items: center; justify-content: space-between;">
                                    <div>
                                        <p style="font-size: 14px; font-weight: 600; margin: 0;">Mode Privasi</p>
                                        <p style="font-size: 11px; color: var(--text-muted); margin: 0;">Sembunyikan identitas di laporan dashboard.</p>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <label class="switch">
                                            <input type="checkbox" name="private_mode" <?= (isset($user_data['private_mode']) && $user_data['private_mode'] == 1) ? 'checked' : '' ?>>
                                            <span class="slider round"></span>
                                        </label>
                                        <button type="submit" name="update_privacy" class="win-btn-primary" style="width: auto; height: 35px; padding: 0 15px; font-size: 12px; background: #6c757d;">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Right Column: Password & Recovery -->
                        <div style="display: flex; flex-direction: column; gap: 45px;">
                            <div>
                                <h4 style="margin-bottom: 25px; font-size: 14px; color: var(--primary); letter-spacing: 1.5px; font-weight: 800;">KEAMANAN AKSES</h4>
                                <form method="POST" style="display: flex; flex-direction: column; gap: 20px;">
                                    <div class="input-group">
                                        <label style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 10px; display: block;">PASSWORD SAAT INI</label>
                                        <input type="password" name="old_password" class="win-input" style="background: rgba(255,255,255,0.7); padding: 14px;" placeholder="••••••••" required>
                                    </div>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                        <div class="input-group">
                                            <label style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 10px; display: block;">PASSWORD BARU</label>
                                            <input type="password" name="new_password" class="win-input" style="background: rgba(255,255,255,0.7); padding: 14px;" placeholder="Baru" required>
                                        </div>
                                        <div class="input-group">
                                            <label style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 10px; display: block;">KONFIRMASI</label>
                                            <input type="password" name="confirm_password" class="win-input" style="background: rgba(255,255,255,0.7); padding: 14px;" placeholder="Ulangi" required>
                                        </div>
                                    </div>
                                    <button type="submit" name="change_password" class="win-btn-primary" style="height: 45px; font-size: 14px; width: 160px; background: var(--primary);">Ganti Password</button>
                                </form>
                            </div>

                            <div style="background: rgba(0, 120, 212, 0.05); padding: 25px; border-radius: 20px; border: 1px solid rgba(0, 120, 212, 0.1);">
                                <h4 style="margin-bottom: 20px; font-size: 14px; color: var(--secondary); font-weight: 800;">PEMULIHAN AKUN</h4>
                                <form method="POST" style="display: flex; flex-direction: column; gap: 15px;">
                                    <select name="security_question" class="win-input" style="background: white; padding: 12px; font-size: 13px;" required>
                                        <option value="" disabled <?= empty($user_data['security_question']) ? 'selected' : '' ?>>Pilih pertanyaan keamanan...</option>
                                        <option value="Apa nama hewan peliharaan pertama Anda?" <?= (isset($user_data['security_question']) && $user_data['security_question'] == 'Apa nama hewan peliharaan pertama Anda?') ? 'selected' : '' ?>>Apa nama hewan peliharaan pertama Anda?</option>
                                        <option value="Di mana kota kelahiran ibu Anda?" <?= (isset($user_data['security_question']) && $user_data['security_question'] == 'Di mana kota kelahiran ibu Anda?') ? 'selected' : '' ?>>Di mana kota kelahiran ibu Anda?</option>
                                        <option value="Apa nama sekolah dasar Anda?" <?= (isset($user_data['security_question']) && $user_data['security_question'] == 'Apa nama sekolah dasar Anda?') ? 'selected' : '' ?>>Apa nama sekolah dasar Anda?</option>
                                    </select>
                                    <div style="display: flex; gap: 12px;">
                                        <input type="text" name="security_answer" class="win-input" style="background: white; padding: 12px; flex: 1;" value="<?= htmlspecialchars($user_data['security_answer'] ?? '') ?>" placeholder="Jawaban rahasia" required>
                                        <button type="submit" name="update_security" class="win-btn-primary" style="height: 45px; width: 100px; font-size: 12px; background: var(--secondary); font-weight: 600;">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
            
            <p style="margin-top: 50px; text-align: center; color: var(--text-muted); font-size: 12px; opacity: 0.5;">
                Sistem Inventaris E-Gadget • Versi Gacor 5.0 Core Premium • © 2026 Admin Panel
            </p>
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
