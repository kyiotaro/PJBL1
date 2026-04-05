<?php 
include '../koneksi.php'; 

// Mengambil beberapa artikel untuk pengetesan
$query = mysqli_query($koneksi, "SELECT * FROM Artikel LIMIT 3");
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
  <link rel="stylesheet" href="../assets/templateHalaman/navbar.css">
  <link rel="stylesheet" href="../assets/templateHalaman/footer.css">
  <link rel="stylesheet" href="../assets/templateHalaman/cardVariant/card2/card2.css">
  <style>
    .card2-list {
      padding: 50px;
      display: flex;
      flex-direction: column;
      gap: 20px;
      max-width: 1000px;
      margin: 0 auto;
    }
  </style>
</head>

<body>
  <?php include '../assets/templateHalaman/navbar.php'; ?>

  <div class="card2-list">
    <?php if (!empty($articles)): ?>
      <?php foreach ($articles as $artikel): ?>
        <?php include '../assets/templateHalaman/cardVariant/card2/card2.php'; ?>
      <?php endforeach; ?>
    <?php else: ?>
      <p>Tidak ada artikel untuk ditampilkan.</p>
    <?php endif; ?>
  </div>

  <?php include '../assets/templateHalaman/footer.php'; ?>
</body>

</html>