<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
require 'koneksi.php';

// Fetch products with double join
$query = "SELECT produk.*, kategori.nama_kategori, merk.nama_merk 
          FROM produk 
          JOIN kategori ON produk.id_kategori = kategori.id_kategori 
          JOIN merk ON produk.id_merk = merk.id_merk
          ORDER BY produk.id_produk DESC";
$result = mysqli_query($koneksi, $query);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - E-Gadget Inventory</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #eaeff2; margin: 0; padding: 0; }
        .navbar { background: #3498db; padding: 15px 30px; color: white; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .navbar h1 { margin: 0; font-size: 22px; font-weight: 600; }
        .navbar a { color: white; text-decoration: none; font-weight: 500; background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 5px; transition: background 0.3s; }
        .navbar a:hover { background: rgba(255,255,255,0.3); }
        .container { max-width: 1000px; margin: 30px auto; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .header-action { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .header-action h2 { margin: 0; color: #2c3e50; }
        .btn { padding: 10px 15px; text-decoration: none; border-radius: 5px; color: white; font-weight: 500; font-size: 14px; transition: opacity 0.3s; }
        .btn:hover { opacity: 0.8; }
        .btn-add { background: #2ecc71; }
        .btn-edit { background: #f39c12; padding: 6px 10px; font-size: 13px; margin-right: 5px; }
        .btn-delete { background: #e74c3c; padding: 6px 10px; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; background: #fff; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #f0f0f0; }
        th { background: #f8f9fa; color: #333; font-weight: 600; text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px; }
        tr:hover { background-color: #fafbfc; }
        .thumbnail { width: 60px; height: 60px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd; padding: 2px; }
        .empty-state { text-align: center; padding: 30px; color: #7f8c8d; font-style: italic; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>E-Gadget System</h1>
        <div>
            <span>Welcome, <?= htmlspecialchars($_SESSION['user']['username']) ?></span> &nbsp;|&nbsp;
            <a href="#" id="btn-register-bio" style="background:#9b59b6; margin-right:5px;" title="Daftarkan Biometrik">Sidik Jari</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="header-action">
            <h2>Data Produk</h2>
            <a href="tambah.php" class="btn btn-add">+ Tambah Produk</a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Foto</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Merk</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php $no = 1; while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <?php if(!empty($row['foto_produk']) && file_exists("img/".$row['foto_produk'])): ?>
                                <img src="img/<?= htmlspecialchars($row['foto_produk']) ?>" class="thumbnail" alt="Foto">
                            <?php else: ?>
                                <span style="font-size:12px;color:#aaa;">No Image</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                        <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                        <td><?= htmlspecialchars($row['nama_merk']) ?></td>
                        <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars($row['stok']) ?></td>
                        <td>
                            <a href="edit.php?id=<?= $row['id_produk'] ?>" class="btn btn-edit">Edit</a>
                            <a href="hapus.php?id=<?= $row['id_produk'] ?>" class="btn btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="empty-state">Belum ada data produk.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
    const btnRegisterBio = document.getElementById('btn-register-bio');
    if (btnRegisterBio) {
        btnRegisterBio.addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                const challenge = new Uint8Array(32);
                window.crypto.getRandomValues(challenge);
                const userId = new Uint8Array(16);
                window.crypto.getRandomValues(userId);

                const publicKey = {
                    challenge: challenge,
                    rp: { id: window.location.hostname, name: "E-Gadget System" },
                    user: {
                        id: userId,
                        name: "<?= $_SESSION['user']['username'] ?>",
                        displayName: "<?= $_SESSION['user']['username'] ?>"
                    },
                    pubKeyCredParams: [{ alg: -7, type: "public-key" }, { alg: -257, type: "public-key" }],
                    authenticatorSelection: { authenticatorAttachment: "platform", userVerification: "required" },
                    timeout: 60000,
                    attestation: "none"
                };

                const credential = await navigator.credentials.create({ publicKey });
                const rawId = btoa(String.fromCharCode.apply(null, new Uint8Array(credential.rawId)));
                
                const response = await fetch('register_biometric.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ credential_id: rawId })
                });
                const result = await response.json();
                
                if (result.status === 'success') {
                    alert("Sidik Jari berhasil didaftarkan untuk akun ini!");
                } else {
                    alert("Gagal menyimpan ke database: " + result.message);
                }
            } catch (err) {
                console.error(err);
                alert("Perangkat ini tidak mendukung biometrik, browser memblokir, atau Anda membatalkannya.");
            }
        });
    }
    </script>
</body>
</html>
