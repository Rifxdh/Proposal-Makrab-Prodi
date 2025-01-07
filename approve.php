<?php
include 'config.php';

$id_pengajuan = $_GET['id_pengajuan'];

if (isset($_POST['approve'])) {
    $sql = "INSERT INTO persetujuan (id_pengajuan, status, tanggal_persetujuan)
            VALUES ('$id_pengajuan', 'Disetujui', NOW())";
    $conn->query($sql);
    echo "Proposal disetujui!";
} elseif (isset($_POST['reject'])) {
    $notes = $_POST['notes'];
    $sql = "INSERT INTO persetujuan (id_pengajuan, status, rejection_notes, tanggal_persetujuan)
            VALUES ('$id_pengajuan', 'Ditolak', '$notes', NOW())";
    $conn->query($sql);
    echo "Proposal ditolak!";
}
?>
