<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: pos.php");
    exit;
}
require 'koneksi.php';

$id = mysqli_real_escape_string($koneksi, $_GET['id']);
if ($id == $_SESSION['user']['id_user']) {
    die("Anda tidak dapat menghapus akun Anda sendiri.");
}

$u = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT username FROM users WHERE id_user='$id'"));
if ($u) {
    mysqli_query($koneksi, "DELETE FROM users WHERE id_user = '$id'");
    log_activity("Hapus User", "Menghapus user " . $u['username']);
}
header("Location: users.php");
exit;
?>
