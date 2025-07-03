<?php
session_start();
include '../config.php';

// Periksa apakah supervisor sudah login
if (!isset($_SESSION['karyawan_divisi_id'])) {
    die("Anda harus login terlebih dahulu.");
}

// Dapatkan ID supervisor yang sedang login
$karyawan_divisi_id = $_SESSION['karyawan_divisi_id'];

// // Debug: Tampilkan data POST untuk memastikan data yang dikirim
// echo "<pre>";
// print_r($_POST);
// echo "</pre>";

// Jika POST data ada
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $tugas_id = $_POST['id']; // Pastikan nama parameter sesuai
        $nama_tugas = $_POST['nama_tugas'];
        $uraian_tugas = $_POST['uraian_tugas'];
        $target = $_POST['target'];
        $tenggat_pengumpulan = $_POST['tenggat_pengumpulan'];

        // Update database
        $stmt = $pdo->prepare("UPDATE tugas SET nama_tugas = ?, uraian_tugas = ?, target = ?, tenggat_pengumpulan = ? WHERE id = ?");
        $stmt->execute([$nama_tugas, $uraian_tugas, $target, $tenggat_pengumpulan, $tugas_id]);

        echo "<script>alert('Tugas berhasil diupdate.!'); window.location.href='tugas.php';</script>";
    } else {
        echo "Error: Tugas ID tidak ditemukan.";
    }
}
?>
