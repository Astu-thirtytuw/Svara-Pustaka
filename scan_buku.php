<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_barcode = isset($_POST['id_barcode']) ? trim($_POST['id_barcode']) : '';

    if (empty($id_barcode)) {
        echo "Not Found";
        exit;
    }

    $query = "SELECT * FROM buku WHERE id_barcode = '$id_barcode'";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) > 0) {
        $buku = mysqli_fetch_assoc($result);
        echo "Found#" . $buku['judul_buku'];
    } else {
        echo "Not Found";
    }
} else {
    echo "Metode Harus POST";
}
?>
