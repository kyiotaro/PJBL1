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