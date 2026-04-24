<?php
// test_koneksi.php - HAPUS SETELAH TEST!
include 'koneksi.php';

$host = mysqli_query($koneksi, "SELECT @@hostname");
$row = mysqli_fetch_assoc($host);
echo "Konek ke: " . $row['@@hostname'];
?>