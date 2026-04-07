-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 07, 2026 at 07:34 AM
-- Server version: 8.4.3
-- PHP Version: 8.1.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_aspirasi`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `username` varchar(50) NOT NULL,
  `password` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`username`, `password`) VALUES
('admin', '$2y$10$BZ47/rO4SBjc87gyy2dTq.mcTejP86KPW4ui04tqy4cjf9953t/am');

-- --------------------------------------------------------

--
-- Table structure for table `aspirasi`
--

CREATE TABLE `aspirasi` (
  `id_aspirasi` int NOT NULL,
  `id_pelaporan` int DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `id_kategori` int DEFAULT NULL,
  `feedback` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `aspirasi`
--

INSERT INTO `aspirasi` (`id_aspirasi`, `id_pelaporan`, `status`, `id_kategori`, `feedback`) VALUES
(1, 1, 'Proses', 1, 'kami pihak guru sedang mencari tersangka. terimakasih atas laporan Akbar'),
(2, 2, 'Proses', 1, 'ya, terserah mau di apakan juga'),
(3, 3, 'Selesai', 2, 'pihak guru sudah menjemputnya untuk kembali kesekolah'),
(4, 4, 'Selesai', 3, 'sudah di beri hukuman'),
(5, 6, 'Menunggu', 4, 'sudah di bersiskan oleh siswa \"Akbar\"'),
(6, 8, 'Selesai', 4, 'sip'),
(7, 9, 'Proses', 3, 'maaf saya tidak bisa menangani kasus ini karena saya tidak bisa wkwkwk'),
(8, 10, 'Selesai', 4, 'selesai di tangani');

-- --------------------------------------------------------

--
-- Table structure for table `input_aspirasi`
--

CREATE TABLE `input_aspirasi` (
  `id_pelaporan` int NOT NULL,
  `nis` varchar(20) DEFAULT NULL,
  `id_kategori` int DEFAULT NULL,
  `tanggal` datetime DEFAULT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `pesan` text,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `input_aspirasi`
--

INSERT INTO `input_aspirasi` (`id_pelaporan`, `nis`, `id_kategori`, `tanggal`, `lokasi`, `pesan`, `foto`) VALUES
(1, '001', 1, '2026-02-15 10:30:00', 'Musium', 'Hergi dan rekan rekan memukul seorang siswa tanpa alasan yang jelas', 'bullying.png'),
(2, '001', 1, '2026-03-10 08:15:00', 'hutan sekolah', 'saya tidak sengaja memukul siswa dengan keras sampai dia pingsang', '69d08eecb7e78.jpg'),
(3, '001', 2, '2025-12-20 14:00:00', 'gudang sekolah', 'isi gudang di curi hergi, sekarang gudang jadi kosong', '69d095be80ed8.png'),
(4, '001', 3, '2026-04-04 11:44:55', 'lingkungan sekolah', 'saya melihat hergi berlari pulang sebelum waktunya', NULL),
(6, '001', 4, '2026-04-05 13:18:44', 'kantor', 'bayak debu dimana mana', NULL),
(8, '004', 4, '2026-04-05 15:19:50', 'Musium', 'bayak sampah masyarakat', NULL),
(9, '005', 3, '2026-04-06 12:59:01', 'Hutan dekat sekolah', 'Saya tidak sengaja mencolok kelamin teman saya dan dia lari ketakutan ke dalam hutan dan tersesat sendiri gak tahu jala', '69d34ba59af45.jpg'),
(10, '252623', 4, '2026-04-06 13:03:31', 'gudang sekolah', 'saat saya pergi ke gudang saya menemukan jasad mayat tikus yang tergeletak di bawah kolong meja, mohon segera tindak lanjuti', '69d34cb3934cf.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int NOT NULL,
  `ket_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `ket_kategori`) VALUES
(1, 'Bullying'),
(2, 'Fasilitas Sekolah'),
(3, 'Keamanan'),
(4, 'Kebersihan'),
(5, 'Lainnya');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `nis` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`nis`, `nama`, `password`) VALUES
('003', 'sahrul', '$2y$10$pW21pU7eu8DweMO5GD3oXOMytKYQujbBdE0abIXcsKXyrTkUCU3sK'),
('004', 'anang', '$2y$10$l0SkVASpxgEIgkynKB2.ceAsMjFuFIAinD9Eqz1luK70kZC.SCCE6'),
('005', 'yusuf', '$2y$10$sZvUFrNTSXOo9vay.HvsveerP7NxEBDh6/aOfozQescU4Q9aeDhEy'),
('252623', 'ihsann', '$2y$10$Eyxh0H50G4afisE.bZOqCuKaLx.viejJPg64FXzV9GLW7FD7S65iW');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `aspirasi`
--
ALTER TABLE `aspirasi`
  ADD PRIMARY KEY (`id_aspirasi`),
  ADD KEY `id_pelaporan` (`id_pelaporan`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `input_aspirasi`
--
ALTER TABLE `input_aspirasi`
  ADD PRIMARY KEY (`id_pelaporan`),
  ADD KEY `nis` (`nis`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`nis`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aspirasi`
--
ALTER TABLE `aspirasi`
  MODIFY `id_aspirasi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `input_aspirasi`
--
ALTER TABLE `input_aspirasi`
  MODIFY `id_pelaporan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `aspirasi`
--
ALTER TABLE `aspirasi`
  ADD CONSTRAINT `aspirasi_ibfk_1` FOREIGN KEY (`id_pelaporan`) REFERENCES `input_aspirasi` (`id_pelaporan`),
  ADD CONSTRAINT `aspirasi_ibfk_2` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
