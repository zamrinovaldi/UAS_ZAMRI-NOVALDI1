<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
require 'koneksi.php';

$error = "";
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    header("Location: dashboard.php");
    exit;
}

$query_lama = mysqli_query($koneksi, "SELECT * FROM produk WHERE id_produk = '$id'");
if (mysqli_num_rows($query_lama) == 0) {
    header("Location: dashboard.php");
    exit;
}
$data = mysqli_fetch_assoc($query_lama);

$kategori_query = mysqli_query($koneksi, "SELECT * FROM kategori");
$merks_query = mysqli_query($koneksi, "SELECT * FROM merk");

if (isset($_POST['submit'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
    $id_kategori = (int)$_POST['id_kategori'];
    $id_merk = (int)$_POST['id_merk'];
    $harga = (int)$_POST['harga'];
    $stok = (int)$_POST['stok'];
    
    $foto_baru = $data['foto_produk'];
    
    if (empty(trim($nama))) {
        $error = "Nama produk tidak boleh kosong!";
    } elseif ($harga < 0 || $stok < 0) {
        $error = "Harga dan Stok minimum bernilai 0!";
    } else {
        if ($_FILES['foto_produk']['error'] == 0) {
            $foto = $_FILES['foto_produk']['name'];
            $tmp = $_FILES['foto_produk']['tmp_name'];
            $ext = strtolower(pathinfo($foto, PATHINFO_EXTENSION));
            
            $allowed = array('jpg', 'jpeg', 'png');
            
            if (!in_array($ext, $allowed)) {
                $error = "Format file hanya diizinkan JPG/PNG!";
            } else {
                if (!empty($data['foto_produk']) && file_exists("img/".$data['foto_produk'])) {
                    unlink("img/".$data['foto_produk']);
                }
                
                $foto_baru = time() . '_' . preg_replace("/[^a-zA-Z0-9.-]/", "", $foto);
                move_uploaded_file($tmp, "img/" . $foto_baru);
            }
        }
        
        if (empty($error)) {
            $query = "UPDATE produk SET 
                        id_kategori = '$id_kategori',
                        id_merk = '$id_merk',
                        nama_produk = '$nama',
                        harga = '$harga',
                        stok = '$stok',
                        foto_produk = '$foto_baru'
                      WHERE id_produk = '$id'";
                      
            if (mysqli_query($koneksi, $query)) {
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Gagal mengupdate data ke database.";
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
    <title>Edit Produk - MENU</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #eaeff2; margin: 0; padding: 0; }
        .navbar { background: #d65b6dff; padding: 15px 30px; color: white; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .navbar h1 { margin: 0; font-size: 22px; }
        .navbar a { color: white; text-decoration: none; padding: 8px 15px; background: rgba(255,255,255,0.2); border-radius: 5px; transition: background 0.3s; }
        .navbar a:hover { background: rgba(255,255,255,0.3); }
        .container { max-width: 600px; margin: 30px auto; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        h2 { margin-top: 0; color: #2c3e50; margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #555; font-weight: 500; font-size: 14px; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 15px; }
        .form-group input:focus, .form-group select:focus { border-color: #e9464eff; outline: none; }
        .form-group input[type="file"] { padding: 8px; }
        .btn-submit { background: #f39c12; width: 100%; padding: 12px; color: white; font-weight: 600; font-size: 15px; border: none; border-radius: 6px; cursor: pointer; transition: background 0.3s; }
        .btn-submit:hover { background: #e67e22; }
        .alert { color: white; background: #e74c3c; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; }
        .img-preview { max-width: 150px; margin-top: 10px; border-radius: 5px; border: 1px solid #ddd; }
        .info-text { font-size: 12px; color: #7f8c8d; margin-top: 5px; display: block; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>PESANAN</h1>
        <div><a href="dashboard.php">Kembali ke Dashboard</a></div>
    </div>
    
    <div class="container">
        <h2>Edit Data Produk</h2>
        <?php if($error): ?>
            <div class="alert"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nama Produk</label>
                <input type="text" name="nama_produk" value="<?= htmlspecialchars($data['nama_produk']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Kategori</label>
                <select name="id_kategori" required>
                    <?php while($k = mysqli_fetch_assoc($kategori_query)): ?>
                        <option value="<?= $k['id_kategori'] ?>" <?= ($data['id_kategori'] == $k['id_kategori']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_kategori']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Merk</label>
                <select name="id_merk" required>
                    <?php while($m = mysqli_fetch_assoc($merks_query)): ?>
                        <option value="<?= $m['id_merk'] ?>" <?= ($data['id_merk'] == $m['id_merk']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['nama_merk']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Harga (Rp)</label>
                <input type="number" name="harga" min="0" value="<?= $data['harga'] ?>" required>
            </div>
            
            <div class="form-group">
                <label>Stok</label>
                <input type="number" name="stok" min="0" value="<?= $data['stok'] ?>" required>
            </div>
            
            <div class="form-group">
                <label>Foto Produk (Biarkan kosong jika tidak diubah)</label>
                <input type="file" name="foto_produk" accept=".jpg, .jpeg, .png">
                <span class="info-text">Foto saat ini:</span>
                <?php if(!empty($data['foto_produk']) && file_exists("img/".$data['foto_produk'])): ?>
                    <div><img src="img/<?= htmlspecialchars($data['foto_produk']) ?>" class="img-preview"></div>
                <?php else: ?>
                    <span class="info-text">Tidak ada foto</span>
                <?php endif; ?>
            </div>
            
            <button type="submit" name="submit" class="btn-submit">Update Produk</button>
        </form>
    </div>
</body>
</html>
