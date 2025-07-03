<?php
session_start(); // Memulai sesi

if (!isset($_SESSION['nama']) || !isset($_SESSION['karyawan_divisi_id'])) {
    header("Location: index.php");
    exit();
}

include '../config.php'; // Koneksi ke database

$loggedInUser = isset($userData['nama']) ? $userData['nama'] : 'Pengguna';
$userPhoto = 'https://via.placeholder.com/150/000000/FFFFFF/?text=User';

// Mengambil karyawan_divisi_id dari sesi
$karyawan_divisi_id = $_SESSION['karyawan_divisi_id'];

// Ambil semua tugas yang sudah dibuat oleh supervisor yang sedang login
$tugas_query = $pdo->prepare(" SELECT t.*, 
           am.nama AS nama_anak_magang, 
           COALESCE(pt.status, 'Belum Dinilai') AS status, 
           COALESCE(pt.nilai, 'Belum Dinilai') AS nilai,
           pt.file_tugas
    FROM tugas t
    JOIN anak_magang am ON t.anak_magang_id = am.nim_nisn
    LEFT JOIN penilaian_tugas pt ON t.id = pt.tugas_id
    WHERE t.karyawan_divisi_id = ?
");

$tugas_query->execute([$karyawan_divisi_id]);
$daftar_tugas = $tugas_query->fetchAll();

// Logika untuk menilai tugas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nilai'])) {
    $tugas_id = $_POST['tugas_id'];
    $nilai = $_POST['nilai'];
    $status = 'Selesai';

    // Memperbarui penilaian tugas
    $sql = "UPDATE penilaian_tugas SET status=?, nilai=? WHERE tugas_id=?";
    $pdo->prepare($sql)->execute([$status, $nilai, $tugas_id]);

    // $stmt = $pdo->prepare("INSERT INTO penilaian_tugas (tugas_id, status, nilai) VALUES (?, ?, ?) 
    //                        ON DUPLICATE KEY UPDATE status = VALUES(status), nilai = VALUES(nilai)");
    // $stmt->execute([$tugas_id, $status, $nilai]);

    // Memperbarui hasil tugas di tabel tugas
    $stmt = $pdo->prepare("UPDATE tugas SET hasil = 'Tercapai' WHERE id = ?");
    $stmt->execute([$tugas_id]);

    echo "<script>alert('Tugas berhasil dinilai.!'); window.location.href='tugas.php';</script>";
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">

    <style>
    body {
    background-color: #f4f6f9;
}

.main-header, .brand-link {
    height: 60px;
    line-height: 60px;
}

.brand-link img {
    max-height: 100%;
    vertical-align: middle;
}

.main-header {
    background-color: #007bff;
}

.main-header .navbar-nav .nav-link {
    color: #ffffff;
    line-height: 60px;
}

.main-header .navbar-nav .nav-link:hover,
.main-header .navbar-nav .nav-link:focus {
    color: #ffffff;
    background-color: transparent;
    border: none;
}

.main-header .navbar-nav .nav-item.dropdown .nav-link {
    color: #ffffff !important;
    display: flex;
    align-items: center;
}

.main-header .navbar-nav .nav-item.dropdown .nav-link img {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    margin-right: 8px;
}

.brand-link {
    background-color: #007bff;
    color: #ffffff;
    padding: 10px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
}

#logo {
    width: 100px;
    height: auto;
}

.main-sidebar {
    background-color: #f0f4f7;
    color: #000000;
    width: 250px;
}

.nav-link {
    color: #000000 !important;
}

.nav-link.active {
    background-color: #e9ecef;
    color: #007bff !important;
}

.nav-link:hover,
.nav-link:focus {
    background-color: #f0f0f0;
    color: #007bff !important;
}

.nav-icon {
    color: #007bff;
}

.nav-link.active .nav-icon,
.nav-link:hover .nav-icon {
    color: #007bff;
}

.content-wrapper {
    overflow-y: auto;
    background-color: #f0f4f7;
    padding: 1px;
}

.content-header {
    border-bottom: 1.2px solid #dee2e6;
}

.content-header .container-fluid {
    padding: 0px 8px;
}

.content-header h1 {
    font-size: 20px;
    margin-bottom: 0.8px;
}

.breadcrumb {
    font-size: 0.875px;
    margin-bottom: 0;
}

.breadcrumb-item a,
.breadcrumb-item.active {
    font-size: 18px;
}

/* Styling untuk kontainer tabel */
.table-container {
    display: flex;
    justify-content: center;
    width: 100%;
    max-width: 1200px; /* Batas maksimal lebar kontainer */
    margin-top: 10px;  /* Mengurangi jarak vertikal di atas tabel */
}

/* Styling untuk tabel */
.table {
    width: 100%; /* Tabel akan mengikuti lebar kontainer */
    max-width: 100%; /* Batas lebar maksimal tabel */
    border-collapse: collapse;
    text-align: center;
    vertical-align: middle;
}

/* Kolom 1 */
.table th:nth-child(1),
.table td:nth-child(1) {
    width: 10%; /* Atur lebar kolom pertama */
}

/* Kolom 2 */
.table th:nth-child(2),
.table td:nth-child(2) {
    width: 20%; /* Atur lebar kolom kedua */
}

/* Kolom 3 */
.table th:nth-child(3),
.table td:nth-child(3) {
    width: 15%; /* Atur lebar kolom ketiga */
}

/* Kolom 4 */
.table th:nth-child(4),
.table td:nth-child(4) {
    width: 10%; /* Atur lebar kolom keempat */
}

/* Kolom 5 */
.table th:nth-child(5),
.table td:nth-child(5) {
    width: 10%; /* Atur lebar kolom kelima */
}

/* Kolom 6 */
.table th:nth-child(6),
.table td:nth-child(6) {
    width: 10%; /* Atur lebar kolom keenam */
}

/* Kolom 7 */
.table th:nth-child(7),
.table td:nth-child(7) {
    width: 10%; /* Atur lebar kolom ketujuh */
}

/* Kolom 8 */
.table th:nth-child(8),
.table td:nth-child(8) {
    width: 15%; /* Atur lebar kolom kedelapan */
}

/* Style untuk kolom dan header */
.table td, .table th {
    border: 1px solid #dee2e6;
    padding: 8px;
    font-size: 14px;
    word-wrap: break-word; /* Membungkus kata jika terlalu panjang */
    word-break: break-word; /* Memutus kata jika terlalu panjang */
    white-space: normal; /* Izinkan teks membungkus ke baris berikutnya */
}

/* Style untuk header tabel */
.table th {
    background-color: #007BFF !important;
    color: white !important;
}

/* Style untuk sel tabel */
.table td {
    background-color: #f0f4f7;
    color: black;
}

/* Style untuk tabel strip */
.table-striped tbody tr:nth-of-type(odd) {
    background-color: #f0f4f7;
}

/* Hover effect */
.table-hover tbody tr:hover {
    background-color: #e9ecef;
}


/* Button styling */
.btn-blue {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

.btn-blue:hover,
.btn-blue:focus {
    background-color: #28a745;
}

.btn-custom-size {
    position: absolute;
    right: 0;
    top: -28px;
    z-index: 20;
    width: 120px;
    height: 35px;
    font-size: 16px;
    padding: 0.5px 1px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 3px;
}

.action-icons a {
    text-decoration: none;
    color: #007bff;
    display: flex;
    align-items: center;
}

.action-icons a i {
    margin-right: 5px;
}

.action-icons a:hover {
    color: #0056b3;
}

/* Centered buttons */
.centered-buttons {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
}

.btn-group-vertical .btn {
    width: 35px;
    height: 35px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0px; /* Mengatur sudut tombol */
}

.btn i {
    font-size: 17px;
}

.card-body {
    margin: 5px; /* Jarak di sekeliling .card-body */
    padding: 5px; /* Jarak di dalam .card-body */
}

</style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-dark">
        <ul class="navbar-nav">
            <!-- <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
            </li> -->
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <img id="profile" src="<?php echo $userPhoto; ?>" alt="profile"> <?php echo htmlspecialchars($_SESSION['nama']); ?>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                    <a class="dropdown-item" href="#">Profil</a>
                    <a class="dropdown-item" href="#">Pengaturan</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="logout.php">Keluar</a>
                </div>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="#" class="brand-link">
            <img id="logo" src="../image/logo3.png" alt="Logo">
        </a>
        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    
                     <li class="nav-item">
                        <a href="Dashboard.php" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="anak_magang.php" class="nav-link">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Anak Magang</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="absensi.php" class="nav-link">
                            <i class="nav-icon fas fa-calendar-check"></i>
                            <p>Absensi</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="tugas.php" class="nav-link">
                            <i class="nav-icon fas fa-tasks"></i>
                            <p>Tugas</p>
                        </a>
                    </li>
                    <!-- <li class="nav-item">
                        <a href="buat_laporan.php" class="nav-link">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>Laporan</p>
                        </a>
                    </li> -->
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>
    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Tugas Anak Magang</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Tugas</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>

        <section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Daftar Tugas</h3>
                        <a href="tambah_tugas.php" class="btn btn-primary" style="margin-left:auto;">
                            <i class="fas fa-plus"></i> Tambah Tugas 
                        </a>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                    <div class="card-body">
    <table id="anak_magang_table" class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Nama Tugas</th>
                <th>Uraian Tugas</th>
                <th>Tenggat Pengumpulan</th>
                <th>Anak Magang</th>
                <th>Status</th>
                <th>Nilai</th>
                <th>File Tugas</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($daftar_tugas as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['nama_tugas']) ?></td>
                    <td><?= htmlspecialchars($t['uraian_tugas']) ?></td>
                    <td><?= htmlspecialchars($t['tenggat_pengumpulan']) ?></td>
                    <td><?= htmlspecialchars($t['nama_anak_magang']) ?></td>
                    <td><?= htmlspecialchars($t['status']) ?></td>
                    <td><?= htmlspecialchars($t['nilai']) ?></td>
                    <td>
                        <?php if ($t['file_tugas']): ?>
                            <a href="../file_tugas/<?= htmlspecialchars($t['file_tugas']) ?>" target="_blank">Lihat</a>
                        <?php else: ?>
                            Belum Diunggah
                        <?php endif; ?>
                    </td>
                    <td>
                    <div class="btn-group-vertical">
    <button class="btn btn-primary btn-sm mb-2" onclick="openNilaiForm(<?= htmlspecialchars($t['id']) ?>)">
        <i class="fas fa-clipboard-check"></i> 
    </button>
    
    <button class="btn btn-warning btn-sm mb-2" 
        onclick="openEditModal(<?= htmlspecialchars($t['id']) ?>, 
                            '<?= htmlspecialchars(addslashes($t['nama_tugas'])) ?>', 
                            '<?= htmlspecialchars(addslashes($t['uraian_tugas'])) ?>', 
                            '<?= htmlspecialchars(addslashes($t['target'])) ?>', 
                            '<?= htmlspecialchars($t['tenggat_pengumpulan']) ?>')">
        <i class="fas fa-edit"></i> 
    </button>

    <a href="hapus_tugas.php?id=<?= htmlspecialchars($t['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus tugas ini?')">
        <i class="fas fa-trash-alt"></i> 
    </a>
</div>

                    </td>




                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Pop-up Form Nilai -->
<div id="nilaiModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Masukkan Nilai</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" action="">
                    <input type="hidden" name="tugas_id" id="nilaiTugasId">
                    <div class="form-group">
                        <label for="nilai">Nilai:</label>
                        <input type="number" name="nilai" min="0" max="100" required placeholder="Nilai" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-success">Simpan Nilai</button>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- Modal Edit Form -->
<div id="editModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Tugas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" action="edit_tugas.php">
                    <input type="hidden" name="id" id="editTugasId">
                    
                    <div class="form-group">
                        <label>Nama Tugas</label>
                        <input type="text" name="nama_tugas" id="editNamaTugas" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Uraian Tugas</label>
                        <textarea name="uraian_tugas" id="editUraianTugas" class="form-control" required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Target</label>
                        <input type="text" name="target" id="editTarget" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Tenggat Pengumpulan</label>
                        <input type="date" name="tenggat_pengumpulan" id="editTenggatPengumpulan" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Tugas</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Kembali</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
function openNilaiForm(tugasId) {
    // Set tugas_id ke dalam form
    document.getElementById('nilaiTugasId').value = tugasId;
    // Tampilkan modal
    $('#nilaiModal').modal('show');
}




function openEditModal(tugasId, namaTugas, uraianTugas, target, tenggatPengumpulan) {
    // Set nilai pada form modal
    document.getElementById('editTugasId').value = tugasId;
    document.getElementById('editNamaTugas').value = namaTugas;
    document.getElementById('editUraianTugas').value = uraianTugas;
    document.getElementById('editTarget').value = target;
    document.getElementById('editTenggatPengumpulan').value = tenggatPengumpulan;
    
    // Tampilkan modal
    $('#editModal').modal('show');
}


</script>

</div>

                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

    </div>
    <!-- /.content-wrapper -->

    <!-- Main Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="float-right d-none d-sm-inline">
                Sistem Manajemen Magang
            </div>
            <strong>&copy; 2024 <a href="#">Afrizal Group Corporation</a>.</strong> All rights reserved.
        </div>
    </footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
</body>
</html>
