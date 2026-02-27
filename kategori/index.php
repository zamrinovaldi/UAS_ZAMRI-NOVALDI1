<?php
include("../ceklogin.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori</title>
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
                        <b>KATEGORI</b>
                        <a href="form_tambah.php" class="float-end btn btn-primary btn-sm"><i class="bi bi-plus"></i></i> Tambah Data</a>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">NO</th>
                                    <th scope="col">Nama Kategori</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                #1. koneksi
                                include("../koneksi.php");

                                #2. menulikan query menampilkan data
                                $qry = "SELECT * FROM kategori";

                                #3. menjalankan query
                                $tampil = mysqli_query($koneksi, $qry);

                                #4. looping hasil query
                                $nomor = 1;
                                foreach ($tampil as $data) {

                                ?>
                                    <tr>
                                        <th scope="row"><?= $nomor++ ?></th>
                                        <td><?= $data['nama_kategori'] ?></td>
                                        <td>

                                            <a href="formedit.php?id_kategori=<?= $data['id_kategori'] ?>" class="btn btn-info btn-sm"><i class="bi bi-pen"></i></a>
                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalhapus<?= $data['id_kategori'] ?>"><i class="bi bi-trash"></i></button>

                                            <!-- Modal Hapus-->
                                            <div class="modal fade" id="modalhapus<?= $data['id_kategori'] ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h1 class="modal-title fs-5" id="exampleModalLabel">Peringatan</h1>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Yakin Data Kategori <?= $data['nama_kategori'] ?> Ingin Dihapus?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <a href="proseshapus.php?id_kategori=<?= $data['id_kategori'] ?>" class="btn btn-danger">Hapus</a>
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