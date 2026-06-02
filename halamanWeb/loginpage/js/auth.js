// ===== LOGIN (Frontend Only) - Auto Detect User Type =====
const ADMIN_SESSION_KEY = 'adminSession';
const USER_SESSION_KEY = 'userSession';
const LANDING_PAGE_URL = '/PJBL-main/halamanWeb/landingpage/landingpage.php';

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

async function safeParseJson(response) {
  const rawText = await response.text();

  if (!rawText) {
    throw new Error('Respons kosong dari server.');
  }

  try {
    return JSON.parse(rawText);
  } catch (error) {
    console.error('Invalid JSON response:', rawText);
    throw new Error('Respons server tidak valid. Cek error PHP di backend.');
  }
}

async function fetchWithTimeout(url, options = {}, timeoutMs = 12000) {
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), timeoutMs);

  try {
    return await fetch(url, {
      ...options,
      signal: controller.signal
    });
  } finally {
    clearTimeout(timeoutId);
  }
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

  // Password Elements
  const passwordSection = document.getElementById('passwordSection');
  const btnShowPasswordLogin = document.getElementById('btnShowPasswordLogin');
  const btnBackToOtp = document.getElementById('btnBackToOtp');
  const btnLoginPassword = document.getElementById('btnLoginPassword');

  if (form) {
    // Toggle ke Login Password
    if (btnShowPasswordLogin) {
      btnShowPasswordLogin.addEventListener('click', () => {
        const email = document.getElementById('email').value.trim();
        if (!email) {
          alert('Email wajib diisi untuk masuk dengan password.');
          return;
        }
        emailSection.style.display = 'none';
        passwordSection.style.display = 'block';
        formSubtitle.textContent = 'Masukkan password untuk akun ' + email;
      });
    }

    // Kembali ke OTP
    if (btnBackToOtp) {
      btnBackToOtp.addEventListener('click', (e) => {
        e.preventDefault();
        passwordSection.style.display = 'none';
        emailSection.style.display = 'block';
        formSubtitle.textContent = 'Masukkan email Anda untuk masuk';
      });
    }

    // Login Password Action
    if (btnLoginPassword) {
      btnLoginPassword.addEventListener('click', async () => {
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();

        if (!password) {
          alert('Masukkan password.');
          return;
        }

        btnLoginPassword.disabled = true;
        btnLoginPassword.textContent = 'Masuk...';

        try {
          const formData = new FormData();
          formData.append('action', 'login_password');
          formData.append('email', email);
          formData.append('password', password);

          const response = await fetchWithTimeout('auth_logic_new.php', {
            method: 'POST',
            body: formData
          }, 20000); // 20 second timeout for password check

          const data = await safeParseJson(response);

          if (data.success) {
            // Success logic sama dengan OTP
            if (data.user_type === 'admin') {
              setAdminSession(email);
              window.location.href = LANDING_PAGE_URL;
            } else {
              setUserSession(email, data.user_level);
              window.location.href = LANDING_PAGE_URL;
            }
          } else {
            alert(data.message);
          }
        } catch (error) {
          console.error('Error Password Login:', error);
          alert('Terjadi kesalahan saat masuk.');
        } finally {
          btnLoginPassword.disabled = false;
          btnLoginPassword.textContent = 'Masuk';
        }
      });
    }

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

        const response = await fetchWithTimeout('auth_logic_new.php', {
          method: 'POST',
          body: formData
        }, 60000); // 60 second timeout

        const data = await safeParseJson(response);

        if (data.success) {
          alert(data.message);
          emailSection.style.display = 'none';
          otpSection.style.display = 'block';
          formSubtitle.textContent = 'Masukkan kode yang dikirim ke ' + email;
        } else {
          let errorMsg = data.message;
          if (data.error_detail) {
            errorMsg += "\nDetail: " + data.error_detail;
          }
          alert(errorMsg);
        }
      } catch (error) {
        console.error('Error:', error);
        if (error.name === 'AbortError') {
          alert('Permintaan waktu habis (timeout). Silakan coba lagi.');
        } else {
          alert('Terjadi kesalahan saat mengirim OTP: ' + error.message);
        }
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

        const response = await fetchWithTimeout('auth_logic_new.php', {
          method: 'POST',
          body: formData
        }, 20000);

        const data = await safeParseJson(response);

        if (data.success) {
          // Auto detect dan redirect berdasarkan user_type
          if (data.user_type === 'admin') {
            setAdminSession(email);
            window.location.href = LANDING_PAGE_URL;
          } else {
            // User type = 'user'
            setUserSession(email, data.user_level);
            window.location.href = LANDING_PAGE_URL;
          }
        } else {
          alert(data.message);
        }
      } catch (error) {
        console.error('Error:', error);
        if (error.name === 'AbortError') {
          alert('Verifikasi terlalu lama. Silakan coba lagi.');
        } else {
          alert(error.message || 'Terjadi kesalahan saat verifikasi.');
        }
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
