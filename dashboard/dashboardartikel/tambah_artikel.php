<?php
include '../../koneksi.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $tanggal = $_POST['tanggal'] ?? '';
    $isi = trim($_POST['isi'] ?? '');
    $slug = trim(strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $judul)), '-');

    if ($slug === '') {
        $slug = 'artikel-' . time();
    }

    $slugKategori = trim($_POST['kategori'] ?? '');
    $stmtKat = mysqli_prepare($koneksi, "SELECT id FROM kategori WHERE slug = ?");
    mysqli_stmt_bind_param($stmtKat, 's', $slugKategori);
    mysqli_stmt_execute($stmtKat);
    $resKat = mysqli_stmt_get_result($stmtKat);
    $rowKat = mysqli_fetch_assoc($resKat);
    $kategoriId = $rowKat['id'] ?? null;
    mysqli_stmt_close($stmtKat);

    if ($judul === '' || $slugKategori === '' || $tanggal === '' || $isi === '') {
        $error = 'Semua field wajib diisi.';
    } elseif (!$kategoriId) {
        $error = 'Kategori tidak valid. Pilih kategori yang tersedia.';
    } elseif (empty($_POST['gambar_url']) && (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] !== UPLOAD_ERR_OK)) {
        $error = 'Pilih gambar dari Pexels atau upload gambar manual.';
    } else {

        // ── Gambar dari Pexels ──────────────────────────────────────
        if (!empty($_POST['gambar_url'])) {
            $gambarPath = trim($_POST['gambar_url']);

            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO Artikel (judul, kategori_id, tanggal, gambar, isi, slug) VALUES (?, ?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, 'sissss', $judul, $kategoriId, $tanggal, $gambarPath, $isi, $slug);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header('Location: dashboard_artikel.php?status=created');
                exit;
            }

            mysqli_stmt_close($stmt);
            $error = 'Artikel gagal disimpan ke database.';

            // ── Upload gambar manual ────────────────────────────────────
        } else {
            $uploadDir = '../../assets/Foto/artikel/' . $kategori . '/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $originalName = basename($_FILES['gambar']['name']);
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($extension, $allowedExtensions, true)) {
                $error = 'Format gambar harus JPG, JPEG, PNG, GIF, atau WEBP.';
            } else {
                $baseFileName = $slug !== '' ? $slug : 'artikel-' . time();
                $gambar = $baseFileName . '-' . date('YmdHis') . '.' . $extension;
                $gambarPath = 'artikel/' . $kategori . '/' . $gambar;
                $uploadPath = $uploadDir . $gambar;

                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadPath)) {
                    $stmt = mysqli_prepare(
                        $koneksi,
                        "INSERT INTO Artikel (judul, kategori_id, tanggal, gambar, isi, slug) VALUES (?, ?, ?, ?, ?, ?)"
                    );
                    mysqli_stmt_bind_param($stmt, 'sissss', $judul, $kategoriId, $tanggal, $gambarPath, $isi, $slug);

                    if (mysqli_stmt_execute($stmt)) {
                        mysqli_stmt_close($stmt);
                        header('Location: dashboard_artikel.php?status=created');
                        exit;
                    }

                    mysqli_stmt_close($stmt);
                    $error = 'Artikel gagal disimpan ke database.';
                } else {
                    $error = 'Gagal upload gambar artikel.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Artikel</title>
    <link rel="stylesheet" href="../dashboardadmin/css/dashboard.css">
    <link rel="stylesheet" href="css/tambah_artikel.css?v=2">
    <!-- Mengarah ke folder AI baru -->
    <link rel="stylesheet" href="/PJBL-main/dashboard/ai-article-generator/css/style.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
</head>

<body class="page-tambah-artikel">

    <div class="user-info">
        <span id="adminEmail"></span>
        <button id="logoutBtn" class="logout-btn">Logout</button>
    </div>

    <div class="dashboard">
        <div class="sidebar">
            <div class="logo">
                <img src="/PJBL-main/assets/Foto/brand/logo.png" alt="Logo">
                <h1>Permata Biru Nusantara</h1>
            </div>
            <ul class="nav-menu">
                <li><a class="nav-link" href="/PJBL-main/dashboard/dashboardadmin/dashboard.php">Dashboard</a></li>
                <li><a class="nav-link active"
                        href="/PJBL-main/dashboard/dashboardartikel/dashboard_artikel.php">Artikel</a></li>
                <li><a class="nav-link"
                        href="/PJBL-main/dashboard/dashboardpengaturan/dashboard_pengaturan.php">Pengaturan</a></li>
            </ul>
        </div>

        <div class="content">
            <h1>Tambah Artikel</h1>

            <?php if ($error !== ''): ?>
                <p style="color: #dc2626; margin-bottom: 16px;"><?= htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <!-- FITUR AI GENERATE & PEXELS DISIMPAN DI FILE TERSENDIRI -->
            <?php include '../ai-article-generator/ai_component.php'; ?>

            <form class="form-box" id="articleForm" action="" method="POST" enctype="multipart/form-data">
                <label for="judul">Judul Artikel</label>
                <input type="text" id="judul" name="judul" placeholder="Masukkan judul..."
                    value="<?= htmlspecialchars($_POST['judul'] ?? ''); ?>" required>

                <label for="kategori">Kategori</label>
                <select id="kategori" name="kategori" required>
                    <option value="">Pilih kategori artikel</option>
                    <?php
                    $qKat = mysqli_query($koneksi, "SELECT nama, slug FROM kategori ORDER BY nama ASC");
                    while ($kat = mysqli_fetch_assoc($qKat)):
                        $selected = (($_POST['kategori'] ?? '') === $kat['slug']) ? 'selected' : '';
                        ?>
                        <option value="<?= htmlspecialchars($kat['slug']); ?>" <?= $selected; ?>>
                            <?= htmlspecialchars($kat['nama']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label for="tanggal">Tanggal</label>
                <input type="date" id="tanggal" name="tanggal" value="<?= htmlspecialchars($_POST['tanggal'] ?? ''); ?>"
                    required>

                <label for="gambar">Gambar (opsional jika pilih dari Pexels)</label>
                <div class="file-upload-field">
                    <label for="gambar" class="file-upload-label">Pilih Gambar</label>
                    <span id="fileName" class="file-upload-name">Belum ada file dipilih</span>
                    <input type="file" id="gambar" name="gambar" accept="image/*">
                </div>

                <label>Isi Artikel</label>
                <div id="editor" style="height: 300px; margin-bottom: 16px; border-radius: 8px;"></div>
                <input type="hidden" name="isi" id="isi" value="<?= htmlspecialchars($_POST['isi'] ?? ''); ?>">

                <input type="hidden" name="gambar_url" id="fieldGambarUrl">
                <input type="hidden" name="gambar_credit" id="fieldGambarCredit">

                <button type="submit" class="submit-btn">Simpan Artikel</button>
            </form>
        </div>
    </div>

    <script src="/PJBL-main/halamanWeb/loginpage/js/auth.js"></script>
    <script src="js/tambah_artikel.js"></script>

</body>

</html>