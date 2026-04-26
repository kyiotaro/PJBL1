(function () {
  const STORAGE_KEY = 'pbSidebarCollapsed';

  /* ── Init saat DOM siap ── */
  document.addEventListener('DOMContentLoaded', () => {
    const sidebar  = document.getElementById('pbSidebar');
    const toggleBtn = document.getElementById('pbSidebarToggle');
    const mainContent = document.querySelector('.pb-main-content');

    if (!sidebar) return;

    /* Pulihkan state collapse dari localStorage */
    const isCollapsed = localStorage.getItem(STORAGE_KEY) === 'true';
    if (isCollapsed) {
      sidebar.classList.add('collapsed');
    }

    /* Toggle klik */
    if (toggleBtn) {
      toggleBtn.addEventListener('click', () => {
        const collapsed = sidebar.classList.toggle('collapsed');
        localStorage.setItem(STORAGE_KEY, collapsed);
      });
    }

    /* Setup info admin (email, initial avatar, logout) */
    setupAdminUI();
  });

  /* ── Override/extend setupAdminUI dari auth.js ── */
  window.setupAdminUI = function () {
    /* getAdminSession() dari auth.js */
    const session = (typeof getAdminSession === 'function') ? getAdminSession() : null;

    const emailEl   = document.getElementById('adminEmail');
    const initialEl = document.getElementById('adminInitial');
    const logoutBtn = document.getElementById('logoutBtn');

    if (session && emailEl) {
      emailEl.textContent = session.email;
    }

    if (session && initialEl) {
      initialEl.textContent = (session.email || 'A').charAt(0).toUpperCase();
    }

    if (logoutBtn && !logoutBtn.dataset.bound) {
      logoutBtn.dataset.bound = 'true';
      logoutBtn.addEventListener('click', () => {
        if (confirm('Apakah Anda yakin ingin logout?')) {
          if (typeof logoutAdmin === 'function') logoutAdmin();
        }
      });
    }
  };
})();
