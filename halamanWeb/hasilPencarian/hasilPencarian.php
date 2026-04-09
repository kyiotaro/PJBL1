<?php 
include '../../koneksi.php';
$search_input = isset($_GET['query']) ? mysqli_real_escape_string($koneksi, $_GET['query']) : ''; 
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hasil Pencarian: <?= htmlspecialchars($search_input) ?></title>
  <link rel="stylesheet" href="hasilPencarian.css">
  <link rel="stylesheet" href="../../assets/templateHalaman/navbar.css">
  <link rel="stylesheet" href="../../assets/templateHalaman/footer.css">
  <link rel="stylesheet" href="../../assets/templateHalaman/cardVariant/card2/card2.css">
</head>

<body>
  <?php include '../../assets/templateHalaman/navbar.php'; ?>

  <main>
    <div class="card2-list">
      <?php
      if ($search_input !== '') {
        $query = mysqli_query($koneksi, "
          SELECT a.*, k.nama AS kategori 
          FROM Artikel a 
          LEFT JOIN kategori k ON k.id = a.kategori_id 
          WHERE a.judul LIKE '%$search_input%' OR a.isi LIKE '%$search_input%'
        ");
        if ($query && mysqli_num_rows($query) > 0) {
          while ($artikel = mysqli_fetch_assoc($query)) {
            include '../../assets/templateHalaman/cardVariant/card2/card2.php';
          }
        } else {
          echo "<p>Tidak ada hasil yang ditemukan untuk \"$search_input\".</p>";
        }
      } else {
        echo "<p>Masukkan kata kunci untuk mencari artikel.</p>";
      }
      ?>
    </div>
  </main>

  <?php include '../../assets/templateHalaman/footer.php'; ?>
</body>

</html>