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

// Ambil data untuk dropdown dropdown
$kategori_query = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
$merks_query = mysqli_query($koneksi, "SELECT * FROM merk ORDER BY nama_merk ASC");

if (isset($_POST['submit'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
    $id_kat = mysqli_real_escape_string($koneksi, $_POST['id_kategori']);
    $id_merk = mysqli_real_escape_string($koneksi, $_POST['id_merk']);
    $harga = mysqli_real_escape_string($koneksi, $_POST['harga']);
    $stok = mysqli_real_escape_string($koneksi, $_POST['stok']);

    // File info
    $foto_name = $_FILES['foto_produk']['name'];
    $foto_tmp = $_FILES['foto_produk']['tmp_name'];
    $foto_ext = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png'];

    if (in_array($foto_ext, $allowed)) {
        $new_name = time() . "_" . $foto_name;
        if (move_uploaded_file($foto_tmp, "img/" . $new_name)) {
            $query = "INSERT INTO produk (nama_produk, id_kategori, id_merk, harga, stok, foto_produk) 
                      VALUES ('$nama', '$id_kat', '$id_merk', '$harga', '$stok', '$new_name')";
            if (mysqli_query($koneksi, $query)) {
                log_activity("Tambah Produk", "Menambahkan produk baru: $nama");
                header("Location: dashboard.php?msg=Produk baru berhasil ditambahkan!&type=success");
                exit;
            } else {
                $error = "Terjadi kesalahan database saat menyimpan.";
            }
        } else {
            $error = "Gagal mengupload file.";
        }
    } else {
        $error = "Format file tidak didukung. Gunakan JPG atau PNG.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - E-Gadget</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container { max-width: 900px; margin: 0 auto; }
        .image-preview-placeholder {
            width: 100%;
            height: 200px;
            background: rgba(0,0,0,0.02);
            border: 2px dashed var(--border-color);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            margin-bottom: 20px;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div class="win-bloom-bg"></div>
    <div class="app-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <div class="fade-in">
                    <h1 style="font-weight: 800; letter-spacing: -1.5px; font-size: 32px;">Tambah Produk</h1>
                    <p style="color: var(--text-muted); font-size: 15px;">Daftarkan stok gadget baru ke katalog sistem.</p>
                </div>
                <div style="display: flex; gap: 15px;">
                    <a href="dashboard.php" class="win-btn-action win-btn-cancel">
                        <span>⬅️</span> Kembali
                    </a>
                    <div class="theme-toggle" id="theme-toggle">🌓</div>
                </div>
            </header>

            <div class="form-container fade-in">
                <div class="glass" style="padding: 40px; border-radius: 24px;">
                    <?php if($error): ?>
                        <div style="background: rgba(209, 52, 56, 0.1); color: var(--danger); padding: 15px; border-radius: 12px; margin-bottom: 25px; font-size: 14px; border-left: 4px solid var(--danger);">
                            ⚠️ <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="win-form-grid">
                            <div class="win-form-group">
                                <label class="win-label">Nama Model Gadget</label>
                                <input type="text" name="nama_produk" class="win-input-premium" placeholder="e.g. iPhone 15 Pro, Galaxy S24..." required>
                            </div>

                            <div class="win-form-group">
                                <label class="win-label">Kategori</label>
                                <select name="id_kategori" class="win-input-premium" required>
                                    <option value="" disabled selected>Pilih Kategori...</option>
                                    <?php while($k = mysqli_fetch_assoc($kategori_query)): ?>
                                        <option value="<?= $k['id_kategori'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="win-form-group">
                                <label class="win-label">Merk / Brand</label>
                                <select name="id_merk" class="win-input-premium" required>
                                    <option value="" disabled selected>Pilih Brand...</option>
                                    <?php while($m = mysqli_fetch_assoc($merks_query)): ?>
                                        <option value="<?= $m['id_merk'] ?>"><?= htmlspecialchars($m['nama_merk']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="win-form-group">
                                <label class="win-label">Harga Jual (Rp)</label>
                                <input type="number" name="harga" class="win-input-premium" placeholder="0" min="0" required>
                            </div>

                            <div class="win-form-group">
                                <label class="win-label">Stok Gudang awal</label>
                                <input type="number" name="stok" class="win-input-premium" placeholder="0" min="0" required>
                            </div>

                            <div class="win-form-group">
                                <label class="win-label">Foto Produk (JPG/PNG)</label>
                                <input type="file" name="foto_produk" class="win-input-premium" accept=".jpg, .jpeg, .png" required id="imgInput">
                            </div>
                        </div>

                        <div id="preview-container" style="display: none; margin-bottom: 30px;">
                            <label class="win-label">Preview Gambar</label>
                            <div class="image-preview-placeholder">
                                <img id="previewImg" src="#" alt="Preview" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                            </div>
                        </div>
                        
                        <div style="display: flex; justify-content: flex-end; gap: 15px; border-top: 1px solid var(--border-color); padding-top: 30px;">
                            <button type="reset" class="win-btn-action win-btn-cancel">Reset Form</button>
                            <button type="submit" name="submit" class="win-btn-action win-btn-save">
                                <span>➕</span> Daftarkan Produk
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Image Preview Logic
        document.getElementById('imgInput').onchange = evt => {
            const [file] = evt.target.files
            if (file) {
                document.getElementById('preview-container').style.display = 'block';
                document.getElementById('previewImg').src = URL.createObjectURL(file)
            }
        }
    </script>
</body>
</html>
        </main>
    </div>
</body>
</html>
