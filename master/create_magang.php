<?php
session_start();
include "../config.php";

// Pastikan pengguna sudah login
if (!isset($_SESSION['login'])) {
    header("Location: index.php");
    exit();
}

// Pastikan `nim_nisn` diterima dari URL
if (isset($_GET['nim_nisn'])) {
    $nim_nisn = $_GET['nim_nisn'];
    $created_by = $_SESSION['user_id']; // Ambil ID akun master yang sedang login

    // Cek apakah akun sudah ada untuk NIM/NISN ini
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM akun_anak_magang WHERE username = ?");
    $stmt->execute([$nim_nisn]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        echo "<script>alert('Akun untuk NIM/NISN ini sudah ada.'); window.location.href='supervisor.php';</script>";
     
    } else {
        // Masukkan data akun baru ke tabel akun_anak_magang
        $stmt = $pdo->prepare("INSERT INTO akun_anak_magang (anak_magang_id, username, password, created_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nim_nisn, $nim_nisn, $nim_nisn, $created_by]);
        echo "<script>alert('Akun supervisor berhasil dibuat.!'); window.location.href='supervisor.php';</script>";
        // Redirect kembali ke halaman anak_magang.php
        echo "<script>alert('Akun Magang berhasil dibuat.!'); window.location.href='anak_magang.php.php';</script>";
    }
} else {
    
    echo "Error: Data anak magang tidak valid.";
}
?>
