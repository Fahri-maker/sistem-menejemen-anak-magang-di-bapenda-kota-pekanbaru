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

// Variabel untuk menampilkan pesan
$error = '';
$success = '';

// Proses ketika formulir disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['divisi_id']) && !empty($_POST['divisi_id']) && isset($_POST['nama']) && !empty($_POST['nama']) && isset($_POST['nip']) && !empty($_POST['nip']) && isset($_POST['jabatan']) && !empty($_POST['jabatan'])) {
        $divisi_id = $_POST['divisi_id'];
        $nama = $_POST['nama'];
        $nip = $_POST['nip'];
        $jabatan = $_POST['jabatan'];

        // Validasi divisi_id
        $stmt = $pdo->prepare("SELECT id FROM divisi WHERE id = ?");
        $stmt->execute([$divisi_id]);
        if ($stmt->rowCount() === 0) {
            $error = 'Error: divisi_id tidak valid.';
        } else {
            // Cek apakah NIP sudah ada
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM karyawan_divisi WHERE nip = ?");
            $stmt->execute([$nip]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'NIP ini sudah ada. Silakan gunakan NIP yang berbeda.';
            } else {
            // Insert data karyawan
            $stmt = $pdo->prepare("INSERT INTO karyawan_divisi (nama, nip, jabatan, divisi_id) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$nama, $nip, $jabatan, $divisi_id])) {
                $success = 'Karyawan berhasil ditambahkan.';
            } else {
                $error = 'Gagal menambahkan karyawan.';
            }
        }
    }
    } else {
        $error = 'Semua field harus diisi.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Karyawan</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
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
        .form-container {
            max-width: 600px;
            margin: 0 auto;
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
                        <a href="home.php" class="nav-link">
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
                        <a href="reports.php" class="nav-link">
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

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Tambah Karyawan</h1>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="form-container">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    <form action="add_karyawan.php" method="post">
                        <div class="form-group">
                            <label for="divisi_id">Divisi</label>
                            <select name="divisi_id" id="divisi_id" class="form-control" required>
                                <option value="">Pilih Divisi</option>
                                <?php foreach ($divisi as $d): ?>
                                    <option value="<?php echo htmlspecialchars($d['id']); ?>"><?php echo htmlspecialchars($d['nama_divisi']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="nama">Nama</label>
                            <input type="text" name="nama" id="nama" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="nip">NIP</label>
                            <input type="text" name="nip" id="nip" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="jabatan">Jabatan</label>
                            <input type="text" name="jabatan" id="jabatan" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Tambah Karyawan</button>
                    </form>
                </div>
            </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Footer -->
    <footer class="main-footer">
        <div class="float-right d-none d-sm-inline">
            <!-- Footer right content -->
        </div>
        <strong>&copy; <?php echo date("Y"); ?> Afrizal Group Corporation.</strong> All rights reserved.
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
