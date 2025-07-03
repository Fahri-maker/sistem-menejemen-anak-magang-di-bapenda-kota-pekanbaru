<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: index.php");
    exit();
}
include "../config.php";

// Mengambil data divisi untuk dropdown
$divisi = $pdo->query("SELECT * FROM divisi")->fetchAll(PDO::FETCH_ASSOC);

// Mendapatkan ID anak magang (nim_nisn) dari parameter URL
$nim_nisn = $_GET['nim_nisn'];

// Memeriksa apakah form telah disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Mengambil data dari form
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $asal_kampus_sekolah = $_POST['asal_kampus_sekolah'];
    $divisi_id = $_POST['divisi_id'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $surat_pengantar_kampus = $_POST['surat_pengantar_kampus'];
    $surat_penerimaan = $_POST['surat_penerimaan'];
    $foto = $_POST['foto'];
    $masa_magang = $_POST['masa_magang'];

    // Update data anak magang di database menggunakan PDO
    $query = "UPDATE anak_magang SET nama = :nama, alamat = :alamat, asal_kampus_sekolah = :asal_kampus_sekolah, divisi_id = :divisi_id, supervisor_id = (SELECT id FROM karyawan_divisi WHERE divisi_id = :divisi_id LIMIT 1), tanggal_mulai = :tanggal_mulai, tanggal_selesai = :tanggal_selesai, surat_pengantar_kampus = :surat_pengantar_kampus, surat_penerimaan = :surat_penerimaan, foto = :foto, masa_magang = :masa_magang WHERE nim_nisn = :nim_nisn";

    $stmt = $pdo->prepare($query);
    
    $stmt->bindParam(':nama', $nama);
    $stmt->bindParam(':alamat', $alamat);
    $stmt->bindParam(':asal_kampus_sekolah', $asal_kampus_sekolah);
    $stmt->bindParam(':divisi_id', $divisi_id);
    $stmt->bindParam(':tanggal_mulai', $tanggal_mulai);
    $stmt->bindParam(':tanggal_selesai', $tanggal_selesai);
    $stmt->bindParam(':surat_pengantar_kampus', $surat_pengantar_kampus);
    $stmt->bindParam(':surat_penerimaan', $surat_penerimaan);
    $stmt->bindParam(':foto', $foto);
    $stmt->bindParam(':masa_magang', $masa_magang);
    $stmt->bindParam(':nim_nisn', $nim_nisn);

    if ($stmt->execute()) {
        // Script untuk menampilkan alert dan redirect
        echo "<script>
                alert('Data anak magang berhasil diperbarui!');
                window.location.href='anak_magang.php';
              </script>";
        exit();
    } else {
        $error = "Terjadi kesalahan saat memperbarui data: " . $stmt->errorInfo()[2];
    }
}

// Mengambil data anak magang berdasarkan nim_nisn menggunakan PDO
$query = "SELECT * FROM anak_magang WHERE nim_nisn = :nim_nisn";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':nim_nisn', $nim_nisn);
$stmt->execute();
$magang = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$magang) {
    die("Data anak magang tidak ditemukan.");
}

// Mengambil nama supervisor berdasarkan divisi_id dari anak magang
$query = "SELECT nama FROM karyawan_divisi WHERE divisi_id = :divisi_id LIMIT 1";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':divisi_id', $magang['divisi_id']);
$stmt->execute();
$supervisor = $stmt->fetch(PDO::FETCH_ASSOC);

$supervisor_name = $supervisor ? $supervisor['nama'] : 'Tidak Ada Supervisor';

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Anak Magang</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-header {
            background-color: #007bff;
            color: white;
            border-bottom: none;
            padding: 15px;
            border-radius: 8px 8px 0 0;
            text-align: center;
            font-size: 1.5rem;
        }

        .card-body {
            padding: 20px;
        }

        .form-group label {
            font-weight: 600;
        }

        .form-control {
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 6px;
            padding: 10px 20px;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .form-group i {
            margin-right: 8px;
            color: #007bff;
        }

        .form-control-file {
            border-radius: 6px;
            padding: 10px;
            transition: all 0.3s ease;
        }

        .form-control-file:focus {
            border-color: #007bff;
            box-shadow: none;
        }
    </style>
    <script>
        // Function to update supervisor name based on selected division
        function updateSupervisorName() {
            var divisiId = document.getElementById('divisi_id').value;
            var supervisorNameField = document.getElementById('supervisor_name');

            if (divisiId) {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'get_supervisor.php?divisi_id=' + divisiId, true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            supervisorNameField.textContent = response.nama || 'Tidak Ada Supervisor';
                        } catch (e) {
                            console.error('Error parsing JSON:', e);
                        }
                    } else {
                        console.error('Server Error:', xhr.status);
                    }
                };
                xhr.onerror = function() {
                    console.error('Request Error');
                };
                xhr.send();
            } else {
                supervisorNameField.textContent = 'Tidak Ada Supervisor';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            var divisiId = document.getElementById('divisi_id').value;
            if (divisiId) {
                updateSupervisorName();
            }
        });
    </script>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Tambah Data Anak Magang</h3>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <div class="card-body">
                        <form method="post">
                            <div class="form-group">
                                <label for="nama"><i class="fas fa-user"></i> Nama</label>
                                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($magang['nama']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="alamat"><i class="fas fa-address-card"></i> Alamat</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php echo htmlspecialchars($magang['alamat']); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="asal_kampus_sekolah"><i class="fas fa-school"></i> Asal Kampus/ Sekolah</label>
                                <input type="text" class="form-control" id="asal_kampus_sekolah" name="asal_kampus_sekolah" value="<?php echo htmlspecialchars($magang['asal_kampus_sekolah']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="divisi_id"><i class="fas fa-building"></i> Divisi</label>
                                <select class="form-control" id="divisi_id" name="divisi_id" onchange="updateSupervisorName()" required>
                                    <option value="">Pilih Divisi</option>
                                    <?php foreach ($divisi as $d): ?>
                                        <option value="<?php echo htmlspecialchars($d['id']); ?>" <?php echo $magang['divisi_id'] == $d['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($d['nama_divisi']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="supervisor_name"><i class="fas fa-user-tie"></i> Nama Supervisor</label>
                                <p id="supervisor_name"><?php echo htmlspecialchars($supervisor_name); ?></p>
                            </div>

                            <div class="form-group">
                                <label for="tanggal_mulai"><i class="fas fa-calendar-day"></i> Tanggal Mulai</label>
                                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" value="<?php echo htmlspecialchars($magang['tanggal_mulai']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="tanggal_selesai"><i class="fas fa-calendar-day"></i> Tanggal Selesai</label>
                                <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" value="<?php echo htmlspecialchars($magang['tanggal_selesai']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="surat_pengantar_kampus"><i class="fas fa-file-alt"></i> Surat Pengantar Kampus</label>
                                <input type="file" class="form-control-file" id="surat_pengantar_kampus" name="surat_pengantar_kampus">
                            </div>

                            <div class="form-group">
                                <label for="surat_penerimaan"><i class="fas fa-file-alt"></i> Surat Penerimaan</label>
                                <input type="file" class="form-control-file" id="surat_penerimaan" name="surat_penerimaan">
                            </div>

                            <div class="form-group">
                                <label for="foto"><i class="fas fa-image"></i> Foto</label>
                                <input type="file" class="form-control-file" id="foto" name="foto">
                            </div>

                            <div class="form-group">
                                <label for="masa_magang"><i class="fas fa-hourglass-start"></i> Masa Magang (dalam hari)</label>
                                <input type="number" class="form-control" id="masa_magang" name="masa_magang" value="<?php echo htmlspecialchars($magang['masa_magang']); ?>" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
