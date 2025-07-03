<?php
session_start();
if (!isset($_SESSION['anak_magang_id'])) {
    header("Location: index.php");
    exit();
}

include '../config.php';
$loggedInUser = isset($userData['nama']) ? $userData['nama'] : 'Pengguna';
$userPhoto = 'https://via.placeholder.com/150/000000/FFFFFF/?text=User';


$nim_nisn = $_SESSION['anak_magang_id']; // Mengambil nim_nisn dari sesi login

// Query untuk mengambil data tugas dan status penilaian
$tugas = $pdo->prepare("
    SELECT t.*, 
           p.status AS penilaian_status, 
           p.file_tugas AS penilaian_file,
           p.nilai AS penilaian_nilai
    FROM tugas t
    LEFT JOIN penilaian_tugas p ON t.id = p.tugas_id
    WHERE t.anak_magang_id = ?
");
$tugas->execute([$nim_nisn]);
$tugas = $tugas->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tugas_id = $_POST['tugas_id'];
    $aksi = $_POST['aksi'];
    $file_tugas = $_FILES['file_tugas']['name'] ?? null;
    $file_tmp_name = $_FILES['file_tugas']['tmp_name'] ?? null;
    $target_file = $file_tugas ? "../file_tugas/" . basename($file_tugas) : null;
    $nilai = $_POST['nilai'] ?? null;

    // Jika aksi submit
    if ($aksi === 'submit' && $file_tugas && move_uploaded_file($file_tmp_name, $target_file)) {
        // Cek apakah penilaian_tugas sudah ada untuk tugas_id tersebut
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM penilaian_tugas WHERE tugas_id = ?");
        $stmt->execute([$tugas_id]);
        $exists = $stmt->fetchColumn();

        if ($exists) {
            // Jika sudah ada, lakukan update
            $stmt = $pdo->prepare("
                UPDATE penilaian_tugas 
                SET status = 'Telah Dibuat', file_tugas = ?, nilai = ? 
                WHERE tugas_id = ?
            ");
            $stmt->execute([$file_tugas, $nilai, $tugas_id]);
        } else {
            // Jika belum ada, lakukan insert
            $stmt = $pdo->prepare("
                INSERT INTO penilaian_tugas (tugas_id, status, file_tugas, nilai) 
                VALUES (?, 'Telah Dibuat', ?, ?)
            ");
            $stmt->execute([$tugas_id, $file_tugas, $nilai]);
        }

    } elseif ($aksi === 'edit' && $file_tugas && move_uploaded_file($file_tmp_name, $target_file)) {
        // Edit hanya akan mengupdate tugas yang sudah ada
        $stmt = $pdo->prepare("
            UPDATE penilaian_tugas 
            SET status = 'Telah Dibuat', file_tugas = ?, nilai = ?
            WHERE tugas_id = ?
        ");
        $stmt->execute([$file_tugas, $nilai, $tugas_id]);

    } elseif ($aksi === 'unsubmit') {
        // Unsubmit, hapus file dan set status ke 'Diberikan'
        $stmt = $pdo->prepare("SELECT file_tugas FROM penilaian_tugas WHERE tugas_id = ?");
        $stmt->execute([$tugas_id]);
        $file = $stmt->fetchColumn();

        if ($file && file_exists("../file_tugas/" . $file)) {
            unlink("../file_tugas/" . $file); // Hapus file dari server
        }

        $stmt = $pdo->prepare("
            UPDATE penilaian_tugas 
            SET status = 'Diberikan', file_tugas = NULL, nilai = NULL
            WHERE tugas_id = ?
        ");
        $stmt->execute([$tugas_id]);

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }

    // Jika ada nilai, ubah status menjadi 'Selesai'
    if ($nilai !== null) {
        $stmt = $pdo->prepare("
            UPDATE penilaian_tugas 
            SET status = 'Selesai'
            WHERE tugas_id = ?
        ");
        $stmt->execute([$tugas_id]);
    }

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
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


.centered-buttons {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%; /* Membuat div memenuhi tinggi sel */
    }

    /* Mengatur ukuran font dan padding tombol */
    .btn-group-vertical .btn {
    width: 35px; /* Atur lebar tombol */
    height: 35px; /* Atur tinggi tombol */
    padding: 0; /* Hapus padding default */
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn i {
    font-size: 17px; /* Ukuran ikon */
}
.btn {
    border-radius: 0; /* Menghapus radius border dari tombol */
}


    /* Memusatkan teks dalam <td> */
    td {
        border-collapse: collapse; /* Menghilangkan jarak antar border */
        text-align: left;
        vertical-align: middle; /* Vertikal tengah */
        height: 10px; /* Atur tinggi sel jika perlu */
    }

    .table {
    border-collapse: collapse; /* Menggabungkan border tabel */
}

.table td, .table th {
    max-width: 110px; /* Atur batas maksimum lebar kolom */
    word-wrap: break-word; /* Memaksa teks untuk pindah ke baris berikutnya jika terlalu panjang */
    white-space: normal; /* Izinkan teks untuk membungkus ke baris berikutnya */
    overflow: hidden; /* Sembunyikan konten yang melebihi batas */
    text-overflow: clip; /* Tidak menambahkan ellipsis (...) */
}




.table th {
    background-color: #007BFF !important; /* Warna biru untuk header kolom */
    color: white !important; /* Warna teks putih untuk header kolom */
}

.table td {
    background-color: #f0f4f7; /* Warna biru muda untuk sel tabel */
    color: black; /* Warna teks hitam untuk sel tabel */
}
.table-striped tbody tr:nth-of-type(odd) {
    background-color: #f0f4f7; /* Warna latar belakang baris genap */
}

.table-hover tbody tr:hover {
    background-color: #e9ecef; /* Warna latar belakang baris saat dihover */
}
.table td, .table th {
    font-size: 12px; /* Ukuran font untuk sel dan header tabel */
}
.table-container {
    position: relative;
}


/* Tombol kustom kecil dengan ikon */
.btn-sm {
    padding: 5px 10px; /* Mengecilkan padding tombol */
    font-size: 14px; /* Mengecilkan ukuran font */
    border-radius: 0px; /* Mengatur sudut tombol */
}

.btn-icon i {
    font-size: 16px; /* Mengatur ukuran ikon */
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
                        <a href="dashboard.php" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="daftar_tugas.php" class="nav-link">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Daftar Tugas</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="absen.php" class="nav-link">
                            <i class="nav-icon fas fa-calendar-check"></i>
                            <p>Absensi</p>
                        </a>
                    </li>
                    <!-- <li class="nav-item">
                        <a href="lihat_laporan.php" class="nav-link">
                            <i class="nav-icon fas fa-tasks"></i>
                            <p>Laporan Magang</p>
                        </a>
                    </li> -->
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

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3>Daftar Tugas</h3>
                            <!-- <button class="btn btn-primary" style="margin-left:auto;">Tambah Tugas</button> -->
                        </div><!-- /.card-header -->

                        <div class="card-body">
                            <table id="anak_magang_table" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama Tugas</th>
                                        <th>Uraian Tugas</th>
                                        <th>Target</th>
                                        <th>Tenggat Pengumpulan</th>
                                        <th>Status</th>
                                        <th>File Tugas</th>
                                        <th>Nilai</th> <!-- Kolom Nilai tetap ditampilkan -->
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($tugas)): ?>
                                        <tr>
                                            <td colspan="8" class="no-data">Tidak ada tugas yang tersedia.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($tugas as $t): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($t['nama_tugas']) ?></td>
                                                <td><?= htmlspecialchars($t['uraian_tugas']) ?></td>
                                                <td><?= htmlspecialchars($t['target']) ?></td>
                                                <td><?= htmlspecialchars($t['tenggat_pengumpulan']) ?></td>
                                                <td><?= htmlspecialchars($t['penilaian_status'] ?? 'Belum Dinilai') ?></td>
                                                <td>
                                                    <?php if ($t['penilaian_file']): ?>
                                                        <a href="../file_tugas/<?= htmlspecialchars($t['penilaian_file']) ?>" target="_blank">Lihat File</a>
                                                    <?php else: ?>
                                                        Belum diunggah
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($t['penilaian_nilai'] ?? 'Belum Dinilai') ?></td> <!-- Nilai ditampilkan -->
                                                <td>
                                                <form method="post" action="" enctype="multipart/form-data">
                                                    <input type="hidden" name="tugas_id" value="<?= htmlspecialchars($t['id']) ?>">
                                                    
                                                    <?php if ($t['penilaian_file']): ?>
                                                        <!-- Tombol Unsubmit dengan ikon -->
                                                        <button type="submit" name="aksi" value="unsubmit" class="btn btn-danger btn-sm btn-icon">
                                                        <i class="fas fa-eraser"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <!-- Input File dan Tombol Submit dengan ikon -->
                                                        <input type="file" name="file_tugas" required class="form-control mb-2">
                                                        <button type="submit" name="aksi" value="submit" class="btn btn-success btn-sm btn-icon">
                                                        <i class="fas fa-upload"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </form>


                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div><!-- /.card-body -->
                    </div><!-- /.card -->
                </div><!-- /.col-12 -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

                 

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