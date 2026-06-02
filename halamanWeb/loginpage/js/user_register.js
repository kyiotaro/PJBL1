// ===== USER REGISTRATION =====
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('signupForm');
  const btnRegister = document.getElementById('btnRegister');

  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const nama = document.getElementById('nama').value.trim();
      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value.trim();

      if (!nama || !email || !password) {
        alert('Semua field wajib diisi.');
        return;
      }

      if (password.length < 6) {
        alert('Password minimal 6 karakter.');
        return;
      }

      btnRegister.disabled = true;
      btnRegister.textContent = 'Mendaftar...';

      try {
        const formData = new FormData();
        formData.append('action', 'register');
        formData.append('nama', nama);
        formData.append('email', email);
        formData.append('password', password);

        const response = await fetch('register_logic.php', {
          method: 'POST',
          body: formData
        });

        const data = await response.json();

        if (data.success) {
          alert(data.message);
          window.location.href = '/PJBL-main/halamanWeb/loginpage/signin.php';
        } else {
          alert(data.message);
        }
      } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mendaftar.');
      } finally {
        btnRegister.disabled = false;
        btnRegister.textContent = 'Daftar';
      }
    });
  }
});
