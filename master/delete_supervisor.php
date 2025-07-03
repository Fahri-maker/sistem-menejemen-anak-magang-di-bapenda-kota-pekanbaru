<?php
session_start();
include "../config.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Hapus dari tabel akun_supervisor
    $stmt = $pdo->prepare("DELETE FROM akun_supervisor WHERE id = ?");
    $stmt->execute([$id]);

    // Redirect kembali setelah menghapus
    header("Location: supervisor.php");
    exit();
} else {
    echo "Parameter tidak valid.";
}
?>
