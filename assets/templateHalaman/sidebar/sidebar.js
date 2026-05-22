(function () {
  const STORAGE_KEY = 'pbSidebarCollapsed';

  document.addEventListener('DOMContentLoaded', () => {
    const sidebar   = document.getElementById('pbSidebar');
    const toggleBtn = document.getElementById('pbSidebarToggle');

    if (!sidebar) return;

    // Pulihkan state collapse dari localStorage
    const isCollapsed = localStorage.getItem(STORAGE_KEY) === 'true';
    if (isCollapsed) sidebar.classList.add('collapsed');

    // Toggle klik
    if (toggleBtn) {
      toggleBtn.addEventListener('click', () => {
        const collapsed = sidebar.classList.toggle('collapsed');
        localStorage.setItem(STORAGE_KEY, collapsed);
      });
    }

    // Setup UI admin dari PHP session
    setupAdminUI();
  });

  // ── setupAdminUI: fetch PHP session, isi email/initial/logout ──
  window.setupAdminUI = async function () {
    const emailEl   = document.getElementById('adminEmail');
    const initialEl = document.getElementById('adminInitial');
    const logoutBtn = document.getElementById('logoutBtn');

    try {
      const res  = await fetch('/PJBL-main/config/session_info.php');

      // Jika 401 → redirect ke login (session expired / belum login)
      if (res.status === 401) {
        window.location.href = '/PJBL-main/halamanWeb/loginpage/signin.php';
        return;
      }

      const data = await res.json();

      if (!data.loggedIn) {
        window.location.href = '/PJBL-main/halamanWeb/loginpage/signin.php';
        return;
      }

      if (emailEl) emailEl.textContent = data.email || 'admin';
      if (initialEl) initialEl.textContent = (data.email || 'A').charAt(0).toUpperCase();

    } catch (e) {
      // Gagal fetch (network error dll) — biarkan, PHP sudah proteksi halaman
      console.warn('sidebar.js: gagal fetch session_info', e);
    }

    // Tombol logout — POST ke logout.php (destroy PHP session)
    if (logoutBtn && !logoutBtn.dataset.bound) {
      logoutBtn.dataset.bound = 'true';
      logoutBtn.addEventListener('click', () => {
        if (confirm('Apakah Anda yakin ingin logout?')) {
          window.location.href = '/PJBL-main/halamanWeb/loginpage/logout.php';
        }
      });
    }
  };

})();
