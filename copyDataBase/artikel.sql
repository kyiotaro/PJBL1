-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 03, 2026 at 05:09 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `permatabirunusantara`
--

-- --------------------------------------------------------

--
-- Table structure for table `artikel`
--

CREATE TABLE `artikel` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `isi` longtext NOT NULL,
  `slug` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `artikel`
--

INSERT INTO `artikel` (`id`, `judul`, `kategori`, `tanggal`, `gambar`, `isi`, `slug`) VALUES
(2, 'Palung Jawa - Jurang laut di Selatan Indonesia', 'geologi', '2026-04-01', 'artikel/geologi/palung-jawa-jurang-laut-di-selatan-indonesia-20260401114737.png', '<p>Palung Jawa, atau Sunda Trench, adalah salah satu fitur geologi laut paling menakjubkan di Indonesia. Terletak di Samudra Hindia, memanjang dari barat Sumatra hingga selatan Jawa, palung ini memiliki panjang sekitar 3.200 kilometer. Titik terdalamnya bisa mencapai lebih dari 7.400 meter, menjadikannya salah satu jurang laut terdalam di dunia. Keberadaan palung ini tidak hanya misterius, tapi juga menyimpan banyak potensi sekaligus ancaman bagi kehidupan di sekitarnya.</p><p>Palung ini terbentuk akibat proses subduksi, yaitu ketika Lempeng Indo-Australia bergerak menyusup ke bawah Lempeng Eurasia (lebih tepatnya Lempeng Sunda). Peristiwa \"saling dorong\" antar lempeng itu membuat dasar laut terlipat dan tercipta jurang raksasa. Selain menghasilkan struktur dasar laut yang terjal, proses ini juga berperan dalam pembentukan gunung api dan gempa bumi yang kerap mengguncang wilayah Indonesia.</p><p>Bukan cuma dalam dan gelap, Palung Jawa ternyata menyimpan kehidupan. Penelitian terbaru menemukan ventil hidrotermal suhu rendah, sedimen kaya oksida besi, serta berbagai organisme laut dalam yang mampu bertahan di tekanan ekstrem. Ini menjadikan Palung Jawa sebagai laboratorium alam untuk mempelajari bagaimana makhluk hidup bisa beradaptasi di lingkungan paling keras di bumi.</p><p>Namun, Palung Jawa juga punya sisi berbahaya. Zona subduksi yang membentuknya adalah sumber dari gempa besar dan tsunami. Sejarah mencatat, gempa di sekitar palung ini beberapa kali menimbulkan gelombang tsunami yang berdampak besar pada pesisir Sumatra dan Jawa. Karena itu, penelitian dan pemantauan di wilayah ini penting untuk meningkatkan sistem peringatan dini dan mengurangi risiko bencana.</p><p>Selain aspek bencana, Palung Jawa juga bernilai strategis untuk ilmu pengetahuan. Sedimen yang terkumpul di dasar palung menyimpan catatan sejarah bumi, mulai dari aktivitas vulkanik hingga perubahan iklim. Tak heran, para ilmuwan terus menjelajahinya dengan teknologi canggih. Palung Jawa akhirnya bukan hanya sekadar jurang laut, tapi juga kunci memahami dinamika bumi dan potensi sumber daya yang harus dijaga dengan bijak.</p><p><br></p>', 'palung-jawa-jurang-laut-di-selatan-indonesia'),
(3, 'Kenapa Penyu Belimbing Disebut Belimbing? Ini Alasannya', 'biota', '2026-04-01', 'artikel/biota/kenapa-penyu-belimbing-disebut-belimbing-ini-alasannya-20260401115631.jpg', '<p>Penyu belimbing (<em>Dermochelys coriacea</em>) punya nama yang unik dan gampang diingat. Berbeda dengan penyu hijau atau penyu sisik yang memiliki karapas keras penuh sisik, penyu belimbing justru memiliki tempurung lunak. Kulitnya tebal, berwarna gelap dengan garis-garis putih yang menonjol seperti alur pada buah belimbing. Dari bentuk inilah masyarakat memberi nama \"penyu belimbing.\"</p><p>Bentuk tubuh penyu belimbing juga sangat berbeda dari kerabatnya. Karapasnya tidak keras, melainkan dilapisi jaringan kulit dan lemak yang elastis. Tekstur ini membuatnya terlihat seperti buah belimbing yang bersegi-segi, sehingga mudah dikenali dibanding penyu laut lainnya. Keunikan fisik ini sekaligus menjadi ciri khas yang membuatnya istimewa di dunia biologi laut.</p><p>Selain bentuknya yang khas, ukuran penyu belimbing juga luar biasa. Mereka bisa tumbuh hingga lebih dari dua meter panjangnya dan mencapai berat hampir satu ton. Dengan tubuh sebesar itu, penyu belimbing tetap mampu berenang cepat dan menyelam lebih dalam dibanding penyu lain. Bentuk tubuh yang streamline dan tempurung lunak membantu mereka bergerak lincah di lautan luas.</p><p>Nama \"belimbing\" bukan hanya sekadar penanda bentuk, tapi juga pengingat tentang identitas satwa ini yang berbeda dari penyu lainnya. Sayangnya, meskipun unik dan tangguh, penyu belimbing termasuk hewan yang terancam punah. Ancaman terbesar datang dari sampah plastik, perburuan telur, hingga perubahan iklim yang memengaruhi proses bertelur mereka.</p><p>Melihat asal-usul nama penyu belimbing seharusnya membuat kita semakin sadar betapa berharganya hewan ini. Keunikannya adalah bagian dari kekayaan alam Indonesia yang tidak dimiliki banyak negara lain. Menjaga penyu belimbing berarti juga menjaga warisan alam, supaya generasi mendatang masih bisa melihat langsung raksasa laut yang namanya terinspirasi dari buah sederhana di darat.</p>', 'kenapa-penyu-belimbing-disebut-belimbing-ini-alasannya'),
(4, 'Pesona Laut Pulau Biak dan Kepulauan Padaido', 'wisata', '2026-04-01', 'artikel/wisata/pesona-laut-pulau-biak-dan-kepulauan-padaido-20260401120324.png', '<p>Pulau Biak di Papua, bersama gugusan Kepulauan Padaido, adalah salah satu destinasi wisata laut paling indah di Indonesia timur. Laut birunya berkilau, dihiasi pulau-pulau kecil dengan pasir putih lembut dan terumbu karang yang masih sehat. Posisi Biak yang berada di Teluk Cenderawasih membuat kawasan ini kaya biota laut dan cocok untuk siapa pun yang ingin menikmati suasana laut alami jauh dari keramaian.</p><p>Kepulauan Padaido ditetapkan sebagai Taman Wisata Perairan dengan luas lebih dari 180 ribu hektar. Perairan ini menyimpan kekayaan ekosistem lengkap: terumbu karang, padang lamun, hingga hutan mangrove. Kawasan ini dikelola dengan sistem zonasi sehingga pariwisata, perikanan, dan konservasi bisa berjalan seimbang. Itulah sebabnya Padaido sering disebut sebagai permata laut Papua yang masih terjaga.</p><p>Banyak kegiatan bisa dilakukan di Biak dan Padaido. Snorkeling dan diving adalah pilihan utama, dengan spot terumbu karang berwarna-warni dan ikan tropis yang melimpah. Selain itu, wisatawan bisa island hopping ke pulau-pulau kecil, menikmati sunset di Pantai Segara Indah, atau mengunjungi rencana Museum Bawah Laut yang menyimpan peninggalan Perang Dunia II. Setiap aktivitas menghadirkan pengalaman unik, mulai dari keindahan alam hingga nilai sejarah.</p><p>Daya tarik Biak ada pada keaslian lingkungannya. Lautnya masih jernih, biota laut berlimpah, dan nuansa pedesaan maritim yang tenang. Namun, tantangan juga ada: akses transportasi ke pulau-pulau kecil kadang memakan waktu, fasilitas wisata belum selengkap destinasi populer lain, dan ancaman kerusakan ekosistem masih perlu diantisipasi. Karena itu, wisatawan diharapkan ikut menjaga kebersihan dan tidak merusak lingkungan laut.</p><p>Pulau Biak dan Kepulauan Padaido menawarkan wisata laut yang berbeda dari tempat lain di Indonesia. Di sini, keindahan alam berpadu dengan nilai sejarah dan budaya masyarakat lokal. Menyelam di karang-karangnya, menyusuri pulau kecil yang sepi, hingga menyaksikan matahari terbenam yang spektakuler akan jadi pengalaman yang tak terlupakan. Biak bukan hanya destinasi wisata, tapi juga perjalanan menyelami kekayaan laut dan kehidupan masyarakat Papua yang penuh kearifan.</p>', 'pesona-laut-pulau-biak-dan-kepulauan-padaido'),
(5, 'Keajaiban Raja Ampat: Jantung Segitiga Karang Dunia', 'wisata', '2026-04-02', 'artikel/wisata/keajaiban-raja-ampat-jantung-segitiga-karang-dunia-20260402124225.jpg', '<p>Raja Ampat, yang terletak di ujung barat laut Semenanjung Kepala Burung di Papua Barat, sering disebut sebagai \"Jantung Segitiga Karang Dunia.\" Kawasan ini bukan hanya sekadar destinasi wisata, melainkan sebuah laboratorium alam yang menyimpan kekayaan hayati laut tertinggi di planet ini. Nama \"Raja Ampat\" sendiri merujuk pada empat pulau utama yang mempesona: <strong>Waigeo</strong>, <strong>Misool</strong>, <strong>Salawati</strong>, dan <strong>Batanta</strong>.</p>\r\n<p>Kekayaan bawah laut Raja Ampat benar-benar tak tertandingi. Para peneliti mencatat ada lebih dari 550 spesies karang keras dan 1.500 spesies ikan karang yang mendiami perairan ini. Dari hiu berjalan (<em>Epaulette Shark</em>) hingga pari manta raksasa, setiap sudut terumbu karangnya menawarkan pemandangan yang memukau bagi para penyelam dan pecinta alam.</p>\r\n<p>Namun, keajaiban Raja Ampat tidak berhenti di bawah permukaan laut. Di daratan, hutan hujan yang rimbun menjadi rumah bagi burung-burung endemik yang eksotis, termasuk Burung Cendrawasih Merah dan Cendrawasih Wilson yang melakukan tarian kawin yang spektakuler. Formasi karst yang ikonik di Wayag dan Piaynemo juga memberikan panorama gugusan pulau batu gamping di tengah laut biru toska yang tak akan terlupakan.</p>\r\n<p>Waktu terbaik untuk berkunjung adalah antara bulan Oktober hingga April, saat kondisi laut tenang dan jarak pandang di bawah air mencapai puncaknya. Mengunjungi Raja Ampat adalah sebuah perjalanan spiritual untuk kembali ke alam, sekaligus pengingat pentingnya menjaga kelestarian ekosistem yang begitu rapuh namun berharga bagi masa depan bumi kita.</p>', 'keajaiban-raja-ampat-jantung-segitiga-karang-dunia');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `artikel`
--
ALTER TABLE `artikel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `artikel`
--
ALTER TABLE `artikel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
