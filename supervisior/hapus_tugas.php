<?php
session_start(); // Memulai sesi
include '../config.php';

// Periksa apakah supervisor sudah login
if (!isset($_SESSION['karyawan_divisi_id'])) {
    die("Anda harus login terlebih dahulu.");
}

// Dapatkan ID supervisor yang sedang login
$karyawan_divisi_id = $_SESSION['karyawan_divisi_id'];

// Dapatkan tugas_id dari query string
$tugas_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$tugas_id) {
    die("ID tugas tidak ditemukan.");
}

try {
    // Mulai transaksi
    $pdo->beginTransaction();

    // Hapus penilaian tugas terlebih dahulu
    $delete_penilaian = $pdo->prepare("DELETE FROM penilaian_tugas WHERE tugas_id = ?");
    $delete_penilaian->execute([$tugas_id]);

    // Hapus tugas dari database
    $delete_tugas = $pdo->prepare("DELETE FROM tugas WHERE id = ? AND karyawan_divisi_id = ?");
    $delete_tugas->execute([$tugas_id, $karyawan_divisi_id]);

    // Komit transaksi
    $pdo->commit();

    echo "<script>alert('Tugas berhasil dihapus!'); window.location.href='tugas.php';</script>";
} catch (Exception $e) {
    // Rollback transaksi jika terjadi kesalahan
    $pdo->rollBack();
    echo "<script>alert('Terjadi kesalahan: " . $e->getMessage() . "'); window.location.href='tugas.php';</script>";
}
?>
