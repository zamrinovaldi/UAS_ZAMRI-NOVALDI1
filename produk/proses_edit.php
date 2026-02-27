<?php
include("../koneksi.php");

$id_produk   = $_POST['id_produk'];
$nama_produk = $_POST['nama_produk'];
$harga       = $_POST['harga'];
$stok        = $_POST['stok'];

$nama_foto = $_FILES['foto_produk']['name'];
$tmp_foto  = $_FILES['foto_produk']['tmp_name'];

if (!empty($nama_foto)) {

    // Ambil foto lama
    $qry = "SELECT foto_produk FROM produk WHERE id_produk='$id_produk'";
    $result = mysqli_query($koneksi, $qry);
    $data = mysqli_fetch_assoc($result);

    $foto_lama = $data['foto_produk'];
    $path_lama = "../foto_produk/$foto_lama";

    // Hapus foto lama jika ada
    if (file_exists($path_lama) && !empty($foto_lama)) {
        unlink($path_lama);
    }

    // Upload foto baru
    move_uploaded_file($tmp_foto, "../foto_produk/$nama_foto");

    // Update dengan foto baru
    $query = "UPDATE produk SET 
                nama_produk='$nama_produk',
                harga='$harga',
                stok='$stok',
                foto_produk='$nama_foto'
              WHERE id_produk='$id_produk'";

} else {

    // Update TANPA ubah foto
    $query = "UPDATE produk SET 
                nama_produk='$nama_produk',
                harga='$harga',
                stok='$stok'
              WHERE id_produk='$id_produk'";
}

$update = mysqli_query($koneksi, $query);

if ($update) {
    header("location:index.php");
    exit;
} else {
    echo "Data Gagal Diedit";
}
?>