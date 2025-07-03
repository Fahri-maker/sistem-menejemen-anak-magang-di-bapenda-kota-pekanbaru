<?php
// Misalkan Anda sudah memiliki koneksi PDO dalam variabel $pdo

session_start();
if (!isset($_SESSION['login'])) {
    header("Location: index.php");
    exit();
}

include '../config.php'; // Koneksi ke database

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

$queries = [
    'totalSupervisors' => "SELECT COUNT(*) as total FROM akun_supervisor",
    'totalInterns' => "SELECT COUNT(*) as total FROM anak_magang",
    'totalDivisions' => "SELECT COUNT(*) as total FROM divisi",
    'totalReports' => "SELECT COUNT(*) as total FROM laporan"
];

$data = [];

foreach ($queries as $key => $query) {
    try {
        $stmt = $pdo->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $data[$key] = $result['total'];
    } catch (PDOException $e) {
        // Tangani error jika terjadi masalah dengan query
        echo "Error: " . $e->getMessage();
    }
}
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
                            <i class="nav-icon fas fa-user-tie"></i>
                            <p>Supervisors</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="anak_magang.php" class="nav-link">
                            <i class="nav-icon fas fa-user-graduate"></i>
                            <p>Interns</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="divisi.php" class="nav-link">
                            <i class="nav-icon fas fa-sitemap"></i>
                            <p>Divisions</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="laporan.php" class="nav-link">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>Reports</p>
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
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fas fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Supervisors</span>
                                <span class="info-box-number"><?php echo $data['totalSupervisors']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fas fa-user-graduate"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Interns</span>
                                <span class="info-box-number"><?php echo $data['totalInterns']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon"><i class="fas fa-sitemap"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Divisions</span>
                                <span class="info-box-number"><?php echo $data['totalDivisions']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="info-box bg-danger">
                            <span class="info-box-icon"><i class="fas fa-file-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Reports</span>
                                <span class="info-box-number"><?php echo $data['totalReports']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /.content -->
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

<!-- REQUIRED SCRIPTS -->
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
</body>
</html>
