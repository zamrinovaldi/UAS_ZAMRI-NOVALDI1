<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (isset($data->credential_id)) {
    $id = $_SESSION['user']['id_user'];
    $cred_id = mysqli_real_escape_string($koneksi, $data->credential_id);
    
    $query = "UPDATE users SET credential_id='$cred_id' WHERE id_user='$id'";
    if (mysqli_query($koneksi, $query)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
}
?>
