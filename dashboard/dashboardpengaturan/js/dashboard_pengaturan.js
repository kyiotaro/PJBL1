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

document.addEventListener('DOMContentLoaded', async () => {
    const session = await setupAdminUI();
    if (!session) {
        return;
    }

    const fullNameInput = document.getElementById('fullName');
    const emailInput = document.getElementById('email');
    const themeSelect = document.getElementById('themeSelect');
    const statusText = document.getElementById('settingsStatus');

    const savedSettings = {
        fullName: '',
        email: session.email || '',
        theme: 'Terang',
        ...getSavedSettings()
    };

    fullNameInput.value = savedSettings.fullName;
    emailInput.value = savedSettings.email;
    themeSelect.value = savedSettings.theme;
    applyTheme(savedSettings.theme);

    document.getElementById('profileForm').addEventListener('submit', (event) => {
        event.preventDefault();

        const updatedSettings = {
            ...getSavedSettings(),
            fullName: fullNameInput.value.trim(),
            email: emailInput.value.trim() || session.email,
            theme: themeSelect.value
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
            theme: themeSelect.value
        };

        saveSettings(updatedSettings);
        applyTheme(updatedSettings.theme);
        statusText.textContent = `Tema ${updatedSettings.theme.toLowerCase()} berhasil diterapkan.`;
    });
});