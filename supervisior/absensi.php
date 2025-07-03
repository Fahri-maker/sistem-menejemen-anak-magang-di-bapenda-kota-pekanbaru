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

// Mengambil semua catatan absensi
$stmt = $pdo->prepare("
    SELECT a.id, a.tanggal, a.status_kehadiran, a.dokumentasi, a.skor, a.validasi_by,
           am.nama, am.nim_nisn
    FROM absensi a
    JOIN anak_magang am ON a.anak_magang_id = am.nim_nisn
");
$stmt->execute();
$absensi = $stmt->fetchAll();

// Menangani validasi absensi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['validasi'])) {
    $id = $_POST['id'];
    $status_kehadiran = $_POST['status_kehadiran']; // Status kehadiran dari anak magang
    $skor = 0;

    // Hitung skor berdasarkan status kehadiran
    if ($status_kehadiran == 'hadir') {
        $skor = 100;
    } elseif ($status_kehadiran == 'sakit') {
        $skor = 50;
    } elseif ($status_kehadiran == 'tidak hadir') {
        $skor = 0;
    }

    // Update record absensi dengan validasi_by sebagai id supervisor dari session
    $stmt = $pdo->prepare("UPDATE absensi SET validasi_by = :validasi_by, skor = :skor WHERE id = :id");
    $stmt->execute(['validasi_by' => $supervisor_id, 'skor' => $skor, 'id' => $id]);

    // Beri pesan sukses dan redirect kembali ke halaman absensi
    $_SESSION['message'] = 'Absensi berhasil divalidasi.';
    header("Location: absensi.php");
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

        .main-header,
        .brand-link {
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
            border-collapse: collapse;
        }

        .table th,
        .table td {
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

        .table td,
        .table th {
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

        .btn-custom {
    background-color: #007bff; /* Blue background */
    color: #ffffff; /* White text */
    border: none; /* Remove default border */
    border-radius: 4px; /* Rounded corners */
    padding: 5px 10px; /* Padding */
    text-decoration: none; /* Remove underline */
    display: inline-block; /* Ensure correct display */
}

.btn-custom:hover {
    background-color: #0056b3; /* Darker blue on hover */
}

/* Centering the button in the column */
.text-center {
    text-align: center;
}

/* Ensure images are clickable and styled appropriately */
.dokumentasi-img img {
    cursor: pointer;
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
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Absensi Anak Magang</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Absensi</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                    </div>
                    <?php endif; ?>

                    <!-- Table -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Daftar Absensi</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
    <table id="anak_magang_table" class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Nama</th>
                <th>NIM/NISN</th>
                <th>Tanggal</th>
                <th>Status Kehadiran</th>
                <th>Dokumentasi</th>
                <th>Validasi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($absensi as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><?= htmlspecialchars($row['nim_nisn']) ?></td>
                    <td><?= htmlspecialchars($row['tanggal']) ?></td>
                    <td><?= htmlspecialchars($row['status_kehadiran']) ?></td>
                    <td class="text-center">
                        <a 
                            href="javascript:void(0);" 
                            id="toggleLink_<?= htmlspecialchars($row['id']) ?>" 
                            onclick="showImage('img_<?= htmlspecialchars($row['id']) ?>', 'toggleLink_<?= htmlspecialchars($row['id']) ?>')"
                            class="btn btn-custom btn-sm"
                        >
                            Lihat
                        </a>
                        <div id="img_<?= htmlspecialchars($row['id']) ?>" class="dokumentasi-img" style="display: none;">
                            <img 
                                src="../uploads/<?= htmlspecialchars($row['dokumentasi']) ?>" 
                                alt="Dokumentasi" 
                                style="width: 100px; height: auto; cursor: pointer;" 
                                onclick="hideImage('img_<?= htmlspecialchars($row['id']) ?>', 'toggleLink_<?= htmlspecialchars($row['id']) ?>')"
                            >
                        </div>
                    </td>
                    <td>
                        <?php if ($row['validasi_by']): ?>
                            <span class="validated">Divalidasi</span>
                        <?php else: ?>
                            <form method="POST" action="">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']) ?>">
                                <input type="hidden" name="status_kehadiran" value="<?= htmlspecialchars($row['status_kehadiran']) ?>">
                                <button type="submit" name="validasi" class="btn btn-primary">Validasi</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function showImage(imgId, linkId) {
    var imgElement = document.getElementById(imgId);
    var linkElement = document.getElementById(linkId);

    imgElement.style.display = "block";
    linkElement.style.display = "none";  // Hides the "Lihat" button
}

function hideImage(imgId, linkId) {
    var imgElement = document.getElementById(imgId);
    var linkElement = document.getElementById(linkId);

    imgElement.style.display = "none";
    linkElement.style.display = "inline";  // Shows the "Lihat" button
}
</script>

                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->

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
