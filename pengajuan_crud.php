<?php
// Konfigurasi koneksi database
require_once 'config.php';

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Logika untuk menghapus data
if (isset($_GET['id'], $_GET['action']) && $_GET['action'] === 'delete') {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM pengajuan WHERE id_pengajuan = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $message = "Pengajuan berhasil dihapus.";
    } else {
        $message = "Error: " . $conn->error;
    }

    $stmt->close();
    header("Location: pengajuan_crud.php");
    exit;
}

// Logika untuk mengunduh file proposal
if (isset($_GET['id'], $_GET['action']) && $_GET['action'] === 'download') {
    $id = intval($_GET['id']);
    $sql = "SELECT file_proposal FROM pengajuan WHERE id_pengajuan = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($file_proposal);
        $stmt->fetch();

        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename=\"proposal_$id.pdf\"");
        echo $file_proposal;
    } else {
        echo "File tidak ditemukan.";
    }

    $stmt->close();
    $conn->close();
    exit;
}

// Fetch data untuk ditampilkan atau mengedit
$edit_data = null;
if (isset($_GET['id'], $_GET['action']) && $_GET['action'] === 'edit') {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM pengajuan WHERE id_pengajuan = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
    $stmt->close();
}

// Proses pengajuan atau pembaruan data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pengajuan = $_POST['id_pengajuan'] ?? null;
    $nama_mahasiswa = htmlspecialchars(trim($_POST['nama_mahasiswa']));
    $judul_proposal = htmlspecialchars(trim($_POST['judul_proposal']));
    $deskripsi = htmlspecialchars(trim($_POST['deskripsi']));
    $tanggal_pelaksanaan = $_POST['tanggal_pelaksanaan'];
    $file_proposal = isset($_FILES['file_proposal']['tmp_name']) && !empty($_FILES['file_proposal']['tmp_name'])
        ? file_get_contents($_FILES['file_proposal']['tmp_name'])
        : null;

    if (!empty($id_pengajuan)) {
        // Update data
        $id_pengajuan = intval($id_pengajuan);
        if ($file_proposal) {
            $sql = "UPDATE pengajuan 
                    SET nama_mahasiswa = ?, judul_proposal = ?, deskripsi = ?, tanggal_pelaksanaan = ?, file_proposal = ? 
                    WHERE id_pengajuan = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $nama_mahasiswa, $judul_proposal, $deskripsi, $tanggal_pelaksanaan, $file_proposal, $id_pengajuan);
        } else {
            $sql = "UPDATE pengajuan 
                    SET nama_mahasiswa = ?, judul_proposal = ?, deskripsi = ?, tanggal_pelaksanaan = ? 
                    WHERE id_pengajuan = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $nama_mahasiswa, $judul_proposal, $deskripsi, $tanggal_pelaksanaan, $id_pengajuan);
        }
    } else {
        // Tambah data baru
        $sql = "INSERT INTO pengajuan (nama_mahasiswa, judul_proposal, deskripsi, tanggal_pelaksanaan, file_proposal) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $nama_mahasiswa, $judul_proposal, $deskripsi, $tanggal_pelaksanaan, $file_proposal);
    }

    if ($stmt->execute()) {
        $message = "Pengajuan berhasil diproses!";
        // Reset $edit_data setelah penyimpanan berhasil
        $edit_data = null;

        // Redirect untuk mencegah pengulangan form submission
        header("Location: pengajuan_crud.php");
        exit;
    } else {
        $message = "Error: " . $conn->error;
    }

    $stmt->close();
}

// Fetch data untuk daftar pengajuan
$sql_select = "SELECT id_pengajuan, nama_mahasiswa, judul_proposal, deskripsi, tanggal_pelaksanaan, tanggal_pengajuan, status 
               FROM pengajuan 
               ORDER BY id_pengajuan DESC";
$data_pengajuan = $conn->query($sql_select)->fetch_all(MYSQLI_ASSOC);

// Fetch data untuk notifikasi
$query = "SELECT judul_proposal, status FROM pengajuan LIMIT 6";
$aside = $conn->query($query);
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
    <script src="https://unpkg.com/feather-icons"></script>
    <title>Pengajuan Proposal</title>
</head>
<body>

<?php include 'header.php'; ?> 

<?php
$query = "SELECT judul_proposal, status FROM pengajuan LIMIT 6";
$aside = $conn->query($query);

$sql_select = "
    SELECT p.id_pengajuan, p.nama_mahasiswa, p.judul_proposal, p.deskripsi, p.tanggal_pelaksanaan, 
           p.tanggal_pengajuan, p.status, s.rejection_notes
    FROM pengajuan p
    LEFT JOIN persetujuan s ON p.id_pengajuan = s.id_pengajuan
    ORDER BY p.id_pengajuan DESC";
$data_pengajuan = $conn->query($sql_select)->fetch_all(MYSQLI_ASSOC);


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
        <h3 class="form-title">Form Pengajuan Proposal</h3>
        <main>
            <?php if (!empty($message)) { echo "<p class='message'>$message</p>"; } ?>
            <form method="POST" enctype="multipart/form-data" class="proposal-form">
                <input type="hidden" name="id_pengajuan" value="<?php echo $edit_data['id_pengajuan'] ?? ''; ?>">
                <label for="nama_mahasiswa">Nama Mahasiswa:</label>
                <input type="text" name="nama_mahasiswa" value="<?php echo $edit_data['nama_mahasiswa'] ?? ''; ?>" required>

                <label for="judul_proposal">Judul Proposal:</label>
                <input type="text" name="judul_proposal" value="<?php echo $edit_data['judul_proposal'] ?? ''; ?>" required>

                <label for="deskripsi">Deskripsi:</label>
                <textarea name="deskripsi" required><?php echo $edit_data['deskripsi'] ?? ''; ?></textarea>

                <label for="tanggal_pelaksanaan">Tanggal Pelaksanaan:</label>
                <input type="date" name="tanggal_pelaksanaan" value="<?php echo $edit_data['tanggal_pelaksanaan'] ?? ''; ?>" required>

                <label for="file_proposal">Upload Proposal:</label>
                <input type="file" name="file_proposal" required>
                <?php if ($edit_data): ?>
                    <p>File saat ini: <a href="?id=<?php echo $edit_data['id_pengajuan']; ?>&action=download">Unduh Proposal</a></p>
                <?php endif; ?>

                <button type="submit" class="submit-button">Simpan Proposal</button>
            </form>

            <h3 class="list-title" >Daftar Pengajuan</h3>
            <table class="proposal-table">
                <thead>
                    <tr>
                        <th>Nama Mahasiswa</th>
                        <th>Judul Proposal</th>
                        <th>Deskripsi</th>
                        <th>Tanggal Pelaksanaan</th>
                        <th>Catatan Penolakan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($data_pengajuan) > 0): ?>
                        <?php foreach ($data_pengajuan as $pengajuan): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pengajuan['nama_mahasiswa']); ?></td>
                                <td><?php echo htmlspecialchars($pengajuan['judul_proposal']); ?></td>
                                <td><?php echo htmlspecialchars($pengajuan['deskripsi']); ?></td>
                                <td><?php echo htmlspecialchars($pengajuan['tanggal_pelaksanaan']); ?></td>
                                <td><?php echo htmlspecialchars($pengajuan['rejection_notes'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($pengajuan['status']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?id=<?php echo $pengajuan['id_pengajuan']; ?>&action=edit" class="action-icon"><i data-feather="edit-2"></i></a>
                                        <a href="?id=<?php echo $pengajuan['id_pengajuan']; ?>&action=delete" class="action-icon" onclick="return confirm('Apakah Anda yakin ingin menghapus?')"><i data-feather="trash"></i></a>
                                        <a href="?id=<?php echo $pengajuan['id_pengajuan']; ?>&action=download" class="action-icon"><i data-feather="download"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">Tidak ada data pengajuan.</td>
                        </tr>
                    <?php endif; ?>
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