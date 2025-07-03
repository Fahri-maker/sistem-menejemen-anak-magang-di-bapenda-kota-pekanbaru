<?php
session_start(); // Memulai sesi

if (!isset($_SESSION['nama']) || !isset($_SESSION['karyawan_divisi_id'])) {
    header("Location: index.php");
    exit();
}

include '../config.php'; // Koneksi ke database


$loggedInUser = isset($userData['nama']) ? $userData['nama'] : 'Pengguna';
$userPhoto = 'https://via.placeholder.com/150/000000/FFFFFF/?text=User';

// Ambil id supervisor dari session
$supervisor_id = $_SESSION['karyawan_divisi_id'] ?? 0;

// Query untuk mendapatkan anak magang yang diawasi oleh supervisor
$stmt = $pdo->prepare("
    SELECT nim_nisn, nama, alamat, asal_kampus_sekolah, masa_magang, tanggal_mulai, tanggal_selesai 
    FROM anak_magang 
    WHERE supervisor_id = :supervisor_id
");
$stmt->execute(['supervisor_id' => $supervisor_id]);
$anak_magang = $stmt->fetchAll();
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
        .breadcrumb-item a {
            font-size: 18px;
        }
        .breadcrumb-item.active {
            font-size: 18px;
        }
        /* CSS untuk tabel */
            .table {
                border-collapse: collapse; /* Menghilangkan jarak antar border */
            }

            .table th, .table td {
                border: 1px solid #dee2e6; /* Warna border tabel */
                padding: 8px; /* Ruang dalam sel tabel */
            }

            .table th {
                background-color: #007BFF; /* Warna latar belakang header tabel */
                color: #ffffff; /* Warna teks header tabel */
            }

            .table td {
                background-color: #f8f9fa; /* Warna latar belakang sel tabel */
                color: #000000; /* Warna teks sel tabel */
            }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f0f4f7;
        }
        .table-hover tbody tr:hover {
            background-color: #e9ecef;
        }
        .table td, .table th {
            font-size: 14px;
        }
        .btn-blue {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
        }
        .btn-blue:hover {
            background-color: #28a745;
            border-color: #28a745;
        }
        .icon-blue {
            color: white;
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
        .btn-custom-size:hover {
            background-color: #28a745;
        }
        .button-wrapper {
            margin-top: 13px;
            position: relative;
            top: -13px;
        }

        .action-icons {
    display: flex;
    flex-direction: column; /* Menyusun ikon secara vertikal */
    gap: 10px; /* Jarak antar ikon */
}

.action-icons a {
    text-decoration: none;
    color: #007bff; /* Warna teks tautan */
    display: flex;
    align-items: center;
}

.action-icons a i {
    margin-right: 5px; /* Jarak antara ikon dan teks */
}

.action-icons a:hover {
    color: #0056b3; /* Warna teks saat hover */
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
                    <li class="nav-item">
                        <a href="buat_laporan.php" class="nav-link">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>Laporan</p>
                        </a>
                    </li>
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

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Daftar Anak Magang</h3>
                                
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <table id="anak_magang_table" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nama</th>
                                            <th>NIM/NISN</th>
                                            <th>Alamat</th>
                                            <th>Asal Kampus/Sekolah</th>
                                            <th>Masa Magang</th>
                                            <th>Tanggal Mulai</th>
                                            <th>Tanggal Selesai</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($anak_magang as $magang): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($magang['nama']); ?></td>
                                                <td><?php echo htmlspecialchars($magang['nim_nisn']); ?></td>
                                                <td><?php echo htmlspecialchars($magang['alamat']); ?></td>
                                                <td><?php echo htmlspecialchars($magang['asal_kampus_sekolah']); ?></td>
                                                <td><?php echo htmlspecialchars($magang['masa_magang']); ?></td>
                                                <td><?php echo htmlspecialchars($magang['tanggal_mulai']); ?></td>
                                                <td><?php echo htmlspecialchars($magang['tanggal_selesai']); ?></td>
                                                <td>
                                                    <a href="detail_anak_magang.php?nim_nisn=<?= urlencode($magang['nim_nisn']) ?>" class="btn btn-info btn-sm d-block mb-1" title="Detail">
                                                        <i class="fas fa-info-circle"></i> <!-- Ikon Detail -->
                                                    </a>
                                                    <a href="edit_anak_magang.php?nim_nisn=<?= urlencode($magang['nim_nisn']) ?>" class="btn btn-warning btn-sm d-block mb-1" title="Edit">
                                                        <i class="fas fa-edit"></i> <!-- Ikon Edit -->
                                                    </a>
                                                </td>

                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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
