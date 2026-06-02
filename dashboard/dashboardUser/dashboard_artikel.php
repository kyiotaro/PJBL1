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
$tanggal = trim($_GET['tanggal'] ?? '');
$filterStatus = $_GET['filter_status'] ?? '';

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

if ($filterStatus !== '') {
    $whereClauses[] = 'a.status = ?';
    $params[] = $filterStatus;
    $types .= 's';
}

if ($tanggal !== '') {
    $whereClauses[] = 'a.tanggal = ?';
    $params[] = $tanggal;
    $types .= 's';
}

$sql = "
    SELECT a.id, a.judul, a.tanggal, a.status, a.Penulis, a.author_type, k.nama AS kategori
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
    <link rel="stylesheet" href="/PJBL-main/dashboard/dashboardAdmin/dashboardartikel/css/dashboard_artikel.css?v=2">
    <style>
        .search-form { grid-template-columns: 1fr 220px 180px 180px auto !important; }
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
                <select name="filter_status">
                    <option value="">Semua Status</option>
                    <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                    <option value="published" <?= $filterStatus === 'published' ? 'selected' : ''; ?>>Disetujui</option>
                    <option value="rejected" <?= $filterStatus === 'rejected' ? 'selected' : ''; ?>>Ditolak</option>
                    <option value="requested_edit" <?= $filterStatus === 'requested_edit' ? 'selected' : ''; ?>>Minta Edit</option>
                </select>
                <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal); ?>">
                <button type="submit">Filter</button>
            </form>
        </div>

        <table class="article-table">
            <thead>
                <tr>
                    <th><span class="no-sort">ID</span></th>
                    <th><span class="no-sort">Judul Artikel</span></th>
                    <th><span class="no-sort">Penulis</span></th>
                    <th><span class="no-sort">Kategori</span></th>
                    <th><span class="no-sort">Tanggal</span></th>
                    <th><span class="no-sort">Status</span></th>
                    <th><span class="no-sort">Aksi</span></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($articles)) : ?>
                    <?php foreach ($articles as $row) : ?>
                        <tr>
                            <td><?= (int) $row['id']; ?></td>
                            <td><?= htmlspecialchars($row['judul']); ?></td>
                            <td><?= htmlspecialchars($row['Penulis']); ?> (<?= ucfirst($row['author_type']); ?>)</td>
                            <td><?= htmlspecialchars(ucfirst($row['kategori'])); ?></td>
                            <td><?= htmlspecialchars($row['tanggal']); ?></td>
                            <td>
                                <span class="badge <?= getStatusBadgeClass($row['status']); ?>">
                                    <?= getStatusLabel($row['status']); ?>
                                </span>
                            </td>
                            <td class="table-actions">
                                <?php if ($row['status'] === 'pending' || $row['status'] === 'rejected') : ?>
                                    <a href="edit_artikel.php?id=<?= (int) $row['id']; ?>" class="action-btn edit">Edit</a>
                                    <a href="hapus_artikel.php?id=<?= (int) $row['id']; ?>" class="action-btn delete" onclick="return confirm('Yakin hapus artikel ini?')">Hapus</a>
                                <?php elseif ($row['status'] === 'published') : ?>
                                    <a href="request_edit.php?id=<?= (int) $row['id']; ?>" class="action-btn allow" onclick="return confirm('Minta izin ke admin untuk mengedit artikel ini?')">Minta Edit</a>
                                <?php elseif ($row['status'] === 'requested_edit') : ?>
                                    <span class="action-btn disabled">Menunggu Izin</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7">Anda belum menulis artikel.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

<script src="/PJBL-main/halamanWeb/loginpage/js/auth.js"></script>
<script src="/PJBL-main/assets/templateHalaman/sidebar/sidebar.js"></script>

</body>
</html>
