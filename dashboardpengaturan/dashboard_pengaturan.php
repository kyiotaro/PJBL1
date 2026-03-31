<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem</title>
    <link rel="stylesheet" href="../dashboardadmin/dashboard.css">
    <link rel="stylesheet" href="dashboard_pengaturan.css">
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
            <img src="../assets/Foto/brand/logo.png" alt="Logo">
            <h1>Permata Biru Nusantara</h1>
        </div>

        <ul class="nav-menu">
            <li><a href="../dashboardadmin/dashboard.php" class="nav-link">Dashboard</a></li>
            <li><a href="../dashboardartikel/dashboard_artikel.php" class="nav-link">Artikel</a></li>
            <li><a href="../dashboardpengaturan/dashboard_pengaturan.php" class="nav-link active">Pengaturan</a></li>
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

<script src="../loginpage/auth.js"></script>
<script>
const SETTINGS_KEY = 'adminDashboardSettings';

function getSavedSettings() {
    try {
        return JSON.parse(localStorage.getItem(SETTINGS_KEY)) || {};
    } catch (error) {
        return {};
    }
}

function saveSettings(data) {
    localStorage.setItem(SETTINGS_KEY, JSON.stringify(data));
}

function applyTheme(theme) {
    document.body.classList.toggle('theme-dark', theme === 'Gelap');
}

document.addEventListener('DOMContentLoaded', () => {
    const session = protectAdminPage();
    if (!session) {
        return;
    }

    setupAdminUI();

    const fullNameInput = document.getElementById('fullName');
    const emailInput = document.getElementById('email');
    const themeSelect = document.getElementById('themeSelect');
    const statusText = document.getElementById('settingsStatus');
    const backupDate = document.getElementById('backupDate');

    const savedSettings = {
        fullName: '',
        email: session.email || '',
        theme: 'Terang',
        lastBackup: '',
        ...getSavedSettings()
    };

    fullNameInput.value = savedSettings.fullName;
    emailInput.value = savedSettings.email;
    themeSelect.value = savedSettings.theme;
    backupDate.textContent = savedSettings.lastBackup || 'Belum ada backup';
    applyTheme(savedSettings.theme);

    document.getElementById('profileForm').addEventListener('submit', (event) => {
        event.preventDefault();

        const updatedSettings = {
            ...getSavedSettings(),
            fullName: fullNameInput.value.trim(),
            email: emailInput.value.trim() || session.email,
            theme: themeSelect.value,
            lastBackup: backupDate.textContent === 'Belum ada backup' ? '' : backupDate.textContent
        };

        saveSettings(updatedSettings);
        setAdminSession(updatedSettings.email);
        setupAdminUI();
        statusText.textContent = 'Profil admin berhasil disimpan.';
    });

    document.getElementById('themeForm').addEventListener('submit', (event) => {
        event.preventDefault();

        const updatedSettings = {
            ...getSavedSettings(),
            fullName: fullNameInput.value.trim(),
            email: emailInput.value.trim() || session.email,
            theme: themeSelect.value,
            lastBackup: backupDate.textContent === 'Belum ada backup' ? '' : backupDate.textContent
        };

        saveSettings(updatedSettings);
        applyTheme(updatedSettings.theme);
        statusText.textContent = `Tema ${updatedSettings.theme.toLowerCase()} berhasil diterapkan.`;
    });

    document.getElementById('backupBtn').addEventListener('click', () => {
        const now = new Date().toLocaleString('id-ID');
        const backupData = {
            ...getSavedSettings(),
            fullName: fullNameInput.value.trim(),
            email: emailInput.value.trim() || session.email,
            theme: themeSelect.value,
            lastBackup: now
        };

        saveSettings(backupData);
        backupDate.textContent = now;

        const blob = new Blob([JSON.stringify(backupData, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'backup-pengaturan-admin.json';
        link.click();
        URL.revokeObjectURL(url);

        statusText.textContent = 'Backup pengaturan berhasil dibuat.';
    });

    document.getElementById('restoreInput').addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (!file) {
            return;
        }

        const reader = new FileReader();
        reader.onload = (loadEvent) => {
            try {
                const restoredData = JSON.parse(loadEvent.target.result);
                const mergedSettings = {
                    fullName: restoredData.fullName || '',
                    email: restoredData.email || session.email,
                    theme: restoredData.theme === 'Gelap' ? 'Gelap' : 'Terang',
                    lastBackup: restoredData.lastBackup || new Date().toLocaleString('id-ID')
                };

                saveSettings(mergedSettings);
                fullNameInput.value = mergedSettings.fullName;
                emailInput.value = mergedSettings.email;
                themeSelect.value = mergedSettings.theme;
                backupDate.textContent = mergedSettings.lastBackup;
                setAdminSession(mergedSettings.email);
                setupAdminUI();
                applyTheme(mergedSettings.theme);
                statusText.textContent = 'Backup pengaturan berhasil dipulihkan.';
            } catch (error) {
                statusText.textContent = 'File backup tidak valid.';
            }
        };

        reader.readAsText(file);
    });

    document.getElementById('backupInfoBtn').addEventListener('click', () => {
        statusText.textContent = 'Gunakan tombol Backup Sekarang atau Restore dari File sesuai kebutuhan.';
    });
});
</script>

</body>
</html>