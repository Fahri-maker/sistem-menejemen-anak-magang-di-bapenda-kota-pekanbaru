<?php
session_start();
if (!isset($_SESSION['anak_magang_id'])) {
    header("Location: index.php");
    exit();
}

include '../config.php';

$nim_nisn = '123456789'; // Gantilah dengan nim_nisn dari sesi login
$laporan = $pdo->query("SELECT * FROM laporan WHERE nim_nisn = '$nim_nisn'")->fetch();

if ($laporan) {
    echo "<h2>Laporan Evaluasi</h2>";
    echo "<p>Nama: " . $laporan['nama'] . "</p>";
    echo "<p>Total Nilai Tugas: " . $laporan['nilai_tugas'] . "</p>";
    echo "<p>Total Nilai Absensi: " . $laporan['nilai_absensi'] . "</p>";
    echo "<p>Nilai Akhir: " . $laporan['nilai_akhir'] . "</p>";
} else {
    echo "Laporan belum tersedia.";
}
?>
