-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 23, 2025 at 12:58 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `firmancoffe`
--

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` int NOT NULL,
  `category` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price_hot` int DEFAULT NULL,
  `price_ice` int DEFAULT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `category`, `name`, `description`, `price_hot`, `price_ice`, `stock`, `image`, `created_at`) VALUES
(1, 'Manual Brew', 'Tubruk', 'kopi arabika pilihan', 12000, NULL, 20, 'uploads/tubruk.jpg\r\n', '2025-09-18 03:33:53'),
(2, 'Manual Brew', 'Vietnam Drip', '', 15000, NULL, 15, 'uploads/menu_68d05a4e43dfe.jpg', '2025-09-18 03:33:53'),
(3, 'Manual Brew', 'V60 (Filter Coffee)', '', 15000, 19000, 10, 'uploads/menu_68d05a6da7477.jpg', '2025-09-18 03:33:53'),
(4, 'Manual Brew', 'V60 Guest Bean', '', 19000, NULL, 8, 'uploads/menu_68d05a7b2963c.jpg', '2025-09-18 03:33:53'),
(5, 'Espresso Base', 'Espresso', '', 10000, NULL, 90, 'uploads/menu_68e8c227ab403.jpg', '2025-09-18 03:33:53'),
(6, 'Espresso Base', 'Americano', NULL, 13000, 15000, 74, 'uploads/americano.jpg', '2025-09-18 03:33:53'),
(7, 'Espresso Base', 'Cafe Latte', NULL, 15000, 19000, 86, 'uploads/latte.jpg', '2025-09-18 03:33:53'),
(8, 'Espresso Base', 'Cappuccino', '', 15000, 19000, 87, 'uploads/menu_68d05a8737f3d.jpg', '2025-09-18 03:33:53'),
(9, 'Espresso Base', 'Caramel Machiatos', '', 15000, 19000, 90, 'uploads/menu_68d05c322fc38.jpg', '2025-09-18 03:33:53'),
(10, 'Espresso Base', 'Mocca', NULL, 15000, 19000, 90, 'uploads/mocca.jpg', '2025-09-18 03:33:53'),
(11, 'Espresso Base', 'Vanilla Latte', '', 15000, 19000, 90, 'uploads/menu_68d05c21d1862.jpg', '2025-09-18 03:33:53'),
(12, 'Espresso Base', 'Hazelnut Latte', '', 15000, 19000, 90, 'uploads/menu_68d05c132cee1.jpg', '2025-09-18 03:33:53'),
(13, 'Espresso Base', 'Coffee Gula Aren', '', 15000, 19000, 90, 'uploads/menu_68d05bffe4695.jpg', '2025-09-18 03:33:53'),
(14, 'Espresso Base', 'Gamel Ice Coffee', '', 15000, 19000, 90, 'uploads/menu_68d05bf021a59.jpg', '2025-09-18 03:33:53'),
(15, 'Milk Base', 'Green Tea Latte', '', 15000, 19000, 12, 'uploads/menu_68d05be34cc55.jpg', '2025-09-18 03:33:53'),
(16, 'Milk Base', 'Red Velvet Latte', '', 15000, 19000, 10, 'uploads/menu_68d05bd79df3f.jpg', '2025-09-18 03:33:53'),
(17, 'Milk Base', 'Taro Latte', '', 15000, 19000, 10, 'uploads/menu_68d05bc9a6884.jpg', '2025-09-18 03:33:53'),
(18, 'Milk Base', 'Choco Latte', '', 15000, 19000, 20, 'uploads/menu_68d05bbe827cd.jpg', '2025-09-18 03:33:53'),
(19, 'Milk Base', 'Choco Vanilla', '', 15000, 19000, 10, 'uploads/menu_68d05baecad86.jpg', '2025-09-18 03:33:53'),
(20, 'Milk Base', 'Choco Hazelnut', '', 15000, 19000, 6, 'uploads/menu_68d05b9eb0ee5.jpg', '2025-09-18 03:33:53'),
(21, 'Milk Base', 'Choco Strawberry', '', 15000, 19000, 10, 'uploads/menu_68d05b92535c7.jpg', '2025-09-18 03:33:53'),
(22, 'Milk Base', 'Gamel Chocolatte', '', 15000, 19000, 12, 'uploads/menu_68d05b709c0fd.jpg', '2025-09-18 03:33:53'),
(23, 'Milk Base', 'Milkshake', NULL, 15000, 19000, 7, 'uploads/milkshake.jpg', '2025-09-18 03:33:53'),
(24, 'Other', 'Lemon Squash', '', NULL, 17000, 10, 'uploads/menu_68d05b3cdf34d.jpg', '2025-09-18 03:33:53'),
(25, 'Other', 'Mojito', NULL, NULL, 17000, 10, 'uploads/mojito.jpg', '2025-09-18 03:33:53'),
(26, 'Other', 'Lemon Tea', '', 5000, 7000, 20, 'uploads/menu_68d05b8381a1b.jpg', '2025-09-18 03:33:53'),
(27, 'Other', 'Ice Tea', '', NULL, 5000, 30, 'uploads/menu_68d05b60286a7.jpg', '2025-09-18 03:33:53'),
(28, 'Other', 'Air Mineral', '', NULL, 5000, 30, 'uploads/menu_68d05b4a5e8d8.jpg', '2025-09-18 03:33:53'),
(29, 'Snack', 'Sosis Goreng', '', 12000, NULL, 20, 'uploads/menu_68d05b318b6de.jpg', '2025-09-18 03:33:53'),
(30, 'Snack', 'Nugget Goreng', NULL, 12000, NULL, 20, 'uploads/nugget.jpg', '2025-09-18 03:33:53'),
(31, 'Snack', 'Kentang Goreng', '', 12000, NULL, 14, 'uploads/menu_68d05b2438ed6.jpg', '2025-09-18 03:33:53'),
(32, 'Snack', 'Bakso Goreng', '', 12000, NULL, 16, 'uploads/menu_68d05b1348725.jpg', '2025-09-18 03:33:53'),
(33, 'Snack', 'Otak-otak', '', 12000, NULL, 15, 'uploads/menu_68d05b06d03ed.jpg', '2025-09-18 03:33:53'),
(34, 'Snack', 'Tahu Krispi', '', 10000, NULL, 0, 'uploads/menu_68d05af8e7109.jpg', '2025-09-18 03:33:53'),
(35, 'Snack', 'Jamur Krispi', '', 10000, NULL, 10, 'uploads/menu_68d05ae8a887e.jpg', '2025-09-18 03:33:53'),
(36, 'Snack', 'Ceker Krispi', '', 10000, NULL, 10, 'uploads/menu_68d05add96ae3.jpg', '2025-09-18 03:33:53'),
(37, 'Snack', 'Seblak Kering', '', 3000, NULL, 40, 'uploads/menu_68d05acf6efdd.jpg', '2025-09-18 03:33:53'),
(38, 'Foods', 'Ayam Geprek', '', 13000, NULL, 12, 'uploads/menu_68d05ac365592.jpg', '2025-09-18 03:33:53'),
(39, 'Foods', 'Nasi Goreng Telur', '', 14000, NULL, 15, 'uploads/menu_68d05ab694d61.jpg', '2025-09-18 03:33:53'),
(40, 'Foods', 'Nasi Goreng Ayam', '', 16000, NULL, 10, 'uploads/menu_68d05aa792b25.jpg', '2025-09-18 03:33:53'),
(41, 'Foods', 'Nasi Telur Krispi', '', 15000, NULL, 8, 'uploads/menu_68d05a98ec267.jpg', '2025-09-18 03:33:53'),
(42, 'Foods', 'Kwetiau', NULL, 15000, NULL, 4, 'uploads/kwetiau.jpg', '2025-09-18 03:33:53'),
(46, 'Snack', 'Pisang Cripsy', '', 15000, NULL, 9990, 'uploads/menu_68e363304a867.jpg', '2025-10-06 06:35:28');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `table_number` varchar(50) DEFAULT NULL,
  `total` int NOT NULL,
  `payment_method` enum('cash','qris') DEFAULT 'cash',
  `status` enum('baru','diproses','selesai') DEFAULT 'baru',
  `qris_selected` tinyint(1) DEFAULT '0',
  `bukti_qris` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `table_number`, `total`, `payment_method`, `status`, `qris_selected`, `bukti_qris`, `created_at`) VALUES
(1, 'aaaaaaaaaaaaaaaa', 'aaaaaaaaaaaaaaaaa', 211000, 'cash', 'baru', 0, NULL, '2025-09-18 04:12:06'),
(2, 'apaaaaadWDw', 'wadawdwqd', 321000, 'cash', 'baru', 0, NULL, '2025-09-18 04:13:08'),
(3, 'apaaaaadWDw', 'wadawdwqd', 75000, 'cash', 'baru', 0, NULL, '2025-09-18 04:15:11'),
(4, 'qqqqqqqqqqq', 'qqqqqqqqqqqqqqqqqq', 272000, 'cash', 'baru', 0, NULL, '2025-09-18 04:16:15'),
(5, 'zzzzzzzzz', 'zzzzzzzzzz', 177000, 'qris', 'baru', 0, 'uploads/qris_1758169027.jpg', '2025-09-18 04:17:07'),
(6, 'bbbbb', 'bbbbbb', 238000, 'cash', 'baru', 0, NULL, '2025-09-18 05:08:12'),
(7, 'tata', '9', 58000, 'cash', 'baru', 0, NULL, '2025-09-21 20:02:23'),
(8, 'dasdsa', '3', 106000, 'cash', 'selesai', 0, NULL, '2025-09-21 20:34:40'),
(9, 'RIGEN', '4', 13000, 'qris', 'selesai', 0, 'uploads/qris_1758487083.jpg', '2025-09-21 20:38:03'),
(10, 'sasa', '1', 30000, 'cash', 'baru', 0, NULL, '2025-09-21 22:00:36'),
(11, 'firda', '4', 30000, 'cash', 'baru', 0, NULL, '2025-09-24 08:03:46'),
(12, 'kun', '6', 50000, 'cash', 'selesai', 0, NULL, '2025-09-29 03:40:08'),
(13, 'dia', '6', 59000, 'cash', 'selesai', 0, NULL, '2025-09-29 04:32:09'),
(14, 'bimo', '9', 165000, 'cash', 'baru', 0, NULL, '2025-10-06 07:07:35'),
(15, 'dia', '1', 39000, 'cash', 'baru', 0, NULL, '2025-10-06 07:15:14'),
(16, 'agagag', '4', 40000, 'cash', 'baru', 0, NULL, '2025-10-06 07:24:38'),
(17, 'Firman', '2', 15000, 'qris', 'baru', 0, 'uploads/qris_1759994858.jpg', '2025-10-09 07:27:38'),
(18, 'asep', '2', 78000, 'cash', 'baru', 0, NULL, '2025-10-09 08:24:49'),
(19, 'asep', '2', 30000, 'cash', 'baru', 0, NULL, '2025-10-09 08:27:36'),
(20, 'hola', '3', 40000, 'cash', 'baru', 0, NULL, '2025-10-09 08:28:13'),
(21, 'dinda', '1', 73000, 'cash', 'baru', 0, NULL, '2025-10-09 08:28:41'),
(22, 'pstimah', '5', 56000, 'cash', 'baru', 0, NULL, '2025-10-09 08:34:00'),
(23, 'gono', '5', 20000, 'cash', 'baru', 0, NULL, '2025-10-09 08:40:58'),
(24, 'supar', '9', 90000, 'cash', 'baru', 0, NULL, '2025-10-09 08:47:39'),
(25, 'yati', '4', 30000, 'cash', 'baru', 0, NULL, '2025-10-09 08:49:52'),
(26, 'pstimah', '4', 32000, 'cash', 'selesai', 0, NULL, '2025-10-09 08:53:33'),
(27, 'penyol', '9', 35000, 'cash', 'baru', 0, NULL, '2025-10-09 10:07:48'),
(28, 'adis', '8', 48000, 'cash', 'baru', 0, NULL, '2025-10-09 10:10:01'),
(29, 'nopi', '15', 20000, 'qris', 'baru', 0, 'uploads/qris_1760004841.jpg', '2025-10-09 10:14:01'),
(30, 'fara', '45', 58000, 'qris', 'baru', 0, 'uploads/qris_1760004930.jpg', '2025-10-09 10:15:30'),
(31, 'ansel', '7', 36000, 'cash', 'baru', 0, NULL, '2025-10-09 10:18:37'),
(32, 'kun', '1', 24000, 'cash', 'baru', 0, NULL, '2025-10-09 10:23:00'),
(33, 'sukar', '5', 12000, 'cash', 'baru', 0, NULL, '2025-10-09 10:23:31'),
(34, 'yanto', '8', 15000, 'cash', 'selesai', 0, NULL, '2025-10-09 10:29:42'),
(35, 'bubu', '5', 20000, 'cash', 'baru', 0, NULL, '2025-10-09 11:13:59'),
(36, 'firda', '1', 30000, 'cash', 'baru', 0, NULL, '2025-10-09 11:34:25'),
(37, 'tina', '2', 30000, 'cash', 'baru', 0, NULL, '2025-10-09 11:35:01'),
(38, 'ruli', '09', 30000, 'cash', 'baru', 0, NULL, '2025-10-09 11:35:51'),
(39, 'asep', '8', 20000, 'cash', 'baru', 0, NULL, '2025-10-09 11:55:09'),
(40, 'gina', '8', 10000, 'cash', 'baru', 0, NULL, '2025-10-09 12:11:23'),
(41, 'kun', '3', 10000, 'cash', 'diproses', 0, NULL, '2025-10-09 12:11:45'),
(42, 'ruli', '6', 10000, 'cash', 'selesai', 0, NULL, '2025-10-09 12:12:08'),
(43, 'asda', '12', 26000, 'cash', 'baru', 0, NULL, '2025-10-10 09:27:17'),
(44, 'anggi', '1', 30000, 'cash', 'baru', 0, NULL, '2025-10-10 09:27:58'),
(45, 'biba', '1', 71000, 'cash', 'baru', 0, NULL, '2025-10-10 10:27:39'),
(46, 'bui', '5', 13000, 'cash', 'selesai', 0, NULL, '2025-10-10 10:28:04'),
(47, 'bui', '5', 30000, 'cash', 'baru', 0, NULL, '2025-10-10 10:30:45'),
(48, 'pstimah', '2', 26000, 'cash', 'baru', 0, NULL, '2025-10-10 10:36:26'),
(49, 'pstimah', '2', 26000, 'cash', 'baru', 0, NULL, '2025-10-10 10:51:57'),
(50, 'pstimah', '5', 26000, 'cash', 'baru', 0, NULL, '2025-10-10 10:55:06'),
(51, 'xjjzixi', '2', 28000, 'cash', 'selesai', 0, NULL, '2025-10-10 10:58:00'),
(52, 'qngg', '5', 79000, 'qris', 'baru', 0, 'uploads/qris_1760094361.jpg', '2025-10-10 11:06:01'),
(53, 'asdas', '3', 30000, 'cash', 'baru', 0, NULL, '2025-10-10 11:57:38'),
(54, 'naget', '4', 30000, 'cash', 'baru', 0, NULL, '2025-10-13 08:24:05'),
(55, 'Firman', '2', 26000, 'cash', 'baru', 0, NULL, '2025-10-13 15:24:23'),
(56, 'anggi', '4', 28000, 'qris', 'baru', 0, 'uploads/qris_1760372850.jpg', '2025-10-13 16:27:30'),
(57, 'asep', '7', 15000, 'cash', 'baru', 0, NULL, '2025-10-13 17:00:53'),
(58, 'sena', '15', 19000, 'qris', 'baru', 0, 'uploads/qris_1760374921.jpg', '2025-10-13 17:02:01'),
(59, 'anggi', '3', 19000, 'cash', 'baru', 0, NULL, '2025-10-13 17:02:28'),
(60, 'Firman', '2', 30000, 'cash', 'baru', 0, NULL, '2025-10-13 17:02:49'),
(61, 'ADAfw', '2', 30000, 'cash', 'baru', 0, NULL, '2025-10-20 09:58:56');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `menu_id` int NOT NULL,
  `menu_name` varchar(255) NOT NULL,
  `variant` enum('hot','ice') DEFAULT 'hot',
  `qty` int NOT NULL,
  `price` int NOT NULL,
  `note` text,
  `subtotal` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `menu_id`, `menu_name`, `variant`, `qty`, `price`, `note`, `subtotal`) VALUES
(1, 1, 8, 'Cappuccino', 'hot', 4, 15000, '111', 60000),
(2, 1, 8, 'Cappuccino', 'ice', 4, 19000, '1111', 76000),
(3, 1, 9, 'Caramel Machiatos', 'hot', 5, 15000, '0', 75000),
(4, 2, 7, 'Cafe Latte', 'hot', 5, 15000, '0', 75000),
(5, 2, 7, 'Cafe Latte', 'ice', 5, 19000, '0', 95000),
(6, 2, 14, 'Gamel Ice Coffee', 'hot', 5, 15000, '0', 75000),
(7, 2, 14, 'Gamel Ice Coffee', 'ice', 4, 19000, '0', 76000),
(8, 3, 9, 'Caramel Machiatos', 'hot', 5, 15000, '0', 75000),
(9, 4, 6, 'Americano', 'hot', 4, 13000, '0', 52000),
(10, 4, 6, 'Americano', 'ice', 4, 15000, '0', 60000),
(11, 4, 5, 'Espresso', 'hot', 1, 10000, '0', 10000),
(12, 4, 10, 'Mocca', 'hot', 1, 15000, '0', 15000),
(13, 4, 11, 'Vanilla Latte', 'hot', 2, 15000, '0', 30000),
(14, 4, 20, 'Choco Hazelnut', 'hot', 4, 15000, '0', 60000),
(15, 4, 15, 'Green Tea Latte', 'hot', 3, 15000, '0', 45000),
(16, 5, 7, 'Cafe Latte', 'hot', 4, 15000, '0', 60000),
(17, 5, 7, 'Cafe Latte', 'ice', 3, 19000, '0', 57000),
(18, 5, 14, 'Gamel Ice Coffee', 'hot', 4, 15000, '0', 60000),
(19, 6, 6, 'Americano', 'hot', 5, 13000, '0', 65000),
(20, 6, 6, 'Americano', 'ice', 4, 15000, '0', 60000),
(21, 6, 8, 'Cappuccino', 'hot', 3, 15000, '0', 45000),
(22, 6, 8, 'Cappuccino', 'ice', 2, 19000, '0', 38000),
(23, 6, 14, 'Gamel Ice Coffee', 'hot', 2, 15000, '0', 30000),
(24, 7, 6, 'Americano', 'hot', 3, 13000, 'adasdasdsa', 39000),
(25, 7, 7, 'Cafe Latte', 'ice', 1, 19000, '', 19000),
(26, 8, 6, 'Americano', 'hot', 2, 13000, 'asfasfas', 26000),
(27, 8, 34, 'Tahu Krispi', 'hot', 5, 10000, 'rjg', 50000),
(28, 8, 6, 'Americano', 'ice', 2, 15000, 'asfasfas', 30000),
(29, 9, 6, 'Americano', 'hot', 1, 13000, 'gAGAGGA', 13000),
(30, 10, 42, 'Kwetiau', 'hot', 2, 15000, '', 30000),
(31, 11, 34, 'Tahu Krispi', 'hot', 3, 10000, 'pedesssss', 30000),
(32, 12, 34, 'Tahu Krispi', 'hot', 5, 10000, 'enakkk', 50000),
(33, 13, 6, 'Americano', 'hot', 3, 13000, 'ggy', 39000),
(34, 13, 34, 'Tahu Krispi', 'hot', 2, 10000, 'pedas', 20000),
(35, 14, 46, 'Pisang Cripsy', 'hot', 8, 15000, '', 120000),
(36, 14, 42, 'Kwetiau', 'hot', 3, 15000, '', 45000),
(37, 15, 38, 'Ayam Geprek', 'hot', 3, 13000, '', 39000),
(38, 16, 5, 'Espresso', 'hot', 4, 10000, '', 40000),
(39, 17, 42, 'Kwetiau', 'hot', 1, 15000, '', 15000),
(40, 18, 40, 'Nasi Goreng Ayam', 'hot', 3, 16000, '', 48000),
(41, 18, 42, 'Kwetiau', 'hot', 2, 15000, 'sapi', 30000),
(42, 19, 5, 'Espresso', 'hot', 3, 10000, '', 30000),
(43, 20, 5, 'Espresso', 'hot', 4, 10000, '', 40000),
(44, 21, 6, 'Americano', 'hot', 1, 13000, '', 13000),
(45, 21, 6, 'Americano', 'ice', 4, 15000, '', 60000),
(46, 22, 6, 'Americano', 'hot', 2, 13000, '', 26000),
(47, 22, 7, 'Cafe Latte', 'hot', 2, 15000, '', 30000),
(48, 23, 5, 'Espresso', 'hot', 2, 10000, '', 20000),
(49, 24, 6, 'Americano', 'ice', 5, 15000, '', 75000),
(50, 24, 8, 'Cappuccino', 'hot', 1, 15000, '', 15000),
(51, 25, 8, 'Cappuccino', 'hot', 2, 15000, '', 30000),
(52, 26, 40, 'Nasi Goreng Ayam', 'hot', 2, 16000, '', 32000),
(53, 27, 5, 'Espresso', 'hot', 2, 10000, '', 20000),
(54, 27, 8, 'Cappuccino', 'hot', 1, 15000, '', 15000),
(55, 28, 31, 'Kentang Goreng', 'hot', 3, 12000, '', 36000),
(56, 28, 32, 'Bakso Goreng', 'hot', 1, 12000, '', 12000),
(57, 29, 5, 'Espresso', 'hot', 2, 10000, '', 20000),
(58, 30, 5, 'Espresso', 'hot', 2, 10000, '', 20000),
(59, 30, 8, 'Cappuccino', 'ice', 2, 19000, '', 38000),
(60, 31, 31, 'Kentang Goreng', 'hot', 1, 12000, '', 12000),
(61, 31, 32, 'Bakso Goreng', 'hot', 2, 12000, '', 24000),
(62, 32, 31, 'Kentang Goreng', 'hot', 1, 12000, '', 12000),
(63, 32, 32, 'Bakso Goreng', 'hot', 1, 12000, '', 12000),
(64, 33, 31, 'Kentang Goreng', 'hot', 1, 12000, '', 12000),
(65, 34, 8, 'Cappuccino', 'hot', 1, 15000, '', 15000),
(66, 35, 5, 'Espresso', 'hot', 2, 10000, '', 20000),
(67, 36, 10, 'Mocca', 'hot', 2, 15000, '', 30000),
(68, 37, 11, 'Vanilla Latte', 'hot', 2, 15000, '', 30000),
(69, 38, 13, 'Coffee Gula Aren', 'hot', 2, 15000, '', 30000),
(70, 39, 5, 'Espresso', 'hot', 2, 10000, '', 20000),
(71, 40, 5, 'Espresso', 'hot', 1, 10000, '', 10000),
(72, 41, 5, 'Espresso', 'hot', 1, 10000, '', 10000),
(73, 42, 5, 'Espresso', 'hot', 1, 10000, '', 10000),
(74, 43, 6, 'Americano', 'hot', 2, 13000, '', 26000),
(75, 44, 5, 'Espresso', 'hot', 3, 10000, '', 30000),
(76, 45, 6, 'Americano', 'hot', 2, 13000, 'dffg', 26000),
(77, 45, 10, 'Mocca', 'hot', 3, 15000, '', 45000),
(78, 46, 6, 'Americano', 'hot', 1, 13000, '', 13000),
(79, 47, 41, 'Nasi Telur Krispi', 'hot', 2, 15000, '', 30000),
(80, 48, 6, 'Americano', 'hot', 2, 13000, '', 26000),
(81, 49, 6, 'Americano', 'hot', 2, 13000, '', 26000),
(82, 50, 6, 'Americano', 'hot', 2, 13000, '', 26000),
(83, 51, 6, 'Americano', 'hot', 1, 13000, '', 13000),
(84, 51, 6, 'Americano', 'ice', 1, 15000, '', 15000),
(85, 52, 10, 'Mocca', 'hot', 3, 15000, '', 45000),
(86, 52, 10, 'Mocca', 'ice', 1, 19000, '', 19000),
(87, 52, 12, 'Hazelnut Latte', 'hot', 1, 15000, '', 15000),
(88, 53, 7, 'Cafe Latte', 'hot', 2, 15000, '', 30000),
(89, 54, 8, 'Cappuccino', 'hot', 2, 15000, '', 30000),
(90, 55, 6, 'Americano', 'hot', 2, 13000, 'tanpa gula', 26000),
(91, 56, 6, 'Americano', 'hot', 1, 13000, '', 13000),
(92, 56, 7, 'Cafe Latte', 'hot', 1, 15000, '', 15000),
(93, 57, 7, 'Cafe Latte', 'hot', 1, 15000, '', 15000),
(94, 58, 8, 'Cappuccino', 'ice', 1, 19000, '', 19000),
(95, 59, 23, 'Milkshake', 'ice', 1, 19000, '', 19000),
(96, 60, 6, 'Americano', 'ice', 2, 15000, 'tanpa gula', 30000),
(97, 61, 46, 'Pisang Cripsy', 'hot', 2, 15000, '', 30000);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_created_at` (`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_id` (`menu_id`),
  ADD KEY `idx_order_items_menu_id` (`menu_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
