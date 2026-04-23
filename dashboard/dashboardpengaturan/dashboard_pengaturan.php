<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem</title>
    <link rel="stylesheet" href="../dashboardadmin/css/dashboard.css">
    <link rel="stylesheet" href="css/dashboard_pengaturan.css">
    <style>
        body.theme-dark {
            background: #0f172a;
            color: #e2e8f0;
        }

        body.theme-dark .content,
        body.theme-dark .sidebar,
        body.theme-dark .settings-section,
        body.theme-dark .backup-container,
        body.theme-dark .user-info {
            background: #1e293b;
            color: #e2e8f0;
            border-color: #334155;
        }

        body.theme-dark .nav-link {
            color: #e2e8f0;
        }

        body.theme-dark .nav-link.active {
            background: #0284c7;
        }

        body.theme-dark .content p,
        body.theme-dark .muted,
        body.theme-dark .settings-form label,
        body.theme-dark .backup-table th,
        body.theme-dark .backup-table td {
            color: #cbd5e1;
        }

        body.theme-dark .settings-form input,
        body.theme-dark .settings-form select,
        body.theme-dark .backup-table th,
        body.theme-dark .backup-table td {
            background: #0f172a;
            color: #e2e8f0;
            border-color: #334155;
        }
    </style>
</head>
<body>

<div class="user-info">
    <span id="adminEmail"></span>
    <button id="logoutBtn" class="logout-btn">Logout</button>
</div>

<div class="dashboard">
    <aside class="sidebar">
        <div class="logo">
            <img src="/PJBL-main/assets/Foto/brand/logo.png" alt="Logo">
            <h1>Permata Biru Nusantara</h1>
        </div>

        <ul class="nav-menu">
            <li><a href="/PJBL-main/dashboard/dashboardadmin/dashboard.php" class="nav-link">Dashboard</a></li>
            <li><a href="/PJBL-main/dashboard/dashboardartikel/dashboard_artikel.php" class="nav-link">Artikel</a></li>
            <li><a href="/PJBL-main/dashboard/dashboardpengaturan/dashboard_pengaturan.php" class="nav-link active">Pengaturan</a></li>
        </ul>
    </aside>

    <main class="content">
        <h2>Pengaturan Sistem</h2>
        <p>Atur preferensi sistem sesuai kebutuhan Anda.</p>

        <section class="settings-section">
            <h3>Backup & Restore</h3>
            <p class="muted">Backup sekarang sudah bisa diekspor ke file JSON dan dipulihkan kembali.</p>

            <div class="backup-container">
                <div class="backup-actions">
                    <button id="backupBtn" type="button" class="btn-primary">Backup Sekarang</button>

                    <label class="btn-outline file-label">
                        Restore dari File
                        <input id="restoreInput" type="file" accept=".json" />
                    </label>
                </div>

                <h4>Riwayat Backup</h4>
                <table class="backup-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td id="backupDate">Belum ada backup</td>
                            <td>Pengaturan admin</td>
                            <td><button type="button" class="btn-small" id="backupInfoBtn">Siap digunakan</button></td>
                        </tr>
                    </tbody>
                </table>
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
</div>

<script src="../../halamanWeb/loginpage/js/auth.js"></script>
<script src="js/dashboard_pengaturan.js"></script>

</body>
</html>l>