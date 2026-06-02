// ===== LOGIN (Frontend Only) - Auto Detect User Type =====
const ADMIN_SESSION_KEY = 'adminSession';
const USER_SESSION_KEY = 'userSession';

function setAdminSession(email) {
  const session = { isAdmin: true, email };
  sessionStorage.setItem(ADMIN_SESSION_KEY, JSON.stringify(session));
}

function setUserSession(email, level) {
  const session = { isUser: true, email, level };
  sessionStorage.setItem(USER_SESSION_KEY, JSON.stringify(session));
}

function setAdminSessionData(session) {
  sessionStorage.setItem(ADMIN_SESSION_KEY, JSON.stringify(session));
}

function setUserSessionData(session) {
  sessionStorage.setItem(USER_SESSION_KEY, JSON.stringify(session));
}

function removeAdminSession() {
  sessionStorage.removeItem(ADMIN_SESSION_KEY);
}

function removeUserSession() {
  sessionStorage.removeItem(USER_SESSION_KEY);
}

function getAdminSession() {
  const session = sessionStorage.getItem(ADMIN_SESSION_KEY);
  return session ? JSON.parse(session) : null;
}

function getUserSession() {
  const session = sessionStorage.getItem(USER_SESSION_KEY);
  return session ? JSON.parse(session) : null;
}

function isAdminLoggedIn() {
  return getAdminSession() !== null;
}

function isUserLoggedIn() {
  return getUserSession() !== null;
}

async function fetchAdminSession() {
  try {
    const res = await fetch('/PJBL-main/config/session_info.php');

    if (res.status === 401) {
      removeAdminSession();
      return null;
    }

    if (!res.ok) {
      return getAdminSession();
    }

    const data = await res.json();

    if (!data.loggedIn) {
      removeAdminSession();
      return null;
    }

    const session = {
      isAdmin: true,
      email: data.email || ''
    };

    setAdminSessionData(session);
    return session;
  } catch (e) {
    return getAdminSession();
  }
}

function protectAdminPage() {
  return getAdminSession();
}

function protectUserPage() {
  return getUserSession();
}

async function setupAdminUI() {
  let session = getAdminSession();

  if (!session) {
    session = await fetchAdminSession();
  }

  const adminEmailElement = document.getElementById('adminEmail');
  const logoutButton = document.getElementById('logoutBtn');
  const adminInitialElement = document.getElementById('adminInitial');

  if (session && adminEmailElement) {
    adminEmailElement.textContent = `Admin: ${session.email}`;
  }

  if (session && adminInitialElement) {
    adminInitialElement.textContent = session.email.charAt(0).toUpperCase();
  }

  if (logoutButton && !logoutButton.dataset.bound) {
    logoutButton.dataset.bound = 'true';
    logoutButton.addEventListener('click', () => {
      if (confirm('Apakah Anda yakin ingin logout?')) {
        logoutAdmin();
      }
    });
  }

  return session;
}

function logoutAdmin() {
  removeAdminSession();
  window.location.href = '/PJBL-main/halamanWeb/loginpage/logout.php';
}

function logoutUser() {
  removeUserSession();
  window.location.href = '/PJBL-main/halamanWeb/loginpage/logout.php';
}

// Handle form login - AUTO DETECT USER TYPE
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('loginForm');
  const emailSection = document.getElementById('emailSection');
  const otpSection = document.getElementById('otpSection');
  const btnSendOtp = document.getElementById('btnSendOtp');
  const btnVerifyOtp = document.getElementById('btnVerifyOtp');
  const btnResendOtp = document.getElementById('btnResendOtp');
  const formSubtitle = document.getElementById('formSubtitle');

  if (form) {
    // Step 1: Kirim OTP
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const email = document.getElementById('email').value.trim();

      if (!email) {
        alert('Email wajib diisi.');
        return;
      }

      btnSendOtp.disabled = true;
      btnSendOtp.textContent = 'Mengirim...';

      try {
        const formData = new FormData();
        formData.append('action', 'send_otp');
        formData.append('email', email);

        const response = await fetch('auth_logic_new.php', {
          method: 'POST',
          body: formData
        });

        const data = await response.json();

        if (data.success) {
          alert(data.message);
          emailSection.style.display = 'none';
          otpSection.style.display = 'block';
          formSubtitle.textContent = 'Masukkan kode yang dikirim ke ' + email;
        } else {
          alert(data.message);
        }
      } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengirim OTP.');
      } finally {
        btnSendOtp.disabled = false;
        btnSendOtp.textContent = 'Kirim OTP';
      }
    });

    // Step 2: Verifikasi OTP
    btnVerifyOtp.addEventListener('click', async () => {
      const otp = document.getElementById('otp').value.trim();
      const email = document.getElementById('email').value.trim();

      if (!otp) {
        alert('Masukkan kode OTP.');
        return;
      }

      btnVerifyOtp.disabled = true;
      btnVerifyOtp.textContent = 'Memverifikasi...';

      try {
        const formData = new FormData();
        formData.append('action', 'verify_otp');
        formData.append('otp', otp);

        const response = await fetch('auth_logic_new.php', {
          method: 'POST',
          body: formData
        });

        const data = await response.json();

        if (data.success) {
          // Auto detect dan redirect berdasarkan user_type
          if (data.user_type === 'admin') {
            setAdminSession(email);
            window.location.href = '/PJBL-main/dashboard/dashboardadmin/dashboard.php';
          } else {
            // User type = 'user'
            setUserSession(email, data.user_level);
            
            // Redirect berdasarkan level
            if (data.user_level === 'admin') {
              // User dengan level admin
              window.location.href = '/PJBL-main/dashboard/dashboardadmin/dashboard.php';
            } else {
              // User biasa
              window.location.href = '/PJBL-main/dashboard/dashboarduser/dashboard.php';
            }
          }
        } else {
          alert(data.message);
        }
      } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat verifikasi.');
      } finally {
        btnVerifyOtp.disabled = false;
        btnVerifyOtp.textContent = 'Verifikasi & Masuk';
      }
    });

    // Resend OTP
    if (btnResendOtp) {
      btnResendOtp.addEventListener('click', (e) => {
        e.preventDefault();
        otpSection.style.display = 'none';
        emailSection.style.display = 'block';
        formSubtitle.textContent = 'Masukkan email Anda untuk masuk';
        document.getElementById('email').value = '';
        document.getElementById('otp').value = '';
      });
    }
  }
});
