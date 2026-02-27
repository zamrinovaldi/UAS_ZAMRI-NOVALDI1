<?php 
    #1. koneksi database
    include_once("../koneksi.php");

    #2. ID hapus
    $idhapus = $_GET['id_merk'];

    #3. menulis query
    $qry = "DELETE FROM merk WHERE id_merk='$idhapus'";

    #4. menjalan query
    $hapus = mysqli_query($koneksi,$qry);
    
    #5. mengalihkan halaman
    header("location:index.php");

    
?>