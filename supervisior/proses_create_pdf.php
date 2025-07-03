<?php
session_start(); // Memulai sesi
include '../config.php';
require_once __DIR__ . '../../vendor/autoload.php';

$nim_nisn = $_POST['nim_nisn']; // Pastikan mendapatkan nim_nisn dari form atau parameter

// Query untuk mengambil data anak magang
$anak_magang_query = $pdo->prepare("
    SELECT 
        anak_magang.nama AS nama_anak_magang, 
        karyawan_divisi.nama AS nama_supervisor, 
        divisi.nama_divisi AS nama_divisi,
        anak_magang.tanggal_mulai,  
        anak_magang.tanggal_selesai,
        karyawan_divisi.nip AS nip_supervisor
    FROM anak_magang
    JOIN karyawan_divisi ON anak_magang.supervisor_id = karyawan_divisi.id
    JOIN divisi ON karyawan_divisi.divisi_id = divisi.id
    WHERE anak_magang.nim_nisn = ?
");

$anak_magang_query->execute([$nim_nisn]);
$anak_magang_data = $anak_magang_query->fetch(PDO::FETCH_ASSOC);

if (!$anak_magang_data) {
    echo "Data anak magang tidak ditemukan!";
    exit;
}

// Query untuk mengambil data tugas anak magang
$tugas_query = $pdo->prepare("
    SELECT 
        tugas.nama_tugas, 
        tugas.uraian_tugas, 
        tugas.target,
        penilaian_tugas.nilai AS nilai_tugas
    FROM tugas
    JOIN penilaian_tugas ON tugas.id = penilaian_tugas.tugas_id
    WHERE tugas.anak_magang_id = ?
");
$tugas_query->execute([$nim_nisn]);
$tugas_data = $tugas_query->fetchAll(PDO::FETCH_ASSOC);

// Query untuk mengambil data absensi anak magang
$absensi_query = $pdo->prepare("
    SELECT 
        absensi.tanggal AS tanggal_absensi, 
        absensi.status_kehadiran, 
        absensi.skor AS nilai_absensi
    FROM absensi
    WHERE absensi.anak_magang_id = ?
");
$absensi_query->execute([$nim_nisn]);
$absensi_data = $absensi_query->fetchAll(PDO::FETCH_ASSOC);

// Menghitung total nilai dan rata-rata dari tugas dan absensi
$total_nilai_tugas = 0;
$total_nilai_absensi = 0;
$total_tugas = count($tugas_data);
$total_absensi = count($absensi_data);

// Menghitung jumlah nilai tugas
foreach ($tugas_data as $tugas) {
    $total_nilai_tugas += $tugas['nilai_tugas'];
}

// Menghitung jumlah nilai absensi
foreach ($absensi_data as $absensi) {
    $total_nilai_absensi += $absensi['nilai_absensi'];
}

// Total nilai keseluruhan
$total_nilai_keseluruhan = $total_nilai_tugas + $total_nilai_absensi;

// Menghitung rata-rata nilai
$jumlah_penilaian = $total_tugas + $total_absensi; // Total penilaian adalah tugas + absensi
$rata_rata_nilai = $jumlah_penilaian > 0 ? $total_nilai_keseluruhan / $jumlah_penilaian : 0;

// Menghitung nilai akhir berdasarkan 80% kegiatan (tugas) dan 20% absensi
$nilai_akhir_tugas = $total_tugas > 0 ? ($total_nilai_tugas / $total_tugas) * 0.80 : 0;
$nilai_akhir_absensi = $total_absensi > 0 ? ($total_nilai_absensi / $total_absensi) * 0.20 : 0;
$nilai_akhir = $nilai_akhir_tugas + $nilai_akhir_absensi;

// Inisialisasi mPDF
$mpdf = new \Mpdf\Mpdf();

// Membuat HTML untuk ditampilkan

$html = '
<h2>Laporan Magang</h2>
<h3>Identitas Anak Magang</h3>
<p>Nama: ' . $anak_magang_data['nama_anak_magang'] . '</p>
<p>Divisi: ' . $anak_magang_data['nama_divisi'] . '</p>
<p>Supervisor: ' . $anak_magang_data['nama_supervisor'] . '</p>
<p>Tanggal Mulai: ' . $anak_magang_data['tanggal_mulai'] . '</p>
<p>Tanggal Selesai: ' . $anak_magang_data['tanggal_selesai'] . '</p>

<h3>Data Kegiatan</h3>
<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="text-align: center; width: 5%;">No</th>
            <th style="text-align: center; width: 20%; word-wrap: break-word; word-break: break-word;">Nama Tugas</th>
            
            <th style="text-align: center; width: 15%;">Target</th>
            <th style="text-align: center; width: 15%;">Nilai</th>
        </tr>
    </thead>
    <tbody>';

$no = 1;
foreach ($tugas_data as $tugas) {
    $html .= '
    <tr>
        <td>' . $no++ . '</td>
        <td style="word-wrap: break-word; word-break: break-all;">' . $tugas['nama_tugas'] . '</td>
        
        <td>' . $tugas['target'] . '</td>
        <td>' . $tugas['nilai_tugas'] . '</td>
    </tr>';

}

$html .= '
    </tbody>
</table>

<h3>Data Absensi</h3>
<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal Absensi</th>
            <th>Status Kehadiran</th>
            <th>Skor Kehadiran</th>
        </tr>
    </thead>
    <tbody>';

$no = 1;
foreach ($absensi_data as $absensi) {
    $html .= '
        <tr>
            <td>' . $no++ . '</td>
            <td>' . $absensi['tanggal_absensi'] . '</td>
            <td>' . $absensi['status_kehadiran'] . '</td>
            <td>' . $absensi['nilai_absensi'] . '</td>
        </tr>';
}

$html .= '
    </tbody>
</table>';

// Tambahkan penilaian akhir
$html .= '
<h3>Penilaian Akhir</h3>
<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th>No</th>
            <th>Penilaian</th>
            <th>Nilai</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>Total Nilai</td>
            <td>' . $total_nilai_keseluruhan . '</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Rata-rata Nilai</td>
            <td>' . round($rata_rata_nilai, 2) . '</td>
        </tr>
        <tr>
            <td>3</td>
            <td>Nilai Akhir</td>
            <td>' . round($nilai_akhir, 2) . '</td>
        </tr>
    </tbody>
</table>';

// Menambahkan bagian tanda tangan
$html .= '

<table border="0" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">

    <tr>
        <td style="width: 50%; text-align: center;">

            <br></br>
            <br></br>
            <br></br>
            <br></br>
            <br></br>
            <br></br>
            <br></br>
            <br></br>
            <br></br>
            <p>' . $anak_magang_data['nama_supervisor'] . '</p>
            <p>___________________________</p>
            <p>NIP: ' . $anak_magang_data['nip_supervisor'] . '</p>
        </td>
        <td style="width: 50%; text-align: center;">
            <br></br>
            <br></br>
            <br></br>
            <br></br>
            <br></br>
            <br></br>
            <br></br>
            <br></br>
            <p>Johanes Supredo Sinaga Rumapea</p>
             <p>_____________________________________</p>
            <p>NIP: 19941226 201609 1 003</p>
        </td>
    </tr>
</table>';

// Menambahkan HTML ke mPDF dan mengenerate file PDF
$mpdf->WriteHTML($html);
$mpdf->Output();
?>
