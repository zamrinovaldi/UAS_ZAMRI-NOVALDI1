<?php
    #1. Meng-koneksikan PHP ke MySQL
    include("../koneksi.php");

    #2. Mengambil Value dari Form Tambah
    $nama_merk = $_POST['nama_merk'];

    #3. Query Insert (proses tambah data)
    $query = "INSERT INTO merk (nama_merk) 
    VALUES ('$nama_merk')";

    $tambah = mysqli_query($koneksi,$query);

    #4. Jika Berhasil triggernya apa? (optional)
    if($tambah){
        header("location:index.php");
    }else{
        echo "Data Gagal ditambah";
    }
?>