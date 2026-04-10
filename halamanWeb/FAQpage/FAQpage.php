<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FAQ</title>
  <link rel="stylesheet" href="css/FAQpage.css">
  <link rel="stylesheet" href="../../assets/templateHalaman/FAQcard.css">
  <link rel="stylesheet" href="../../assets/templateHalaman/navbar.css">
  <link rel="stylesheet" href="../../assets/templateHalaman/footer.css">
</head>
<body>
  <?php include '../../assets/templateHalaman/navbar.php'; ?>

  <section class="hero">
    <img src="../../assets/Foto/FAQ/FAQ_hero.jpg" alt="FAQ Hero Image" class="hero-img">
    <div class="hero-content">
      <h1>Frequently Asked Questions (FAQ)</h1>
      <p>Temukan jawaban atas pertanyaan umum seputar layanan dan kebijakan kami.</p>
    </div>
  </section>

  <div class="content-wrapper">
    <?php
    $faqList = [
      [
        'q' => 'Apakah semua artikel di sini berbasis riset ilmiah?',
        'a' => 'Ya, setiap artikel yang kami publikasikan telah melalui proses kurasi dan bersumber dari referensi riset ilmiah atau data terpercaya dari lembaga kelautan resmi.'
      ],
      [
        'q' => 'Bagaimana cara melaporkan artikel yang informasinya tidak akurat?',
        'a' => 'Anda dapat menghubungi kami melalui formulir kontak di halaman Tentang Kami atau mengirimkan email ke permatabirudev@gmail.com dengan menyertakan tautan artikel yang dimaksud.'
      ],
      [
        'q' => 'Apa bedanya kategori Biota, Konservasi, dan Geologi?',
        'a' => 'Biota fokus pada keberagaman makhluk hidup laut, Konservasi membahas upaya perlindungan ekosistem, sementara Geologi mengeksplorasi struktur fisik dan bentang alam dasar laut.'
      ],
      [
        'q' => 'Apakah saya bisa berkontribusi menulis artikel?',
        'a' => 'Tentu! Kami sangat terbuka bagi kontributor yang ingin berbagi ilmu dan pengalaman. Silakan hubungi tim redaksi kami melalui email untuk informasi syarat dan ketentuan penulisan.'
      ],
      [
        'q' => 'Bagaimana cara mendukung kampanye konservasi di platform ini?',
        'a' => 'Anda dapat membantu dengan menyebarkan artikel kami ke media sosial, mengikuti program kolaborasi yang kami umumkan, atau menerapkan gaya hidup ramah lingkungan dalam keseharian.'
      ],
      [
        'q' => 'Apakah konten di sini boleh digunakan untuk tugas pendidikan?',
        'a' => 'Boleh sekali! Seluruh materi di platform ini dapat digunakan untuk tujuan edukasi dan non-komersial, selama Anda tetap mencantumkan sumber aslinya dengan benar.'
      ],
      [
        'q' => 'Apakah platform ini memiliki komunitas luring (offline)?',
        'a' => 'Saat ini kami lebih fokus pada platform digital, namun kami rutin mengadakan webinar dan workshop daring secara berkala bagi para pecinta laut di seluruh Indonesia.'
      ],
      [
        'q' => 'Bagaimana cara mendapatkan update artikel terbaru?',
        'a' => 'Anda dapat mengikuti akun media sosial resmi kami atau mengunjungi halaman utama platform secara berkala untuk melihat terbitan artikel terbaru kami.'
      ],
    ];
    include '../../assets/templateHalaman/FAQcard.php';
    ?>
  </div>

  <section class="contact-cta">
    <div class="cta-content">
      <h2>Masih ada pertanyaan?</h2>
      <p>Jika Anda tidak menemukan jawaban yang dicari, tim kami siap membantu Anda.</p>
      <a href="../tentangpage/tentang.php" class="cta-button">Hubungi Kami</a>
    </div>
  </section>

  <?php include '../../assets/templateHalaman/footer.php'; ?>
</body>
</html>