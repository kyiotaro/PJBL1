<?php
require_once '../../../config/auth_check.php';
include '../../../koneksi.php';

if (isset($_GET['approve'])) {
    $id = (int) $_GET['approve'];
    $stmt = mysqli_prepare($koneksi, "UPDATE artikel SET status = 'published' WHERE id = ? AND author_type = 'user' AND status = 'pending'");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header('Location: dashboard_inbox.php?status=approved');
    exit;
}

if (isset($_GET['reject'])) {
    $id = (int) $_GET['reject'];
    $stmt = mysqli_prepare($koneksi, "UPDATE artikel SET status = 'rejected' WHERE id = ? AND author_type = 'user' AND status = 'pending'");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header('Location: dashboard_inbox.php?status=rejected');
    exit;
}

if (isset($_GET['allow_edit'])) {
    $id = (int) $_GET['allow_edit'];
    $stmt = mysqli_prepare($koneksi, "UPDATE artikel SET status = 'pending' WHERE id = ? AND author_type = 'user' AND status = 'requested_edit'");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header('Location: dashboard_inbox.php?status=edit_allowed');
    exit;
}

$statusMessage = '';
$status = $_GET['status'] ?? '';

if ($status === 'approved') {
    $statusMessage = 'Artikel kontributor telah disetujui dan dipublikasikan.';
} elseif ($status === 'rejected') {
    $statusMessage = 'Artikel kontributor telah ditolak.';
} elseif ($status === 'edit_allowed') {
    $statusMessage = 'Izin edit diberikan. Penulis dapat memperbarui artikelnya.';
}

$filterType = $_GET['tipe'] ?? 'semua';
$search = trim($_GET['search'] ?? '');

$pendingCount = 0;
$editRequestCount = 0;

$countPending = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM artikel WHERE author_type = 'user' AND status = 'pending'");
if ($countPending && $row = mysqli_fetch_assoc($countPending)) {
    $pendingCount = (int) $row['total'];
}

$countEdit = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM artikel WHERE author_type = 'user' AND status = 'requested_edit'");
if ($countEdit && $row = mysqli_fetch_assoc($countEdit)) {
    $editRequestCount = (int) $row['total'];
}

$whereClauses = ["a.author_type = 'user'", "a.status IN ('pending', 'requested_edit')"];
$params = [];
$types = '';

if ($filterType === 'pending') {
    $whereClauses = ["a.author_type = 'user'", "a.status = 'pending'"];
} elseif ($filterType === 'requested_edit') {
    $whereClauses = ["a.author_type = 'user'", "a.status = 'requested_edit'"];
}

if ($search !== '') {
    $whereClauses[] = '(a.judul LIKE ? OR a.Penulis LIKE ? OR u.email LIKE ? OR u.nama LIKE ?)';
    $searchParam = '%' . $search . '%';
    $params = array_fill(0, 4, $searchParam);
    $types = 'ssss';
}

$sql = "
    SELECT a.id, a.judul, a.tanggal, a.status, a.Penulis,
           k.nama AS kategori, u.email AS user_email, u.nama AS user_nama
    FROM artikel a
    LEFT JOIN kategori k ON k.id = a.kategori_id
    LEFT JOIN users u ON u.id = a.author_id
    WHERE " . implode(' AND ', $whereClauses) . "
    ORDER BY a.tanggal DESC, a.id DESC
";

$items = [];

if ($types !== '') {
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
            $items[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
} else {
    $result = mysqli_query($koneksi, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
    }
}

function inboxRequestLabel(string $status): string
{
    if ($status === 'requested_edit') {
        return 'Permintaan Edit';
    }
    return 'Artikel Baru';
}

function inboxRequestClass(string $status): string
{
    return $status === 'requested_edit' ? 'request-edit' : 'request-new';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox Admin</title>
    <link rel="stylesheet" href="/PJBL-main/assets/templateHalaman/sidebar/sidebar.css">
    <link rel="stylesheet" href="/PJBL-main/dashboard/dashboardAdmin/dashboardmain/css/dashboard.css">
    <link rel="stylesheet" href="/PJBL-main/dashboard/dashboardAdmin/dashboardartikel/css/dashboard_artikel.css?v=2">
    <link rel="stylesheet" href="css/dashboard_inbox.css">
</head>
<body>

<?php
    $activePage = 'inbox';
    include '../../../assets/templateHalaman/sidebar/sidebar.php';
?>

<main class="pb-main-content">
    <h1>Inbox</h1>
    <p class="inbox-subtitle">Konfirmasi artikel baru dan permintaan edit dari kontributor.</p>

    <?php if ($statusMessage !== '') : ?>
        <p class="inbox-flash"><?= htmlspecialchars($statusMessage); ?></p>
    <?php endif; ?>

    <div class="inbox-stats">
        <div class="inbox-stat-card">
            <span class="inbox-stat-value"><?= $pendingCount; ?></span>
            <span class="inbox-stat-label">Menunggu Review</span>
        </div>
        <div class="inbox-stat-card">
            <span class="inbox-stat-value"><?= $editRequestCount; ?></span>
            <span class="inbox-stat-label">Permintaan Edit</span>
        </div>
    </div>

    <div class="dashboard-actions inbox-toolbar">
        <form action="dashboard_inbox.php" method="GET" class="search-form inbox-search-form">
            <input type="text" name="search" placeholder="Cari judul, penulis, atau email..." value="<?= htmlspecialchars($search); ?>">
            <select name="tipe">
                <option value="semua" <?= $filterType === 'semua' ? 'selected' : ''; ?>>Semua Permintaan</option>
                <option value="pending" <?= $filterType === 'pending' ? 'selected' : ''; ?>>Artikel Baru</option>
                <option value="requested_edit" <?= $filterType === 'requested_edit' ? 'selected' : ''; ?>>Permintaan Edit</option>
            </select>
            <button type="submit">Filter</button>
        </form>
    </div>

    <table class="article-table">
        <thead>
            <tr>
                <th><span class="no-sort">ID</span></th>
                <th><span class="no-sort">Tipe</span></th>
                <th><span class="no-sort">Judul</span></th>
                <th><span class="no-sort">Kontributor</span></th>
                <th><span class="no-sort">Kategori</span></th>
                <th><span class="no-sort">Tanggal</span></th>
                <th><span class="no-sort">Aksi</span></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($items)) : ?>
                <?php foreach ($items as $row) : ?>
                    <tr>
                        <td><?= (int) $row['id']; ?></td>
                        <td>
                            <span class="inbox-request-badge <?= inboxRequestClass($row['status']); ?>">
                                <?= inboxRequestLabel($row['status']); ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($row['judul']); ?></td>
                        <td class="contributor-cell">
                            <?= htmlspecialchars($row['Penulis'] ?: ($row['user_nama'] ?? '-')); ?>
                            <?php if (!empty($row['user_email'])) : ?>
                                <small class="contributor-email"><?= htmlspecialchars($row['user_email']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars(ucfirst($row['kategori'] ?? '-')); ?></td>
                        <td><?= htmlspecialchars($row['tanggal']); ?></td>
                        <td class="table-actions">
                            <a href="/PJBL-main/halamanWeb/artikelTemplate/artikel.php?id=<?= (int) $row['id']; ?>" class="action-btn preview" target="_blank" rel="noopener">Lihat</a>
                            <?php if ($row['status'] === 'pending') : ?>
                                <a href="?approve=<?= (int) $row['id']; ?>&amp;tipe=<?= urlencode($filterType); ?>&amp;search=<?= urlencode($search); ?>" class="action-btn approve" onclick="return confirm('Setujui dan publikasikan artikel ini?')">Setujui</a>
                                <a href="?reject=<?= (int) $row['id']; ?>&amp;tipe=<?= urlencode($filterType); ?>&amp;search=<?= urlencode($search); ?>" class="action-btn reject" onclick="return confirm('Tolak artikel ini?')">Tolak</a>
                            <?php elseif ($row['status'] === 'requested_edit') : ?>
                                <a href="?allow_edit=<?= (int) $row['id']; ?>&amp;tipe=<?= urlencode($filterType); ?>&amp;search=<?= urlencode($search); ?>" class="action-btn allow" onclick="return confirm('Izinkan kontributor mengedit artikel ini?')">Izinkan Edit</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="7">Tidak ada permintaan yang perlu dikonfirmasi.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>

<script src="/PJBL-main/halamanWeb/loginpage/js/auth.js"></script>
<script src="/PJBL-main/assets/templateHalaman/sidebar/sidebar.js"></script>

</body>
</html>
