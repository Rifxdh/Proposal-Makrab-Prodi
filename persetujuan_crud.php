<?php

require_once 'config.php'; 


// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
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
    <script src="https://unpkg.com/feather-icons"></script>
    <title>Persetujuan</title>
</head>
<body>

<?php include 'header.php'; ?> 

<?php 

$query = "SELECT judul_proposal, status FROM pengajuan LIMIT 6";
$aside = $conn->query($query);

// Proses aksi persetujuan atau penolakan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pengajuan = $_POST['id_pengajuan'];
    $approver = $conn->real_escape_string($_POST['approver']); // Hindari SQL injection

    if (isset($_POST['approve'])) {
        // Update status di tabel pengajuan
        if (!empty($_FILES['file_proposal']['tmp_name'])) {
            $file_proposal = file_get_contents($_FILES['file_proposal']['tmp_name']);
            $file_proposal = $conn->real_escape_string($file_proposal);
            $sql_update_pengajuan = "UPDATE pengajuan SET status = 'Disetujui', approver = '$approver', file_proposal = '$file_proposal' WHERE id_pengajuan = $id_pengajuan";
        } else {
            $sql_update_pengajuan = "UPDATE pengajuan SET status = 'Disetujui', approver = '$approver' WHERE id_pengajuan = $id_pengajuan";
        }
        $conn->query($sql_update_pengajuan);

        // Tambahkan ke tabel persetujuan
        $sql_insert_persetujuan = "INSERT INTO persetujuan (id_pengajuan, status, tanggal_persetujuan)
                                   VALUES ('$id_pengajuan', 'Disetujui', NOW())";
        $conn->query($sql_insert_persetujuan);

        echo "<script>alert('Proposal disetujui oleh $approver!'); window.location.href = 'persetujuan_crud.php';</script>";
    } elseif (isset($_POST['reject'])) {
        // Validasi input untuk penolakan
        $notes = $conn->real_escape_string($_POST['notes']);
        if (empty($approver)) {
            echo "<script>alert('Harap pilih approver sebelum menolak proposal!'); window.history.back();</script>";
            exit;
        }

        // Update status di tabel pengajuan
        $sql_update_pengajuan = "UPDATE pengajuan SET status = 'Ditolak', approver = '$approver', rejection_notes = '$notes' WHERE id_pengajuan = $id_pengajuan";
        $conn->query($sql_update_pengajuan);

        // Tambahkan ke tabel persetujuan
        $sql_insert_persetujuan = "INSERT INTO persetujuan (id_pengajuan, status, rejection_notes, tanggal_persetujuan)
                                   VALUES ('$id_pengajuan', 'Ditolak', '$notes', NOW())";
        $conn->query($sql_insert_persetujuan);

        echo "<script>alert('Proposal ditolak oleh $approver!'); window.location.href = 'persetujuan_crud.php';</script>";
    }
}

// Logika untuk mengunduh file proposal
if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] === 'download') {
    $id = intval($_GET['id']); // Sanitasi ID
    $sql = "SELECT file_proposal FROM pengajuan WHERE id_pengajuan = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($file_proposal);
            $stmt->fetch();

            // Periksa jika file ada
            if (empty($file_proposal)) {
                die("File proposal tidak ditemukan di database.");
            }

            // Set header untuk unduhan file
            header("Content-Type: application/pdf"); // Tipe file PDF
            header("Content-Disposition: attachment; filename=\"proposal_$id.pdf\"");
            header("Content-Length: " . strlen($file_proposal));

            echo $file_proposal; // Output isi file

        } else {
            echo "File tidak ditemukan di database.";
        }
        $stmt->close();
    } else {
        echo "Terjadi kesalahan pada query.";
    }

    $conn->close();
    exit; // Hentikan eksekusi setelah file diunduh
}

// Ambil data dari tabel pengajuan
$sql = "SELECT id_pengajuan, nama_mahasiswa, judul_proposal, deskripsi, tanggal_pelaksanaan, tanggal_pengajuan, status, approver, rejection_notes 
        FROM pengajuan 
        ORDER BY id_pengajuan DESC";
$result = $conn->query($sql);
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
        <h3 class="list-title">Tabel Persetujuan</h3>
        <table class="proposal-table">
            <thead>
                <tr>
                    <th>Nama Mahasiswa</th>
                    <th>Judul Proposal</th>
                    <th>Deskripsi</th>
                    <th>Tanggal Pelaksanaan</th>
                    <th>Status</th>
                    <th>Approver</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['nama_mahasiswa']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['judul_proposal']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['deskripsi']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['tanggal_pelaksanaan']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                        echo "<td>" . (!empty($row['approver']) ? htmlspecialchars($row['approver']) : "Belum Ditentukan") . "</td>";
                        echo "<td>";

                        if ($row['status'] === 'Pending') {
                            echo '<div class="action-buttons">';
                            echo '<a href="?id=' . $row['id_pengajuan'] . '&action=download" class="action-icon"><i data-feather="download"></i> Unduh</a>';
                            echo "<button class='popup-trigger' data-type='approve' data-id='" . htmlspecialchars($row['id_pengajuan']) . "'>Setujui</button>";
                            echo "<button class='popup-trigger' data-type='reject' data-id='" . htmlspecialchars($row['id_pengajuan']) . "' style='background-color: red; color: white;'>Tolak</button>";
                            echo '</div>';

                            // Form Menyetujui Proposal
                            echo "<form class='form-approve' action='' method='POST' enctype='multipart/form-data' style='display: none;'>";
                            echo "<input type='hidden' name='id_pengajuan' value='" . htmlspecialchars($row['id_pengajuan']) . "'>";
                            echo "<label for='approver'>Pilih Approver:</label>";
                            echo "<select name='approver' required>";
                            echo "<option value='' disabled selected>Pilih Approver</option>";
                            echo "<option value='Admin'>Admin</option>";
                            echo "<option value='BPM'>BPM</option>";
                            echo "<option value='Pembina Hima'>Pembina Hima</option>";
                            echo "<option value='Wakil Dekan'>Wakil Dekan</option>";
                            echo "<option value='BKAL'>BKAL</option>";
                            echo "<option value='Wakil Rektor'>Wakil Rektor</option>";
                            echo "</select><br/>";
                            echo "<label for='file_proposal'>Upload Proposal Baru:</label>";
                            echo "<input type='file' name='file_proposal'><br/>";
                            echo "<button type='submit' name='approve'>Setujui</button>";
                            echo "</form>";

                            // Form Menolak Proposal
                            echo "<form class='form-reject' action='' method='POST' enctype='multipart/form-data' style='display: none;'>";
                            echo "<input type='hidden' name='id_pengajuan' value='" . htmlspecialchars($row['id_pengajuan']) . "'>";
                            echo "<label for='notes'>Catatan Penolakan:</label>";
                            echo "<textarea name='notes' placeholder='Catatan Penolakan' required></textarea><br/>";
                            echo "<button type='submit' name='reject' style='background-color: red; color: white;'>Tolak</button>";
                            echo "</form>";
                        }

                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>Tidak ada data</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <div id="popup-wrapper" style="display: none;">
    <div id="popup-content">
        <button id="popup-close">X</button>
        <div id="popup-body"></div>
    </div>
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
            $(".popup-trigger").click(function () {
        const type = $(this).data("type"); // Jenis pop-up (approve atau reject)
        const id = $(this).data("id"); // ID Pengajuan

        let formHtml = "";

        if (type === "approve") {
            // Form Menyetujui
            formHtml = `
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id_pengajuan" value="${id}">
        <label for="approver">Pilih Approver:</label>
        <select name="approver" required>
            <option value="" disabled selected>Pilih Approver</option>
            <option value="Admin">Admin</option>
            <option value="BPM">BPM</option>
            <option value="Pembina Hima">Pembina Hima</option>
            <option value="Wakil Dekan">Wakil Dekan</option>
            <option value="BKAL">BKAL</option>
            <option value="Wakil Rektor">Wakil Rektor</option>
        </select><br />
        <label for="file_proposal">Upload Proposal Baru:</label>
        <input type="file" name="file_proposal"><br />
        
        
        <button type="submit" name="approve">Setujui</button>
        <br />
        
    </form>`;

        } else if (type === "reject") {
            // Form Menolak
            formHtml = `
                <form action="" method="POST" enctype="multipart/form-data">
                    <select name="approver" required>
            <option value="" disabled selected>Pilih Approver</option>
            <option value="Admin">Admin</option>
            <option value="BPM">BPM</option>
            <option value="Pembina Hima">Pembina Hima</option>
            <option value="Wakil Dekan">Wakil Dekan</option>
            <option value="BKAL">BKAL</option>
            <option value="Wakil Rektor">Wakil Rektor</option>
        </select><br />
                    <input type="hidden" name="id_pengajuan" value="${id}">
                    <label for="notes">Catatan Penolakan:</label>
                    <textarea name="notes" placeholder="Catatan Penolakan" required></textarea><br />
                    <button type="submit" name="reject" style="background-color: red; color: white;">Tolak</button>
                </form>`;
        }

        // Tampilkan form di pop-up
        $("#popup-body").html(formHtml);
        $("#popup-wrapper").fadeIn();
    });

    // Menangani klik pada tombol close
    $("#popup-close").click(function () {
        $("#popup-wrapper").fadeOut();
    });

    // Menangani klik di luar pop-up untuk menutup
    $("#popup-wrapper").click(function (e) {
        if (e.target === this) {
            $(this).fadeOut();
        }
    });
        });

    </script>

<?php include 'footer.php'; ?>
<script>feather.replace();</script>
</body>
</html>


