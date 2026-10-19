<?php
include 'koneksi.php';

// ==========================================
// 1. LOGIKA PROSES AKSI (PHP)
// ==========================================

// --- AKSI SISWA ---
if (isset($_POST['tambah_siswa'])) {
    $nis      = trim($_POST['nis']);
    $nama     = trim($_POST['nama']);
    $password = trim($_POST['password']);
    if (!empty($nis) && !empty($nama)) {
        $query = "INSERT INTO siswa (nis, nama, password) VALUES ('$nis', '$nama', '$password')";
        mysqli_query($koneksi, $query);
    }
    header("Location: admin.php?tab=siswa");
    exit();
}

if (isset($_POST['edit_siswa'])) {
    $nis      = trim($_POST['nis']);
    $nama     = trim($_POST['nama']);
    $password = trim($_POST['password']);
    $query = "UPDATE siswa SET nama='$nama', password='$password' WHERE nis='$nis'";
    mysqli_query($koneksi, $query);
    header("Location: admin.php?tab=siswa");
    exit();
}

if (isset($_GET['hapus_siswa'])) {
    $nis = $_GET['hapus_siswa'];
    mysqli_query($koneksi, "DELETE FROM siswa WHERE nis='$nis'");
    header("Location: admin.php?tab=siswa");
    exit();
}

// --- AKSI BUKU ---
if (isset($_POST['tambah_buku'])) {
    $id_barcode = trim($_POST['id_barcode']);
    $judul_buku = trim($_POST['judul_buku']);
    if (!empty($id_barcode) && !empty($judul_buku)) {
        $query = "INSERT INTO buku (id_barcode, judul_buku) VALUES ('$id_barcode', '$judul_buku')";
        mysqli_query($koneksi, $query);
    }
    header("Location: admin.php?tab=buku");
    exit();
}

if (isset($_POST['edit_buku'])) {
    $id_barcode = trim($_POST['id_barcode']);
    $judul_buku = trim($_POST['judul_buku']);
    $query = "UPDATE buku SET judul_buku='$judul_buku' WHERE id_barcode='$id_barcode'";
    mysqli_query($koneksi, $query);
    header("Location: admin.php?tab=buku");
    exit();
}

if (isset($_GET['hapus_buku'])) {
    $id_barcode = $_GET['hapus_buku'];
    mysqli_query($koneksi, "DELETE FROM buku WHERE id_barcode='$id_barcode'");
    header("Location: admin.php?tab=buku");
    exit();
}

// --- AKSI PENGEMBALIAN BUKU ---
if (isset($_GET['kembali_id'])) {
    $id_pinjam = $_GET['kembali_id'];
    $tgl_kembali = date('Y-m-d');
    $query = "UPDATE peminjaman SET status = 'Dikembalikan', tgl_kembali = '$tgl_kembali' WHERE id_pinjam = '$id_pinjam'";
    mysqli_query($koneksi, $query);
    header("Location: admin.php?tab=rekap");
    exit();
}

// Menentukan Tab Aktif
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'rekap';
if (isset($_GET['edit_siswa'])) $active_tab = 'siswa';
if (isset($_GET['edit_buku'])) $active_tab = 'buku';

// Hitung Statistik
$tot_pinjam = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE status='Dipinjam'"));
$tot_siswa  = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM siswa"));
$tot_buku   = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM buku"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nitya Sastra Loka - Admin Perpustakaan</title>
    <style>
        :root {
            --primary: #1e88e5;
            --primary-dark: #1565c0;
            --primary-light: #e3f2fd;
            --accent: #0288d1;
            --bg: #f4f7fa;
            --text: #2c3e50;
            --border: #e0e6ed;
            --success: #2e7d32;
            --danger: #d32f2f;
            --warning: #f57c00;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        body { background-color: var(--bg); color: var(--text); padding: 25px; }

        .dashboard-header {
            display: flex; justify-content: space-between; align-items: center;
            background: #ffffff; padding: 20px 30px; border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03); margin-bottom: 25px;
            border-left: 6px solid var(--primary);
        }
        .dashboard-title h1 { font-size: 24px; color: var(--primary-dark); }
        .dashboard-title p { font-size: 13px; color: #7f8c8d; margin-top: 4px; }

        /* Stats Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .stat-card {
            background: #ffffff; padding: 20px; border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02); display: flex; align-items: center; justify-content: space-between;
        }
        .stat-number { font-size: 28px; font-weight: bold; color: var(--primary-dark); }
        .stat-label { font-size: 13px; color: #7f8c8d; }

        /* Navigation Tabs */
        .nav-tabs { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid var(--border); padding-bottom: 10px; }
        .tab-btn {
            padding: 10px 22px; background: transparent; border: none; font-size: 14px; font-weight: 600;
            color: #7f8c8d; cursor: pointer; border-radius: 8px; transition: all 0.2s ease; text-decoration: none;
        }
        .tab-btn.active, .tab-btn:hover { background-color: var(--primary-light); color: var(--primary-dark); }

        /* Content Container */
        .card { background: #ffffff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); margin-bottom: 25px; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .card-title { font-size: 18px; color: var(--primary-dark); }

        /* Form Styling */
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px; }
        .form-group label { display: block; font-size: 12px; font-weight: 600; color: #555; margin-bottom: 6px; }
        .form-control { width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 6px; font-size: 14px; outline: none; }
        .form-control:focus { border-color: var(--primary); }

        /* Table Styling */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: var(--primary-light); color: var(--primary-dark); text-align: left; padding: 12px 15px; font-size: 13px; font-weight: 700; }
        td { padding: 12px 15px; border-bottom: 1px solid var(--border); font-size: 14px; }
        tr:hover { background-color: #fdfdfd; }

        /* Badges & Buttons */
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
        .badge-dipinjam { background-color: #ffebee; color: var(--danger); }
        .badge-dikembalikan { background-color: #e8f5e9; color: var(--success); }

        .btn { padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none; border: none; cursor: pointer; display: inline-block; transition: background 0.2s; }
        .btn-primary { background-color: var(--primary); color: white; }
        .btn-primary:hover { background-color: var(--primary-dark); }
        .btn-success { background-color: #2e7d32; color: white; }
        .btn-success:hover { background-color: #1b5e20; }
        .btn-warning { background-color: var(--warning); color: white; }
        .btn-danger { background-color: var(--danger); color: white; }
        .btn-pdf { background-color: #d32f2f; color: white; }

        /* CSS Khusus Cetak PDF */
        @media print {
            body { background-color: #ffffff; padding: 0; }
            .nav-tabs, .stats-grid, .btn, .dashboard-header { display: none !important; }
            .card { box-shadow: none; border: none; padding: 0; margin: 0; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #000 !important; padding: 8px; color: #000; }
            th { background-color: #f2f2f2 !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="dashboard-header">
        <div class="dashboard-title">
            <h1>Nitya Sastra Loka</h1>
            <p>Panel Kontrol Admin untuk Rekapitulasi, Siswa, dan Buku</p>
        </div>
        <button onclick="window.print()" class="btn btn-pdf">📄 Cetak / Simpan PDF</button>
    </div>

    <!-- Stat Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div>
                <div class="stat-number"><?= $tot_pinjam ?></div>
                <div class="stat-label">Buku Sedang Dipinjam</div>
            </div>
            <div style="font-size: 30px;">📖</div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-number"><?= $tot_siswa ?></div>
                <div class="stat-label">Total Siswa / User</div>
            </div>
            <div style="font-size: 30px;">👨‍🎓</div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-number"><?= $tot_buku ?></div>
                <div class="stat-label">Total Judul Buku</div>
            </div>
            <div style="font-size: 30px;">📚</div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="nav-tabs">
        <a href="admin.php?tab=rekap" class="tab-btn <?= $active_tab == 'rekap' ? 'active' : '' ?>">📋 Rekapitulasi & Pengembalian</a>
        <a href="admin.php?tab=siswa" class="tab-btn <?= $active_tab == 'siswa' ? 'active' : '' ?>">👨‍🎓 Kelola Siswa</a>
        <a href="admin.php?tab=buku" class="tab-btn <?= $active_tab == 'buku' ? 'active' : '' ?>">📚 Kelola Buku</a>
    </div>

<!-- ========================================== -->
<!-- TAB 1: REKAPITULASI & PENGEMBALIAN BUKU    -->
<!-- ========================================== -->
<?php if ($active_tab == 'rekap'): ?>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Daftar Transaksi Peminjaman Buku</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID Pinjam</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Judul Buku</th>
                    <th>Tanggal Pinjam</th>
                    <th>Status</th>
                    <th>Aksi Pengembalian</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT p.id_pinjam, p.nis, s.nama, b.judul_buku, p.tgl_pinjam, p.status 
                          FROM peminjaman p 
                          JOIN siswa s ON p.nis = s.nis 
                          JOIN buku b ON p.id_barcode = b.id_barcode
                          ORDER BY p.id_pinjam DESC";
                $result = mysqli_query($koneksi, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    $badge = ($row['status'] == 'Dipinjam') ? 'badge-dipinjam' : 'badge-dikembalikan';
                    echo "<tr>
                            <td>#{$row['id_pinjam']}</td>
                            <td>{$row['nis']}</td>
                            <td>{$row['nama']}</td>
                            <td>{$row['judul_buku']}</td>
                            <td>{$row['tgl_pinjam']}</td>
                            <td><span class='badge {$badge}'>{$row['status']}</span></td>
                            <td>";
                    if ($row['status'] == 'Dipinjam') {
                        echo "<a href='admin.php?kembali_id={$row['id_pinjam']}' class='btn btn-success' onclick='return confirm(\"Proses pengembalian buku ini?\")'>✔ Proses Kembali</a>";
                    } else {
                        echo "<span style='color: #7f8c8d; font-size:13px;'>Selesai</span>";
                    }
                    echo "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- ========================================== -->
<!-- TAB 2: CRUD SISWA                          -->
<!-- ========================================== -->
<?php if ($active_tab == 'siswa'): ?>
    <?php
    $edit_siswa_data = null;
    if (isset($_GET['edit_siswa'])) {
        $edit_nis = $_GET['edit_siswa'];
        $res = mysqli_query($koneksi, "SELECT * FROM siswa WHERE nis='$edit_nis'");
        $edit_siswa_data = mysqli_fetch_assoc($res);
    }
    ?>
    <div class="card">
        <h2 class="card-title" style="margin-bottom: 15px;"><?= $edit_siswa_data ? 'Edit Data Siswa' : 'Tambah Siswa Baru' ?></h2>
        <form method="POST" action="admin.php">
            <div class="form-grid">
                <div class="form-group">
                    <label>NIS (Nomor Induk Siswa)</label>
                    <input type="text" name="nis" class="form-control" value="<?= $edit_siswa_data['nis'] ?? '' ?>" <?= $edit_siswa_data ? 'readonly' : 'required' ?>>
                </div>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" value="<?= $edit_siswa_data['nama'] ?? '' ?>" required>
                </div>
                <div class="form-group">
                    <label>Password Akun</label>
                    <input type="text" name="password" class="form-control" value="<?= $edit_siswa_data['password'] ?? '' ?>" required>
                </div>
            </div>
            <?php if ($edit_siswa_data): ?>
                <button type="submit" name="edit_siswa" class="btn btn-warning">Update Siswa</button>
                <a href="admin.php?tab=siswa" class="btn btn-danger">Batal</a>
            <?php else: ?>
                <button type="submit" name="tambah_siswa" class="btn btn-primary">Simpan Siswa</button>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <h2 class="card-title" style="margin-bottom: 15px;">Daftar Siswa Terdaftar</h2>
        <table>
            <thead>
                <tr>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Password</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = mysqli_query($koneksi, "SELECT * FROM siswa ORDER BY nis ASC");
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                            <td>{$row['nis']}</td>
                            <td>{$row['nama']}</td>
                            <td>{$row['password']}</td>
                            <td>
                                <a href='admin.php?tab=siswa&edit_siswa={$row['nis']}' class='btn btn-warning'>Edit</a>
                                <a href='admin.php?hapus_siswa={$row['nis']}' class='btn btn-danger' onclick='return confirm(\"Hapus siswa ini?\")'>Hapus</a>
                            </td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- ========================================== -->
<!-- TAB 3: CRUD BUKU                           -->
<!-- ========================================== -->
<?php if ($active_tab == 'buku'): ?>
    <?php
    $edit_buku_data = null;
    if (isset($_GET['edit_buku'])) {
        $edit_id = $_GET['edit_buku'];
        $res = mysqli_query($koneksi, "SELECT * FROM buku WHERE id_barcode='$edit_id'");
        $edit_buku_data = mysqli_fetch_assoc($res);
    }
    ?>
    <div class="card">
        <h2 class="card-title" style="margin-bottom: 15px;"><?= $edit_buku_data ? 'Edit Data Buku' : 'Tambah Buku Baru' ?></h2>
        <form method="POST" action="admin.php">
            <div class="form-grid">
                <div class="form-group">
                    <label>ID Barcode Buku</label>
                    <input type="text" name="id_barcode" class="form-control" value="<?= $edit_buku_data['id_barcode'] ?? '' ?>" <?= $edit_buku_data ? 'readonly' : 'required' ?>>
                </div>
                <div class="form-group">
                    <label>Judul Buku</label>
                    <input type="text" name="judul_buku" class="form-control" value="<?= $edit_buku_data['judul_buku'] ?? '' ?>" required>
                </div>
            </div>
            <?php if ($edit_buku_data): ?>
                <button type="submit" name="edit_buku" class="btn btn-warning">Update Buku</button>
                <a href="admin.php?tab=buku" class="btn btn-danger">Batal</a>
            <?php else: ?>
                <button type="submit" name="tambah_buku" class="btn btn-primary">Simpan Buku</button>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <h2 class="card-title" style="margin-bottom: 15px;">Daftar Katalog Buku</h2>
        <table>
            <thead>
                <tr>
                    <th>ID Barcode</th>
                    <th>Judul Buku</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = mysqli_query($koneksi, "SELECT * FROM buku ORDER BY id_barcode ASC");
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                            <td>{$row['id_barcode']}</td>
                            <td>{$row['judul_buku']}</td>
                            <td>
                                <a href='admin.php?tab=buku&edit_buku={$row['id_barcode']}' class='btn btn-warning'>Edit</a>
                                <a href='admin.php?hapus_buku={$row['id_barcode']}' class='btn btn-danger' onclick='return confirm(\"Hapus buku ini?\")'>Hapus</a>
                            </td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

</body>
</html>