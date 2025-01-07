<?php
include 'config.php';

if (isset($_GET['id_pengajuan'])) {
    $id_pengajuan = $_GET['id_pengajuan'];
    $sql = "SELECT file_proposal FROM pengajuan WHERE id_pengajuan = $id_pengajuan";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="proposal.pdf"');
        echo $data['file_proposal'];
    } else {
        echo "File tidak ditemukan.";
    }
}
?>
