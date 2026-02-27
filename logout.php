<?php
session_start();

// Hapus semua session
session_unset();
session_destroy();

// Hapus cookie jika ada
setcookie("coo_user", "", time() - 3600, "/");

// Redirect ke login
header("Location: login.php");
exit;
?>