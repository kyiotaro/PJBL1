<?php
require_once '../../../config/auth_check.php';
include '../../../koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem</title>
    <link rel="stylesheet" href="/PJBL-main/assets/templateHalaman/sidebar/sidebar.css">
    <link rel="stylesheet" href="/PJBL-main/dashboard/dashboardAdmin/dashboardmain/css/dashboard.css">
    <link rel="stylesheet" href="css/dashboard_pengaturan.css">
</head>
<body>

<?php
    $activePage = 'pengaturan'; 
    include '../../../assets/templateHalaman/sidebar/sidebar.php';
?>

<main class="pb-main-content">
    <h2>Pengaturan Sistem</h2>
    <p>Atur preferensi sistem sesuai kebutuhan Anda.</p>

    <div class="settings-grid">
        <!-- TAMPILAN -->
        <section id="appearance-section" class="settings-section">
            <h3>Tampilan & Sistem</h3>
            <form class="settings-form" id="themeForm">
                <input type="hidden" name="action" value="update_settings">
                <div class="form-row">
                    <div class="form-group">
                        <label for="themeSelect">Mode Tema</label>
                        <select id="themeSelect" name="settings[theme]">
                            <option value="Terang">Terang</option>
                            <option value="Gelap">Gelap</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="maintenance_mode">Mode Perawatan</label>
                        <select id="maintenance_mode" name="settings[maintenance_mode]">
                            <option value="0">Nonaktif</option>
                            <option value="1">Aktif</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn-primary">Terapkan Perubahan</button>
            </form>
        </section>

        <!-- KATEGORI -->
        <section id="categories-section" class="settings-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin-bottom: 0;">Manajemen Kategori</h3>
                <button class="btn-primary" onclick="showAddCategoryModal()">+ Tambah Kategori</button>
            </div>
            <div id="categoryContainer">
                <!-- Loaded via JS -->
            </div>
        </section>

        <!-- DATABASE -->
        <section id="database-section" class="settings-section">
            <h3>Manajemen Database</h3>
            <div class="backup-container">
                <div class="backup-actions">
                    <a href="backup_logic.php" class="btn-primary" style="text-decoration: none;">BACKUP SEKARANG</a>
                    <label class="btn-outline file-label">
                        RESTORE (SQL)
                        <input type="file" id="restoreFile" accept=".sql">
                    </label>
                </div>

                <h4>Riwayat Backup</h4>
                <div style="overflow-x: auto;">
                    <table class="backup-table">
                        <thead>
                            <tr>
                                <th>Nama File</th>
                                <th>Ukuran</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="backupHistory">
                            <!-- Data loaded via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <p id="settingsStatus" class="muted"></p>
</main>

<!-- Modals -->
<div id="categoryModal" class="modal" style="display:none;">
    <div class="modal-content">
        <h4 id="catModalTitle">Tambah Kategori</h4>
        <form id="catForm">
            <input type="hidden" id="catId">
            <label>Nama</label>
            <input type="text" id="catNama" required>
            <label>Slug</label>
            <input type="text" id="catSlug" required>
            <label>Warna (Hex)</label>
            <input type="color" id="catWarna" style="height: 40px;">
            <div style="margin-top: 15px; display: flex; gap: 10px;">
                <button type="submit" class="btn-primary">Simpan</button>
                <button type="button" class="btn-outline" onclick="closeCatModal()">Batal</button>
            </div>
        </form>
    </div>
</div>

<script src="/PJBL-main/halamanWeb/loginpage/js/auth.js"></script>
<script src="js/dashboard_pengaturan.js?v=<?= time() ?>"></script>
<script src="/PJBL-main/assets/templateHalaman/sidebar/sidebar.js"></script>

</body>
</html>