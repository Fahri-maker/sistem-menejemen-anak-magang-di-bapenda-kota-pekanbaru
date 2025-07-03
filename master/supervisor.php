<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: index.php");
    exit();
}
include "../config.php";

$userId = $_SESSION['user_id'];

try {
    $query = "SELECT nama FROM akun_master WHERE id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    $loggedInUser = isset($userData['nama']) ? $userData['nama'] : 'Pengguna';
    $userPhoto = 'https://via.placeholder.com/150/000000/FFFFFF/?text=User';
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Ambil data divisi dari tabel divisi
$divisi = $pdo->query("SELECT * FROM divisi")->fetchAll();

// Inisialisasi variabel untuk menyimpan data karyawan
$karyawan = [];

// Jika ada divisi_id yang dipilih
if (isset($_POST['divisi_id']) && !empty($_POST['divisi_id'])) {
    $divisi_id = $_POST['divisi_id'];

    // Ambil data karyawan berdasarkan divisi_id dari tabel karyawan_divisi
    $stmt = $pdo->prepare("
        SELECT id, nama, nip, jabatan 
        FROM karyawan_divisi 
        WHERE divisi_id = ?
    ");
    $stmt->execute([$divisi_id]);
    $karyawan = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Supervisor</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* Styling untuk header, sidebar, dan konten */
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
        .main-header .navbar-nav .nav-link:hover {
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
            padding-top: 10px;
            padding-bottom: 10px;
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
        }
        .nav-link {
            color: #000000 !important;
        }
        .nav-link.active {
            background-color: #e9ecef;
            color: #007bff !important;
        }
        .nav-link:hover {
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
            overflow-y: auto; /* Hanya konten yang bisa digulir */
            background-color: #f0f4f7;
        }
        .content-header {
            border-bottom: 1px solid #dee2e6;
        }
        .table-responsive .table {
            color: #212529; /* Warna teks tabel, sesuaikan dengan warna tema Anda */
            background-color: #ffffff; /* Warna latar belakang tabel, sesuaikan dengan tema Anda */
        }
        .table-responsive .thead-dark th {
            background-color: #007bff; /* Warna latar belakang header tabel */
            color: #ffffff; /* Warna teks header tabel */
        }
        .table-responsive .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f8f9fa; /* Warna latar belakang baris genap */
        }
        .table-responsive .table-hover tbody tr:hover {
            background-color: #e9ecef; /* Warna latar belakang baris saat dihover */
        }
/* pengaturan tombol */
        .card-tools {
    float: right;
}

/* Flexbox untuk form */
.d-flex {
    display: flex;
    align-items: center; /* Vertikal center jika perlu */
}

.form-group {
    margin-bottom: 0px; /* Menghapus margin bawah form-group */
}

/* Lebar dropdown */
.form-group select {
    width: 200px; /* Sesuaikan dengan lebar yang diinginkan */
}

.mr-2 {
    margin-right: 10px; /* Menambahkan jarak di sebelah kanan dropdown */
}

/* Mengatur tampilan tombol vertikal */
.action-buttons {
    display: flex;
    flex-direction: column; /* Menampilkan tombol secara vertikal */
    align-items: flex-start; /* Menyelaraskan tombol ke kiri */
}

/* Mengatur ukuran ikon di tombol */
.btn i {
    font-size: 16px; /* Ukuran ikon */
    margin-right: 8px; /* Jarak antara ikon dan teks */
}

/* Menyelaraskan teks tombol dan ikon */
.btn {
    display: flex;
    align-items: center; /* Vertikal center untuk teks dan ikon */
    padding: 8px 12px; /* Jarak di dalam tombol */
    font-size: 20px; /* Ukuran font untuk teks tombol */
    border-radius: 4px; /* Sudut tombol melengkung */
    text-decoration: none; /* Menghapus garis bawah pada link tombol */
}

/* Menambahkan jarak antara tombol */
.action-buttons .btn {
    margin-bottom: 10px; /* Jarak antara tombol */
}

/* Warna dan efek hover untuk tombol */
.btn-warning {
    background-color: #ffc107; /* Warna tombol untuk Edit */
    color: #212529;
}

.btn-warning:hover {
    background-color: #e0a800; /* Warna tombol saat hover */
}

.btn-danger {
    background-color: #dc3545; /* Warna tombol untuk Hapus */
    color: white;
}

.btn-danger:hover {
    background-color: #c82333; /* Warna tombol saat hover */
}

.btn-info {
    background-color: #007bff; /* Warna tombol untuk Buat Akun */
    color: white;
}

.btn-info:hover {
    background-color: #138496; /* Warna tombol saat hover */
}




//* Mengatur ukuran ikon di tombol */
.btn i {
    font-size: 16px; /* Ukuran ikon */
}

/* Mengatur padding tombol */
.btn {
    padding: 5px 10px; /* Jarak di dalam tombol */
}

/* Mengatur ukuran heading di content header */
.content-header h1 {
    font-size: 24px; /* Ukuran heading lebih besar */
}

/* Mengatur ukuran font breadcrumb */
.breadcrumb {
    font-size: 16px; /* Ukuran font breadcrumb */
}

/* Mengatur ukuran dan margin card */
.card {
    max-width: 900px; /* Lebar card lebih besar */
    margin: 10px auto; /* Jarak margin atas dan bawah */
}

/* Mengatur padding pada card header */
.card-header {
    padding: 10px 20px; /* Padding lebih besar untuk card header */
}

/* Mengatur ukuran font card title */
.card-title {
    font-size: 20px; /* Ukuran font title card lebih besar */
}

/* Mengatur padding pada card body */
.card-body {
    padding: 15px; /* Padding yang lebih besar untuk kenyamanan */
}

/* Mengatur margin bawah form-group */
.form-group {
    margin-bottom: 0px; /* Memberikan ruang lebih antara elemen form */
}

/* Mengatur ukuran font dan padding form control */
.form-control {
    font-size: 16px; /* Ukuran font form control */
    padding: 8px; /* Padding yang lebih besar untuk form control */
}

/* Mengatur tampilan tombol */
.btn-primary {
    position: relative;
    right: 0; /* Posisi tombol di sebelah kanan kontainer */
    top: 0; /* Posisi tombol sejajar dengan elemen lain */
    z-index: 20; /* Menjaga tombol tetap di atas elemen lain */
    width: 120px; /* Lebar tombol */
    height: 36px; /* Tinggi tombol */
    font-size: 16px; /* Ukuran font untuk teks tombol */
    padding: 8px 25px; /* Padding yang lebih nyaman di dalam tombol */
    background-color: #007BFF; /* Warna tombol */
    color: white; /* Warna teks tombol */
    border: none; /* Menghapus border default tombol */
    border-radius: 4px; /* Membuat sudut tombol melengkung */
}



/* tabel */

/* Mengatur ukuran heading di card header */
/* Mengatur ukuran font hanya untuk judul tabel */
.card .card-header h3.card-title {
    font-size: 20px; /* Ukuran font untuk judul tabel */
    margin-bottom: 0;
}



/* Mengatur ukuran dan posisi tombol tambah data */
.card-tools .btn {
    font-size: 16px; /* Ukuran font pada tombol */
    padding: 8px 12px; /* Padding tombol */
    border-radius: 4px; /* Sudut tombol melengkung */
}

/* Mengatur ukuran tabel */
.table {
    font-size: 16px; /* Ukuran font dalam tabel */
}

.table th, .table td {
    padding:10px; /* Padding untuk sel tabel */
    vertical-align: middle; /* Mengatur vertikal-align agar teks berada di tengah */
}

/* Mengatur ukuran ikon pada tombol aksi */
.btn-sm i {
    font-size: 15px; /* Ukuran ikon lebih kecil */
}

/* Mengatur ukuran tombol aksi */
.action-buttons .btn {
    font-size: 14px; /* Ukuran font pada tombol aksi */
    padding: 6px 10px; /* Padding tombol aksi */
    border-radius: 3px; /* Sudut melengkung tombol aksi */
    margin-right: 5px; /* Memberikan jarak antar tombol aksi */
}



/* Mengatur ukuran font dalam tabel */
/* Mengatur lebar tabel dan kolom */
.table {
    table-layout: fixed; /* Memastikan lebar kolom sesuai dengan lebar yang ditetapkan */
    width: 100%; /* Tabel menggunakan lebar penuh dari kontainer */
}

.table th, .table td {
     text-align: center; /* Menyelaraskan teks ke tengah horizontal */
    vertical-align: middle; /* Menyelaraskan teks ke tengah vertikal padding: 6px; /* Padding dalam sel tabel */
    border: 1px solid #dee2e6; /* Border untuk sel tabel */
    box-sizing: border-box; /* Memastikan padding dan border termasuk dalam lebar kolom */
    max-height: 130px; /* Batasi tinggi kolom */
    overflow-y: auto; /* Tampilkan scrollbar vertikal jika konten melebihi batas */
    white-space: nowrap; /* Mencegah teks membungkus */
}


.table th:nth-child(1),
.table td:nth-child(1) {
    width: 10%; /* Lebar kolom No */
}

.table th:nth-child(2),
.table td:nth-child(2) {
    width: 25%; /* Lebar kolom Nama */
}

.table th:nth-child(3),
.table td:nth-child(3) {
    width: 20%; /* Lebar kolom NIP */
}

.table th:nth-child(4),
.table td:nth-child(4) {
    width: 30%; /* Lebar kolom Jabatan */
}

.table th:nth-child(5),
.table th:nth-child(5) {
    position: relative; /* Menetapkan posisi relatif untuk header */
    padding: 8px; /* Padding yang sama dengan data */
    
    
}


/* Mengatur ukuran ikon pada tombol aksi */
.btn-sm i {
    font-size: 14px; /* Ukuran ikon lebih kecil */
}

/* Mengatur ukuran tombol aksi */
.action-buttons .btn {
    font-size: 14px; /* Ukuran font pada tombol aksi */
    padding: 5px 8px; /* Padding tombol aksi */
    border-radius: 3px; /* Sudut melengkung tombol aksi */
    margin-right: 5px; /* Jarak antar tombol aksi */
   
}

.card-tools {
    float: right; /* Mengatur tombol ke sisi kanan header */
}

.card-tools .btn-success {
    font-size: 14px; /* Ukuran font tombol */
    padding: 6px 10px; /* Padding di dalam tombol */
    border-radius: 5px; /* Sudut melengkung tombol */
    margin: 0px; /* Margin tombol untuk jarak */
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
                    <img id="profile" src="<?php echo $userPhoto; ?>" alt="profile"> <?php echo htmlspecialchars($loggedInUser); ?>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                    <a class="dropdown-item" href="#">Profile</a>
                    <a class="dropdown-item" href="#">Settings</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="logout.php">Logout</a>
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
                        <a href="dashboard.php" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="supervisor.php" class="nav-link">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Supervisor</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="anak_magang.php" class="nav-link">
                            <i class="nav-icon fas fa-paper-plane"></i>
                            <p>Anak Magang</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="manage.php" class="nav-link">
                            <i class="nav-icon fas fa-calendar-alt"></i>
                            <p>Data Magang</p>
                        </a>
                    </li>
                    <!-- <li class="nav-item">
                        <a href="laporan.php" class="nav-link">
                            <i class="nav-icon fas fa-user-plus"></i>
                            <p>Laporan</p>
                        </a>
                    </li> -->
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Data Supervisor</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Data Supervisor</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Form untuk memilih divisi -->
                <div class="card">
    <div class="card-header">
        <h3 class="card-title">Filter Supervisor Berdasarkan Divisi</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="" class="d-flex align-items-center">
            <div class="form-group mr-2">
                <label for="divisi_id" class="sr-only">Pilih Divisi:</label>
                <select id="divisi_id" name="divisi_id" class="form-control" required>
                    <option value="">Pilih Divisi</option>
                    <?php foreach ($divisi as $div): ?>
                        <option value="<?php echo htmlspecialchars($div['id']); ?>">
                            <?php echo htmlspecialchars($div['nama_divisi']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Tampilkan </button>
        </form>
    </div>
</div>


                  

                <!-- Tabel Supervisor -->
<div class="card">
<div class="card-header">
    <h3 class="card-title table-title">Daftar Supervisor</h3>
    <!-- Tombol Tambah Data -->
    <div class="card-tools">
    <a href="add_karyawan.php" class="btn btn-primary">Tambah Data</a>
</div>

</div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>NIP</th>
                    <th>Jabatan</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (count($karyawan) > 0): ?>
                    <?php foreach ($karyawan as $index => $row): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                            <td><?php echo htmlspecialchars($row['nip']); ?></td>
                            <td><?php echo htmlspecialchars($row['jabatan']); ?></td>
                            <td class="action-buttons">
                            <a href="create_supervisor.php?nip=<?php echo htmlspecialchars($row['nip']); ?>&divisi_id=<?php echo htmlspecialchars($divisi_id); ?>" class="btn btn-info btn-sm" title="Buat Akun">
                                    <i class="fas fa-user-plus"></i> 
                                </a>
                                <a href="edit_supervisor.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i> 
                                </a>
                                <a href="hapus_supervisor.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus supervisor ini?');">
                                    <i class="fas fa-trash-alt"></i> 
                                </a>
                                
                            </td>


                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data supervisor.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

            </div>
        </section>
    </div>
    <!-- /.content-wrapper -->
     <!-- Footer -->
     <footer class="main-footer">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <strong>&copy; 2024 <a href="#">Afrizal Group Corporation</a>.</strong> All rights reserved.
                </div>
            </div>
        </div>
    </footer>
    <!-- /.footer -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
</body>
</html>
