<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../../koneksi.php';
require_once '../../assets/helpers/foto_helper.php';
require_once '../../assets/helpers/favorit_helper.php';

$defaultArticle = [
  'id' => 0,
  'judul' => 'Eksplorasi Teluk Tomoni: Keindahan yang Tersembunyi',
  'kategori' => 'wisata',
  'status' => 'published',
  'tanggal' => date('Y-m-d'),
  'gambar' => 'kima.png',
  'isi' => '<p>Di balik birunya laut tropis Indonesia, ada satu biota yang sering bikin penyelam terpesona: kima raksasa (Tridacna gigas). Hewan ini bukan sekadar kerang biasa. Ia adalah salah satu moluska terbesar di dunia, dengan ukuran bisa mencapai lebih dari satu meter dan berat ratusan kilogram.</p><p>Kima raksasa punya keistimewaan unik. Warna tubuhnya sering terlihat berkilau kehijauan, kebiruan, atau bahkan keemasan. Hubungan simbiosis dengan alga mikroskopis membuatnya mampu memanfaatkan cahaya matahari untuk bertahan hidup dalam waktu yang sangat lama.</p><p>Keberadaan kima raksasa di perairan Indonesia punya fungsi penting bagi ekosistem terumbu karang. Menjaganya berarti menjaga kesehatan laut sekaligus merawat warisan alam Indonesia untuk generasi mendatang.</p>'
];

$articleId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$article = null;

$isAdmin = !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$currentUserId = $_SESSION['user_id'] ?? 0;

if ($articleId > 0) {
  // Allow admin to see anything, allow user to see published or their own pending/rejected.
  // Some environments may not have author/status columns yet, so we keep a safe fallback query.
  $query = "
    SELECT a.*, k.nama AS kategori, k.id AS kategori_id
    FROM artikel a
    LEFT JOIN kategori k ON k.id = a.kategori_id
    WHERE a.id = ?
  ";

  if (!$isAdmin) {
      $query .= " AND (a.status = 'published' OR (a.author_id = ? AND a.author_type = 'user'))";
  }

  $stmt = mysqli_prepare($koneksi, $query);

  if ($stmt) {
    if ($isAdmin) {
      mysqli_stmt_bind_param($stmt, 'i', $articleId);
    } else {
      mysqli_stmt_bind_param($stmt, 'ii', $articleId, $currentUserId);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $article = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
  } else {
    // Fallback for older schema: load by id only for admins, or only published for non-admins.
    $fallbackQuery = "
      SELECT a.*, k.nama AS kategori, k.id AS kategori_id
      FROM artikel a
      LEFT JOIN kategori k ON k.id = a.kategori_id
      WHERE a.id = " . (int) $articleId;

    if (!$isAdmin) {
      $fallbackQuery .= " AND a.status = 'published'";
    }

    $fallbackResult = mysqli_query($koneksi, $fallbackQuery);
    if ($fallbackResult) {
      $article = mysqli_fetch_assoc($fallbackResult);
    }
  }
}

if (!$article) {
  $latestQuery = mysqli_query($koneksi, "
    SELECT a.*, k.nama AS kategori, k.id AS kategori_id 
    FROM artikel a
    LEFT JOIN kategori k ON k.id = a.kategori_id
    WHERE a.status = 'published'
    ORDER BY a.tanggal DESC, a.id DESC LIMIT 1
  ");
  if ($latestQuery && mysqli_num_rows($latestQuery) > 0) {
    $article = mysqli_fetch_assoc($latestQuery);
  }
}

if (!$article) {
  $article = $defaultArticle;
}

$article['gambar'] = !empty($article['gambar']) ? $article['gambar'] : $defaultArticle['gambar'];
$article['status'] = $article['status'] ?? 'published';
$articleImagePath = resolveFotoWebPath($article['gambar']);
$articleContent = trim($article['isi'] ?? '');

if ($articleContent === '') {
  $articleContent = $defaultArticle['isi'];
} elseif ($articleContent === strip_tags($articleContent)) {
  $articleContent = '<p>' . nl2br(htmlspecialchars($articleContent)) . '</p>';
}

$relatedArticles = [];
$currentArticleId = (int) ($article['id'] ?? 0);
$currentCategoryId = (int) ($article['kategori_id'] ?? 0);
$favoritActor = favoritGetActor();
$articleIsLiked = $favoritActor && $currentArticleId > 0
  ? favoritIsLiked($koneksi, $currentArticleId, $favoritActor)
  : false;

if ($currentArticleId > 0 && $currentCategoryId > 0) {
  $relatedStmt = mysqli_prepare($koneksi, "
    SELECT a.id, a.judul, k.nama AS kategori, a.tanggal, a.gambar 
    FROM artikel a
    LEFT JOIN kategori k ON k.id = a.kategori_id
    WHERE a.id != ? AND a.kategori_id = ? AND a.status = 'published'
    ORDER BY a.tanggal DESC, a.id DESC LIMIT 9
  ");
  if ($relatedStmt) {
    mysqli_stmt_bind_param($relatedStmt, 'ii', $currentArticleId, $currentCategoryId);
    mysqli_stmt_execute($relatedStmt);
    $relatedResult = mysqli_stmt_get_result($relatedStmt);

    while ($relatedRow = mysqli_fetch_assoc($relatedResult)) {
      $relatedArticles[] = $relatedRow;
    }

    mysqli_stmt_close($relatedStmt);
  }
}

if ($currentArticleId > 0 && count($relatedArticles) < 9) {
  $existingIds = array_merge([$currentArticleId], array_column($relatedArticles, 'id'));
  $excludeIn = implode(',', array_map('intval', $existingIds));
  $remainingLimit = 9 - count($relatedArticles);

  $fallbackQuery = "
    SELECT a.id, a.judul, k.nama AS kategori, a.tanggal, a.gambar 
    FROM artikel a
    LEFT JOIN kategori k ON k.id = a.kategori_id
    WHERE a.id NOT IN ($excludeIn) AND a.status = 'published'
    ORDER BY a.tanggal DESC, a.id DESC LIMIT $remainingLimit
  ";
  $fallbackResult = mysqli_query($koneksi, $fallbackQuery);

  while ($fallbackRow = mysqli_fetch_assoc($fallbackResult)) {
    $relatedArticles[] = $fallbackRow;
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($article['judul']); ?> | Permata Biru Nusantara</title>
  <link rel="stylesheet" href="css/artikel.css?v=2">
  <link rel="stylesheet" href="../../assets/templateHalaman/navbar/navbar.css">
  <link rel="stylesheet" href="../../assets/templateHalaman/footer/footer.css">
  <link rel="stylesheet" href="../../assets/templateHalaman/cardVariant/card1/card1.css">
  <link rel="stylesheet" href="../../assets/templateHalaman/hero/hero.css">
  <link rel="stylesheet" href="../../assets/templateHalaman/sectionHeader/sectionHeader.css">
  <link rel="stylesheet" href="../../assets/templateHalaman/likeBtn/likeBtn.css">
</head>

<body>
  <?php include '../../assets/templateHalaman/navbar/navbar.php'; ?>

  <?php
  $heroImage = $articleImagePath;
  $heroTitle = $article['judul'];
  $heroSubtitle = 'Jelajahi cerita, pengetahuan, dan pesona bahari Indonesia dalam satu halaman yang lebih nyaman dibaca.';
  include '../../assets/templateHalaman/hero/hero.php';
  ?>

  <main class="content-wrapper">
    <article class="article-card-detail">
      <div class="article-head">
        <h2>Tentang Artikel Ini</h2>
        <p>Informasi dirangkum untuk memperluas wawasan tentang kekayaan laut Indonesia.</p>
        <?php if (($article['status'] ?? 'published') !== 'published') : ?>
            <div style="background: #FEF3C7; color: #92400E; padding: 12px; border-radius: 8px; margin-top: 10px; font-weight: 600;">
                ⚠️ Artikel ini berstatus: <strong><?= htmlspecialchars($article['status'] ?? 'draft'); ?></strong>. Hanya Anda dan Admin yang bisa melihat pratinjau ini.
            </div>
        <?php endif; ?>
      </div>

      <div class="article article-body">
        <?= $articleContent; ?>
      </div>
    </article>

    <aside class="side-panel">
      <div class="side-card">
        <img src="<?= htmlspecialchars($articleImagePath); ?>" alt="<?= htmlspecialchars($article['judul']); ?>">
        <div class="side-card-body">
          <h3><?= htmlspecialchars($article['judul']); ?></h3>
          <p>Kategori: <strong><?= htmlspecialchars(ucfirst($article['kategori'])); ?></strong></p>
          <p>Diterbitkan: <strong><?= date('d M Y', strtotime($article['tanggal'])); ?></strong></p>
          <p>Penulis: <strong><?= htmlspecialchars($article['Penulis'] ?? 'Admin'); ?></strong></p>
          <?php if ($currentArticleId > 0): ?>
            <div class="article-like-row">
              <button
                type="button"
                class="like-btn like-btn--inline<?= $articleIsLiked ? ' is-liked' : '' ?>"
                data-artikel-id="<?= (int) $currentArticleId ?>"
                data-toggle-url="/PJBL-main/halamanWeb/Favorit/toggle_favorit.php"
                aria-pressed="<?= $articleIsLiked ? 'true' : 'false' ?>"
                aria-label="<?= $articleIsLiked ? 'Hapus dari favorit' : 'Tambah ke favorit' ?>"
                title="<?= $articleIsLiked ? 'Hapus dari favorit' : 'Tambah ke favorit' ?>">
                <svg class="like-icon" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
                  <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>
                <span class="like-label"><?= $articleIsLiked ? 'Disukai' : 'Suka' ?></span>
              </button>
              <span class="article-like-hint">
                <?php if ($favoritActor): ?>
                  Artikel disukai akan muncul di <a href="/PJBL-main/halamanWeb/Favorit/favorit.php">halaman Favorit</a>.
                <?php else: ?>
                  <a href="/PJBL-main/halamanWeb/loginpage/signin.php">Login</a> untuk menyimpan artikel ke favorit.
                <?php endif; ?>
              </span>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </aside>

  </main>
  
  <section class="others">
    <?php
    $sectionTitle = 'Artikel lainnya';
    $sectionSubtitle = 'Temukan cerita laut Indonesia lainnya.';
    include '../../assets/templateHalaman/sectionHeader/sectionHeader.php';
    ?>

    <div class="related-grid">
      <?php if (!empty($relatedArticles)): ?>
        <?php foreach ($relatedArticles as $artikel): ?>
          <?php include '../../assets/templateHalaman/cardVariant/card1/card1.php'; ?>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-related">
          <p>Belum ada artikel lain yang tersedia saat ini.</p>
        </div>
      <?php endif; ?>
    </div>
  </section>


  <?php include '../../assets/templateHalaman/footer/footer.php'; ?>

  <?php if ($currentArticleId > 0): ?>
    <script src="../Favorit/js/like.js"></script>
    <script>
      document.querySelector('.article-like-row .like-btn')?.addEventListener('like:toggled', function (e) {
        var label = this.querySelector('.like-label');
        if (label) {
          label.textContent = e.detail.liked ? 'Disukai' : 'Suka';
        }
      });
    </script>
  <?php endif; ?>
</body>

</html>