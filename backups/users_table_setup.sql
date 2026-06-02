-- Script untuk create/update tabel users dengan kolom level
-- Jalankan di database Anda

-- Create table users jika belum ada
CREATE TABLE IF NOT EXISTS users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nama VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  level ENUM('user', 'admin') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Jika tabel sudah ada, tambahkan kolom level (jika belum ada)
-- ALTER TABLE users ADD COLUMN level ENUM('user', 'admin') DEFAULT 'user' AFTER password;

-- Contoh insert user test:
-- INSERT INTO users (nama, email, password, level) VALUES ('Test User', 'user@example.com', PASSWORD('password123'), 'user');
-- INSERT INTO users (nama, email, password, level) VALUES ('Test Admin', 'admin@example.com', PASSWORD('password123'), 'admin');
