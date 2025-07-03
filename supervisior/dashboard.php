<?php
session_start(); // Memulai sesi

if (!isset($_SESSION['nama']) || !isset($_SESSION['karyawan_divisi_id'])) {
    header("Location: index.php");
    exit();
    
}

include '../config.php'; // Koneksi ke database


$loggedInUser = isset($userData['nama']) ? $userData['nama'] : 'Pengguna';
$userPhoto = 'https://via.placeholder.com/150/000000/FFFFFF/?text=User';

// Sample data for placeholders
$data = [
    'totalInterns' => '10',    // Replace with actual data
    'totalAbsensi' => '25',    // Replace with actual data
    'totalTugas' => '30',      // Replace with actual data
    'totalLaporan' => '5'      // Replace with actual data
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .info-box {
            border-radius: .5rem;
            box-shadow: 0 4px 8px rgba(0,0,0,.2);
            margin-bottom: 1rem;
            transition: transform 0.3s ease-in-out;
        }
        .info-box:hover {
            transform: scale(1.05);
            cursor: pointer;
        }
        .info-box-icon {
            color: #fff;
            text-align: center;
            line-height: 4rem;
            font-size: 2.5rem;
            width: 4rem;
            height: 4rem;
            border-radius: .5rem;
            display: inline-block;
            float: left;
            transition: background-color 0.3s ease-in-out;
        }
        .info-box.bg-info .info-box-icon {
            background-color: #17a2b8;
        }
        .info-box.bg-success .info-box-icon {
            background-color: #28a745;
        }
        .info-box.bg-warning .info-box-icon {
            background-color: #ffc107;
        }
        .info-box.bg-danger .info-box-icon {
            background-color: #dc3545;
        }
        .info-box:hover .info-box-icon {
            background-color: rgba(0,0,0,0.1);
        }
        .info-box-content {
            padding: 1rem;
            background: #fff;
            border-radius: .5rem;
        }
        .info-box-text {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: .5rem;
        }
        .info-box-number {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
        }
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
        .main-header .navbar-nav .nav-link,
        .brand-link {
            color: #ffffff;
            padding: 10px;
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

        .breadcrumb {
            font-size: 0.875px;
            margin-bottom: 0;
        }
        .breadcrumb-item a {
            font-size: 18px;
        }
        .breadcrumb-item.active {
            font-size: 18px;
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
                    <li class="nav-item">
                        <a href="buat_laporan.php" class="nav-link">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>Laporan</p>
                        </a>
                    </li>
                   
                        </a>
                    </li>
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
    <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Daftar Anak Magang</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Daftar Anak Magang</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <a href="anak_magang.php" style="text-decoration:none;">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Anak Magang</span>
                                    <span class="info-box-number"><?php echo $data['totalInterns']; ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-6">
                        <a href="absensi.php" style="text-decoration:none;">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-calendar-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Absensi</span>
                                    <span class="info-box-number"><?php echo $data['totalAbsensi']; ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-6">
                        <a href="tugas.php" style="text-decoration:none;">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-tasks"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Tugas</span>
                                    <span class="info-box-number"><?php echo $data['totalTugas']; ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <!-- <div class="col-lg-3 col-6">
                        <a href="buat_laporan.php" style="text-decoration:none;">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon"><i class="fas fa-file-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Laporan</span>
                                    <span class="info-box-number"><?php echo $data['totalLaporan']; ?></span>
                                </div>
                            </div>
                        </a>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-wrapper -->

    <!-- Footer -->
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

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
</body>
</html>
