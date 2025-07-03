<?php
session_start(); // Memulai sesi

if (!isset($_SESSION['nama']) || !isset($_SESSION['karyawan_divisi_id'])) {
    header("Location: index.php");
    exit();
}

include '../config.php'; // Koneksi ke database

$nim_nisn = $_GET['nim_nisn'] ?? null;

// Menggunakan `karyawan_divisi_id` yang disimpan dalam sesi saat login
$supervisor_id = $_SESSION['karyawan_divisi_id'];

if ($nim_nisn) {
    $stmt = $pdo->prepare("SELECT * FROM anak_magang WHERE nim_nisn = :nim_nisn AND supervisor_id = :supervisor_id");
    $stmt->execute(['nim_nisn' => $nim_nisn, 'supervisor_id' => $supervisor_id]);
    $magang = $stmt->fetch();
} else {
    die("NIM/NISN tidak valid.");
}

if (!$magang) {
    die("Data tidak ditemukan atau Anda tidak memiliki akses.");
}

// Proses pembaruan data jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $asal_kampus_sekolah = $_POST['asal_kampus_sekolah'] ?? '';
    $masa_magang = $_POST['masa_magang'] ?? '';
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';

    $stmt = $pdo->prepare("UPDATE anak_magang SET nama = :nama, alamat = :alamat, asal_kampus_sekolah = :asal_kampus_sekolah, masa_magang = :masa_magang, tanggal_mulai = :tanggal_mulai, tanggal_selesai = :tanggal_selesai WHERE nim_nisn = :nim_nisn AND supervisor_id = :supervisor_id");
    
    $stmt->execute([
        'nama' => $nama,
        'alamat' => $alamat,
        'asal_kampus_sekolah' => $asal_kampus_sekolah,
        'masa_magang' => $masa_magang,
        'tanggal_mulai' => $tanggal_mulai,
        'tanggal_selesai' => $tanggal_selesai,
        'nim_nisn' => $nim_nisn,
        'supervisor_id' => $supervisor_id
    ]);

    // Menampilkan alert dan mengarahkan ke halaman anak_magang.php
    echo "<script>alert('Data berhasil diperbarui.'); window.location.href='anak_magang.php';</script>";
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Anak Magang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        label {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
            display: block;
        }
        input[type="text"],
        input[type="date"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        a {
            text-align: center;
            display: block;
            margin-top: 20px;
            color: #4CAF50;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }
        a:hover {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Anak Magang</h2>
        <form method="POST">
            <label for="nama">Nama:</label>
            <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($magang['nama']) ?>" required>

            <label for="alamat">Alamat:</label>
            <input type="text" id="alamat" name="alamat" value="<?= htmlspecialchars($magang['alamat']) ?>" required>

            <label for="asal_kampus_sekolah">Asal Kampus/Sekolah:</label>
            <input type="text" id="asal_kampus_sekolah" name="asal_kampus_sekolah" value="<?= htmlspecialchars($magang['asal_kampus_sekolah']) ?>" required>

            <label for="masa_magang">Masa Magang:</label>
            <input type="text" id="masa_magang" name="masa_magang" value="<?= htmlspecialchars($magang['masa_magang']) ?>" required>

            <label for="tanggal_mulai">Tanggal Mulai:</label>
            <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="<?= htmlspecialchars($magang['tanggal_mulai']) ?>" required>

            <label for="tanggal_selesai">Tanggal Selesai:</label>
            <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="<?= htmlspecialchars($magang['tanggal_selesai']) ?>" required>

            <input type="submit" value="Update">
        </form>
        <a href="anak_magang.php">Kembali</a>
    </div>
</body>
</html>
