<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../../koneksi.php';
require_once '../../assets/helpers/favorit_helper.php';

$actor = favoritGetActor();
$isLoggedIn = $actor !== null;
$favoriteArticles = $isLoggedIn ? favoritGetArticles($koneksi, $actor) : [];
// #region agent log
file_put_contents(dirname(__DIR__, 2) . '/debug-34f9b0.log', json_encode([
    'sessionId' => '34f9b0',
    'hypothesisId' => 'B,G',
    'location' => 'favorit.php:load',
    'message' => 'favorit page load',
    'data' => [
        'isLoggedIn' => $isLoggedIn,
        'actorType' => $actor['type'] ?? null,
        'actorId' => $actor['id'] ?? null,
        'favoriteCount' => count($favoriteArticles),
    ],
    'timestamp' => (int) round(microtime(true) * 1000),
], JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
// #endregion
$loginUrl = '/PJBL-main/halamanWeb/loginpage/signin.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Favorit | Permata Biru Nusantara</title>
  <link rel="stylesheet" href="css/favorit.css">
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
  $heroImage = '../../assets/Foto/ui/background.png';
  $heroTitle = 'Artikel Favorit';
  $heroSubtitle = 'Kumpulan artikel yang Anda sukai — tersimpan di satu tempat untuk dibaca kembali kapan saja.';
  include '../../assets/templateHalaman/hero/hero.php';
  ?>

  <main class="favorit-main">
    <?php if (!$isLoggedIn): ?>
      <div class="favorit-empty favorit-empty--login">
        <div class="favorit-empty-icon" aria-hidden="true">♥</div>
        <h2>Masuk untuk melihat favorit</h2>
        <p>Login sebagai pengguna, lalu tekan tombol suka pada artikel untuk menyimpannya di halaman ini.</p>
        <a href="<?= htmlspecialchars($loginUrl) ?>" class="favorit-login-btn">Login sekarang</a>
      </div>
    <?php elseif (empty($favoriteArticles)): ?>
      <div class="favorit-empty">
        <div class="favorit-empty-icon" aria-hidden="true">♡</div>
        <h2>Belum ada artikel favorit</h2>
        <p>Jelajahi artikel di beranda, lalu ketuk ikon hati untuk menambahkannya ke daftar favorit Anda.</p>
        <a href="/PJBL-main/halamanWeb/landingpage/landingpage.php" class="favorit-login-btn">Jelajahi artikel</a>
      </div>
    <?php else: ?>
      <?php
      $sectionTitle = 'Disukai (' . count($favoriteArticles) . ')';
      $sectionSubtitle = 'Artikel yang pernah Anda beri tanda suka.';
      include '../../assets/templateHalaman/sectionHeader/sectionHeader.php';
      ?>
      <div class="favorit-grid grid">
        <?php foreach ($favoriteArticles as $artikel): ?>
          <div class="favorit-card-wrap">
            <?php include '../../assets/templateHalaman/cardVariant/card1/card1.php'; ?>
            <button
              type="button"
              class="like-btn is-liked favorit-remove-btn"
              data-artikel-id="<?= (int) $artikel['id'] ?>"
              data-toggle-url="/PJBL-main/halamanWeb/Favorit/toggle_favorit.php"
              aria-label="Hapus dari favorit"
              title="Hapus dari favorit">
              <svg class="like-icon" width="22" height="22" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
              </svg>
            </button>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

  <?php include '../../assets/templateHalaman/footer/footer.php'; ?>

  <?php if ($isLoggedIn && !empty($favoriteArticles)): ?>
    <script src="js/like.js"></script>
    <script>
      document.querySelectorAll('.favorit-remove-btn').forEach(function(btn) {
        btn.addEventListener('like:toggled', function(e) {
          if (!e.detail.liked) {
            var wrap = btn.closest('.favorit-card-wrap');
            if (wrap) {
              wrap.remove();
            }
            if (!document.querySelector('.favorit-card-wrap')) {
              location.reload();
            }
          }
        });
      });
    </script>
  <?php endif; ?>
</body>

</html>
