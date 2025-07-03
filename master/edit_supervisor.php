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

if (isset($_GET['id'])) {
    $id = htmlspecialchars($_GET['id']);

    // Fetch the supervisor's existing data
    $query = "SELECT * FROM karyawan_divisi WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo "Data tidak ditemukan!";
        exit;
    }

    if (isset($_POST['submit'])) {
        // Get form data
        $nama = htmlspecialchars($_POST['nama']);
        $nip = htmlspecialchars($_POST['nip']);
        $jabatan = htmlspecialchars($_POST['jabatan']);

        // Check if new NIP already exists in the database (excluding current record)
        $query = "SELECT COUNT(*) FROM karyawan_divisi WHERE nip = ? AND id <> ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$nip, $id]);
        if ($stmt->fetchColumn() > 0) {
            echo "<script>alert('NIP ini sudah digunakan!');</script>";
        } else {
            // Update the supervisor's data
            $query = "UPDATE karyawan_divisi SET nama = ?, nip = ?, jabatan = ? WHERE id = ?";
            $stmt = $pdo->prepare($query);
            if ($stmt->execute([$nama, $nip, $jabatan, $id])) {
                echo "<script>alert('Data berhasil diupdate!'); window.location.href='supervisor.php';</script>";
            } else {
                echo "Gagal mengupdate data: " . $pdo->errorInfo()[2];
            }
        }
    }
} else {
    echo "ID tidak disediakan!";
    exit;
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Admin LTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <style>
        body {
            background-color: #f4f6f9;
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
            padding: 1px; /* Tambahkan padding di sekitar konten */
        }
        .content-header {
            border-bottom: 1.2px solid #dee2e6;
        }
        /* Mengatur ukuran font dan margin di dalam .content-header */
        .content-header .container-fluid {
            padding: 0px 8px; /* Mengurangi padding keseluruhan */
        }

        .content-header h1 {
            font-size: 20px; /* Memperkecil ukuran font heading */
            margin-bottom: 0.8px; /* Mengurangi margin bawah */
        }

        .breadcrumb {
            font-size: 0.875px; /* Memperkecil ukuran font breadcrumb */
            margin-bottom: 0; /* Menghilangkan margin bawah breadcrumb */
        }

        .breadcrumb-item a {
            font-size: 18px; /* Memperkecil ukuran font link di breadcrumb */
        }

        .breadcrumb-item.active {
            font-size: 18px; /* Memperkecil ukuran font teks aktif di breadcrumb */
        }
    </style>
</head>
<body>
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


    
   <!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <!-- Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Update Data Supervisor</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Data Anak Magang</li>
                </ol>
            </div>
        </div>
    </div>
</div>


    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <form action="" method="post">
                <div class="form-group">
                    <label for="nama">Nama</label>
                    <input type="text" name="nama" id="nama" class="form-control" value="<?php echo htmlspecialchars($row['nama']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="nip">NIP</label>
                    <input type="text" name="nip" id="nip" class="form-control" value="<?php echo htmlspecialchars($row['nip']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="jabatan">Jabatan</label>
                    <input type="text" name="jabatan" id="jabatan" class="form-control" value="<?php echo htmlspecialchars($row['jabatan']); ?>" required>
                </div>
                <button type="submit" name="submit" class="btn btn-primary">Update</button>
            </form>
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
