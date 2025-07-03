<?php 
session_start(); // Memulai sesi

include '../config.php';

// Pastikan NIM/NISN tidak kosong
if (empty($_POST['nim_nisn'])) {
    $errors['name'] = 'NIM/NISN is required.';
    exit;
}

$nim_nisn = $_POST['nim_nisn'];
$karyawan_divisi_id = $_SESSION['karyawan_divisi_id'];

// Ambil data anak magang yang di bawah naungan supervisor yang sedang login
$anak_magang_query = $pdo->prepare("SELECT * FROM anak_magang WHERE supervisor_id = ? AND nim_nisn = ?");
$anak_magang_query->execute([$karyawan_divisi_id, $nim_nisn]);
$anak_magang = $anak_magang_query->fetch();

// Ambil data kegiatan dan absensi
$laporan_query = $pdo->prepare("
    SELECT 
        anak_magang.nama AS nama_anak_magang, 
        karyawan_divisi.nama AS nama_supervisor, 
        divisi.nama_divisi AS nama_divisi,
        anak_magang.tanggal_mulai,
        anak_magang.tanggal_selesai,
        tugas.nama_tugas, 
        tugas.uraian_tugas, 
        tugas.target,
        penilaian_tugas.id AS id_penilaian_tugas, 
        penilaian_tugas.nilai AS nilai_tugas,
        absensi.id AS id_absensi, 
        absensi.tanggal AS tanggal_absensi, 
        absensi.status_kehadiran, 
        absensi.skor AS nilai_absensi
    FROM anak_magang
    JOIN tugas ON anak_magang.nim_nisn = tugas.anak_magang_id
    JOIN penilaian_tugas ON tugas.id = penilaian_tugas.tugas_id
    JOIN absensi ON anak_magang.nim_nisn = absensi.anak_magang_id
    JOIN karyawan_divisi ON tugas.karyawan_divisi_id = karyawan_divisi.id
    JOIN divisi ON karyawan_divisi.divisi_id = divisi.id
    WHERE anak_magang.nim_nisn = ?
");

$laporan_query->execute([$nim_nisn]);
$laporan = $laporan_query->fetchAll(PDO::FETCH_ASSOC);

// Cek apakah data ditemukan
if (empty($laporan)) {
    echo "Tidak ada data yang ditemukan untuk NIM/NISN yang dipilih.";
    exit;
}

// Menghitung total nilai tugas dan absensi
$total_nilai_tugas = 0;
$total_nilai_absensi = 0;
$jumlah_tugas = 0;
$jumlah_absensi = 0;

foreach ($laporan as $row) {
    if (!empty($row['nilai_tugas'])) {
        $total_nilai_tugas += $row['nilai_tugas'];
        $jumlah_tugas++;
    }
    if (!empty($row['nilai_absensi'])) {
        $total_nilai_absensi += $row['nilai_absensi'];
        $jumlah_absensi++;
    }
}

// Hitung rata-rata tugas dan absensi
$rata_rata_tugas = $jumlah_tugas > 0 ? $total_nilai_tugas / $jumlah_tugas : 0;
$rata_rata_absensi = $jumlah_absensi > 0 ? $total_nilai_absensi / $jumlah_absensi : 0;

// Menghitung nilai akhir (80% tugas dan 20% absensi)
$nilai_akhir = ($rata_rata_tugas * 0.8) + ($rata_rata_absensi * 0.2);

// Simpan laporan ke dalam tabel laporan
$insert_laporan = $pdo->prepare("
    INSERT INTO laporan (nilai_akhir, penilaian_tugas_id, absensi_id) 
    VALUES (?, ?, ?)
");
$insert_laporan->execute([$nilai_akhir, $laporan[0]['id_penilaian_tugas'], $laporan[0]['id_absensi']]);

// Data untuk dikembalikan sebagai respons JSON
$arr_data = [
    'nilai_akhir' => $nilai_akhir,
    'total_nilai_tugas' => $total_nilai_tugas,
    'total_nilai_absensi' => $total_nilai_absensi,
    'rata_rata_tugas' => $rata_rata_tugas,
    'rata_rata_absensi' => $rata_rata_absensi,
    'jumlah_tugas' => $jumlah_tugas,
    'jumlah_absensi' => $jumlah_absensi
];

// Kembalikan data dalam format JSON
echo json_encode([
    'status' => 'ok',
    'res_data' => $laporan,
    'laporan' => $arr_data,
    'data_anak_magang' => $anak_magang
]);
die;
?>
