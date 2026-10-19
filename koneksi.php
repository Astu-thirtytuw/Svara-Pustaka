<?php
include 'koneksi.php';

$nis = isset($_POST['nis']) ? trim($_POST['nis']) : '';

if (!empty($nis)) {
    $query = "SELECT * FROM peminjaman WHERE nis = '$nis' ORDER BY id_pinjam DESC";
    
    $result = mysqli_query($koneksi, $query);
    $data = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    echo json_encode($data);
} else {
    echo "NIS Kosong";
}
?>