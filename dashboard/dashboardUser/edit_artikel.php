<?php
session_start();
require_once '../../config/auth_check_user.php';
include '../../koneksi.php';

$id = (int) ($_GET['id'] ?? 0);
$userId = $_SESSION['user_id'];

if ($id <= 0) {
    header('Location: dashboard_artikel.php');
    exit;
}

$stmt = mysqli_prepare($koneksi, "SELECT * FROM artikel WHERE id = ? AND author_id = ? AND author_type = 'user'");
mysqli_stmt_bind_param($stmt, 'ii', $id, $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$article = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$article) {
    header('Location: dashboard_artikel.php');
    exit;
}

// Restricted Edit: Only if pending or rejected
if ($article['status'] !== 'pending' && $article['status'] !== 'rejected') {
    header('Location: dashboard_artikel.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $slugKategori = trim($_POST['kategori'] ?? '');
    $tanggal = $_POST['tanggal'] ?? '';
    $isi = trim($_POST['isi'] ?? '');
    $slug = trim(strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $judul)), '-');
    $gambar = $article['gambar'];

    $stmtKat = mysqli_prepare($koneksi, "SELECT id FROM kategori WHERE slug = ?");
    mysqli_stmt_bind_param($stmtKat, 's', $slugKategori);
    mysqli_stmt_execute($stmtKat);
    $resKat = mysqli_stmt_get_result($stmtKat);
    $rowKat = mysqli_fetch_assoc($resKat);
    $kategoriId = $rowKat['id'] ?? null;
    mysqli_stmt_close($stmtKat);

    if ($judul === '' || !$kategoriId || $tanggal === '' || $isi === '') {
        $error = 'Semua field wajib diisi.';
    } else {
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
                $error = 'Upload gambar baru gagal.';
            } else {
                $uploadDir = '../../assets/Foto/artikel/' . $slugKategori . '/';
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
                    $gambar = 'artikel/' . $slugKategori . '/' . $gambar;
                    $uploadPath = '../../assets/Foto/' . $gambar;

                    if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadPath)) {
                        $error = 'Gagal menyimpan gambar baru.';
                    }
                }
            }
        }

        if ($error === '') {
            $updateStmt = mysqli_prepare(
                $koneksi,
                "UPDATE artikel SET judul = ?, kategori_id = ?, tanggal = ?, gambar = ?, isi = ?, slug = ?, status = 'pending' WHERE id = ?"
            );
            mysqli_stmt_bind_param($updateStmt, 'sissssi', $judul, $kategoriId, $tanggal, $gambar, $isi, $slug, $id);

            if (mysqli_stmt_execute($updateStmt)) {
                mysqli_stmt_close($updateStmt);
                header('Location: dashboard_artikel.php?status=updated');
                exit;
            }
            mysqli_stmt_close($updateStmt);
            $error = 'Artikel gagal diperbarui.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Artikel</title>
    <link rel="stylesheet" href="/PJBL-main/assets/templateHalaman/sidebar/sidebar.css">
    <link rel="stylesheet" href="/PJBL-main/dashboard/dashboardAdmin/dashboardmain/css/dashboard.css">
    <link rel="stylesheet" href="/PJBL-main/dashboard/dashboardAdmin/dashboardartikel/css/edit_artikel.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
</head>
<body>

<?php
    $activePage = 'artikel'; 
    include '../../assets/templateHalaman/sidebar/sidebar.php';
?>

<main class="pb-main-content">
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
                <?php
                $qKat = mysqli_query($koneksi, "SELECT id, nama, slug FROM kategori ORDER BY nama ASC");
                while ($kat = mysqli_fetch_assoc($qKat)):
                    $selected = ($article['kategori_id'] == $kat['id']) ? 'selected' : '';
                    ?>
                    <option value="<?= htmlspecialchars($kat['slug']); ?>" <?= $selected; ?>>
                        <?= htmlspecialchars($kat['nama']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="tanggal">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" value="<?= htmlspecialchars($article['tanggal']); ?>" required>

            <label for="gambar">Ganti Gambar (opsional)</label>
            <div class="file-upload-field">
                <label for="gambar" class="file-upload-label">Pilih Gambar Baru</label>
                <span id="fileName" class="file-upload-name">Gunakan gambar lama jika tidak diganti</span>
                <input type="file" id="gambar" name="gambar" accept="image/*">
            </div>

            <label>Isi Artikel</label>
            <div id="editor" style="height: 300px; margin-bottom: 16px; border-radius: 8px;"></div>
            <input type="hidden" name="isi" id="isi" value="<?= htmlspecialchars($article['isi']); ?>">

            <button type="submit" class="submit-btn">Simpan Perubahan</button>
        </form>
    </main>

<script src="/PJBL-main/halamanWeb/loginpage/js/auth.js"></script>
<script src="/PJBL-main/assets/templateHalaman/sidebar/sidebar.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Perbarui isi artikel di sini...'
        });

        var isiInput = document.getElementById('isi');
        var fileInput = document.getElementById('gambar');
        var fileName = document.getElementById('fileName');

        quill.root.innerHTML = isiInput.value || '';

        fileInput.addEventListener('change', function() {
            fileName.textContent = fileInput.files.length > 0 ? fileInput.files[0].name : 'Gunakan gambar lama jika tidak diganti';
        });

        document.getElementById('articleForm').addEventListener('submit', function(event) {
            var plainText = quill.getText().trim();
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
