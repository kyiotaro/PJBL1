<?php
session_start();
if (
  (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) ||
  (!empty($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true)
) {
  header('Location: /PJBL-main/halamanWeb/landingpage/landingpage.php');
  exit;
}
?>
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
            <!-- Section Email (Awal) -->
            <div id="emailSection" class="form-group">
              <label for="email">Email</label>
              <input id="email" name="email" type="email" placeholder="Masukkan email Anda" required>
              <div class="button-group">
                <button class="primary-button" type="submit" id="btnSendOtp">Kirim OTP</button>
                <button class="social-button" type="button" id="btnShowPasswordLogin" style="background: #f8f9fa; border: 1px solid #dee2e6; color: #444; margin-top: 10px;">
                  Masuk dengan Password
                </button>
              </div>
            </div>

            <!-- Section Password (Baru) -->
            <div id="passwordSection" class="form-group" style="display: none;">
              <label for="password">Password</label>
              <input id="password" name="password" type="password" placeholder="Masukkan password Anda">
              <button class="primary-button" type="button" id="btnLoginPassword">Masuk</button>
              <p class="resend-text">
                <a href="#" id="btnBackToOtp">Gunakan Kode OTP?</a>
              </p>
            </div>

            <!-- Section OTP -->
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
