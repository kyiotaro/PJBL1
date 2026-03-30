// ===== USER LOGIN (Frontend Only) =====

// Simpan data user yang login
function setUserSession(email, rememberMe = false) {
  const userData = { isUser: true, email: email };
  
  if (rememberMe) {
    localStorage.setItem('userSession', JSON.stringify(userData));
  } else {
    sessionStorage.setItem('userSession', JSON.stringify(userData));
  }
}

// Cek apakah user sudah login
function isUserLoggedIn() {
  const session = sessionStorage.getItem('userSession') || localStorage.getItem('userSession');
  return session !== null;
}

// Logout user
function logoutUser() {
  sessionStorage.removeItem('userSession');
  localStorage.removeItem('userSession');
  window.location.href = '../landingpage/landingpage.html';
}

// Get info user
function getUserSession() {
  let session = sessionStorage.getItem('userSession') || localStorage.getItem('userSession');
  return session ? JSON.parse(session) : null;
}

// Handle form login user
document.addEventListener('DOMContentLoaded', () => {
  const loginForm = document.getElementById('userLoginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const email = document.getElementById('email').value;
      const rememberMe = document.getElementById('rememberMe')?.checked || false;
      setUserSession(email, rememberMe);
      window.location.href = '../landingpage/landingpage.html';
    });
  }
});
