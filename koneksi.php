<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_gadget_nim";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Auto-refresh user session if role is missing (smooth upgrade)
if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user']) && !isset($_SESSION['user']['role'])) {
    $u_id = (int)$_SESSION['user']['id_user'];
    $r = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = $u_id");
    if ($r && mysqli_num_rows($r) > 0) {
        $_SESSION['user'] = mysqli_fetch_assoc($r);
    }
}

function log_activity($aksi, $keterangan = "") {
    global $koneksi;
    if (isset($_SESSION['user'])) {
        $user_id = $_SESSION['user']['id_user'];
        $aksi = mysqli_real_escape_string($koneksi, $aksi);
        $keterangan = mysqli_real_escape_string($koneksi, $keterangan);
        mysqli_query($koneksi, "INSERT INTO activity_logs (id_user, aksi, keterangan) VALUES ('$user_id', '$aksi', '$keterangan')");
    }
}
?>
