<?php
require_once 'config.php';

// Handle delete action
if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM pengajuan WHERE id_pengajuan = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: kelolaPengajuan.php");
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pengajuan']) && isset($_POST['status'])) {
    $id = intval($_POST['id_pengajuan']);
    $status = $_POST['status'];
    $sql = "UPDATE pengajuan SET status = ? WHERE id_pengajuan = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
}

// Fetch data for display
$sql_select = "SELECT id_pengajuan, nama_mahasiswa, judul_proposal, deskripsi, tanggal_pelaksanaan, tanggal_pengajuan, status FROM pengajuan ORDER BY id_pengajuan DESC";
$result = $conn->query($sql_select);
$data_pengajuan = $result->fetch_all(MYSQLI_ASSOC);
// $conn->close();

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
    <title>Kelola Pengajuan Proposal</title>
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
        <h3 class="form-title">Kelola Pengajuan Proposal</h3>
        <main>
            <table class="proposal-table">
                <thead>
                    <tr>
                        <th>Nama Mahasiswa</th>
                        <th>Judul Proposal</th>
                        <th>Deskripsi</th>
                        <th>Tanggal Pelaksanaan</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data_pengajuan as $pengajuan): ?>
                        <tr>
                            <td><?php echo $pengajuan['nama_mahasiswa']; ?></td>
                            <td><?php echo $pengajuan['judul_proposal']; ?></td>
                            <td><?php echo $pengajuan['deskripsi']; ?></td>
                            <td><?php echo $pengajuan['tanggal_pelaksanaan']; ?></td>
                            <td><?php echo $pengajuan['tanggal_pengajuan']; ?></td>
                            <td>
                                <form action="" method="POST" style="display: inline;">
                                    <input type="hidden" name="id_pengajuan" value="<?php echo $pengajuan['id_pengajuan']; ?>">
                                    <select name="status" class="action-icon" onchange="this.form.submit()">
                                        <option value="Pending" <?php echo $pengajuan['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Disetujui" <?php echo $pengajuan['status'] === 'Disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                                        <option value="Ditolak" <?php echo $pengajuan['status'] === 'Ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                                    </select>
                                </form>
                            </td>
                            <td class="action-buttons">
                                <a href="?id=<?php echo $pengajuan['id_pengajuan']; ?>&action=delete" onclick="return confirm('Apakah Anda yakin ingin menghapus?')" class="action-icon">
                                    <i data-feather="trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
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
<script>feather.replace();</script>
</body>
</html>