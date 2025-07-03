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

// Menangani pengiriman formulir absensi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $anak_magang_id = $_SESSION['anak_magang_id'];
    $status_kehadiran = $_POST['status_kehadiran'];

    // Cek apakah dokumentasi diupload dengan benar
    if (isset($_FILES['dokumentasi']) && $_FILES['dokumentasi']['error'] == UPLOAD_ERR_OK) {
        $dokumentasi = $_FILES['dokumentasi']['name'];
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($dokumentasi);

        // Pindahkan file yang diupload ke folder tujuan
        if (move_uploaded_file($_FILES['dokumentasi']['tmp_name'], $target_file)) {
            // Insert ke database
            $stmt = $pdo->prepare("INSERT INTO absensi (tanggal, status_kehadiran, dokumentasi, anak_magang_id) VALUES (NOW(), ?, ?, ?)");
            $stmt->execute([$status_kehadiran, $dokumentasi, $anak_magang_id]);

            // Redirect setelah data berhasil disimpan
            header("Location: absen.php");
            exit();
        } else {
            echo "Gagal mengupload dokumentasi.";
        }
    } else {
        // Tangani kesalahan pengunggahan file
        echo "Dokumentasi tidak dikirim atau terjadi kesalahan.";
    }
}

// Mengambil semua catatan absensi untuk anak magang yang sedang login
$stmt = $pdo->prepare("SELECT a.tanggal, a.status_kehadiran, a.dokumentasi, a.validasi_by
                       FROM absensi a
                       WHERE a.anak_magang_id = ?");
$stmt->execute([$_SESSION['anak_magang_id']]);
$absensi = $stmt->fetchAll();
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



.modal {
        display: none;
        position: fixed;
        z-index: 1000; /* Ensure modal is above other elements */
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.5); /* Slightly darker overlay */
    }

    .modal-content {
        background-color: #fefefe;
        margin: 10% auto; /* Center modal and add space from the top */
        padding: 20px;
        border: 1px solid #888;
        width: 80%; /* Reduced width */
        max-width: 500px; /* Further limit the maximum width */
        box-shadow: 0 2px 15px rgba(0,0,0,0.3); /* Slightly larger shadow */
        border-radius: 8px; /* Rounded corners */
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 24px; /* Slightly smaller close button */
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-control {
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .btn-primary {
    width: 130px; /* Atur lebar tombol */
    height: 35px; /* Atur tinggi tombol */
    background-color: #007bff;
    border: none;
    color: white;
    display: flex; /* Flexbox layout */
    align-items: center; /* Vertically center */
    justify-content: center; /* Horizontally center */
    font-size: 16px;
    margin-top: 10px;
    cursor: pointer;
    border-radius: 4px; /* Rounded corners */
}

    .btn-primary:hover {
        background-color: #0056b3;
    }

    #absenBtn {
  margin-left: auto; /* Menggeser button ke kanan dalam container flex */
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
                    <h1 class="m-0 text-dark">Menu Absensi</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Absensi</li>
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
                        <h3>Daftar Absensi</h3>
                        <button id="absenBtn" class="btn btn-primary ml-auto">
                            <i class="fa fa-plus"></i> Input Absensi
                        </button>
                    </div><!-- /.card-header -->

                    <div class="card-body">
                        <table id="anak_magang_table" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal Kehadiran</th>
                                    <th>Status Kehadiran</th>
                                    <th>Status Validasi</th>
                                    <th>Dokumentasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                foreach ($absensi as $row): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= $row['tanggal'] ?></td>
                                        <td><?= $row['status_kehadiran'] ?></td>
                                        <td>
                                            <?php if ($row['validasi_by']): ?>
                                                Divalidasi
                                            <?php else: ?>
                                                Belum Divalidasi
                                            <?php endif; ?>
                                        </td>
                                        <td><img src="../uploads/<?= $row['dokumentasi'] ?>" alt="Dokumentasi" style="max-width: 100px;"></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- Modal untuk form absensi -->
                        <div id="absenModal" class="modal">
                            <div class="modal-content">
                                <span class="close">&times;</span>
                                <h2>Form Absensi</h2>
                                <form method="post" action="" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label>Status Kehadiran</label>
                                        <select name="status_kehadiran" class="form-control" required>
                                            <option value="Hadir">Hadir</option>
                                            <option value="Sakit">Sakit</option>
                                            <option value="Tidak Hadir">Tidak Hadir</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Dokumentasi</label>
                                        <input type="file" name="dokumentasi" class="form-control" required>
                                    </div>

                                    <button type="submit" class="btn btn-primary">Submit Absensi</button>
                                </form>
                            </div>
                        </div>

                        

                        <script>
                            // Ambil elemen-elemen modal
                            var modal = document.getElementById("absenModal");
                            var btn = document.getElementById("absenBtn");
                            var span = document.getElementsByClassName("close")[0];

                            // Ketika tombol diklik, buka modal
                            btn.onclick = function() {
                                modal.style.display = "block";
                            }

                            // Ketika tombol 'X' diklik, tutup modal
                            span.onclick = function() {
                                modal.style.display = "none";
                            }

                            // Ketika pengguna mengklik di luar modal, tutup modal
                            window.onclick = function(event) {
                                if (event.target == modal) {
                                    modal.style.display = "none";
                                }
                            }
                        </script>
                    </div><!-- /.card-body -->
                </div><!-- /.card -->
            </div><!-- /.col-12 -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</section><!-- /.content -->
                        </div>

                 

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
