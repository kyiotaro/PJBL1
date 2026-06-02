<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up - Permata Biru Nusantara</title>
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
          <h1>Daftar Akun User</h1>
          <p class="subtitle" id="formSubtitle">Buat akun user untuk upload artikel</p>
          <input type="hidden" id="userRole" value="user">

          <form class="login-form" id="signupForm">
            <div class="form-group">
              <label for="nama">Nama Lengkap</label>
              <input id="nama" name="nama" type="text" placeholder="Masukkan nama Anda" required>
            </div>

            <div class="form-group">
              <label for="email">Email</label>
              <input id="email" name="email" type="email" placeholder="user@example.com" required>
            </div>

            <div class="form-group">
              <label for="password">Password</label>
              <input id="password" name="password" type="password" placeholder="Buat password baru" required>
            </div>

            <button class="primary-button" type="submit" id="btnRegister">Daftar</button>
          </form>
        </div>

        <div class="signup-cta">
          <p>Sudah punya akun? <a href="/PJBL-main/halamanWeb/loginpage/signin.php">Masuk di sini</a></p>
          <p><a href="/PJBL-main/halamanWeb/landingpage/landingpage.php">← Kembali ke halaman utama</a></p>
        </div>
      </div>
    </div>
  </div>

  <script src="js/user_register.js"></script>
</body>

</html>
