<?php
session_start();
$pesan = "";
if (isset($_POST['tombol'])) {
    //proses login

    #1. koneksi
    include("koneksi.php");

    #2. mengambil value data input
    $email = $_POST['username'];
    $pass = md5($_POST['pass']);

    #3. cek apakah email dan password ada di database
    $qry = "SELECT * FROM users WHERE username='$email' AND pass='$pass'";
    $result = mysqli_query($koneksi, $qry);
    $cek_login = mysqli_num_rows($result);

    if ($cek_login == 0) {
        //login gagal
        $pesan = "Login Gagal";
    } else {
        //login berhasil
        $pesan = "Login berhasil";
        // SESSION & COOKIE
        if (isset($_POST['cek']) == "yes") {
            //simpan cookie
            setcookie("coo_email", $email, time() + (3600 * 24 * 30), "/");
            header("location:index.php");
        } else {
            //simpan session
            $_SESSION['ses_email'] = $email;
            header("location:index.php");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body style="background-color:#d1e6d4">
    <div class="container">
        <div class="row my-5">
            <div class="col-8 m-auto">
                <div class="card shadow p-3 mb-5 bg-body-tertiary rounded">
                    <div class="card-header">
                        <b>HALAMAN LOGIN</b>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-primary" role="alert">
                            <?= $pesan ?>
                        </div>
                        <form action="login.php" method="post">
                            <div class="mb-3">
                                <label for="exampleInputEmail1" class="form-label">Email address</label>
                                <input type="email" name="username" class="form-control" id="exampleInputEmail1"
                                    aria-describedby="emailHelp">

                            </div>
                            <div class="mb-3">
                                <label for="exampleInputPassword1" class="form-label">Password</label>
                                <input type="password" name="pass" class="form-control" id="exampleInputPassword1">
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="cek" value="yes" class="form-check-input"
                                    id="exampleCheck1">
                                <label class="form-check-label" for="exampleCheck1">Check me out</label>
                            </div>
                            <button type="submit" name="tombol" class="btn btn-primary">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
</body>

</html>