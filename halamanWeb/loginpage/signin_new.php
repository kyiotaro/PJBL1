<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign in - Permata Biru Nusantara</title>
  <link rel="stylesheet" href="css/signin.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inika:wght@400;700&family=Inria+Sans:wght@300;400;500;700&display=swap');
  </style>
</head>

<body>
  <div class="container">
    <div class="kiri">
      <div class="auth-card">
        <div class="logo">
          <img src="/PJBL-main/assets/Foto/brand/logo.png" alt="Logo">
        </div>

        <div class="step active" id="step1">
          <h1>Sign in</h1>
          <p class="subtitle" id="formSubtitle">Masukkan email Anda untuk masuk</p>

          <form class="login-form" id="loginForm">
            <div id="emailSection" class="form-group">
              <label for="email">Email</label>
              <input id="email" name="email" type="email" placeholder="Masukkan email Anda" required>
              <button class="primary-button" type="submit" id="btnSendOtp">Kirim OTP</button>
            </div>

            <div id="otpSection" class="form-group" style="display: none;">
              <label for="otp">Kode OTP</label>
              <input id="otp" name="otp" type="text" placeholder="6 Digit Kode" maxlength="6">
              <button class="primary-button" type="button" id="btnVerifyOtp">Verifikasi & Masuk</button>
              <p class="resend-text">
                <a href="#" id="btnResendOtp">Kirim ulang email?</a>
              </p>
            </div>
          </form>
        </div>

        <div class="signup-cta">
          <p>Belum punya akun? <a href="/PJBL-main/halamanWeb/loginpage/signup.php">Daftar di sini</a></p>
          <p><a href="/PJBL-main/halamanWeb/landingpage/landingpage.php">← Kembali ke halaman utama</a></p>
        </div>
      </div>
    </div>
  </div>

  <script src="js/auth.js"></script>
</body>

</html>
