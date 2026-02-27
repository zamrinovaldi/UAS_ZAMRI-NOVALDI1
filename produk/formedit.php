<?php
include("../koneksi.php");

$id = $_GET['id_produk'];
$qry = "SELECT * FROM produk WHERE id_produk='$id'";
$result = mysqli_query($koneksi, $qry);
$data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background-color:#d1e6d4">

<?php include_once("../navbar.php"); ?>

<div class="container">
    <div class="row my-5">
        <div class="col-8 m-auto">
            <div class="card shadow p-3 mb-5 bg-body-tertiary rounded">
                <div class="card-header">
                    <b>FORM EDIT PRODUK</b>
                </div>
                <div class="card-body">

                    <form action="proses_edit.php" method="POST" enctype="multipart/form-data">

                        <!-- hidden id -->
                        <input type="hidden" name="id_produk" value="<?= $data['id_produk'] ?>">

                        <div class="mb-3">
                            <label class="form-label">Nama Produk</label>
                            <input name="nama_produk" type="text" class="form-control"
                                   value="<?= $data['nama_produk'] ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Harga</label>
                            <input name="harga" type="number" class="form-control"
                                   value="<?= $data['harga'] ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Stok</label>
                            <input name="stok" type="number" class="form-control"
                                   value="<?= $data['stok'] ?>">
                        </div>

                        <!-- KATEGORI -->
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select class="form-control" name="id_kategori">
                                <?php
                                $qry_kat = "SELECT * FROM kategori";
                                $data_kat = mysqli_query($koneksi, $qry_kat);
                                foreach ($data_kat as $item_kat) {
                                    $selected = ($item_kat['id_kategori'] == $data['id_kategori']) ? "selected" : "";
                                ?>
                                    <option value="<?= $item_kat['id_kategori'] ?>" <?= $selected ?>>
                                        <?= $item_kat['nama_kategori'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <!-- MERK -->
                        <div class="mb-3">
                            <label class="form-label">Merk</label>
                            <select class="form-control" name="id_merk">
                                <?php
                                $qry_merk = "SELECT * FROM merk";
                                $data_merk = mysqli_query($koneksi, $qry_merk);
                                foreach ($data_merk as $item_merk) {
                                    $selected = ($item_merk['id_merk'] == $data['id_merk']) ? "selected" : "";
                                ?>
                                    <option value="<?= $item_merk['id_merk'] ?>" <?= $selected ?>>
                                        <?= $item_merk['nama_merk'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <!-- FOTO -->
                        <div class="mb-3">
                            <label class="form-label">Foto</label><br>
                            <input name="foto_produk" type="file" class="form-control" accept="image/*">
                            <small class="text-muted">Kosongkan jika tidak ingin mengganti foto</small>
                        </div>

                        <button type="submit" class="btn btn-primary">Update</button>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>