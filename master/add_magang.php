<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: index.php");
    exit();
}
include "../config.php";

$userId = $_SESSION['user_id'];

try {
    $query = "SELECT nama FROM akun_master WHERE id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    $loggedInUser = isset($userData['nama']) ? $userData['nama'] : 'Pengguna';
    $userPhoto = 'https://via.placeholder.com/150/000000/FFFFFF/?text=User';
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Ambil data divisi
$divisi = $pdo->query("SELECT * FROM divisi")->fetchAll(PDO::FETCH_ASSOC);

// Cek jika form dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $asal_kampus_sekolah = $_POST['asal_kampus_sekolah'];
    $nim_nisn = $_POST['nim_nisn'];
    $masa_magang = $_POST['masa_magang'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $divisi_id = $_POST['divisi_id'];

    // Menangani upload file
    $surat_pengantar_kampus = $_FILES['surat_pengantar_kampus']['name'];
    $surat_penerimaan = $_FILES['surat_penerimaan']['name'];
    $foto = $_FILES['foto']['name'];

    $upload_dir = '../uploads/';
    move_uploaded_file($_FILES['surat_pengantar_kampus']['tmp_name'], $upload_dir . $surat_pengantar_kampus);
    move_uploaded_file($_FILES['surat_penerimaan']['tmp_name'], $upload_dir . $surat_penerimaan);
    move_uploaded_file($_FILES['foto']['tmp_name'], $upload_dir . $foto);

    // Dapatkan supervisor berdasarkan divisi yang dipilih
    $query = "SELECT id FROM karyawan_divisi WHERE divisi_id = :divisi_id ORDER BY RAND() LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':divisi_id', $divisi_id);
    $stmt->execute();
    $supervisor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Jika tidak ada supervisor ditemukan
    if ($supervisor === false) {
        echo "Tidak ada supervisor yang ditemukan untuk divisi ini.";
        exit();
    }
    
    $supervisor_id = $supervisor['id'];  // Menggunakan id sebagai PK

    // Tentukan status berdasarkan tanggal selesai magang
    $today = date("Y-m-d");
    $status = ($tanggal_selesai > $today) ? 'aktif' : 'telah selesai';

    try {
        $query = "
            INSERT INTO anak_magang 
            (nama, alamat, asal_kampus_sekolah, nim_nisn, masa_magang, tanggal_mulai, tanggal_selesai, status, supervisor_id, divisi_id, surat_pengantar_kampus, surat_penerimaan, foto)
            VALUES 
            (:nama, :alamat, :asal_kampus_sekolah, :nim_nisn, :masa_magang, :tanggal_mulai, :tanggal_selesai, :status, :supervisor_id, :divisi_id, :surat_pengantar_kampus, :surat_penerimaan, :foto)
        ";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':alamat', $alamat);
        $stmt->bindParam(':asal_kampus_sekolah', $asal_kampus_sekolah);
        $stmt->bindParam(':nim_nisn', $nim_nisn);
        $stmt->bindParam(':masa_magang', $masa_magang);
        $stmt->bindParam(':tanggal_mulai', $tanggal_mulai);
        $stmt->bindParam(':tanggal_selesai', $tanggal_selesai);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':supervisor_id', $supervisor_id);
        $stmt->bindParam(':divisi_id', $divisi_id);
        $stmt->bindParam(':surat_pengantar_kampus', $surat_pengantar_kampus);
        $stmt->bindParam(':surat_penerimaan', $surat_penerimaan);
        $stmt->bindParam(':foto', $foto);
        $stmt->execute();
        echo "<script>alert('Data Berhasil Ditambahkan.!'); window.location.href='anak_magang.php';</script>";
        
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

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
        }

        .card-header {
            background-color: #007bff;
            color: white;
            border-bottom: none;
            padding: 10px;
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
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Tambah Data Anak Magang</h3>
                    </div>
                    <div class="card-body">
                        <form action="add_magang.php" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="nama"><i class="fas fa-user"></i> Nama</label>
                                <input type="text" class="form-control" id="nama" name="nama" required>
                            </div>
                            <div class="form-group">
                                <label for="alamat"><i class="fas fa-map-marker-alt"></i> Alamat</label>
                                <input type="text" class="form-control" id="alamat" name="alamat" required>
                            </div>
                            <div class="form-group">
                                <label for="asal_kampus_sekolah"><i class="fas fa-school"></i> Asal Kampus/Sekolah</label>
                                <input type="text" class="form-control" id="asal_kampus_sekolah" name="asal_kampus_sekolah" required>
                            </div>
                            <div class="form-group">
                                <label for="nim_nisn"><i class="fas fa-id-card"></i> NIM/NISN</label>
                                <input type="text" class="form-control" id="nim_nisn" name="nim_nisn" required>
                            </div>
                            <div class="form-group">
                                <label for="masa_magang"><i class="fas fa-calendar"></i> Masa Magang</label>
                                <input type="text" class="form-control" id="masa_magang" name="masa_magang" required>
                            </div>
                            <div class="form-group">
                                <label for="tanggal_mulai"><i class="fas fa-calendar-alt"></i> Tanggal Mulai</label>
                                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
                            </div>
                            <div class="form-group">
                                <label for="tanggal_selesai"><i class="fas fa-calendar-check"></i> Tanggal Selesai</label>
                                <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" required>
                            </div>
                                                            <div class="form-group">
                                    <label for="divisi_id"><i class="fas fa-building"></i> Divisi</label>
                                    <select id="divisi_id" name="divisi_id" class="form-control form-control-sm" required>
                                        <option value="">Pilih Divisi</option>
                                        <?php foreach ($divisi as $row) : ?>
                                            <option value="<?= $row['id']; ?>"><?= $row['nama_divisi']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="supervisor_name" class="small">Nama Supervisor</label>
                                    <input type="text" class="form-control" id="supervisor_name" name="supervisor_name" readonly>
                                </div>

                                <!-- File Uploads -->
                                <div class="form-group">
                                <label for="surat_pengantar_kampus"><i class="fas fa-file-alt"></i> Surat Pengantar Kampus</label>
                                <input type="file" class="form-control-file" id="surat_pengantar_kampus" name="surat_pengantar_kampus">
                            </div>
                            <div class="form-group">
                                <label for="surat_penerimaan"><i class="fas fa-file-signature"></i> Surat Penerimaan</label>
                                <input type="file" class="form-control-file" id="surat_penerimaan" name="surat_penerimaan">
                            </div>
                            <div class="form-group">
                                <label for="foto"><i class="fas fa-camera"></i> Foto</label>
                                <input type="file" class="form-control-file" id="foto" name="foto">
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Simpan Data</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
document.getElementById('divisi_id').addEventListener('change', function() {
    var divisiId = this.value;

    if (divisiId) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_supervisor.php?divisi_id=' + divisiId, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    // Parse the JSON response
                    var response = JSON.parse(xhr.responseText);
                    // Update the supervisor name field with the received data
                    document.getElementById('supervisor_name').value = response.nama || 'Tidak Ada Supervisor';
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
        document.getElementById('supervisor_name').value = 'Tidak Ada Supervisor';
    }
});

</script>

</body>
</html>
