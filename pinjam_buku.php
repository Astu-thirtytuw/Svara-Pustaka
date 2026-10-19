<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nis = $_POST['nis'];
    $id_barcode = $_POST['id_barcode'];
    $tgl_pinjam = date('Y-m-d'); 

    $query = "INSERT INTO peminjaman (nis, id_barcode, tgl_pinjam, status) VALUES ('$nis', '$id_barcode', '$tgl_pinjam', 'Dipinjam')";
    
    if (mysqli_query($koneksi, $query)) {
        echo "Success";
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
}
?>
