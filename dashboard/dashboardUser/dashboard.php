<?php
session_start();
require_once '../../config/auth_check_user.php';
include '../../koneksi.php';

$userId = (int) ($_SESSION['user_id'] ?? 0);

$totalArtikel = 0;
$menungguReview = 0;
$dipublikasikan = 0;
$ditolak = 0;
$artikelTerbaru = [];

if ($userId > 0) {
    $summaryStmt = mysqli_prepare(
        $koneksi,
        "SELECT 
            COUNT(*) AS total_artikel,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS total_pending,
            SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) AS total_published,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS total_rejected
         FROM artikel
         WHERE author_id = ? AND author_type = 'user'"
    );

    if ($summaryStmt) {
        mysqli_stmt_bind_param($summaryStmt, 'i', $userId);
        mysqli_stmt_execute($summaryStmt);
        $summaryResult = mysqli_stmt_get_result($summaryStmt);

        if ($summaryResult && $row = mysqli_fetch_assoc($summaryResult)) {
            $totalArtikel = (int) ($row['total_artikel'] ?? 0);
            $menungguReview = (int) ($row['total_pending'] ?? 0);
            $dipublikasikan = (int) ($row['total_published'] ?? 0);
            $ditolak = (int) ($row['total_rejected'] ?? 0);
        }

        mysqli_stmt_close($summaryStmt);
    }

    $latestStmt = mysqli_prepare(
        $koneksi,
        "SELECT judul, tanggal, status
         FROM artikel
         WHERE author_id = ? AND author_type = 'user'
         ORDER BY tanggal DESC, id DESC
         LIMIT 5"
    );

    if ($latestStmt) {
        mysqli_stmt_bind_param($latestStmt, 'i', $userId);
        mysqli_stmt_execute($latestStmt);
        $latestResult = mysqli_stmt_get_result($latestStmt);

        while ($latestResult && $row = mysqli_fetch_assoc($latestResult)) {
            $artikelTerbaru[] = $row;
        }

        mysqli_stmt_close($latestStmt);
    }
}

function formatStatusUser($status)
{
    switch ($status) {
        case 'published':
            return 'Disetujui';
        case 'pending':
            return 'Menunggu';
        case 'rejected':
            return 'Ditolak';
        case 'requested_edit':
            return 'Minta Edit';
        default:
            return ucfirst((string) $status);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User</title>
    <link rel="stylesheet" href="/PJBL-main/assets/templateHalaman/sidebar/sidebar.css">
    <link rel="stylesheet" href="/PJBL-main/assets/templateHalaman/statCard/statCard.css">
    <link rel="stylesheet" href="/PJBL-main/dashboard/dashboardAdmin/dashboardmain/css/dashboard_admin.css">
</head>
<body>

<?php
    $activePage = 'dashboard';
    include '../../assets/templateHalaman/sidebar/sidebar.php';
?>

<main class="pb-main-content">
    <h1 class="page-title">Dashboard User</h1>

    <div class="stats-grid">
        <?php
        $statValue = $totalArtikel;
        $statLabel = 'Total Artikel Saya';
        $statIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>';
        include '../../assets/templateHalaman/statCard/statCard.php';

        $statValue = $menungguReview;
        $statLabel = 'Menunggu Review';
        $statIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';
        include '../../assets/templateHalaman/statCard/statCard.php';

        $statValue = $dipublikasikan;
        $statLabel = 'Disetujui';
        $statIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
        include '../../assets/templateHalaman/statCard/statCard.php';

        $statValue = $ditolak;
        $statLabel = 'Ditolak';
        $statIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
        include '../../assets/templateHalaman/statCard/statCard.php';
        ?>
    </div>

    <div class="card">
        <h3>Artikel Terbaru Saya</h3>

        <?php if (!empty($artikelTerbaru)) : ?>
            <?php foreach ($artikelTerbaru as $artikel) : ?>
                <div class="article-item">
                    <div class="article-info">
                        <h4><?= htmlspecialchars($artikel['judul']); ?></h4>
                        <p class="meta">
                            <?= date('d M Y', strtotime($artikel['tanggal'])); ?> - <?= htmlspecialchars(formatStatusUser($artikel['status'])); ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p class="meta">Belum ada artikel yang dibuat.</p>
        <?php endif; ?>
    </div>
</main>

<script>
  (function() {
    const theme = localStorage.getItem('theme');
    if (theme === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
    }
  })();
</script>
<script src="/PJBL-main/halamanWeb/loginpage/js/auth.js"></script>
<script src="/PJBL-main/assets/templateHalaman/sidebar/sidebar.js"></script>
</body>
</html>
