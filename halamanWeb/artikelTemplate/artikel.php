<?php
include '../../koneksi.php';
require_once '../../assets/helpers/foto_helper.php';

$defaultArticle = [
  'id' => 0,
  'judul' => 'Eksplorasi Teluk Tomoni: Keindahan yang Tersembunyi',
  'kategori' => 'wisata',
  'tanggal' => date('Y-m-d'),
  'gambar' => 'kima.png',
  'isi' => '<p>Di balik birunya laut tropis Indonesia, ada satu biota yang sering bikin penyelam terpesona: kima raksasa (Tridacna gigas). Hewan ini bukan sekadar kerang biasa. Ia adalah salah satu moluska terbesar di dunia, dengan ukuran bisa mencapai lebih dari satu meter dan berat ratusan kilogram.</p><p>Kima raksasa punya keistimewaan unik. Warna tubuhnya sering terlihat berkilau kehijauan, kebiruan, atau bahkan keemasan. Hubungan simbiosis dengan alga mikroskopis membuatnya mampu memanfaatkan cahaya matahari untuk bertahan hidup dalam waktu yang sangat lama.</p><p>Keberadaan kima raksasa di perairan Indonesia punya fungsi penting bagi ekosistem terumbu karang. Menjaganya berarti menjaga kesehatan laut sekaligus merawat warisan alam Indonesia untuk generasi mendatang.</p>'
];

$articleId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$article = null;

if ($articleId > 0) {
  $stmt = mysqli_prepare($koneksi, "SELECT id, judul, kategori, tanggal, gambar, isi FROM Artikel WHERE id = ? LIMIT 1");
  mysqli_stmt_bind_param($stmt, 'i', $articleId);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $article = mysqli_fetch_assoc($result);
  mysqli_stmt_close($stmt);
}

if (!$article) {
  $latestQuery = mysqli_query($koneksi, "SELECT id, judul, kategori, tanggal, gambar, isi FROM Artikel ORDER BY tanggal DESC, id DESC LIMIT 1");
  if ($latestQuery && mysqli_num_rows($latestQuery) > 0) {
    $article = mysqli_fetch_assoc($latestQuery);
  }
}

if (!$article) {
  $article = $defaultArticle;
}

$article['gambar'] = !empty($article['gambar']) ? $article['gambar'] : $defaultArticle['gambar'];
$articleImagePath = resolveFotoWebPath($article['gambar']);
$articleContent = trim($article['isi'] ?? '');

if ($articleContent === '') {
  $articleContent = $defaultArticle['isi'];
} elseif ($articleContent === strip_tags($articleContent)) {
  $articleContent = '<p>' . nl2br(htmlspecialchars($articleContent)) . '</p>';
}

$relatedArticles = [];
$currentArticleId = (int) ($article['id'] ?? 0);

if ($currentArticleId > 0) {
  $relatedStmt = mysqli_prepare($koneksi, "SELECT id, judul, kategori, tanggal, gambar FROM Artikel WHERE id != ? ORDER BY tanggal DESC, id DESC LIMIT 4");
  mysqli_stmt_bind_param($relatedStmt, 'i', $currentArticleId);
  mysqli_stmt_execute($relatedStmt);
  $relatedResult = mysqli_stmt_get_result($relatedStmt);

  while ($relatedRow = mysqli_fetch_assoc($relatedResult)) {
    $relatedArticles[] = $relatedRow;
  }

  mysqli_stmt_close($relatedStmt);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($article['judul']); ?> | Permata Biru Nusantara</title>
  <link rel="stylesheet" href="artikel.css?v=2">
  <link rel="stylesheet" href="../../assets/variables.css">
  <link rel="stylesheet" href="../../assets/templateHalaman/navbar.css">
  <link rel="stylesheet" href="../../assets/templateHalaman/footer.css">
  <link rel="stylesheet" href="../../assets/templateHalaman/card.css">
</head>
<body>
  <?php include '../../assets/templateHalaman/navbar.php'; ?>

  <section class="hero">
    <img src="<?= htmlspecialchars($articleImagePath); ?>" alt="<?= htmlspecialchars($article['judul']); ?>" class="hero-img">
    <div class="hero-content">
      <h1><?= htmlspecialchars($article['judul']); ?></h1>
      <p>Jelajahi cerita, pengetahuan, dan pesona bahari Indonesia dalam satu halaman yang lebih nyaman dibaca.</p>
    </div>
  </section>

  <main class="content-wrapper">
    <article class="article-card-detail">
      <div class="article-head">
        <h2>Tentang Artikel Ini</h2>
        <p>Informasi dirangkum untuk memperluas wawasan tentang kekayaan laut Indonesia.</p>
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
        </div>
      </div>
    </aside>
  </main>

  <section class="others">
    <div class="section-heading">
      <h2>Artikel lainnya</h2>
      <p>Temukan cerita laut Indonesia lainnya dengan tampilan kartu yang lebih rapi dan konsisten.</p>
    </div>

    <div class="related-grid">
      <?php if (!empty($relatedArticles)) : ?>
        <?php foreach ($relatedArticles as $artikel) : ?>
          <?php include '../../assets/templateHalaman/card.php'; ?>
        <?php endforeach; ?>
      <?php else : ?>
        <div class="empty-related">
          <p>Belum ada artikel lain yang tersedia saat ini.</p>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <?php include '../../assets/templateHalaman/footer.php'; ?>
</body>
</html>
