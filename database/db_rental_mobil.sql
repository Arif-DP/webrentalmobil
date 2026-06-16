-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 15, 2026 at 03:39 PM
-- Server version: 8.0.30
-- PHP Version: 8.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_rental_mobil`
--

-- --------------------------------------------------------

--
-- Table structure for table `mobil`
--

CREATE TABLE `mobil` (
  `plat_nomor` varchar(20) NOT NULL,
  `merk` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `tahun` int NOT NULL,
  `harga_per_hari` int NOT NULL,
  `status` enum('Tersedia','Disewa','Maintenance') DEFAULT 'Tersedia',
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `mobil`
--

INSERT INTO `mobil` (`plat_nomor`, `merk`, `model`, `tahun`, `harga_per_hari`, `status`, `foto`) VALUES
('AG 2679 PA', 'Toyota', 'Avanza 1.5 G', 2022, 400000, 'Tersedia', '14062026081924_Avanza_2022.png'),
('AG 4150 BF', 'Suzuki', 'Ertiga Hybrid GX', 2023, 420000, 'Tersedia', '14062026081932_Ertiga_Hybrid_GX_2023.png'),
('AG 6313 FA', 'Daihatsu', 'Ayla 1.0 X', 2020, 250000, 'Tersedia', '14062026082133_Daihatsu_Ayla_1.0_X_2020.jpg'),
('B 3590 BGN', 'Toyota', 'Avanza 1.3 G', 2019, 350000, 'Disewa', '12062026092000_whatsapp-image-2024-03-28-at-12-20240328121840.jpg'),
('B 3615 IEY', 'Daihatsu', 'Xenia 1.5 R CVT', 2022, 400000, 'Tersedia', '15062026130830_Daihatsu_Xenia_1.5_R_CVT.jpeg'),
('B 4582 NA', 'Toyota', 'Veloz 1.5 Q CVT', 2023, 450000, 'Tersedia', '15062026131127_Toyota_Veloz_1.5_Q_CVT_2023.jpg'),
('L 2169 NG', 'Toyota', 'Agya 1.2 G', 2021, 275000, 'Tersedia', '15062026131439_Toyota_Agya_1.2G_2021.jpeg'),
('L 2307 WJ', 'Honda', 'Brio Satya S', 2020, 275000, 'Disewa', '15062026131533_Honda_Brio_Satya_2020.jpg'),
('L 2674 JJ', 'Mitsubishi', 'Xpander Exceed', 2020, 400000, 'Tersedia', '15062026131643_Mitsubishi_Xpander_Exceed_2020.jpg'),
('L 5333 SN', 'Mitsubishi', 'Xpander Ultimate', 2022, 480000, 'Tersedia', '15062026131813_Mitsubishi_Xpander_ultimate_2022.jpg'),
('L 7572 BXO', 'Daihatsu', 'Ayla 1.2 R', 2022, 300000, 'Tersedia', '15062026130806_Daihatsu_Ayla_1.2_R.jpg'),
('S 6977 JF', 'Honda', 'Brio Satya E', 2021, 325000, 'Tersedia', '15062026131941_Honda_Brio_Satya_E_2021.jpg'),
('S 9928 FP', 'Daihatsu', 'Xenia 1.3 R', 2021, 350000, 'Disewa', '15062026132134_Daihatsu_Xenia_1.3_R_2021.jpg'),
('W 2291 VQ', 'Suzuki', 'Ertiga GL', 2019, 350000, 'Tersedia', '15062026132300_Suzuki_Ertiga_GL_2019.jpg'),
('W 2824 TG', 'Toyota', 'Avanza 1.3 G', 2021, 350000, 'Tersedia', '15062026132427_Toyota_Avanza_1.3G_2021.jpg'),
('W 7216 ZQ', 'Toyota', 'Agya 1.2 GR Sport', 2023, 350000, 'Tersedia', '15062026132620_Toyota_Agya_1.2GR_sport_2023.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telepon` varchar(20) NOT NULL,
  `alamat` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`id`, `nama`, `email`, `telepon`, `alamat`) VALUES
(1, 'Auril Adiya I.D', 'udilmoakanna@gmail.com', '081226909055', 'Jl. Pasar Wage No. 08'),
(2, 'Ahmad Subarjo', 'ahmad.subarjo@gmail.com\r\n', '081234567890\r\n', 'Jl. Merdeka No. 12, Malang\r\n'),
(3, 'Siti Aminah', 'siti.aminah@gmail.com', '081345678901', 'Jl. Mawar No. 45, Surabaya'),
(4, 'Budi Setiawan', 'budi.setiawan@gmail.com', '081987654321', 'Jl. Diponegoro No. 89, Batu'),
(5, 'Dewi Lestari', 'dewi.lestari@gmail.com', '085712345678', 'Jl. Anggrek No. 21, Pasuruan'),
(6, 'Eko Prasetyo', 'eko.prasetyo@gmail.com', '082134567892', 'Jl. Gatot Subroto No. 5, Blitar'),
(7, 'Rina Wijaya', 'rina.wijaya@gmail.com', '081298765434', 'Jl. Melati No. 14, Sidoarjo'),
(8, 'Fajar Nugroho', 'fajar.nugroho@gmail.com', '087812345675', 'Jl. Sudirman No. 67, Kediri'),
(9, 'Sari Utami', 'sari.utami@gmail.com', '081398765432', 'Jl. Dahlia No. 33, Probolinggo'),
(10, 'Hendra Wijaya', 'hendra.wijaya@gmail.com', '085612345671', 'Jl. Pahlawan No. 102, Mojokerto');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int NOT NULL,
  `pelanggan_id` int NOT NULL,
  `plat_nomor` varchar(20) NOT NULL,
  `tanggal_sewa` date NOT NULL,
  `durasi_hari` int NOT NULL,
  `total_harga` int NOT NULL,
  `status_transaksi` enum('Berjalan','Selesai') DEFAULT 'Berjalan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `pelanggan_id`, `plat_nomor`, `tanggal_sewa`, `durasi_hari`, `total_harga`, `status_transaksi`) VALUES
(1, 1, 'B 3590 BGN', '2026-06-13', 3, 1050000, 'Berjalan'),
(2, 2, 'AG 6313 FA', '2026-06-14', 1, 250000, 'Selesai'),
(3, 4, 'L 2307 WJ', '2026-06-15', 2, 550000, 'Berjalan'),
(4, 6, 'S 9928 FP', '2026-06-15', 1, 350000, 'Berjalan');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff') DEFAULT 'staff'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '$2a$12$PLnNx1.Skn/a1vfivuwunOpY0ZsLwUj/rZPmFOUK80Ay0DrBZ5eVS', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `mobil`
--
ALTER TABLE `mobil`
  ADD PRIMARY KEY (`plat_nomor`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pelanggan_id` (`pelanggan_id`),
  ADD KEY `plat_nomor` (`plat_nomor`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`pelanggan_id`) REFERENCES `pelanggan` (`id`),
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`plat_nomor`) REFERENCES `mobil` (`plat_nomor`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
