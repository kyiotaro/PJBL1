<?php
require_once __DIR__ . '/../helpers/foto_helper.php';
$cardImagePath = resolveFotoWebPath($artikel['gambar'] ?? '');
?>
<article data-category="<?php echo $artikel['kategori']; ?>">
  <img src="<?php echo htmlspecialchars($cardImagePath); ?>" alt="<?php echo $artikel['judul']; ?>">
  <h4><?php echo $artikel['judul']; ?></h4>
  <p class="date"><?php echo date('d.m.Y', strtotime($artikel['tanggal'])); ?></p>
  <a href="/PJBL-main/halamanWeb/artikelTemplate/artikel.php?id=<?php echo $artikel['id']; ?>">Baca selengkapnya →</a>
</article>