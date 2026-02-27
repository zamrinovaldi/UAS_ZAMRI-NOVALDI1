<?php
$conn = mysqli_connect("localhost", "root", "", "db_gadget_nim");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>