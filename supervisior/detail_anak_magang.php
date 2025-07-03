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
$nim_nisn = $_GET['nim_nisn'] ?? null;

if ($nim_nisn) {
    $stmt = $pdo->prepare("
        SELECT am.*, k.nama AS nama_supervisor, k.nip AS nip_supervisor, k.jabatan AS jabatan_supervisor, d.nama_divisi 
        FROM anak_magang am 
        JOIN karyawan_divisi k ON am.supervisor_id = k.id 
        JOIN divisi d ON am.divisi_id = d.id
        WHERE am.nim_nisn = ?
    ");
    $stmt->execute([$nim_nisn]);
    $magang = $stmt->fetch();
} else {
    die("NIM/NISN tidak valid.");
}

if (!$magang) {
    die("Data tidak ditemukan atau Anda tidak memiliki akses.");
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
        .table {
            border-collapse: collapse;
        }
        .table th, .table td {
            border: 1px solid #dee2e6;
            padding: 8px;
        }
        .table th {
            background-color: #007BFF;
            color: #ffffff;
        }
        .table td {
            background-color: #f8f9fa;
            color: #000000;
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
            flex-direction: column;
            gap: 10px;
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

        /* General styling for sections and cards */
.card-header, .card-footer {
    background-color: #007BFF;
    padding: 12px;
    border-bottom: 1px solid #e9ecef;
    text-align: center; /* Center the text */
    font-size: 20px; /* Adjust font size if needed */
    font-weight: bold; /* Make the font bold if needed */
    color: #ffffff;
    
}

.card-footer {
    border-top: 1px solid #e9ecef;
    text-align: right;
}

/* Styling for section titles */
.section-title {
    font-size: 20px;
    margin-bottom: 15px;
    border-bottom: 2px solid #007bff;
    padding-bottom: 2px;
}

/* Container for info and photo */
.info-container {
    display: flex;
    align-items: center;
}

/* Styling for photo box */
.photo-box {
    margin-right: 15px;
}

.photo-box img {
    border-radius: 0%;
    width: 120px;
    height: 150px;
    object-fit: cover;
    border: 2px solid #007bff;
}

/* Styling for text information */
.info-text p {
    margin: 5px 0;
}

/* Styling for buttons */
.btn {
    border-radius: 4px;
    padding: 10px 20px;
    margin-left: 10px;
}

.btn-primary {
    background-color: #007bff;
    color: #fff;
    border: none;
}

.btn-danger {
    background-color: #dc3545;
    color: #fff;
    border: none;
}

.btn-primary:hover, .btn-danger:hover {
    opacity: 0.9;
}

/* Jarak antara header konten dan card */
.content-header {
    margin-bottom: 10px; /* Menambahkan jarak di bawah header konten */
    padding-bottom: 10px; /* Menambahkan padding bawah jika diperlukan */
}

/* Jarak antara card-header dan card-body */
.card-header {
    margin-bottom: 5px; /* Menambahkan jarak di bawah header card */
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
        <!-- Content Header -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Detail Magang</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Detail Magang</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main content -->
        <section class="content">
    <div class="container">
        <div class="card">
            <div class="card-header">
                Bio Data Anak Magang
            </div>
            <div class="card-body">
                <!-- Informasi Diri Section -->
                <div class="section">
                    <h4 class="section-title">Informasi Diri</h4>
                    <div class="info-container">
                        <div class="photo-box">
                            <img src="../uploads/<?= htmlspecialchars($magang['foto']) ?>" alt="Foto">
                        </div>
                        <div class="info-text">
                            <p><strong>Nama:</strong> <?= htmlspecialchars($magang['nama']) ?></p>
                            <p><strong>Alamat:</strong> <?= htmlspecialchars($magang['alamat']) ?></p>
                            <p><strong>NIM/NISN:</strong> <?= htmlspecialchars($magang['nim_nisn']) ?></p>
                            <p><strong>Asal Kampus/Sekolah:</strong> <?= htmlspecialchars($magang['asal_kampus_sekolah']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Data Magang Section -->
                <div class="section">
                    <h4 class="section-title">Data Magang</h4>
                    <p><strong>Masa Magang:</strong> <?= htmlspecialchars($magang['masa_magang']) ?> bulan</p>
                    <p><strong>Mulai Magang:</strong> <?= htmlspecialchars(date('d-m-Y', strtotime($magang['tanggal_mulai']))) ?></p>
                    <p><strong>Selesai Magang:</strong> <?= htmlspecialchars(date('d-m-Y', strtotime($magang['tanggal_selesai']))) ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($magang['status']) ?></p>
                </div>

                <!-- Data Supervisor Section -->
                <div class="section">
                    <h4 class="section-title">Data Supervisor</h4>
                    <p><strong>Nama Supervisor:</strong> <?= htmlspecialchars($magang['nama_supervisor']) ?></p>
                    <p><strong>NIP:</strong> <?= htmlspecialchars($magang['nip_supervisor']) ?></p>
                    <p><strong>Jabatan:</strong> <?= htmlspecialchars($magang['jabatan_supervisor']) ?></p>
                </div>
            </div>
            <!-- Card Footer with Buttons -->
            
        </div>
    </div>
</section>

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

<!-- REQUIRED SCRIPTS -->
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
</body>
</html>
