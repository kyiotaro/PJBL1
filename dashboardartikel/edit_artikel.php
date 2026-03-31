<?php
include '../koneksi.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: dashboard_artikel.php');
    exit;
}

$stmt = mysqli_prepare($koneksi, "SELECT id, judul, kategori, tanggal, gambar, isi FROM Artikel WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$article = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$article) {
    header('Location: dashboard_artikel.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $tanggal = $_POST['tanggal'] ?? '';
    $isi = trim($_POST['isi'] ?? '');
    $slug = trim(strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $judul)), '-');
    $gambar = $article['gambar'];

    if ($slug === '') {
        $slug = 'artikel-' . $id;
    }

    if ($judul === '' || $kategori === '' || $tanggal === '' || $isi === '') {
        $error = 'Semua field wajib diisi.';
    } else {
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
                $error = 'Upload gambar baru gagal.';
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
                    $baseFileName = $slug !== '' ? $slug : 'artikel-' . $id;
                    $gambar = $baseFileName . '-' . date('YmdHis') . '.' . $extension;
                    $gambar = 'artikel/' . $kategori . '/' . $gambar;
                    $uploadPath = '../assets/Foto/' . $gambar;

                    if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadPath)) {
                        $error = 'Gagal menyimpan gambar baru.';
                    }
                }
            }
        }

        if ($error === '') {
            $updateStmt = mysqli_prepare(
                $koneksi,
                "UPDATE Artikel SET judul = ?, kategori = ?, tanggal = ?, gambar = ?, isi = ?, slug = ? WHERE id = ?"
            );
            mysqli_stmt_bind_param($updateStmt, 'ssssssi', $judul, $kategori, $tanggal, $gambar, $isi, $slug, $id);

            if (mysqli_stmt_execute($updateStmt)) {
                mysqli_stmt_close($updateStmt);
                header('Location: dashboard_artikel.php?status=updated');
                exit;
            }

            mysqli_stmt_close($updateStmt);
            $error = 'Artikel gagal diperbarui.';
        }
    }

    $article['judul'] = $judul;
    $article['kategori'] = $kategori;
    $article['tanggal'] = $tanggal;
    $article['isi'] = $isi;
    $article['gambar'] = $gambar;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Artikel</title>
    <link rel="stylesheet" href="../dashboardadmin/dashboard.css">
    <link rel="stylesheet" href="edit_artikel.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
</head>
<body class="page-edit-artikel">

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
        <h1>Edit Artikel</h1>

        <?php if ($error !== '') : ?>
            <p style="color: #dc2626; margin-bottom: 16px;"><?= htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form class="form-box" id="articleForm" action="" method="POST" enctype="multipart/form-data">
            <label for="judul">Judul Artikel</label>
            <input type="text" id="judul" name="judul" value="<?= htmlspecialchars($article['judul']); ?>" required>

            <label for="kategori">Kategori</label>
            <select id="kategori" name="kategori" required>
                <option value="">Pilih kategori artikel</option>
                <option value="biota" <?= ($article['kategori'] === 'biota') ? 'selected' : ''; ?>>Biota</option>
                <option value="wisata" <?= ($article['kategori'] === 'wisata') ? 'selected' : ''; ?>>Wisata</option>
                <option value="konservasi" <?= ($article['kategori'] === 'konservasi') ? 'selected' : ''; ?>>Konservasi</option>
                <option value="geologi" <?= ($article['kategori'] === 'geologi') ? 'selected' : ''; ?>>Geologi</option>
            </select>

            <label for="tanggal">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" value="<?= htmlspecialchars($article['tanggal']); ?>" required>

            <label for="gambar">Ganti Gambar (opsional)</label>
            <div class="file-upload-field">
                <label for="gambar" class="file-upload-label">Pilih Gambar Baru</label>
                <span id="fileName" class="file-upload-name">Gunakan gambar lama jika tidak diganti</span>
                <input type="file" id="gambar" name="gambar" accept="image/*">
            </div>

            <?php if (!empty($article['gambar'])) : ?>
                <p style="margin-bottom: 12px; color: #475569;">Gambar saat ini: <?= htmlspecialchars($article['gambar']); ?></p>
            <?php endif; ?>

            <label>Isi Artikel</label>
            <div id="editor" style="height: 300px; margin-bottom: 16px; border-radius: 8px;"></div>
            <input type="hidden" name="isi" id="isi" value="<?= htmlspecialchars($article['isi']); ?>">

            <button type="submit" class="submit-btn">Update Artikel</button>
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
        placeholder: 'Perbarui isi artikel di sini...'
    });

    const isiInput = document.getElementById('isi');
    const fileInput = document.getElementById('gambar');
    const fileName = document.getElementById('fileName');

    quill.root.innerHTML = isiInput.value || '';

    fileInput.addEventListener('change', () => {
        fileName.textContent = fileInput.files.length > 0 ? fileInput.files[0].name : 'Gunakan gambar lama jika tidak diganti';
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