// ===== ADMIN LOGIN (Frontend Only) =====

// Simpan data admin yang login
function setAdminSession(email) {
  sessionStorage.setItem('adminSession', JSON.stringify({
    isAdmin: true,
    email: email
  }));
}

// Cek apakah admin sudah login
function isAdminLoggedIn() {
  return sessionStorage.getItem('adminSession') !== null;
}

// Logout admin
function logoutAdmin() {
  sessionStorage.removeItem('adminSession');
  window.location.href = '/PJBL-main/halamanWeb/landingpage/landingpage.php';
}

// Get info admin
function getAdminSession() {
  const session = sessionStorage.getItem('adminSession');
  return session ? JSON.parse(session) : null;
}

// Proteksi halaman admin
function protectAdminPage() {
  if (!isAdminLoggedIn()) {
    alert('Anda harus login sebagai admin untuk mengakses halaman ini.');
    window.location.href = '/PJBL-main/halamanWeb/loginpage/signin.html';
    return null;
  }

  return getAdminSession();
}

// Setup info admin & tombol logout
function setupAdminUI() {
  const session = getAdminSession();
  const adminEmailElement = document.getElementById('adminEmail');
  const logoutButton = document.getElementById('logoutBtn');

  if (session && adminEmailElement) {
    adminEmailElement.textContent = `Admin: ${session.email}`;
  }

  if (logoutButton && !logoutButton.dataset.bound) {
    logoutButton.dataset.bound = 'true';
    logoutButton.addEventListener('click', () => {
      if (confirm('Apakah Anda yakin ingin logout?')) {
        logoutAdmin();
      }
    });
  }
}

// Handle form login
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('loginForm');
  if (form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const email = document.getElementById('email').value.trim();

      if (!email) {
        alert('Email wajib diisi.');
        return;
      }

      setAdminSession(email);
      window.location.href = '/PJBL-main/dashboard/dashboardadmin/dashboard.php';
    });
  }
});

