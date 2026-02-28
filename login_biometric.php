<?php
session_start();
require 'koneksi.php';

$data = json_decode(file_get_contents("php://input"));

if (isset($data->credential_id)) {
    $cred_id = mysqli_real_escape_string($koneksi, $data->credential_id);
    
    $query = "SELECT * FROM users WHERE credential_id='$cred_id' LIMIT 1";
    $result = mysqli_query($koneksi, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user'] = $user;
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Data biometrik tidak dikenali di sistem. Silakan login manual dan daftarkan melalui Dashboard.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
}
?>
