<?php
session_start();
include "../config.php";

// Pastikan akun master sudah login
if (!isset($_SESSION['user_id'])) {
    echo "Akses ditolak.";
    exit();
}

$master_id = $_SESSION['user_id']; // ID akun master dari sesi

if (isset($_GET['nip']) && isset($_GET['divisi_id'])) {
    $nip = $_GET['nip'];
    $divisi_id = $_GET['divisi_id'];

    // Ambil ID karyawan dari tabel karyawan_divisi berdasarkan NIP
    $stmt_karyawan = $pdo->prepare("SELECT id FROM karyawan_divisi WHERE nip = ?");
    $stmt_karyawan->execute([$nip]);
    $karyawan = $stmt_karyawan->fetch();

    if ($karyawan) {
        // Cek jika akun supervisor sudah ada
        $stmt_check = $pdo->prepare("SELECT id FROM akun_supervisor WHERE username = ?");
        $stmt_check->execute([$nip]);
        $supervisor = $stmt_check->fetch();

        if (!$supervisor) {
            // Insert ke tabel akun_supervisor
            $stmt = $pdo->prepare("INSERT INTO akun_supervisor (username, password, created_by, karyawan_divisi_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nip, $nip, $master_id, $karyawan['id']]); // Gunakan nip sebagai username dan password, id karyawan_divisi
            echo "<script>alert('Akun supervisor berhasil dibuat.!'); window.location.href='supervisor.php';</script>";
           
        } else {
            echo "<script>alert('Akun supervisor dengan NIP ini sudah ada!'); window.location.href='supervisor.php';</script>";
         
        }
    } else {
        echo "<script>alert('NIP tidak ditemukan!'); window.location.href='supervisor.php';</script>";
       
    }
} else {
    echo "Parameter tidak valid.";
}
?>
