# Project: Permata Biru Nusantara (PJBL-main)

This project is a PHP-based web application, likely a portal or CMS for "Permata Biru Nusantara," featuring article management, admin dashboards, and a landing page.

## Tech Stack
- **Backend:** PHP
- **Database:** MySQL (schema in `dataBase/permatabirunusantara.sql`)
- **Frontend:** HTML, CSS, JavaScript
- **Web Server:** XAMPP (Apache)

## Database Schema
The database `permatabirunusantara` primarily uses the following table for article management:

- **`artikel`**:
  - `id` (INT, PK, AUTO_INCREMENT): Unique identifier.
  - `judul` (VARCHAR): Article title.
  - `kategori` (VARCHAR): Article category (e.g., 'biota', 'geologi', 'konservasi', 'wisata').
  - `tanggal` (DATE): Publication date.
  - `gambar` (VARCHAR): Image filename.
  - `isi` (LONGTEXT): Article content in HTML format.

## Project Structure
- `halamanWeb/`:
  - `artikelTemplate/`: Templates for article detail display.
  - `landingpage/`: Public-facing home page.
  - `hasilPencarian/`: Search results page.
- `assets/`:
  - `variables.css`: Global CSS variables (source of truth for spacing, colors, and component variables).
  - `helpers/foto_helper.php`: Helper for resolving article image paths.
  - `templateHalaman/`:
    - `cardVariant/`: Contains different card designs (e.g., `card1` for grid, `card2` for list/row).
- `Percobaan/`: Sandbox for testing components (e.g., `test.php`).

## Engineering Standards
- **PHP:**
  - Use `koneksi.php` for database connectivity.
  - Always use `assets/helpers/foto_helper.php` (specifically `resolveFotoWebPath()`) to display article images.
- **Styling:**
  - Adhere to `assets/variables.css`. Components should use global variables or define their own in `:root` inside their specific CSS files (linked back to global variables).
  - **Grid Layout:** Standard grid uses `repeat(3, 1fr)` for article sections on the landing page.
  - **Full Width:** Detailed pages like `artikel.php` and `hasilPencarian.php` use full-screen width layouts without `max-width` constraints on main containers.
- **Naming:** Lowercase naming convention for files and directories (kebab-case preferred for new files).
- **Paths:** Use relative paths (e.g., `../assets/...`) to ensure portability across different directories.

## Component Memory
- **Card Varian 2 (card2):** A row-based layout with the image on the left/right and stacked content. Optimized for lists and search results. Includes mobile-responsive stacking.
- **Related Articles:** Displays 3 articles in a row at the bottom of the article template.
