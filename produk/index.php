
<?php
include("../ceklogin.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>

<body style="background-color:#d1e6d4">
    <?php
    include_once("../navbar.php");
    ?>

    <div class="container">
        <div class="row my-5">
            <div class="col-10 m-auto">
                <div class="card shadow p-3 mb-5 bg-body-tertiary rounded">
                    <div class="card-header">
                        <b>PRODUK</b>
                        <a href="form_tambah.php" class="float-end btn btn-primary btn-sm"><i class="bi bi-plus"></i></i> Tambah Data</a>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Nama Produk</th>
                                    <th scope="col">Harga</th>
                                    <th scope="col">Stok</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                #1. koneksi
                                include("../koneksi.php");

                                #2. menulikan query menampilkan data
                                $qry = "SELECT produk.*, 
               kategori.nama_kategori, 
               merk.nama_merk,
               produk.id_produk AS ids
        FROM produk
        LEFT JOIN merk ON produk.id_merk = merk.id_merk
        LEFT JOIN kategori ON produk.id_kategori = kategori.id_kategori";
                                #3. menjalankan query
                                $tampil = mysqli_query($koneksi, $qry);

                                #4. looping hasil query
                                $nomor = 1;
                                foreach ($tampil as $data) {

                                ?>
                                    <tr>
                                        <th scope="row"><?= $nomor++ ?></th>
                                        <td><?= $data['nama_produk'] ?></td>
                                        <td><?= $data['harga'] ?></td>
                                        <td><?= $data['stok'] ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#exampleModal<?= $data['ids'] ?>"><i class="bi bi-search"></i></i></button>
                                            <a href="formedit.php?id_produk=<?= $data['ids'] ?>" class="btn btn-info btn-sm"><i class="bi bi-pen"></i></i></a>
                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalhapus<?= $data['ids'] ?>"><i class="bi bi-trash"></i></i></button>

                                            <!-- Modal Detail-->
                                            <div class="modal fade" id="exampleModal<?= $data['ids'] ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h1 class="modal-title fs-5" id="exampleModalLabel">Data Detail <?= $data['nama_produk'] ?></h1>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <table class="table">
                                                                <tbody>
                                                                    <tr>
                                                                        <td colspan="2"><img src="../foto_produk/<?= $data['foto_produk'] ?>" height="150" alt=""></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Nama produk</td>
                                                                        <th scope="row"><?= $data['nama_produk'] ?></th>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Harga</td>
                                                                        <th scope="row"><?= $data['harga'] ?></th>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Stok </td>
                                                                        <th scope="row"><?= $data['stok'] ?></th>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Kategori </td>
                                                                        <th scope="row"><?= $data['nama_kategori'] ?></th>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Merk </td>
                                                                        <th scope="row"><?= $data['nama_merk'] ?></th>
                                                                    </tr>

                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Modal Hapus-->
                                            <div class="modal fade" id="modalhapus<?= $data['ids'] ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h1 class="modal-title fs-5" id="exampleModalLabel">Peringatan</h1>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Yakin Data Dengan Nama <?= $data['nama_produk'] ?> Ingin Dihapus?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <a href="proseshapus.php?id_produk=<?= $data['ids'] ?>" class="btn btn-danger">Hapus</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php
                                }
                                ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
    <script src="../js/all.js"></script>
</body>

</html>