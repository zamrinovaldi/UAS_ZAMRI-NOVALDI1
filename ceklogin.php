<?php 
session_start();
if(!isset($_SESSION['ses_email']) AND !isset($_COOKIE['coo_email'])){
    header("http://localhost/UAS_ZAMRI1/uas_zamri1/login.php");
}
?>