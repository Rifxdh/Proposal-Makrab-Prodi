<?php
require_once 'config.php';

// Fetch data header dan footer dari database
$headers = $conn->query("SELECT * FROM header");
$footers = $conn->query("SELECT * FROM footer");

// Proses update data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_data'])) {
    $id = $_POST['id'];
    $type = $_POST['type'];
    $info_web = $_POST['info_web'];
    $nama_web = $_POST['nama_web'];

    // Ambil logo lama dari database jika tidak ada logo baru yang diupload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        // Jika ada logo baru yang diupload, ambil kontennya
        $logo = file_get_contents($_FILES['logo']['tmp_name']);
    } else {
        // Ambil logo lama berdasarkan ID yang diupdate
        if ($type === 'header') {
            $result = $conn->query("SELECT logo FROM header WHERE id_header = $id");
            $row = $result->fetch_assoc();
            $logo = $row['logo'];  // Simpan logo lama
        } else {
            $result = $conn->query("SELECT logo FROM footer WHERE id_footer = $id");
            $row = $result->fetch_assoc();
            $logo = $row['logo'];  // Simpan logo lama
        }
    }

    // Update query
    if ($type === 'header') {
        if (isset($logo) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $query = "UPDATE header SET informasi_web = ?, nama_web = ?, logo = ? WHERE id_header = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssi", $info_web, $nama_web, $logo, $id);
        } else {
            $query = "UPDATE header SET informasi_web = ?, nama_web = ? WHERE id_header = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssi", $info_web, $nama_web, $id);
        }
    } else {
        if (isset($logo) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $query = "UPDATE footer SET informasi_web = ?, nama_web = ?, logo = ? WHERE id_footer = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssi", $info_web, $nama_web, $logo, $id);
        } else {
            $query = "UPDATE footer SET informasi_web = ?, nama_web = ? WHERE id_footer = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssi", $info_web, $nama_web, $id);
        }
    }

    // Execute statement
    if ($stmt->execute()) {
        echo "<script>
            alert('Data berhasil diperbarui');
            window.location.href = 'kelola_header_footer.php';
        </script>";
    } else {
        echo "<script>
            alert('Gagal memperbarui data');
            window.location.href = 'kelola_header_footer.php';
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <script src="jquery.js"></script>
    <title>Manage Header and Footer</title>
</head>
<body>

<style>
    td img {
            max-width: 100px;
            max-height: 100px;
            object-fit: contain;
        }
</style>

<?php include 'header.php'; 

$query = "SELECT judul_proposal, status FROM pengajuan LIMIT 6";
$aside = $conn->query($query);?>

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
        <h3 class="form-title">Kelola Header dan Footer</h3>

        
        <table class="proposal-table">
            <thead>
                <tr>
                    <th>Info Website</th>
                    <th>Nama Website</th>   
                    <th>Logo</th>
                    <th>Action</th>
                </tr>
            </thead>
            
            <tbody>
            <?php while ($row = $headers->fetch_assoc()) { ?>
            <tr>
                
                <td><?= htmlspecialchars($row['informasi_web']) ?></td>
                <td><?= htmlspecialchars($row['nama_web']) ?></td>

                <td>
                    <?php if (!empty($row['logo'])): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($row['logo']) ?>" alt="Logo">
                    <?php else: ?>
                        No logo
                    <?php endif; ?>
                </td>
                <td>
                    <button class="popup-trigger" data-id="<?= $row['id_header'] ?>" data-type="header" data-info-web="<?= htmlspecialchars($row['informasi_web']) ?>" data-nama-web="<?= htmlspecialchars($row['nama_web']) ?>">
                    <i data-feather="edit"></i>
                    </button>
                </td>
            </tr>
            <?php } ?>
            </tbody>
            
        </table>
        <div id="popup-wrapper" style="display: none;">
            <div id="popup-content">
                <button id="popup-close">&times;</button>
                <form id="editForm" method="POST" enctype="multipart/form-data">
                    <h2>Edit Data</h2>
                    <input type="hidden" name="id" id="editId">
                    <input type="hidden" name="type" id="editType">
                    <label for="info_web">Info Website:</label>
                    <input type="text" name="info_web" id="editInfoWeb" required>
                    <label for="nama_web">Nama Website:</label>
                    <input type="text" name="nama_web" id="editNamaWeb" required>
                    <label for="logo">Upload Logo (optional):</label>
                    <input type="file" name="logo" accept="image/*">
                    <input type="hidden" name="update_data" value="1">
                    <button class="submit-button" type="submit">Simpan</button>
                </form>
            </div>
        </div>
    </section>
    <aside>
        <h3>Notifikasi Pengajuan</h3>
    <table>
        <tbody>
            <?php
            if ($aside->num_rows > 0) {
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
        $(".popup-trigger").click(function() {
            const id = $(this).data("id");
            const type = $(this).data("type");
            const infoWeb = $(this).data("info-web");
            const namaWeb = $(this).data("nama-web");

            $("#editId").val(id);
            $("#editType").val(type);
            $("#editInfoWeb").val(infoWeb);
            $("#editNamaWeb").val(namaWeb);

            $("#popup-wrapper").fadeIn(300);
        });

        $("#popup-close, #popup-wrapper").click(function(event) {
            if (event.target === this) {
                $("#popup-wrapper").fadeOut(300);
            }
        });

        $("#popup-content").click(function(event) {
            event.stopPropagation();
        });
    });
</script>

<?php include 'footer.php'; ?>
</body>
</html>