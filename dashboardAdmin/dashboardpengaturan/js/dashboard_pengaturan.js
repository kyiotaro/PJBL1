async function fetchAPI(formData) {
    try {
        const res = await fetch('settings_logic.php', {
            method: 'POST',
            body: formData
        });
        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
        return await res.json();
    } catch (e) {
        console.error('Fetch error:', e);
        return { success: false, message: 'Gagal menghubungi server: ' + e.message };
    }
}

async function loadAllData() {
    // Load Settings
    const formData = new FormData();
    formData.append('action', 'get_settings');
    const data = await fetchAPI(formData);
    
    if (data && data.success) {
        if (data.settings && data.settings.theme) {
            document.getElementById('themeSelect').value = data.settings.theme;
            applyTheme(data.settings.theme);
        }
        if (data.settings && data.settings.maintenance_mode) {
            document.getElementById('maintenance_mode').value = data.settings.maintenance_mode;
        }
    } else if (data) {
        console.warn('Settings load failed:', data.message);
    }

    // Load Backups & Categories
    loadBackupHistory();
    loadCategories();
}

async function loadBackupHistory() {
    const formData = new FormData();
    formData.append('action', 'list_backups');
    const data = await fetchAPI(formData);
    
    const tbody = document.getElementById('backupHistory');
    if (!tbody) return;
    tbody.innerHTML = '';
    
    if (data && data.success) {
        if (data.backups && data.backups.length > 0) {
            data.backups.forEach(b => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><strong>${b.name}</strong></td>
                    <td>${b.size}</td>
                    <td>${b.date}</td>
                    <td class="backup-actions">
                        <a href="../../backups/${b.name}" class="btn-small" download title="Unduh backup">Unduh</a>
                        <button class="btn-danger" onclick="deleteBackup('${b.name}')" title="Hapus backup">Hapus</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            const tr = document.createElement('tr');
            tr.innerHTML = '<td colspan="4" style="text-align: center; color: #64748b; padding: 20px;">Belum ada backup. Buat backup pertama Anda sekarang.</td>';
            tbody.appendChild(tr);
        }
    } else {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td colspan="4" style="text-align: center; color: #dc2626; padding: 20px;">Gagal memuat backup: ${data?.message || 'Error tidak diketahui'}</td>`;
        tbody.appendChild(tr);
    }
}

async function deleteBackup(fileName) {
    if (!confirm('Hapus backup ini?')) return;
    const formData = new FormData();
    formData.append('action', 'delete_backup');
    formData.append('fileName', fileName);
    const data = await fetchAPI(formData);
    alert(data.message);
    loadBackupHistory();
}

async function loadCategories() {
    try {
        const res = await fetch('category_logic.php?action=list');
        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
        const data = await res.json();
        
        const container = document.getElementById('categoryContainer');
        if (!container) return;
        container.innerHTML = '';
        
        if (data.success) {
            if (data.categories && data.categories.length > 0) {
                data.categories.forEach(c => {
                    const div = document.createElement('div');
                    div.className = 'cat-card';
                    div.innerHTML = `
                        <div class="cat-info">
                            <div class="cat-color" style="background: ${c.warna || '#cccccc'}"></div>
                            <span class="cat-name">${c.nama}</span>
                        </div>
                        <div class="muted">Slug: ${c.slug}</div>
                        <div class="cat-actions">
                            <button class="btn-small" onclick="editCategory(${JSON.stringify(c).replace(/"/g, '&quot;')})">Edit</button>
                            <button class="btn-danger" onclick="deleteCategory(${c.id})">Hapus</button>
                        </div>
                    `;
                    container.appendChild(div);
                });
            } else {
                container.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: #64748b; padding: 20px;">Belum ada kategori. Tambahkan kategori baru untuk memulai.</div>';
            }
        } else {
            container.innerHTML = `<div style="grid-column: 1/-1; text-align: center; color: #dc2626; padding: 20px;">Gagal memuat kategori: ${data.message}</div>`;
        }
    } catch (error) {
        console.error('Error loading categories:', error);
        const container = document.getElementById('categoryContainer');
        if (container) {
            container.innerHTML = `<div style="grid-column: 1/-1; text-align: center; color: #dc2626; padding: 20px;">Error memuat kategori: ${error.message}</div>`;
        }
    }
}

// Category Modal Logic
function showAddCategoryModal() {
    document.getElementById('catModalTitle').textContent = 'Tambah Kategori';
    document.getElementById('catId').value = '';
    document.getElementById('catForm').reset();
    document.getElementById('categoryModal').style.display = 'flex';
}

function editCategory(c) {
    document.getElementById('catModalTitle').textContent = 'Edit Kategori';
    document.getElementById('catId').value = c.id;
    document.getElementById('catNama').value = c.nama;
    document.getElementById('catSlug').value = c.slug;
    document.getElementById('catWarna').value = c.warna;
    document.getElementById('categoryModal').style.display = 'flex';
}

function closeCatModal() {
    document.getElementById('categoryModal').style.display = 'none';
}

async function deleteCategory(id) {
    if (!confirm('Hapus kategori ini? Semua artikel terkait mungkin bermasalah.')) return;
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    const res = await fetch('category_logic.php', { method: 'POST', body: formData });
    const data = await res.json();
    alert(data.message);
    loadCategories();
}

function applyTheme(theme) {
    document.body.classList.toggle('theme-dark', theme === 'Gelap');
    localStorage.setItem('adminTheme', theme);
}

document.addEventListener('DOMContentLoaded', async () => {
    const session = await setupAdminUI();
    if (!session) return;

    loadAllData();

    // Theme form handler
    const themeForm = document.getElementById('themeForm');
    if (themeForm) {
        themeForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(themeForm);
            const data = await fetchAPI(formData);
            document.getElementById('settingsStatus').textContent = data.message;
            if (data.success) {
                const theme = themeForm.querySelector('[name="settings[theme]"]').value;
                applyTheme(theme);
            }
        });
    }

    // Restore Database
    document.getElementById('restoreFile').addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;
        if (!confirm('HATI-HATI! Restore database akan menghapus data saat ini. Lanjutkan?')) return;

        const formData = new FormData();
        formData.append('action', 'restore_db');
        formData.append('backup_file', file);
        
        document.getElementById('settingsStatus').textContent = 'Memulihkan database...';
        const data = await fetchAPI(formData);
        alert(data.message);
        location.reload();
    });

    // Category Form
    document.getElementById('catForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData();
        const id = document.getElementById('catId').value;
        formData.append('action', id ? 'update' : 'add');
        if (id) formData.append('id', id);
        formData.append('nama', document.getElementById('catNama').value);
        formData.append('slug', document.getElementById('catSlug').value);
        formData.append('warna', document.getElementById('catWarna').value);

        const res = await fetch('category_logic.php', { method: 'POST', body: formData });
        const data = await res.json();
        alert(data.message);
        if (data.success) {
            closeCatModal();
            loadCategories();
        }
    });
});
