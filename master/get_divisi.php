<?php
include "../config.php";

if (isset($_POST['divisi_id'])) {
    $divisi_id = $_POST['divisi_id'];

    $stmt = $pdo->prepare("SELECT nama_divisi FROM divisi WHERE id = ?");
    $stmt->execute([$divisi_id]);
    $divisi = $stmt->fetch();

    if ($divisi) {
        echo "<h3>Nama Divisi: " . htmlspecialchars($divisi['nama_divisi']) . "</h3>";
    } else {
        echo "Divisi tidak ditemukan.";
    }
}
?>
