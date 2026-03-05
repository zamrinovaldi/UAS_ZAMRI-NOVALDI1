<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
require 'koneksi.php';

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$id = $_GET['id'];
$query_data = mysqli_query($koneksi, "SELECT * FROM produk WHERE id_produk = '$id'");
$data = mysqli_fetch_assoc($query_data);

if (!$data) {
    header("Location: dashboard.php");
    exit;
}

// Ambil data untuk dropdown
$kategori_query = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
$merks_query = mysqli_query($koneksi, "SELECT * FROM merk ORDER BY nama_merk ASC");

$error = "";

if (isset($_POST['submit'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
    $id_kat = mysqli_real_escape_string($koneksi, $_POST['id_kategori']);
    $id_merk = mysqli_real_escape_string($koneksi, $_POST['id_merk']);
    $harga = mysqli_real_escape_string($koneksi, $_POST['harga']);
    $stok = mysqli_real_escape_string($koneksi, $_POST['stok']);

    $new_foto = $data['foto_produk'];
    if (!empty($_FILES['foto_produk']['name'])) {
        $foto_name = $_FILES['foto_produk']['name'];
        $foto_tmp = $_FILES['foto_produk']['tmp_name'];
        $foto_ext = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];

        if (in_array($foto_ext, $allowed)) {
            $new_foto = time() . "_" . $foto_name;
            if (move_uploaded_file($foto_tmp, "img/" . $new_foto)) {
                if (!empty($data['foto_produk']) && file_exists("img/" . $data['foto_produk'])) {
                    unlink("img/" . $data['foto_produk']);
                }
            } else {
                $error = "Gagal mengupload file baru.";
            }
        } else {
            $error = "Format file tidak didukung.";
        }
    }

    if (empty($error)) {
        $update = "UPDATE produk SET 
                   nama_produk = '$nama', 
                   id_kategori = '$id_kat', 
                   id_merk = '$id_merk', 
                   harga = '$harga', 
                   stok = '$stok', 
                   foto_produk = '$new_foto' 
                   WHERE id_produk = '$id'";
        if (mysqli_query($koneksi, $update)) {
            log_activity("Edit Produk", "Memperbarui produk: " . $data['nama_produk'] . " menjadi $nama");
            header("Location: dashboard.php?msg=Produk berhasil diperbarui!&type=success");
            exit;
        } else {
            $error = "Gagal memperbarui database.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - E-Gadget</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container { max-width: 900px; margin: 0 auto; }
        .edit-image-preview {
            width: 100%;
            height: 250px;
            background: rgba(0,0,0,0.02);
            border: 2px solid var(--border-color);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 20px;
            position: relative;
        }
        .edit-image-preview img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
            transition: transform 0.3s;
        }
        .image-overlay {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0, 120, 212, 0.8);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
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
                    <h1 style="font-weight: 800; letter-spacing: -1.5px; font-size: 32px;">Edit Produk</h1>
                    <p style="color: var(--text-muted); font-size: 15px;">Perbarui data inventaris untuk: <strong><?= htmlspecialchars($data['nama_produk']) ?></strong></p>
                </div>
                <div style="display: flex; gap: 15px;">
                    <a href="dashboard.php" class="win-btn-action win-btn-cancel">
                        <span>⬅️</span> Batal
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
                                <label class="win-label">Nama Produk</label>
                                <input type="text" name="nama_produk" class="win-input-premium" value="<?= htmlspecialchars($data['nama_produk']) ?>" required>
                            </div>

                            <div class="win-form-group">
                                <label class="win-label">Kategori</label>
                                <select name="id_kategori" class="win-input-premium" required>
                                    <?php mysqli_data_seek($kategori_query, 0); while($k = mysqli_fetch_assoc($kategori_query)): ?>
                                        <option value="<?= $k['id_kategori'] ?>" <?= ($data['id_kategori'] == $k['id_kategori']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($k['nama_kategori']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="win-form-group">
                                <label class="win-label">Merk / Brand</label>
                                <select name="id_merk" class="win-input-premium" required>
                                    <?php mysqli_data_seek($merks_query, 0); while($m = mysqli_fetch_assoc($merks_query)): ?>
                                        <option value="<?= $m['id_merk'] ?>" <?= ($data['id_merk'] == $m['id_merk']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($m['nama_merk']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="win-form-group">
                                <label class="win-label">Harga Jual (Rp)</label>
                                <input type="number" name="harga" class="win-input-premium" value="<?= $data['harga'] ?>" min="0" required>
                            </div>

                            <div class="win-form-group">
                                <label class="win-label">Stok Tersedia</label>
                                <input type="number" name="stok" class="win-input-premium" value="<?= $data['stok'] ?>" min="0" required>
                            </div>

                            <div class="win-form-group">
                                <label class="win-label">Ganti Foto (Kosongkan jika tidak diubah)</label>
                                <input type="file" name="foto_produk" class="win-input-premium" accept=".jpg, .jpeg, .png" id="imgInput">
                            </div>
                        </div>

                        <div style="margin-bottom: 30px;">
                            <label class="win-label">Visual Produk</label>
                            <div class="edit-image-preview">
                                <?php if(!empty($data['foto_produk']) && file_exists("img/" . $data['foto_produk'])): ?>
                                    <img id="previewImg" src="img/<?= htmlspecialchars($data['foto_produk']) ?>" alt="Product">
                                    <span class="image-overlay" id="imgStatus">GAMBAR LAMA</span>
                                <?php else: ?>
                                    <img id="previewImg" src="img/default.png" alt="No Image">
                                    <span class="image-overlay" style="background: var(--danger);">TIDAK ADA FOTO</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div style="display: flex; justify-content: flex-end; gap: 15px; border-top: 1px solid var(--border-color); padding-top: 30px;">
                            <a href="dashboard.php" class="win-btn-action win-btn-cancel">Batal</a>
                            <button type="submit" name="submit" class="win-btn-action win-btn-save" style="background: var(--secondary);">
                                <span>💾</span> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('imgInput').onchange = evt => {
            const [file] = evt.target.files
            if (file) {
                document.getElementById('previewImg').src = URL.createObjectURL(file)
                const status = document.getElementById('imgStatus');
                if(status) {
                    status.innerText = 'PREVIEW BARU';
                    status.style.background = 'var(--success)';
                }
            }
        }
    </script>
</body>
</html>
        </main>
    </div>
</body>
</html>
