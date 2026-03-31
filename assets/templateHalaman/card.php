<article data-category="<?php echo $artikel['kategori']; ?>">
  <img src="/PJBL-main/assets/Foto/<?php echo $artikel['gambar']; ?>" alt="<?php echo $artikel['judul']; ?>">
  <h4><?php echo $artikel['judul']; ?></h4>
  <p class="date"><?php echo date('d.m.Y', strtotime($artikel['tanggal'])); ?></p>
  <a href="/PJBL-main/artikelTemplate/artikel.php?id=<?php echo $artikel['id']; ?>">Baca selengkapnya →</a>
</article>