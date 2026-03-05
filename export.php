<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
require 'koneksi.php';

// Set filename
$filename = "Inventory_Gadget_" . date('Y-m-d_H-i-s') . ".csv";

// Fetch data
$query = "SELECT produk.id_produk, produk.nama_produk, kategori.nama_kategori, merk.nama_merk, produk.harga, produk.stok 
          FROM produk 
          JOIN kategori ON produk.id_kategori = kategori.id_kategori 
          JOIN merk ON produk.id_merk = merk.id_merk 
          ORDER BY produk.id_produk DESC";
$result = mysqli_query($koneksi, $query);

// Open output stream
$f = fopen('php://memory', 'w');

// Set column headers
$fields = array('ID', 'NAMA PRODUK', 'KATEGORI', 'MERK', 'HARGA', 'STOK');
fputcsv($f, $fields, ',');

// Fetch and put data
while($row = mysqli_fetch_assoc($result)) {
    $lineData = array($row['id_produk'], $row['nama_produk'], $row['nama_kategori'], $row['nama_merk'], $row['harga'], $row['stok']);
    fputcsv($f, $lineData, ',');
}

// Move back to beginning of file
fseek($f, 0);

// Set headers to download file rather than displayed
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '";');

// Output all remaining data on a file pointer
fpassthru($f);
exit;
?>
