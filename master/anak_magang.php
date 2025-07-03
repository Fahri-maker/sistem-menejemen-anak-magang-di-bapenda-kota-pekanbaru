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

// Ambil data divisi dari database
$divisi = $pdo->query("SELECT * FROM divisi")->fetchAll(PDO::FETCH_ASSOC);

// Ambil data anak magang berdasarkan divisi jika ada
$anak_magang = [];
$divisi_id = isset($_GET['divisi_id']) ? $_GET['divisi_id'] : null;

if ($divisi_id) {
    try {
        $query = "
        SELECT am.nama, am.alamat, am.asal_kampus_sekolah, am.nim_nisn, am.masa_magang,
               k.nama AS nama_supervisor, d.nama_divisi AS nama_divisi, 
               CASE WHEN ak.id IS NOT NULL THEN 'Aktif' ELSE 'Nonaktif' END AS status,
               am.tanggal_selesai
        FROM anak_magang am
        LEFT JOIN karyawan_divisi k ON am.supervisor_id = k.id
        LEFT JOIN divisi d ON am.divisi_id = d.id
        LEFT JOIN akun_anak_magang ak ON am.nim_nisn = ak.anak_magang_id
        WHERE am.divisi_id = :divisi_id
    ";
    
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':divisi_id', $divisi_id, PDO::PARAM_INT);
        $stmt->execute();
        $anak_magang = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Debugging: Cek apakah supervisor_id benar dan cocok dengan data di tabel karyawan_divisi
        if (empty($anak_magang)) {
            echo "Tidak ada data anak magang untuk divisi ini, atau data supervisor kosong.";
        }
    } catch (PDOException $e) {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

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

        .table {
    border-collapse: collapse; /* Menggabungkan border tabel */
}

.table td, .table th {
    max-width: 300px; /* Atur batas maksimum lebar kolom */
    overflow: hidden; /* Sembunyikan konten yang melebihi batas */
    text-overflow: ellipsis; /* Tambahkan ellipsis (...) jika konten terlalu panjang */
    white-space: nowrap; /* Jangan biarkan teks membungkus baris baru */
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

.btn-blue {
        background-color: #28a745; /* Warna biru */
        border-color: #28a745; /* Warna biru */
        color: white; /* Warna teks putih */
    }

    .btn-blue:hover {
        background-color: #28a745; /* Warna biru lebih gelap untuk hover */
        border-color: #28a745; /* Warna biru lebih gelap untuk hover */
    }
    
    .icon-blue {
        color: white; /* Warna putih untuk ikon di tombol */
    }
    

    .btn-custom-size {
    position: absolute; /* Agar tombol bisa diposisikan relatif terhadap kontainer */
    right: 0; /* Posisi tombol di sebelah kanan kontainer */
    top: -28px; /* Posisi tombol dari atas kontainer, sesuaikan jika diperlukan */
    z-index: 20; /* Untuk memastikan tombol berada di atas elemen lain */
    width: 120px; /* Lebar tombol */
    height: 35px; /* Tinggi tombol */
    font-size: 16px; /* Ukuran font untuk teks tombol */
    padding: 0.5px 1px; /* Jarak di dalam tombol */
    background-color: #007bff; /* Warna tombol biru (ganti sesuai kebutuhan) */
    color: white; /* Warna teks putih */
    border: none; /* Menghapus border default tombol */
    border-radius: 3px; /* Membuat sudut tombol melengkung (opsional) */
}

.btn-custom-size:hover {
    background-color: #007bff; /* Warna tombol saat hover */
}

/* Mengatur margin-top untuk elemen button-wrapper */
.button-wrapper {
    margin-top: 13px; /* Sesuaikan dengan kebutuhan Anda */
}

/* Jika Anda menggunakan positioning untuk menaikkan elemen */
.button-wrapper {
    position: relative; /* Agar top bisa diterapkan */
    top: -13px; /* Sesuaikan dengan kebutuhan Anda */
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

   <!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <!-- Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Input Data Anak Magang</h1>
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

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Filter and Buttons -->
        <div class="button-wrapper mb-3" style="margin-top: 40px;"> <!-- Atur jarak di sini -->
            <form action="" method="GET" class="form-inline">
                <div class="form-group mb-2">
                    <label for="divisi" class="sr-only">Divisi</label>
                    <select id="divisi" name="divisi_id" class="form-control" required>
                        <option value="">-- Pilih Divisi --</option>
                        <?php foreach ($divisi as $d): ?>
                            <option value="<?php echo htmlspecialchars($d['id']); ?>" <?php echo ($d['id'] == $divisi_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['nama_divisi']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mb-2 ml-2">Tampilkan</button>
            </form>
        </div>

            <!-- Data Table -->
            <div class="table-container">
            <button class="btn btn-custom-size btn-blue"onclick="window.location.href='add_magang.php'">
    <i class="fas fa-plus icon-custom-size"></i> Input Data
</button>


    </button>
    <!-- Tabel Anda dimulai di sini -->
    <table class="table">
        <!-- Konten tabel Anda -->
    </table>
</div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>Asal Kampus/ Sekolah</th>
                            <th>NIM/NISN</th>
                            <th>Masa Magang</th>
                            <th>Supervisor</th>
                            <th>Divisi</th>
                            <th>Status</th>
                            <th>Tanggal Selesai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($anak_magang) > 0): ?>
                            <?php foreach ($anak_magang as $am): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($am['nama']); ?></td>
                                    <td><?php echo htmlspecialchars($am['alamat']); ?></td>
                                    <td><?php echo htmlspecialchars($am['asal_kampus_sekolah']); ?></td>
                                    <td><?php echo htmlspecialchars($am['nim_nisn']); ?></td>
                                    <td><?php echo htmlspecialchars($am['masa_magang']); ?></td>
                                    <td><?php echo htmlspecialchars($am['nama_supervisor']); ?></td>
                                    <td><?php echo htmlspecialchars($am['nama_divisi']); ?></td>
                                    <td><?php echo htmlspecialchars($am['status']); ?></td>
                                    <td><?php echo htmlspecialchars($am['tanggal_selesai']); ?></td>
                                    <td>
                                        <a href="edit_magang.php?nim_nisn=<?php echo urlencode($am['nim_nisn']); ?>" class="btn btn-warning btn-sm d-block mb-1">
                                            <i class="fas fa-edit"></i> <!-- Ikon Edit -->
                                        </a>
                                        <a href="create_magang.php?nim_nisn=<?php echo urlencode($am['nim_nisn']); ?>" class="btn btn-primary btn-sm d-block mb-1">
                                            <i class="fas fa-user-plus"></i> <!-- Ikon Buat Akun -->
                                        </a>
                                        <a href="delete_magang.php?nim_nisn=<?php echo urlencode($am['nim_nisn']); ?>" class="btn btn-danger btn-sm d-block" onclick="return confirm('Anda yakin ingin menghapus data anak magang ini')">
                                            <i class="fas fa-trash-alt"></i> <!-- Ikon Hapus -->
                                        </a>
                                    </td>


                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center">No data available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
