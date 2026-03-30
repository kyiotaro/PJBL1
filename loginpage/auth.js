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
  window.location.href = '../landingpage/landingpage.html';
}

// Get info admin
function getAdminSession() {
  const session = sessionStorage.getItem('adminSession');
  return session ? JSON.parse(session) : null;
}

// Handle form login
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('loginForm');
  if (form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const email = document.getElementById('email').value;
      setAdminSession(email);
      window.location.href = '../dashboardadmin/dashboard.html';
    });
  }
});
