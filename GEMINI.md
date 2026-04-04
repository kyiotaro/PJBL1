# Project: Permata Biru Nusantara (PJBL-main)

This project is a PHP-based web application, likely a portal or CMS for "Permata Biru Nusantara," featuring article management, admin dashboards, and a landing page.

## Tech Stack
- **Backend:** PHP
- **Database:** MySQL (schema in `dataBase/permatabirunusantara.sql`)
- **Frontend:** HTML, CSS, JavaScript
- **Web Server:** XAMPP (Apache)

## Database Schema
The database `permatabirunusantara` (schema in `dataBase/permatabirunusantara.sql`) primarily uses the following table for article management:

- **`artikel`**:
  - `id` (INT, PK, AUTO_INCREMENT): Unique identifier.
  - `judul` (VARCHAR): Article title.
  - `kategori` (VARCHAR): Article category (e.g., 'biota', 'geologi', 'konservasi', 'wisata').
  - `tanggal` (DATE): Publication date.
  - `gambar` (VARCHAR): Relative path to the image starting from `artikel/` (e.g., `artikel/biota/image.jpg`).
  - `isi` (LONGTEXT): Article content in HTML format.
  - `slug` (VARCHAR, UNIQUE): URL-friendly article identifier.

## Project Structure
- `artikelTemplate/`: Templates for article display.
- `assets/`: Global styles, images, and helper scripts.
  - `Foto/artikel/`: Organized by category subdirectories: `biota/`, `geologi/`, `konservasi/`, and `wisata/`.
- `dashboard*/`: Administrative interfaces for management and settings.
- `landingpage/`: Public-facing home page.
- `loginpage/`: Authentication flows.
- `tentangpage/`: "About Us" section.

## Engineering Standards
- **PHP:** Use `koneksi.php` for database connectivity.
- **Styling:** Follow the existing CSS variable patterns in `assets/variables.css`.
- **Naming:** Maintain the current lowercase naming convention for files and directories.
- **Templates:** Use components from `assets/templateHalaman/` (navbar, footer, cards) to ensure UI consistency across pages.


