<?php
require_once __DIR__ . '/../../../helpers/foto_helper.php';
$cardImagePath = resolveFotoWebPath($artikel['gambar'] ?? '');
$kategoriLabel = ucfirst($artikel['kategori'] ?? '');
$tanggalFmt    = !empty($artikel['tanggal'])
    ? date('d M Y', strtotime($artikel['tanggal']))
    : '';
?>
<a class="card2"
   href="../artikelTemplate/artikel.php?id=<?php echo (int) $artikel['id']; ?>"
   data-category="<?php echo htmlspecialchars($artikel['kategori'] ?? ''); ?>">

  <div class="card2-image">
    <img
      src="<?php echo htmlspecialchars($cardImagePath); ?>"
      alt="<?php echo htmlspecialchars($artikel['judul'] ?? ''); ?>"
      loading="lazy"
    >
    <?php if ($kategoriLabel): ?>
      <span class="card2-badge"><?php echo htmlspecialchars($kategoriLabel); ?></span>
    <?php endif; ?>
  </div>

  <div class="card2-content">
    <h2><?php echo htmlspecialchars($artikel['judul'] ?? ''); ?></h2>

    <?php if (!empty($artikel['isi'])): ?>
      <p><?php echo htmlspecialchars(strip_tags($artikel['isi']), ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <div class="card2-meta">
      <?php if ($tanggalFmt): ?>
        <span class="card2-date"><?php echo $tanggalFmt; ?></span>
      <?php endif; ?>
      <?php if ($kategoriLabel): ?>
        <span class="card2-category"><?php echo htmlspecialchars($kategoriLabel); ?></span>
      <?php endif; ?>
    </div>

    <span class="card2-button">Baca selengkapnya →</span>
  </div>

</a>