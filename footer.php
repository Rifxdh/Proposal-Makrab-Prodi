<?php

// Memasukkan koneksi ke database
require_once 'config.php';

// Memulai sesi untuk mendapatkan informasi login
// if (session_status() == PHP_SESSION_NONE) {
//     session_start();
// }

// Mengambil data mahasiswa jika sudah login
$mahasiswa = null;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if (isset($_SESSION['nim'])) {
        $nim = $_SESSION['nim'];

        // Query untuk mengambil informasi sosial media mahasiswa berdasarkan NIM
        $info_mahasiswa = "
            SELECT i.akun_x, i.akun_facebook, i.akun_instagram, i.nama_mahasiswa
            FROM info_mahasiswa AS i
            INNER JOIN akun_mahasiswa a ON a.nim = i.nim
            WHERE i.nim = '$nim'
        ";

        $result_mahasiswa = mysqli_query($conn, $info_mahasiswa);

        // Memastikan query berhasil
        if ($result_mahasiswa && mysqli_num_rows($result_mahasiswa) > 0) {
            $mahasiswa = mysqli_fetch_assoc($result_mahasiswa);
        }
    }
}

$query = "SELECT nama_web, logo, nama_web, informasi_web, alamat_lokasi FROM header LIMIT 1";
$result = mysqli_query($conn, $query);


// Mengambil data dari tabel footer
$query_footer = "SELECT nama_web, informasi_web, copyright, social_media FROM footer LIMIT 1";
$result_footer = mysqli_query($conn, $query_footer);

$footer = ($result_footer && mysqli_num_rows($result_footer) > 0) ? mysqli_fetch_assoc($result_footer) : null;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/feather-icons"></script>
    <link rel="stylesheet" href="https://use.typekit.net/your-adobe-font-id.css">
    <title>Profil Mahasiswa</title>
</head>
<body>

<footer>
    <div class="footer">
        <!-- Left: Info Akun -->
        <div class="social">
            <?php if ($footer): ?>
                <p>Twitter: <a href="https://x.com/<?= htmlspecialchars($footer['social_media'] ?? '') ?>" target="_blank">@<?= htmlspecialchars($footer['social_media'] ?? '') ?></a></p>
                <p>Facebook: <a href="https://facebook.com/<?= htmlspecialchars($footer['social_media'] ?? '') ?>" target="_blank">@<?= htmlspecialchars($footer['social_media'] ?? '') ?></a></p>
                <p>Instagram: <a href="https://instagram.com/<?= htmlspecialchars($footer['social_media'] ?? '') ?>" target="_blank">@<?= htmlspecialchars($footer['social_media'] ?? '') ?></a></p>
            <?php else: ?>
                <p>Informasi sosial media tidak tersedia.</p>
            <?php endif; ?>
        </div>

        <!-- Center: Copyright -->
        <div class="cp">
            <p><b><?= htmlspecialchars($footer['copyright'] ?? 'No copyright info available') ?></b></p>
        </div>

        <!-- Right: Nama Web -->
        <div class="nama">
            <h3><?= htmlspecialchars($header['nama_web'] ?? 'Nama Web') ?></h3>
            <p><?= htmlspecialchars($header['informasi_web'] ?? 'Informasi Web tidak tersedia') ?></p>
        </div>
    </div>
</footer>


    <script>
        feather.replace();
    </script>
</body>
</html>