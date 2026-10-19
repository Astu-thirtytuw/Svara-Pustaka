<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nis = trim($_POST['nis']);
    $password = trim($_POST['password']);

    $query = "SELECT * FROM siswa WHERE nis = '$nis' AND password = '$password'";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) > 0) {
        $siswa = mysqli_fetch_assoc($result);
        echo "Success#" . $siswa['nama']; 
    } else {
        echo "Failed. Server menerima NIS: [" . $nis . "] dan Password: [" . $password . "]";
    }
}
?>
