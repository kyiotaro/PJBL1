<?php
require_once '../../config/auth_check.php';
include '../../koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem</title>
    <link rel="stylesheet" href="../../assets/templateHalaman/sidebar/sidebar.css">
    <link rel="stylesheet" href="../dashboardadmin/css/dashboard.css">
    <link rel="stylesheet" href="css/dashboard_pengaturan.css">
</head>
<body>

<?php
    $activePage = 'pengaturan'; 
    include '../../assets/templateHalaman/sidebar/sidebar.php';
?>

<main class="pb-main-content">
        <h2>Pengaturan Sistem</h2>
        <p>Atur preferensi sistem sesuai kebutuhan Anda.</p>

        <section class="settings-section">
            <h3>Backup Database</h3>            
            <div class="backup-container">
                <div class="backup-actions">
                    <a href="backup_logic.php" class="btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; width: fit-content; padding: 10px 30px;">BACKUP</a>
                </div>
            </div>
        </section>

        <section class="settings-section">
            <h3>Profil Admin</h3>
            <form class="settings-form" id="profileForm">
                <label for="fullName">Nama Lengkap</label>
                <input id="fullName" type="text" placeholder="Masukkan nama Anda">

                <label for="email">Email</label>
                <input id="email" type="email" placeholder="Masukkan email Anda">

                <button type="submit">Simpan Perubahan</button>
            </form>
        </section>

        <section class="settings-section">
            <h3>Tampilan</h3>
            <form class="settings-form" id="themeForm">
                <label for="themeSelect">Mode Tema</label>
                <select id="themeSelect">
                    <option value="Terang">Terang</option>
                    <option value="Gelap">Gelap</option>
                </select>

                <button type="submit">Terapkan</button>
            </form>
        </section>

        <p id="settingsStatus" class="muted"></p>
    </main>

<script src="../../halamanWeb/loginpage/js/auth.js"></script>
<script src="js/dashboard_pengaturan.js"></script>
<script src="../../assets/templateHalaman/sidebar/sidebar.js"></script>

</body>
</html>l>