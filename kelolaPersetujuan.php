<?php
require_once 'config.php';
// Menangani pembaruan status persetujuan
if (isset($_POST['update'])) {
    $id_pengajuan = $_POST['id_pengajuan'];
    $status = $_POST['status'];

    // Validasi input status
    if (in_array($status, ['Pending', 'Disetujui', 'Ditolak'])) {
        $query = "UPDATE pengajuan SET status = '$status' WHERE id_pengajuan = '$id_pengajuan'";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Status berhasil diperbarui!'); window.location='kelolaPersetujuan.php';</script>";
        } else {
            echo "<script>alert('Gagal memperbarui status!');</script>";
        }
    } else {
        echo "<script>alert('Status tidak valid!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <script src="https://unpkg.com/feather-icons"></script>
    <link rel="stylesheet" href="https://use.typekit.net/your-adobe-font-id.css">
    <script src="jquery.js"></script>
    <title>Kelola Persetujuan</title>
</head>
<body>

<?php include 'header.php'; ?> 

<?php 

$query = "SELECT judul_proposal, status FROM pengajuan LIMIT 6";
$aside = $conn->query($query);

// $query = "SELECT  nim, nama_mahasiswa, foto_mahasiswa, program_studi FROM info_mahasiswa";
// $bio = $conn->query($query);

?>

<div class="menu">
        <nav>
            <div class="nav-menu">
                <ul>
                    <li><button id="btnPengajuan" onclick="window.location.href='pengajuan_crud.php'">Pengajuan</button></li>
                    <li><button id="btnPersetujuan" onclick="window.location.href='persetujuan_crud.php'">Persetujuan</button></li>
                    <li><button id="btnKelolaPengajuan" onclick="window.location.href='kelolaPengajuan.php'">Kelola Pengajuan</button></li>
                    <li><button id="btnKelolaPersetujuan" onclick="window.location.href='kelolaPersetujuan.php'">Kelola Persetujuan</button></li>
                    <li><button id="btnKelolaHeaderFooter" onclick="window.location.href='kelola_header_footer.php'">Kelola Header dan Footer</button></li>
                </ul>
            </div>
        </nav>


        <section>
    <div id="wadah" class="wadah">
        <h3 class="form-title">Kelola Persetujuan</h3>
        <table class="proposal-table" border="1">
            <thead>
                <tr> 
                    <th>Nama Mahasiswa</th>
                    <th>Subjek Pengajuan</th>
                    <th>Deskripsi</th>
                    <th>Status</th>
                    <th>Tanggal Pengajuan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM pengajuan";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                            <td>{$row['nama_mahasiswa']}</td>
                            <td>{$row['judul_proposal']}</td>
                            <td>{$row['deskripsi']}</td>
                            <td>{$row['status']}</td>
                            <td>{$row['tanggal_pengajuan']}</td>
                            <td class='action-buttons'>
                                <form method='POST' action='kelolaPersetujuan.php'>
                                    <input type='hidden' name='id_pengajuan' value='{$row['id_pengajuan']}'>
                                    <select name='status' class='action-icon'>
                                        <option value='Pending' " . ($row['status'] == 'Pending' ? 'selected' : '') . ">Pending</option>
                                        <option value='Disetujui' " . ($row['status'] == 'Disetujui' ? 'selected' : '') . ">Disetujui</option>
                                        <option value='Ditolak' " . ($row['status'] == 'Ditolak' ? 'selected' : '') . ">Ditolak</option>
                                    </select>
                                    <button type='submit' name='update' class='submit-button'>Update</button>
                                </form>
                            </td>
                        </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</section>


        <aside>
        <h3>Notifikasi Pengajuan</h3>
    <table>
        <tbody>
            <?php
            if ($aside->num_rows > 0) {
                // Output data dari setiap baris
                while($row = $aside->fetch_assoc()) {
                    echo "<tr>
                            <td id='td1'>" . htmlspecialchars($row['judul_proposal']) . "</td>
                            <td id='td2'>" . htmlspecialchars($row['status']) . "</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='2'>Tidak ada data pengajuan</td></tr>";
            }
            ?>
        </tbody>
    </table>
        </aside>
    </div>

    <script>

        $(document).ready(function() {
            
        });

    </script>

<?php include 'footer.php'; ?>

</body>
</html>