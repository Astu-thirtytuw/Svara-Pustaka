<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

include 'koneksi.php';

$nis = "";
if (isset($_POST['nis'])) {
    $nis = trim($_POST['nis']);
} elseif (isset($_GET['nis'])) {
    $nis = trim($_GET['nis']);
}

if (!empty($nis)) {
    $query = "SELECT b.judul_buku, p.tgl_pinjam FROM peminjaman p 
              JOIN buku b ON p.id_barcode = b.id_barcode 
              WHERE p.nis = '$nis' AND p.status = 'Dipinjam' 
              ORDER BY p.id_pinjam DESC";
              
    $result = mysqli_query($koneksi, $query);
    $data_buku = array();

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data_buku[] = $row['judul_buku'] . " (" . $row['tgl_pinjam'] . ")";
        }
        echo implode(",", $data_buku);
    } else {
        echo "Kosong";
    }
} else {
    echo "Kosong";
}
?>