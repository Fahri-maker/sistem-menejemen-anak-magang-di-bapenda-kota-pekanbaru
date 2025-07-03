<?php
include "../config.php"; // Include file koneksi dengan PDO

header('Content-Type: application/json');

if (isset($_GET['divisi_id'])) {
    $divisi_id = $_GET['divisi_id'];

    // Query untuk mengambil nama supervisor berdasarkan divisi yang dipilih
    $query = "SELECT k.nama FROM karyawan_divisi k 
              JOIN divisi d ON k.divisi_id = d.id 
              WHERE d.id = :divisi_id";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':divisi_id', $divisi_id, PDO::PARAM_INT);
    $stmt->execute();
    $supervisor_name = $stmt->fetchColumn();

    // Mengirimkan nama supervisor dalam format JSON
    echo json_encode(['nama' => $supervisor_name]);
}
?>
