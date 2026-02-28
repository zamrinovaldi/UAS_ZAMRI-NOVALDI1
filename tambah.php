<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
require 'koneksi.php';

$error = "";

$kategori_query = mysqli_query($koneksi, "SELECT * FROM kategori");
$merks_query = mysqli_query($koneksi, "SELECT * FROM merk");

if (isset($_POST['submit'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
    $id_kategori = (int)$_POST['id_kategori'];
    $id_merk = (int)$_POST['id_merk'];
    $harga = (int)$_POST['harga'];
    $stok = (int)$_POST['stok'];
    
    if (empty(trim($nama))) {
        $error = "Nama produk tidak boleh kosong!";
    } elseif ($harga < 0 || $stok < 0) {
        $error = "Harga dan Stok minimum bernilai 0!";
    } else {
        $foto = $_FILES['foto_produk']['name'];
        $tmp = $_FILES['foto_produk']['tmp_name'];
        $ext = strtolower(pathinfo($foto, PATHINFO_EXTENSION));
        
        $allowed = array('jpg', 'jpeg', 'png');
        
        if (!in_array($ext, $allowed)) {
            $error = "Format file hanya diizinkan JPG/PNG!";
        } else {
            $new_foto = time() . '_' . preg_replace("/[^a-zA-Z0-9.-]/", "", $foto);
            $path = "img/" . $new_foto;
            
            if (move_uploaded_file($tmp, $path)) {
                $query = "INSERT INTO produk (id_kategori, id_merk, nama_produk, harga, stok, foto_produk) 
                          VALUES ('$id_kategori', '$id_merk', '$nama', '$harga', '$stok', '$new_foto')";
                if (mysqli_query($koneksi, $query)) {
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $error = "Gagal menyimpan data ke database.";
                }
            } else {
                $error = "Gagal mengupload gambar.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - E-Gadget</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #eaeff2; margin: 0; padding: 0; }
        .navbar { background: #3498db; padding: 15px 30px; color: white; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .navbar h1 { margin: 0; font-size: 22px; }
        .navbar a { color: white; text-decoration: none; padding: 8px 15px; background: rgba(255,255,255,0.2); border-radius: 5px; transition: background 0.3s; }
        .navbar a:hover { background: rgba(255,255,255,0.3); }
        .container { max-width: 600px; margin: 30px auto; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        h2 { margin-top: 0; color: #2c3e50; margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #555; font-weight: 500; font-size: 14px; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 15px; }
        .form-group input:focus, .form-group select:focus { border-color: #3498db; outline: none; }
        .form-group input[type="file"] { padding: 8px; }
        .btn-submit { background: #2ecc71; width: 100%; padding: 12px; color: white; font-weight: 600; font-size: 15px; border: none; border-radius: 6px; cursor: pointer; transition: background 0.3s; }
        .btn-submit:hover { background: #27ae60; }
        .alert { color: white; background: #e74c3c; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>E-Gadget System</h1>
        <div><a href="dashboard.php">Kembali ke Dashboard</a></div>
    </div>
    
    <div class="container">
        <h2>Tambah Data Produk</h2>
        <?php if($error): ?>
            <div class="alert"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nama Produk</label>
                <input type="text" name="nama_produk" required>
            </div>
            
            <div class="form-group">
                <label>Kategori</label>
                <select name="id_kategori" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php while($k = mysqli_fetch_assoc($kategori_query)): ?>
                        <option value="<?= $k['id_kategori'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Merk</label>
                <select name="id_merk" required>
                    <option value="">-- Pilih Merk --</option>
                    <?php while($m = mysqli_fetch_assoc($merks_query)): ?>
                        <option value="<?= $m['id_merk'] ?>"><?= htmlspecialchars($m['nama_merk']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Harga (Rp)</label>
                <input type="number" name="harga" min="0" required>
            </div>
            
            <div class="form-group">
                <label>Stok</label>
                <input type="number" name="stok" min="0" required>
            </div>
            
            <div class="form-group">
                <label>Foto Produk (JPG/PNG)</label>
                <input type="file" name="foto_produk" accept=".jpg, .jpeg, .png" required>
            </div>
            
            <button type="submit" name="submit" class="btn-submit">Simpan Produk</button>
        </form>
    </div>
</body>
</html>
