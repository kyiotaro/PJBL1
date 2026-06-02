<?php
session_start();
require_once '../../config/auth_check_user.php'; // New auth check for users
include '../../koneksi.php';

$userId = $_SESSION['user_id'];

$statusMessage = '';
$status = $_GET['status'] ?? '';

if ($status === 'created') {
    $statusMessage = 'Artikel berhasil diajukan dan menunggu konfirmasi admin.';
} elseif ($status === 'updated') {
    $statusMessage = 'Artikel berhasil diperbarui.';
} elseif ($status === 'deleted') {
    $statusMessage = 'Artikel berhasil dihapus.';
} elseif ($status === 'requested') {
    $statusMessage = 'Permintaan edit telah dikirim ke admin.';
}

$articles = [];
$categories = [];
$search = trim($_GET['search'] ?? '');
$categoryId = (int) ($_GET['kategori'] ?? 0);

$categoryQuery = mysqli_query($koneksi, "SELECT id, nama FROM kategori ORDER BY nama ASC");
if ($categoryQuery) {
    while ($row = mysqli_fetch_assoc($categoryQuery)) {
        $categories[] = $row;
    }
}

$whereClauses = ["a.author_id = ? AND a.author_type = 'user'"];
$params = [$userId];
$types = 'i';

if ($search !== '') {
    $whereClauses[] = "(a.judul LIKE ?)";
    $searchParam = '%' . $search . '%';
    $params[] = $searchParam;
    $types .= 's';
}

if ($categoryId > 0) {
    $whereClauses[] = 'a.kategori_id = ?';
    $params[] = $categoryId;
    $types .= 'i';
}

$sql = "
    SELECT a.id, a.judul, a.tanggal, a.status, k.nama AS kategori
    FROM artikel a
    LEFT JOIN kategori k ON k.id = a.kategori_id
    WHERE " . implode(' AND ', $whereClauses) . "
    ORDER BY a.tanggal DESC, a.id DESC
";

$stmt = mysqli_prepare($koneksi, $sql);
if ($stmt) {
    $bindParams = [];
    $bindParams[] = &$types;
    foreach ($params as $key => $value) {
        $bindParams[] = &$params[$key];
    }
    call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt], $bindParams));

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $articles[] = $row;
    }
    mysqli_stmt_close($stmt);
}

function getStatusBadgeClass($status) {
    switch ($status) {
        case 'published': return 'badge-success';
        case 'pending': return 'badge-warning';
        case 'rejected': return 'badge-danger';
        case 'requested_edit': return 'badge-info';
        default: return 'badge-secondary';
    }
}

function getStatusLabel($status) {
    switch ($status) {
        case 'published': return 'Disetujui';
        case 'pending': return 'Menunggu';
        case 'rejected': return 'Ditolak';
        case 'requested_edit': return 'Minta Edit';
        default: return $status;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artikel Saya</title>
    <link rel="stylesheet" href="/PJBL-main/assets/templateHalaman/sidebar/sidebar.css">
    <link rel="stylesheet" href="/PJBL-main/dashboard/dashboardAdmin/dashboardmain/css/dashboard.css">
    <link rel="stylesheet" href="/PJBL-main/dashboard/dashboardAdmin/dashboardartikel/css/dashboard_artikel.css">
    <style>
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            color: white;
        }
        .badge-success { background: #10B981; }
        .badge-warning { background: #F59E0B; }
        .badge-danger { background: #EF4444; }
        .badge-info { background: #3B82F6; }
        .badge-secondary { background: #6B7280; }
        
        .action-btn.disabled {
            background: #94A3B8;
            cursor: not-allowed;
            pointer-events: none;
        }
        .request-edit { background: #6366F1; }
    </style>
</head>
<body>

<?php
    $activePage = 'artikel'; 
    include '../../assets/templateHalaman/sidebar/sidebar.php';
?>

<main class="pb-main-content">
        <h1>Artikel Saya</h1>

        <?php if ($statusMessage !== '') : ?>
            <p style="margin-bottom: 16px; color: #0369A1; font-weight: 600;"><?= htmlspecialchars($statusMessage); ?></p>
        <?php endif; ?>

        <div class="dashboard-actions">
            <a href="tambah_artikel.php" class="add-btn">+ Tambah Artikel</a>
            <form action="" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Cari artikel..." value="<?= htmlspecialchars($search); ?>">
                <select name="kategori">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $cat) : ?>
                        <option value="<?= (int) $cat['id']; ?>" <?= $categoryId === (int) $cat['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($cat['nama']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Filter</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Judul Artikel</th>
                    <th>Kategori</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($articles)) : ?>
                    <?php foreach ($articles as $row) : ?>
                        <tr>
                            <td><?= (int) $row['id']; ?></td>
                            <td><?= htmlspecialchars($row['judul']); ?></td>
                            <td><?= htmlspecialchars(ucfirst($row['kategori'])); ?></td>
                            <td><?= htmlspecialchars($row['tanggal']); ?></td>
                            <td>
                                <span class="badge <?= getStatusBadgeClass($row['status']); ?>">
                                    <?= getStatusLabel($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['status'] === 'pending' || $row['status'] === 'rejected') : ?>
                                    <a href="edit_artikel.php?id=<?= (int) $row['id']; ?>" class="action-btn edit">Edit</a>
                                    <a href="hapus_artikel.php?id=<?= (int) $row['id']; ?>" class="action-btn delete" onclick="return confirm('Yakin hapus artikel ini?')">Hapus</a>
                                <?php elseif ($row['status'] === 'published') : ?>
                                    <a href="request_edit.php?id=<?= (int) $row['id']; ?>" class="action-btn request-edit" onclick="return confirm('Minta izin ke admin untuk mengedit artikel ini?')">Minta Edit</a>
                                <?php elseif ($row['status'] === 'requested_edit') : ?>
                                    <span class="action-btn disabled">Menunggu Izin</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6">Anda belum menulis artikel.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

<script src="/PJBL-main/halamanWeb/loginpage/js/auth.js"></script>
<script src="/PJBL-main/assets/templateHalaman/sidebar/sidebar.js"></script>

</body>
</html>
