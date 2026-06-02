<?php
include '../../koneksi.php';
$search_input = isset($_GET['query']) ? trim($_GET['query']) : '';

$artikelHasStatus = false;
$statusCheck = mysqli_query($koneksi, "SHOW COLUMNS FROM artikel LIKE 'status'");
if ($statusCheck && mysqli_num_rows($statusCheck) > 0) {
  $artikelHasStatus = true;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hasil Pencarian: <?= htmlspecialchars($search_input) ?></title>
  <link rel="stylesheet" href="css/hasilPencarian.css">
  <link rel="stylesheet" href="../../assets/templateHalaman/navbar/navbar.css">
  <link rel="stylesheet" href="../../assets/templateHalaman/footer/footer.css">
  <link rel="stylesheet" href="../../assets/templateHalaman/cardVariant/card2/card2.css">
</head>

<body>
  <?php include '../../assets/templateHalaman/navbar/navbar.php'; ?>
  
  <main>
    <div class="card2-list">
      <?php
      if ($search_input !== '') {
        $sql = "
          SELECT a.*, k.nama AS kategori
          FROM artikel a
          LEFT JOIN kategori k ON k.id = a.kategori_id
          WHERE (a.judul LIKE ? OR a.isi LIKE ?)
        ";

        if ($artikelHasStatus) {
          $sql .= " AND a.status = 'published'";
        }

        $sql .= " ORDER BY a.id DESC";

        $stmt = mysqli_prepare($koneksi, $sql);
        if ($stmt) {
          $searchLike = '%' . $search_input . '%';
          mysqli_stmt_bind_param($stmt, 'ss', $searchLike, $searchLike);
          mysqli_stmt_execute($stmt);

          if (function_exists('mysqli_stmt_get_result')) {
            $query = mysqli_stmt_get_result($stmt);
          } else {
            $escaped = mysqli_real_escape_string($koneksi, $search_input);
            $fallbackSql = "
              SELECT a.*, k.nama AS kategori
              FROM artikel a
              LEFT JOIN kategori k ON k.id = a.kategori_id
              WHERE (a.judul LIKE '%$escaped%' OR a.isi LIKE '%$escaped%')
            ";
            if ($artikelHasStatus) {
              $fallbackSql .= " AND a.status = 'published'";
            }
            $fallbackSql .= " ORDER BY a.id DESC";
            $query = mysqli_query($koneksi, $fallbackSql);
          }

          if ($query && mysqli_num_rows($query) > 0) {
            while ($artikel = mysqli_fetch_assoc($query)) {
              include '../../assets/templateHalaman/cardVariant/card2/card2.php';
            }
          } else {
            echo "<p>Tidak ada hasil yang ditemukan untuk \"" . htmlspecialchars($search_input) . "\".</p>";
          }

          mysqli_stmt_close($stmt);
        } else {
          echo "<p>Pencarian gagal dijalankan. Coba lagi sebentar.</p>";
        }
      } else {
        echo "<p>Masukkan kata kunci untuk mencari artikel.</p>";
      }
      ?>
    </div>
  </main>

  <?php include '../../assets/templateHalaman/footer/footer.php'; ?>
</body>

</html>