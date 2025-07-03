<?php
include '../config.php';
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['nama'])) {
    header("Location: login.php");
    exit();
}

// Dapatkan ID supervisor yang sedang login dari sesi
$karyawan_divisi_id = $_SESSION['karyawan_divisi_id'];

// Ambil data anak magang yang di bawah naungan supervisor yang sedang login
$anak_magang_query = $pdo->prepare("SELECT * FROM anak_magang WHERE supervisor_id = ?");
$anak_magang_query->execute([$karyawan_divisi_id]);
$anak_magang = $anak_magang_query->fetchAll();

// if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//     $nim_nisn = $_POST['nim_nisn'];

//     // Ambil data supervisor, anak magang, kegiatan, dan absensi
//     $laporan_query = $pdo->prepare("
//         SELECT 
//             anak_magang.nama AS nama_anak_magang, 
//             karyawan_divisi.nama AS nama_supervisor, 
//             divisi.nama_divisi AS nama_divisi,
//             tugas.nama_tugas, 
//             tugas.uraian_tugas, 
//             penilaian_tugas.id AS id_penilaian_tugas, 
//             penilaian_tugas.nilai AS nilai_tugas,
//             absensi.id AS id_absensi, 
//             absensi.tanggal AS tanggal_absensi, 
//             absensi.status_kehadiran, 
//             absensi.skor AS nilai_absensi
//         FROM anak_magang
//         JOIN tugas ON anak_magang.nim_nisn = tugas.anak_magang_id
//         JOIN penilaian_tugas ON tugas.id = penilaian_tugas.tugas_id
//         JOIN absensi ON anak_magang.nim_nisn = absensi.anak_magang_id
//         JOIN karyawan_divisi ON tugas.karyawan_divisi_id = karyawan_divisi.id
//         JOIN divisi ON karyawan_divisi.divisi_id = divisi.id
//         WHERE anak_magang.nim_nisn = ?
//     ");
//     $laporan_query->execute([$nim_nisn]);
//     $laporan = $laporan_query->fetchAll(PDO::FETCH_ASSOC);

//     // Cek apakah data ditemukan
//     if (empty($laporan)) {
//         echo "Tidak ada data yang ditemukan untuk NIM/NISN yang dipilih.";
//         exit;
//     }

//     // Menghitung nilai akhir (80% tugas dan 20% absensi)
//     $total_nilai_tugas = 0;
//     $total_nilai_absensi = 0;
//     foreach ($laporan as $data) {
//         $total_nilai_tugas += $data['nilai_tugas'];
//         $total_nilai_absensi += $data['nilai_absensi'];
//     }

//     $nilai_akhir = ($total_nilai_tugas * 0.8) + ($total_nilai_absensi * 0.2);

//     // Simpan laporan ke dalam tabel laporan
//     $insert_laporan = $pdo->prepare("
//         INSERT INTO laporan (nilai_akhir, penilaian_tugas_id, absensi_id) 
//         VALUES (?, ?, ?)
//     ");
//     $insert_laporan->execute([$nilai_akhir, $laporan[0]['id_penilaian_tugas'], $laporan[0]['id_absensi']]);

//     // Tampilkan hasil laporan
//     echo "<h3>Laporan untuk " . htmlspecialchars($laporan[0]['nama_anak_magang']) . "</h3>";
//     echo "<p>Supervisor: " . htmlspecialchars($laporan[0]['nama_supervisor']) . " (Divisi: " . htmlspecialchars($laporan[0]['nama_divisi']) . ")</p>";
//     echo "<p>Kegiatan: " . htmlspecialchars($laporan[0]['nama_tugas']) . " - " . htmlspecialchars($laporan[0]['uraian_tugas']) . "</p>";
//     echo "<p>Nilai Tugas: " . htmlspecialchars($total_nilai_tugas) . "</p>";
//     echo "<p>Nilai Absensi: " . htmlspecialchars($total_nilai_absensi) . "</p>";
//     echo "<p>Nilai Akhir: " . htmlspecialchars($nilai_akhir) . "</p>";
// }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Laporan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap-theme.min.css" integrity="sha384-6pzBo3FDv/PJ8r2KRkGHifhEocL+1X2rVCTTkUfGk7/0pbek5mMa1upzvWbrUbOZ" crossorigin="anonymous">
</head>
<body>
    
    <h2>Buat Laporan Magang</h2>
  
    <form method="post" action="proses_create_pdf.php" id="formlaporan">
        <label>Anak Magang</label>
        <select name="nim_nisn" required>
            <?php foreach ($anak_magang as $anak): ?>
                <option value="<?= htmlspecialchars($anak['nim_nisn']) ?>"><?= htmlspecialchars($anak['nama']) ?></option>
            <?php endforeach; ?>
        </select><br><br>
        <button type="submit" value="Submit">Buat Laporan</button>
    </form>
            
   
    <!-- <button onclick="send()">Buat Laporan</button> -->

    <div id="hasil" class="mt-20">
        <!-- Hasil akan ditampilkan di sini -->
    </div>
</body>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"></script>

<script>
  function send() {
    $.ajax({
        type: 'POST',
        url: "proses_create_pdf.php",  
        data: $('#formlaporan').serialize(),
        dataType: "json",
        success: function(data) {
            // Tampilkan Identitas Siswa
            var html = '<h3>Identitas Siswa</h3>';
            html += '<p>Nama: ' + data.data_anak_magang.nama + '</p>';
            html += '<p>NIM/NISN: ' + data.data_anak_magang.nim_nisn + '</p>';
            html += '<p>Divisi: ' + data.res_data.nama_divisi + '</p>';
            html += '<p>Supervisor: ' + data.res_data.nama_supervisor + '</p>';
            html += '<p>Periode Magang: ' + data.res_data.tanggal_mulai + ' - ' + data.res_data.tanggal_selesai + '</p>';

            // Tabel Kegiatan
            html += '<h3>Data Kegiatan</h3>';
            html += '<table class="table table-bordered">';
            html += '<thead><tr><th>No</th><th>Nama Kegiatan</th><th>Target</th><th>Kegiatan</th><th>Nilai</th></tr></thead>';
            html += '<tbody>';
            html += '<tr><td>1</td><td>' + data.res_data.nama_tugas + '</td><td>' + data.res_data.target + '</td><td>' + data.res_data.uraian_tugas + '</td><td>' + data.res_data.nilai_tugas + '</td></tr>';
            html += '</tbody></table>';

            // Tabel Presensi
            html += '<h3>Data Presensi</h3>';
            html += '<table class="table table-bordered">';
            html += '<thead><tr><th>No</th><th>Tanggal Presensi</th><th>Status Kehadiran</th><th>Nilai</th></tr></thead>';
            html += '<tbody>';
            html += '<tr><td>1</td><td>' + data.res_data.tanggal_absensi + '</td><td>' + data.res_data.status_kehadiran + '</td><td>' + data.res_data.nilai_absensi + '</td></tr>';
            html += '</tbody></table>';

            // Tabel Penilaian Akhir
            html += '<h3>Penilaian Akhir</h3>';
            html += '<table class="table table-bordered">';
            html += '<thead><tr><th>No</th><th>Jumlah Nilai</th><th>Rata-rata</th><th>Total Nilai Akhir</th></tr></thead>';
            html += '<tbody>';
            html += '<tr><td>1</td><td>' + data.laporan.total_nilai_tugas + '</td><td>' + data.laporan.rata_rata_tugas + '</td><td>' + data.laporan.nilai_akhir + '</td></tr>';
            html += '</tbody></table>';

            $('#hasil').html(html); // Menampilkan hasil
        }
    });
}

</script>
</html>
