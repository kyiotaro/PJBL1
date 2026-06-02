# Setup Sistem Login & Register dengan Level User

## 📋 Fitur yang Ditambahkan

1. **Halaman Login** - Mendukung login Admin dan User
2. **Halaman Register** - User bisa mendaftar dengan otomatis level "user"
3. **Management User** - Admin dapat mengubah level user dari "user" menjadi "admin"
4. **Navbar Update** - Tombol Login dan Daftar di navbar

---

## 🔧 Setup Database

### Option 1: Menggunakan PHPMyAdmin (XAMPP)

1. Buka **PHPMyAdmin** di `http://localhost/phpmyadmin`
2. Pilih database `permatabirunusantara`
3. Buka **SQL** tab
4. Copy dan jalankan script berikut:

```sql
CREATE TABLE IF NOT EXISTS users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nama VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  level ENUM('user', 'admin') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Option 2: Import File SQL

1. File SQL sudah tersedia di: `backups/users_table_setup.sql`
2. Import file tersebut di PHPMyAdmin

---

## 📁 File-File Baru yang Ditambahkan

```
halamanWeb/loginpage/
├── signup.php                    # Halaman form register
├── register_logic.php            # Backend logic register
├── signin.php                    # (Updated) Sekarang support Admin & User
├── auth_logic.php                # (Updated) Tambah level checking
├── logout.php                    # (Tidak berubah)
├── js/
│   ├── auth.js                   # (Updated) Multi-role session
│   ├── user_auth.js              # (Sudah ada) User session
│   └── user_register.js          # Frontend logic register
└── css/
    └── signin.css                # (Updated) Tambah role selector styling

dashboard/dashboardadmin/
└── manage_users.php              # Admin panel untuk manage user level

assets/templateHalaman/navbar/
├── navbar.php                    # (Updated) Tambah tombol Daftar
└── navbar.css                    # (Updated) Styling untuk login & signup btn
```

---

## 🚀 Cara Menggunakan

### 1. User Register
- Klik tombol **"Daftar"** di navbar
- Isi form dengan nama, email, dan password
- Otomatis terdaftar dengan level **"user"**

### 2. User Login
- Klik tombol **"Login"** di navbar
- Pilih **"User"** tab
- Masukkan email dan password
- Akan redirect ke dashboard user

### 3. Admin Login
- Klik tombol **"Login"** di navbar
- Pilih **"Admin"** tab
- Masukkan email admin
- Verifikasi dengan OTP
- Akan redirect ke dashboard admin

### 4. Admin Manage User Level
- Login sebagai Admin
- Buka: `/dashboard/dashboardadmin/manage_users.php`
- Lihat semua user yang terdaftar
- Klik **"Edit Level"** untuk mengubah level user
- Pilih level "user" atau "admin"
- Klik **"Simpan"**

---

## 🔐 Data Session

### User Session
```javascript
// User session di sessionStorage
{
  isUser: true,
  email: "user@example.com"
}
```

### Admin Session
```javascript
// Admin session di sessionStorage
{
  isAdmin: true,
  email: "admin@example.com"
}
```

---

## 📝 Database Schema

### Tabel `users`

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | INT | Primary Key |
| nama | VARCHAR(255) | Nama lengkap user |
| email | VARCHAR(255) | Email unique |
| password | VARCHAR(255) | Password hashed |
| level | ENUM('user', 'admin') | Level user (default: 'user') |
| created_at | TIMESTAMP | Waktu registrasi |
| updated_at | TIMESTAMP | Waktu update terakhir |

---

## 🎯 Next Steps

Untuk fitur upload artikel user, perlu membuat:

1. **Dashboard User** (`dashboard/dashboarduser/dashboard.php`)
   - Upload form artikel
   - List artikel yang sudah diupload
   - Status approval

2. **Dashboard Admin - Approve Artikel**
   - List artikel pending
   - Approve/reject artikel
   - Publish ke website

3. **Tabel `artikel`** dengan kolom:
   - user_id (FK ke users)
   - status (pending, approved, rejected)
   - created_at, updated_at, approved_by (FK ke admin)

---

## 🐛 Troubleshooting

### Email sudah terdaftar
- Email unique, tidak bisa register dua kali
- Coba dengan email baru atau reset password

### Password tidak cocok
- Pastikan password dan konfirmasi password sama
- Password minimal 6 karakter

### OTP tidak diterima
- Cek konfigurasi SMTP di database (tabel `pengaturan`)
- Pastikan SMTP sudah benar konfigurasinya

### User tidak bisa login
- Pastikan email sudah terdaftar di tabel `users`
- Cek password yang diinput

---

## ✅ Checklist Verifikasi

- [ ] Tabel `users` sudah dibuat
- [ ] Halaman signup bisa diakses
- [ ] User bisa register dengan level default "user"
- [ ] User bisa login
- [ ] Admin bisa login dengan OTP
- [ ] Admin bisa melihat list user di manage_users.php
- [ ] Admin bisa mengubah level user dari "user" menjadi "admin"
- [ ] Tombol Login & Daftar sudah ada di navbar

---

**Version**: 1.0  
**Last Updated**: June 2, 2026
