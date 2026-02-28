<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
require 'koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $query = mysqli_query($koneksi, "SELECT foto_produk FROM produk WHERE id_produk = '$id'");
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        $foto = $data['foto_produk'];
        
        if (!empty($foto) && file_exists("img/".$foto)) {
            unlink("img/".$foto);
        }
        
        mysqli_query($koneksi, "DELETE FROM produk WHERE id_produk = '$id'");
    }
}

header("Location: dashboard.php");
exit;
?>
