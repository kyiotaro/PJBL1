<?php
include '../koneksi.php';

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

    if ($judul === '' || $kategori === '' || $tanggal === '' || $isi === '') {
        $error = 'Semua field wajib diisi.';
    } elseif (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Gambar artikel wajib diunggah.';
    } else {
        $uploadDir = '../assets/Foto/artikel/' . $kategori . '/';

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
                    "INSERT INTO Artikel (judul, kategori, tanggal, gambar, isi, slug) VALUES (?, ?, ?, ?, ?, ?)"
                );
                mysqli_stmt_bind_param($stmt, 'ssssss', $judul, $kategori, $tanggal, $gambarPath, $isi, $slug);

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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Artikel</title>
    <link rel="stylesheet" href="../dashboardadmin/dashboard.css">
    <link rel="stylesheet" href="tambah_artikel.css?v=2">
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
            <img src="../assets/Foto/brand/logo.png" alt="Logo">
            <h1>Permata Biru Nusantara</h1>
        </div>
        <ul class="nav-menu">
            <li><a class="nav-link" href="../dashboardadmin/dashboard.php">Dashboard</a></li>
            <li><a class="nav-link active" href="../dashboardartikel/dashboard_artikel.php">Artikel</a></li>
            <li><a class="nav-link" href="../dashboardpengaturan/dashboard_pengaturan.php">Pengaturan</a></li>
        </ul>
    </div>

    <div class="content">
        <h1>Tambah Artikel</h1>

        <?php if ($error !== '') : ?>
            <p style="color: #dc2626; margin-bottom: 16px;"><?= htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form class="form-box" id="articleForm" action="" method="POST" enctype="multipart/form-data">
            <label for="judul">Judul Artikel</label>
            <input type="text" id="judul" name="judul" placeholder="Masukkan judul..." value="<?= htmlspecialchars($_POST['judul'] ?? ''); ?>" required>

            <label for="kategori">Kategori</label>
            <select id="kategori" name="kategori" required>
                <option value="">Pilih kategori artikel</option>
                <option value="biota" <?= (($_POST['kategori'] ?? '') === 'biota') ? 'selected' : ''; ?>>Biota</option>
                <option value="wisata" <?= (($_POST['kategori'] ?? '') === 'wisata') ? 'selected' : ''; ?>>Wisata</option>
                <option value="konservasi" <?= (($_POST['kategori'] ?? '') === 'konservasi') ? 'selected' : ''; ?>>Konservasi</option>
                <option value="geologi" <?= (($_POST['kategori'] ?? '') === 'geologi') ? 'selected' : ''; ?>>Geologi</option>
            </select>

            <label for="tanggal">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" value="<?= htmlspecialchars($_POST['tanggal'] ?? ''); ?>" required>

            <label for="gambar">Gambar</label>
            <div class="file-upload-field">
                <label for="gambar" class="file-upload-label">Pilih Gambar</label>
                <span id="fileName" class="file-upload-name">Belum ada file dipilih</span>
                <input type="file" id="gambar" name="gambar" accept="image/*" required>
            </div>

            <label>Isi Artikel</label>
            <div id="editor" style="height: 300px; margin-bottom: 16px; border-radius: 8px;"></div>
            <input type="hidden" name="isi" id="isi" value="<?= htmlspecialchars($_POST['isi'] ?? ''); ?>">

            <button type="submit" class="submit-btn">Simpan Artikel</button>
        </form>
    </div>
</div>

<script src="../loginpage/auth.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (!protectAdminPage()) {
        return;
    }

    setupAdminUI();

    const quill = new Quill('#editor', {
        theme: 'snow',
        placeholder: 'Tulis isi artikel di sini...'
    });

    const isiInput = document.getElementById('isi');
    const fileInput = document.getElementById('gambar');
    const fileName = document.getElementById('fileName');

    if (isiInput.value) {
        quill.root.innerHTML = isiInput.value;
    }

    fileInput.addEventListener('change', () => {
        fileName.textContent = fileInput.files.length > 0 ? fileInput.files[0].name : 'Belum ada file dipilih';
    });

    document.getElementById('articleForm').addEventListener('submit', (event) => {
        const plainText = quill.getText().trim();

        if (!plainText) {
            event.preventDefault();
            alert('Isi artikel tidak boleh kosong.');
            return;
        }

        isiInput.value = quill.root.innerHTML;
    });
});
</script>

</body>
</html>