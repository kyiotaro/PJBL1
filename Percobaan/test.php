<?php 
include '../koneksi.php'; 

// Mengambil beberapa artikel untuk pengetesan
$query = mysqli_query($koneksi, "
    SELECT a.*, k.nama AS kategori 
    FROM Artikel a 
    LEFT JOIN kategori k ON k.id = a.kategori_id 
    LIMIT 3
");
$articles = [];
while ($row = mysqli_fetch_assoc($query)) {
    $articles[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Test Card Variant 2</title>
  <link rel="stylesheet" href="../assets/templateHalaman/navbar/navbar.css">
  <link rel="stylesheet" href="../assets/templateHalaman/footer/footer.css">
  <link rel="stylesheet" href="../assets/templateHalaman/cardVariant/card2/card2.css">
  <style>
    .card2-list {
      padding: 15px 50px;
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
  </style>
</head>

<body>
  <?php include '../assets/templateHalaman/navbar/navbar.php'; ?>

  <div class="card2-list">
    <?php if (!empty($articles)): ?>
      <?php foreach ($articles as $artikel): ?>
        <?php include '../assets/templateHalaman/cardVariant/card2/card2.php'; ?>
      <?php endforeach; ?>
    <?php else: ?>
      <p>Tidak ada artikel untuk ditampilkan.</p>
    <?php endif; ?>
  </div>

  <?php include '../assets/templateHalaman/footer/footer.php'; ?>
</body>

</html>