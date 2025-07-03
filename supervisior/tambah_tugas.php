<?php
session_start(); // Memulai sesi
include '../config.php';

// Periksa apakah supervisor sudah login
if (!isset($_SESSION['karyawan_divisi_id'])) {
    die("Anda harus login terlebih dahulu.");
}

// Dapatkan ID supervisor yang sedang login
$karyawan_divisi_id = $_SESSION['karyawan_divisi_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_tugas = $_POST['nama_tugas'];
    $uraian_tugas = $_POST['uraian_tugas'];
    $target = $_POST['target'];
    $tenggat_pengumpulan = $_POST['tenggat_pengumpulan'];
    $anak_magang_ids = $_POST['anak_magang_id'];

    // Array untuk menyimpan ID anak magang yang masa magangnya tidak mencukupi tenggat pengumpulan
    $invalid_ids = [];
    $valid_ids = [];

    // Periksa semua anak magang
    foreach ($anak_magang_ids as $anak_magang_id) {
        // Ambil tanggal selesai magang dari anak magang yang dipilih
        $anak_magang_query = $pdo->prepare("SELECT nama, tanggal_selesai FROM anak_magang WHERE nim_nisn = ?");
        $anak_magang_query->execute([$anak_magang_id]);
        $anak_magang_data = $anak_magang_query->fetch();

        if ($anak_magang_data) {
            $tanggal_selesai = $anak_magang_data['tanggal_selesai'];

            // Periksa apakah tenggat pengumpulan melebihi tanggal selesai magang
            if ($tenggat_pengumpulan > $tanggal_selesai) {
                $invalid_ids[] = [
                    'id' => $anak_magang_id,
                    'nama' => $anak_magang_data['nama']
                ];
            } else {
                $valid_ids[] = $anak_magang_id;
            }
        } else {
            echo "Data anak magang tidak ditemukan untuk ID: " . htmlspecialchars($anak_magang_id);
        }
    }

    // Jika ada anak magang dengan tenggat pengumpulan yang tidak sesuai, tampilkan notifikasi
    if (!empty($invalid_ids)) {
        $invalid_names = array_map(function($item) {
            return htmlspecialchars($item['nama']);
        }, $invalid_ids);
        
        $invalid_names_list = implode(', ', $invalid_names);
        echo "<script>alert('Tenggat pengumpulan tidak boleh melebihi tanggal selesai magang untuk: " . $invalid_names_list . "'); window.location.href='tugas.php';</script>";
    } else {
        // Jika semua valid, masukkan data tugas ke database
        foreach ($valid_ids as $anak_magang_id) {
            $stmt = $pdo->prepare("INSERT INTO tugas (nama_tugas, uraian_tugas, target, tenggat_pengumpulan, anak_magang_id, karyawan_divisi_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nama_tugas, $uraian_tugas, $target, $tenggat_pengumpulan, $anak_magang_id, $karyawan_divisi_id]);
        }
        
        echo "<script>alert('Tugas Berhasil Ditambahkan.!'); window.location.href='tugas.php';</script>";
    }
}

// Ambil data anak magang yang di bawah naungan supervisor yang sedang login
$anak_magang_query = $pdo->prepare("SELECT * FROM anak_magang WHERE supervisor_id = ?");
$anak_magang_query->execute([$karyawan_divisi_id]);
$anak_magang = $anak_magang_query->fetchAll();
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Tugas</title>
    <!-- Link ke Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-group label {
            font-weight: bold;
        }
        .selected-children {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            background-color: #e9ecef;
            min-height: 50px;
        }
        .form-row {
            align-items: center;
        }
        .form-group.button-group {
            margin-top: 24px;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Tambah Tugas</h2>

        <form method="post" action="">
            <div class="form-row">
                <div class="form-group col-md-9">
                    <label>Anak Magang yang Dipilih</label>
                    <input type="text" class="form-control" id="selectedAnakMagang" readonly placeholder="Tidak ada anak magang yang dipilih">
                </div>
                <div class="form-group col-md-3 button-group">
                    <!-- Tombol untuk membuka modal -->
                    <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#anakMagangModal">
                        Pilih Anak Magang
                    </button>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="anakMagangModal" tabindex="-1" role="dialog" aria-labelledby="anakMagangModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="anakMagangModalLabel">Pilih Anak Magang</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <?php if (!empty($anak_magang)): ?>
                                <?php foreach ($anak_magang as $anak): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="anak_magang_id[]" value="<?= htmlspecialchars($anak['nim_nisn']) ?>" id="anak_<?= htmlspecialchars($anak['nim_nisn']) ?>" data-name="<?= htmlspecialchars($anak['nama']) ?>">
                                        <label class="form-check-label" for="anak_<?= htmlspecialchars($anak['nim_nisn']) ?>">
                                            <?= htmlspecialchars($anak['nama']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>Data anak magang tidak ditemukan.</p>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                            <button type="button" class="btn btn-primary" id="confirmSelection">Pilih</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Nama Tugas</label>
                <input type="text" name="nama_tugas" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Uraian Tugas</label>
                <textarea name="uraian_tugas" class="form-control" required></textarea>
            </div>

            <div class="form-group">
                <label>Target</label>
                <input type="text" name="target" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Tenggat Pengumpulan</label>
                <input type="date" name="tenggat_pengumpulan" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-success">Tambah Tugas</button>
        </form>
    </div>

    <!-- Link ke Bootstrap JS dan jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('confirmSelection').addEventListener('click', function () {
            var selectedNames = [];
            var selectedItems = [];
            
            document.querySelectorAll('input[name="anak_magang_id[]"]:checked').forEach(function (checkbox) {
                selectedItems.push(checkbox.value);
                selectedNames.push(checkbox.getAttribute('data-name'));
            });

            var displayElement = document.getElementById('selectedAnakMagang');
            if (selectedNames.length > 0) {
                displayElement.value = selectedNames.join(', ');
            } else {
                displayElement.value = 'Tidak ada anak magang yang dipilih.';
            }

            $('#anakMagangModal').modal('hide');
        });
    </script>
</body>
</html>
