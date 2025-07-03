<?php
session_start();
include '../config.php'; // Pastikan koneksi ke database ada di file ini

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query untuk mengecek username dan password
    $query = "SELECT akun_supervisor.karyawan_divisi_id, karyawan_divisi.nama 
              FROM akun_supervisor 
              JOIN karyawan_divisi ON akun_supervisor.karyawan_divisi_id = karyawan_divisi.id 
              WHERE akun_supervisor.username = :username AND akun_supervisor.password = :password";

    $stmt = $pdo->prepare($query);
    
    // Bind parameters menggunakan PDO
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    
    // Eksekusi query
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Jika login berhasil
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $karyawan_divisi_id = $row['karyawan_divisi_id'];
        $nama = $row['nama'];
        
        // Simpan data ke session
        $_SESSION['login'] = true; // Set session login
        $_SESSION['nama'] = $nama;
        $_SESSION['karyawan_divisi_id'] = $karyawan_divisi_id;
        $_SESSION['karyawan_divisi_id'] = $row['karyawan_divisi_id']; // Asumsi user_id adalah karyawan_divisi_id
        
        // Redirect ke halaman dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Username atau Password salah.";
    }
    
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: #f7f7f7;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .login-box {
            width: 360px;
            padding: 20px;
            text-align: center;
        }
        .login-card-body {
            border-top: 3px solid #007bff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .login-box-msg {
            font-size: 1.5rem; /* Ukuran teks yang lebih besar */
            margin-bottom: 20px;
            color: #555;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        .login-box-msg img {
            width: 60px; /* Ukuran logo yang lebih besar */
            height: auto;
        }
        .form-control {
            border-radius: 20px;
            padding-left: 50px;
            font-size: 1rem; /* Ukuran font input */
        }
        .input-group {
            position: relative;
        }
        .input-group-text {
            border-radius: 20px 0 0 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 12px 20px;
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .form-control {
            padding-left: 50px;
            height: 50px;
            border: 1px solid #ced4da;
            font-size: 1rem; /* Ukuran font input */
        }
        .btn-primary {
            border-radius: 20px;
            background-color: #007bff;
            border-color: #007bff;
            font-size: 1rem; /* Ukuran font tombol */
            padding: 12px;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
    </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">
                <img src="../image/logo.jpg" alt="Logo">
                Silahkan Login
            </p>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="input-group mb-3">
                    <div class="input-group-text">
                        <span class="fas fa-user"></span>
                    </div>
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                </div>
                <div class="input-group mb-3">
                    <div class="input-group-text">
                        <span class="fas fa-lock"></span>
                    </div>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">Login</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
