<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Sign in - Permata Biru Nusantara</title>
  <link rel="stylesheet" href="css/signin.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inika:wght@400;700&family=Inria+Sans:wght@300;400;500;700&display=swap');

    .step { display: none; }
    .step.active { display: block; }

    .otp-input {
      letter-spacing: 8px;
      font-size: 24px;
      text-align: center;
      text-transform: uppercase;
      font-weight: bold;
    }

    .timer {
      text-align: center;
      font-size: 13px;
      color: #666;
      margin-top: 8px;
    }

    .timer span {
      color: #dc3545;
      font-weight: bold;
    }

    .resend-btn {
      background: none;
      border: none;
      color: #0d6efd;
      cursor: pointer;
      font-size: 13px;
      text-decoration: underline;
      display: none;
      margin: 8px auto 0;
    }

    .resend-btn.show {
      display: block;
    }

    .back-btn {
      background: none;
      border: none;
      color: #666;
      cursor: pointer;
      font-size: 13px;
      text-decoration: underline;
      margin-top: 8px;
    }

    .msg {
      font-size: 13px;
      margin-top: 8px;
      text-align: center;
    }

    .msg.error { color: #dc3545; }
    .msg.success { color: #198754; }
  </style>
</head>
<body>
  <div class="container">
    <div class="kiri">
      <div class="logo">
        <img src="/PJBL-main/assets/Foto/brand/logo.png" alt="Logo">
      </div>

      <!-- STEP 1: Input Email -->
      <div class="step active" id="step1">
        <h1>Admin Sign in</h1>
        <p class="subtitle">Masukkan email admin untuk mendapatkan kode OTP</p>

        <div class="button-group">
          <form class="login-form" id="formEmail">
            <label for="email">Email Admin</label>
            <input id="email" name="email" type="email" placeholder="admin@example.com" required>
            <p class="msg" id="msgEmail"></p>
            <button class="primary-button" type="submit" id="btnKirim">Kirim Kode OTP</button>
          </form>
        </div>
      </div>

      <!-- STEP 2: Input OTP -->
      <div class="step" id="step2">
        <h1>Masukkan Kode</h1>
        <p class="subtitle" id="subtitleOtp">Kode OTP telah dikirim ke email kamu</p>

        <div class="button-group">
          <form class="login-form" id="formOtp">
            <label for="kode">Kode OTP</label>
            <input id="kode" name="kode" type="text" maxlength="6"
                   placeholder="ABC123" class="otp-input" required autocomplete="off">

            <div class="timer">Kode berlaku: <span id="countdown">15:00</span></div>
            <button type="button" class="resend-btn" id="resendBtn">Kirim ulang kode</button>

            <p class="msg" id="msgOtp"></p>
            <button class="primary-button" type="submit" id="btnVerifikasi">Verifikasi</button>
            <button type="button" class="back-btn" id="backBtn">← Ganti email</button>
          </form>
        </div>
      </div>

      <div class="signup-cta">
        <p><a href="/PJBL-main/halamanWeb/landingpage/landingpage.php">← Kembali ke halaman utama</a></p>
      </div>
    </div>

    <div class="kanan">
      <img src="/PJBL-main/assets/Foto/ui/background.png" alt="Background Image">
    </div>
  </div>

  <script>
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const formEmail = document.getElementById('formEmail');
    const formOtp = document.getElementById('formOtp');
    const msgEmail = document.getElementById('msgEmail');
    const msgOtp = document.getElementById('msgOtp');
    const subtitleOtp = document.getElementById('subtitleOtp');
    const btnKirim = document.getElementById('btnKirim');
    const btnVerifikasi = document.getElementById('btnVerifikasi');
    const resendBtn = document.getElementById('resendBtn');
    const backBtn = document.getElementById('backBtn');

    let countdownInterval = null;
    let currentEmail = '';

    // ── Kirim OTP ──
    async function kirimOtp(email) {
      btnKirim.disabled = true;
      btnKirim.textContent = 'Mengirim...';
      msgEmail.textContent = '';
      msgEmail.className = 'msg';

      const formData = new FormData();
      formData.append('email', email);

      try {
        const res = await fetch('proses_login.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
          currentEmail = email;
          subtitleOtp.textContent = `Kode OTP dikirim ke ${email}`;
          step1.classList.remove('active');
          step2.classList.add('active');
          document.getElementById('kode').focus();
          startCountdown(15 * 60);
        } else {
          msgEmail.textContent = data.message;
          msgEmail.classList.add('error');
        }
      } catch (e) {
        msgEmail.textContent = 'Terjadi kesalahan. Coba lagi.';
        msgEmail.classList.add('error');
      }

      btnKirim.disabled = false;
      btnKirim.textContent = 'Kirim Kode OTP';
    }

    // ── Countdown timer ──
    function startCountdown(seconds) {
      clearInterval(countdownInterval);
      resendBtn.classList.remove('show');
      const el = document.getElementById('countdown');

      countdownInterval = setInterval(() => {
        const m = Math.floor(seconds / 60).toString().padStart(2, '0');
        const s = (seconds % 60).toString().padStart(2, '0');
        el.textContent = `${m}:${s}`;

        if (seconds <= 0) {
          clearInterval(countdownInterval);
          el.textContent = '00:00';
          resendBtn.classList.add('show');
        }
        seconds--;
      }, 1000);
    }

    // ── Form email submit ──
    formEmail.addEventListener('submit', (e) => {
      e.preventDefault();
      kirimOtp(document.getElementById('email').value.trim());
    });

    // ── Form OTP submit ──
    formOtp.addEventListener('submit', async (e) => {
      e.preventDefault();
      const kode = document.getElementById('kode').value.trim().toUpperCase();

      btnVerifikasi.disabled = true;
      btnVerifikasi.textContent = 'Memverifikasi...';
      msgOtp.textContent = '';
      msgOtp.className = 'msg';

      const formData = new FormData();
      formData.append('kode', kode);

      try {
        const res = await fetch('verifikasi_otp.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
          msgOtp.textContent = 'Login berhasil! Mengalihkan...';
          msgOtp.classList.add('success');
          clearInterval(countdownInterval);
          setTimeout(() => { window.location.href = data.redirect; }, 800);
        } else {
          msgOtp.textContent = data.message;
          msgOtp.classList.add('error');
          btnVerifikasi.disabled = false;
          btnVerifikasi.textContent = 'Verifikasi';
        }
      } catch (e) {
        msgOtp.textContent = 'Terjadi kesalahan. Coba lagi.';
        msgOtp.classList.add('error');
        btnVerifikasi.disabled = false;
        btnVerifikasi.textContent = 'Verifikasi';
      }
    });

    // ── Tombol kirim ulang ──
    resendBtn.addEventListener('click', () => {
      kirimOtp(currentEmail);
    });

    // ── Tombol balik ke step 1 ──
    backBtn.addEventListener('click', () => {
      clearInterval(countdownInterval);
      step2.classList.remove('active');
      step1.classList.add('active');
      msgOtp.textContent = '';
    });
  </script>
</body>
</html>