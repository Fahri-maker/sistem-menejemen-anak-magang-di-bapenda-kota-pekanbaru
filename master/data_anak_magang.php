<?php
session_start();
include "../config.php";

if (!isset($_SESSION['login'])) {
    header("Location: index.php");
    exit();
}

// Ambil data anak magang berdasarkan nim_nisn
$anakMagangId = isset($_GET['anak_magang_id']) ? $_GET['anak_magang_id'] : null;

$query = "
    SELECT am.*, k.nama AS nama_supervisor, k.nip AS nip_supervisor, k.jabatan AS jabatan_supervisor, d.nama_divisi 
    FROM anak_magang am 
    JOIN karyawan_divisi k ON am.supervisor_id = k.id 
    JOIN divisi d ON am.divisi_id = d.id
    WHERE am.nim_nisn = ?
";

$stmt = $pdo->prepare($query);
$stmt->execute([$anakMagangId]);
$anakMagang = $stmt->fetch();

if (!$anakMagang) {
    die('Data tidak ditemukan.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Data Diri</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 20px;
        }
        .container {
            margin-top: 20px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            background-color: #ffffff;
        }
        .card-header {
            background-color: #007bff;
            color: white;
            font-size: 1.5rem;
            border-bottom: 1px solid #007bff;
            text-align: center; /* Menempatkan judul di tengah */
        }
        .card-body {
            padding: 20px;
        }
        .card-body img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #ddd;
        }
        .info-container {
            display: flex;
            align-items: center;
        }
        .info-container .photo-box {
            margin-right: 20px;
        }
        .info-container div {
            max-width: 500px;
        }
        .info-container p {
            margin: 6px 0;
            line-height: 1.6;
        }
        .section-title {
            margin-top: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                Bio Data Anak Magang
            </div>
            <div class="card-body">
                <div class="section">
                    <h4 class="section-title">Informasi Diri</h4>
                    <div class="info-container">
                        <div class="photo-box">
                            <img src="../uploads/<?= htmlspecialchars($anakMagang['foto']) ?>" alt="Foto">
                        </div>
                        <div>
                            <p><strong>Nama:</strong> <?= htmlspecialchars($anakMagang['nama']) ?></p>
                            <p><strong>Alamat:</strong> <?= htmlspecialchars($anakMagang['alamat']) ?></p>
                            <p><strong>NIM/NISN:</strong> <?= htmlspecialchars($anakMagang['nim_nisn']) ?></p>
                            <p><strong>Asal Kampus/Sekolah:</strong> <?= htmlspecialchars($anakMagang['asal_kampus_sekolah']) ?></p>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h4 class="section-title">Data Magang</h4>
                    <p><strong>Masa Magang:</strong> <?= htmlspecialchars($anakMagang['masa_magang']) ?> bulan</p>
                    <p><strong>Mulai Magang:</strong> <?= htmlspecialchars(date('d-m-Y', strtotime($anakMagang['tanggal_mulai']))) ?></p>
                    <p><strong>Selesai Magang:</strong> <?= htmlspecialchars(date('d-m-Y', strtotime($anakMagang['tanggal_selesai']))) ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($anakMagang['status']) ?></p>
                </div>

                <div class="section">
                    <h4 class="section-title">Data Supervisor</h4>
                    <p><strong>Nama Supervisor:</strong> <?= htmlspecialchars($anakMagang['nama_supervisor']) ?></p>
                    <p><strong>NIP:</strong> <?= htmlspecialchars($anakMagang['nip_supervisor']) ?></p>
                    <p><strong>Jabatan:</strong> <?= htmlspecialchars($anakMagang['jabatan_supervisor']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
