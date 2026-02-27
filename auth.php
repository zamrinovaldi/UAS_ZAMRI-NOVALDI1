<?php
session_start();
// Jika tidak ada session login, tendang balik ke login.php di folder kamu
if (!isset($_SESSION['ses_email']) && !isset($_COOKIE['coo_email'])) {
    header("location: login.php");
    exit;
}
?>