-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 07, 2026 at 04:58 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `onelivesched`
--

-- --------------------------------------------------------

--
-- Table structure for table `queue`
--

CREATE TABLE `queue` (
  `id` int(11) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `date_schedule` date NOT NULL,
  `time_schedule` time NOT NULL,
  `staff_confirmed` varchar(255) NOT NULL,
  `seller_photo` varchar(255) DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `queue`
--

INSERT INTO `queue` (`id`, `client_name`, `date_schedule`, `time_schedule`, `staff_confirmed`, `seller_photo`, `created_at`) VALUES
(14, 'Dexter - Onelive', '2026-08-07', '16:00:00', 'Jay-R Robielos', 'uploads/seller_6a7582d3c34bc8.10622388.png', '2026-08-07 07:01:39'),
(15, 'Florafe Almuete - RDR', '2026-08-08', '16:00:00', 'Jay-R Robielos', 'uploads/seller_6a75b827d53476.35920215.jpg', '2026-08-07 10:49:11'),
(16, 'MSD- ONELIVE', '2026-08-15', '16:00:00', 'Jay-R Robielos', 'uploads/seller_6a75dd1435cbd4.51793160.jpg', '2026-08-07 13:26:44'),
(17, 'Treasure\'s of Sansan', '2026-08-08', '17:00:00', 'Jay-R Robielos', 'uploads/seller_6a76b72ad1a6a7.44902917.png', '2026-08-08 04:57:14'),
(18, 'KTIN Gutierrez - Onelive Picture Taking', '2026-08-10', '16:00:00', 'Jay-R Robielos', 'uploads/seller_6a796d8e6b1773.83193377.png', '2026-08-10 06:19:58'),
(19, 'Mary Ann Pilapil - Onelive Picture Taking', '2026-08-10', '16:00:00', 'Jay-R Robielos', 'uploads/seller_6a7970e4e52342.80543108.jpeg', '2026-08-10 06:34:12'),
(20, 'Richard Magallon Japson -  RDR Warehouse Visit', '2026-08-11', '10:00:00', 'Jay-R Robielos', 'uploads/seller_6a79b53075dd45.94477179.png', '2026-08-10 11:25:36'),
(21, 'Jhaika Japan', '2026-09-22', '15:00:00', 'Jay-R Robielos', 'uploads/seller_6a79c2c5d83c31.53541081.jpg', '2026-08-10 12:23:33'),
(22, 'Jhaika Japan', '2026-09-23', '15:00:00', 'Jay-R Robielos', 'uploads/seller_6a79c2e6e7e312.41635537.jpg', '2026-08-10 12:24:06'),
(23, 'Gwen Gaspar - Onelive Picture Taking', '2026-08-11', '14:00:00', 'Jay-R Robielos', 'uploads/seller_6a7a9c2a6f6353.20005160.png', '2026-08-11 03:51:06'),
(24, 'Ms.Gaile', '2026-08-29', '15:00:00', 'Jay-R Robielos', '', '2026-08-11 07:15:53'),
(25, 'Pamela Dy - RDR Warehouse Visit', '2026-08-12', '13:00:00', 'Jay-R Robielos', '', '2026-08-11 11:57:33'),
(26, 'Jollymie Alafriz Dacuyan', '2026-08-12', '15:00:00', 'Jay-R Robielos', 'uploads/seller_6a7b0f2649f116.39991085.png', '2026-08-11 12:01:42'),
(27, 'Jesselle Ann Razon - RDR Warehouse Visit', '2026-08-12', '11:00:00', 'Jay-R Robielos', '', '2026-08-11 12:24:59'),
(29, 'Luxx4Less - Onelive Live Selling', '2026-08-14', '10:00:00', 'Jay-R Robielos', 'uploads/seller_6a7d96237f0f87.65676421.png', '2026-08-13 10:02:11'),
(30, 'Florafe Almuete - RDR Live Selling', '2026-08-15', '14:00:00', 'Jay-r Robielos', 'uploads/seller_6a7da987a728a1.28973814.jpg', '2026-08-13 11:24:55'),
(31, 'Cathy Pullan', '2026-08-15', '15:00:00', 'Jay-r Robielos', '', '2026-08-13 11:48:25'),
(32, 'Justine Tamayo - RDR Picture Taking', '2026-08-14', '11:00:00', 'Jay-r Robielos', '', '2026-08-13 13:09:29'),
(33, 'MSD- ONELIVE Live Selling', '2025-08-15', '16:00:00', 'Jay-r Robielos', 'uploads/seller_6a7eb5bc8b0e70.55629220.jpg', '2026-08-14 06:29:16'),
(34, 'LUNAVI - ONELIVE LIVE SELLING', '2026-08-15', '16:00:00', 'Jay-r Robielos', 'uploads/seller_6a7ece9a4f76d7.82922181.png', '2026-08-14 08:15:22'),
(35, 'Luxx4Less - Onelive Live Selling', '2026-08-15', '11:00:00', 'Jay-r Robielos', 'uploads/seller_6a7fb9be4b7a98.54465014.png', '2026-08-15 00:58:38'),
(36, 'Gwen Gaspar - Onelive Picture Taking', '2025-08-15', '16:00:00', 'Jay-r Robielos', 'uploads/seller_6a7fee19e3fbf8.86226314.png', '2026-08-15 04:42:01'),
(37, 'Treasure\'s of Sansan', '2026-08-15', '15:00:00', 'Jay-r Robielos', 'uploads/seller_6a80077cd9a185.92173803.png', '2026-08-15 06:30:20'),
(38, 'Jocylene Basbas - RDR Warehouse Visit', '2026-08-19', '13:00:00', 'Jay-r Robielos', '', '2026-08-15 06:47:13'),
(39, 'Jons - Onelive Studio Live Selling', '2026-08-15', '16:30:00', 'Jay-r Robielos', '', '2026-08-15 06:55:20'),
(40, 'Shai Micah - Luxxe Trust Visit', '2026-08-17', '16:00:00', 'Yla', '', '2026-08-17 03:50:28'),
(41, 'Jons Garlit - Live Selling', '2026-08-17', '16:30:00', 'Kym', 'uploads/seller_6a828785c7baa3.02842582.png', '2026-08-17 04:01:09'),
(42, 'Priti ', '2026-08-17', '17:01:00', 'Kym', '', '2026-08-17 07:01:55'),
(44, 'Dani Baretto - Visit', '2026-08-21', '19:00:00', 'Jay-r Robielos', 'uploads/seller_6a82ebb3f25ff6.70110969.jpeg', '2026-08-17 11:08:35'),
(45, 'Jinky Thompson - Live Selling', '2026-08-19', '19:00:00', 'Kym', 'uploads/seller_6a82ec0e3dc641.73667596.png', '2026-08-17 11:10:06'),
(46, 'Mary Cruz - Visit ', '2026-08-19', '19:00:00', 'Kym', 'uploads/seller_6a82ec330870b5.21285967.jpeg', '2026-08-17 11:10:43'),
(48, 'Auie G. Bautista - RDR Warehouse Visit', '2026-08-20', '10:30:00', 'Jay-r Robielos', '', '2026-08-19 11:43:45'),
(49, 'Seth Malabad - RDR Warehouse Visit', '2026-08-20', '13:00:00', 'Jay-r Robielos', '', '2026-08-19 11:44:58'),
(50, 'Jenny S', '2026-08-21', '10:00:00', 'Kym', '', '2026-08-20 03:11:09'),
(51, 'AMARA SHIA - ONELIVE VISIT FOR CONSIGNMENT', '2026-08-20', '12:00:00', 'Jay-r Robielos', 'uploads/seller_6a867450ac73d4.08151549.png', '2026-08-20 03:28:16'),
(52, 'King alahas gold', '2026-08-21', '11:00:00', 'Kym', '', '2026-08-20 07:56:21'),
(53, 'Mary Ann Pilapil -  Live Schedule', '2026-08-31', '18:00:00', 'Kym', '', '2026-08-21 03:52:21'),
(54, 'RAID MY CLOSET ( CATHY LACSON) - LIVE', '2026-08-21', '15:00:00', 'JEROME GACELO', 'uploads/seller_6a87ccd2696d46.78012972.png', '2026-08-21 03:58:10'),
(55, 'Elize Ohana', '2026-08-21', '15:00:00', 'Kym', '', '2026-08-21 04:47:56'),
(56, 'SVIP Gwen Gaspar (picture taking)', '2026-08-22', '13:00:00', 'Kym', '', '2026-08-22 02:45:14'),
(57, 'China Roces', '2026-08-26', '13:00:00', 'Jay-r Robielos', 'uploads/seller_6a8957f86d8c73.82246680.jpg', '2026-08-22 08:04:08'),
(58, 'Marissa Fabio - OneLive Live Selling', '2026-08-24', '15:00:00', 'Jay-r Robielos', 'uploads/seller_6a896c2ce27aa0.16605424.png', '2026-08-22 09:30:20'),
(59, 'Irish Clotatio - RDR Visit', '2026-08-24', '14:00:00', 'Kym', '', '2026-08-24 02:21:50'),
(60, 'Michelle Andrei Dominguez - Onelive Live Seling', '2026-08-28', '12:00:00', 'Jay-r Robielos', 'uploads/seller_6a8c1b45cb4866.42642800.png', '2026-08-24 10:21:57'),
(61, 'Jarret Pulido - Photoshoot', '2026-08-26', '21:00:00', 'Kym', '', '2026-08-26 01:26:37'),
(62, 'Cathy Pullan', '2026-08-29', '15:00:00', 'Kym', 'uploads/seller_6a8e48fb47ef29.64658940.png', '2026-08-26 01:27:22'),
(63, 'China Roces - Onelive Live Selling', '2026-08-27', '13:00:00', 'Jay-r Robielos', 'uploads/seller_6a8eff8dbe7678.39725508.jpg', '2026-08-26 15:00:29'),
(65, 'Lara Gumpal - Onelive Live Selling', '2026-08-28', '16:00:00', 'Jay-r Robielos', 'uploads/seller_6a8fa9cf904242.83002135.jpg', '2026-08-27 03:06:55'),
(66, 'Yanyan - Onelive Live Selling', '2026-08-28', '15:00:00', 'Jay-r Robielos', 'uploads/seller_6a90e6909fdca3.45697829.png', '2026-08-28 01:38:24'),
(67, 'China Roces - Onelive Live Selling', '2026-08-29', '13:00:00', 'Jay-r Robielos', 'uploads/seller_6a90f80eb60560.93292224.jpg', '2026-08-28 02:53:02'),
(68, 'Treasure\'s of Sansan - Live Selling', '2026-08-29', '13:00:00', 'Jay-r Robielos', 'uploads/seller_6a9155bf0e0950.58429641.png', '2026-08-28 09:32:47'),
(69, 'Bernadette RDR - live selling', '2026-08-31', '15:00:00', 'Kym', '', '2026-08-29 00:06:46'),
(70, 'BERNADETH LAYO - RDR LIVE SELLING', '2026-09-02', '15:00:00', 'Jay-r Robielos', '', '2026-09-01 04:10:21'),
(71, 'MARYANN PILAPIL - ONELIVE LIVE SELLING', '2026-09-01', '18:00:00', 'Jay-r Robielos', '', '2026-09-01 04:10:52'),
(72, 'AMARA SHIA - ONELIVE VISIT FOR CONSIGNMENT', '2026-09-03', '11:00:00', 'Jay-r Robielos', 'uploads/seller_6a98e54ca8ab07.61746343.png', '2026-09-03 03:11:08'),
(74, 'LOIS - LIVE SCHEDULE', '2026-09-05', '17:00:00', 'JEROME GACELO', 'uploads/seller_6a9a8afe983079.13939967.png', '2026-09-04 09:10:22'),
(75, 'MSD JEWELRIES - LIVE SCHEDULE', '2026-09-05', '16:00:00', 'JEROME GACELO', 'uploads/seller_6a9ab14e089b04.90435739.png', '2026-09-04 11:53:50'),
(76, 'Bernadeth Layo', '2026-09-05', '17:00:00', 'Jay-r Robielos', '', '2026-09-05 00:31:50'),
(79, 'Ms. Coco - LIVE SCHEDULE', '2026-09-05', '13:00:00', 'JEROME GACELO', 'uploads/seller_6a9b83424aafd2.24783118.png', '2026-09-05 02:49:38'),
(80, 'HAZEL SUMATRA - RDR (VISIT)', '2026-09-08', '18:00:00', 'JAY ROBIELOS', '', '2026-09-05 03:57:32'),
(81, 'Rovelyn Fortaleza - Live selling RDR', '2026-09-12', '14:00:00', 'Kym', '', '2026-09-05 09:10:52'),
(82, 'Mommy Inengs - Live Selling', '2026-09-05', '14:14:00', 'Kym', 'uploads/seller_6a9bdebaa70201.78634373.jfif', '2026-09-05 09:14:27'),
(83, 'Florence Cruz - RDR (Visit)', '2026-09-07', '13:00:00', 'Jay-r Robielos', '', '2026-09-05 10:01:33'),
(84, 'Marife Guevarra - RDR Visit', '2026-09-07', '13:00:00', 'Jay-r Robielos', '', '2026-09-05 10:01:55'),
(85, 'Elijah Wayne Chuacuco', '2026-09-08', '11:00:00', 'Jay-r Robielos', 'uploads/seller_6a9f6f333a87d1.82289896.jpg', '2026-09-05 10:02:29'),
(86, 'Alnie Shayne Delima - RDR Live Selling', '2026-09-07', '19:00:00', 'Jay-r Robielos', 'uploads/seller_6a9e33dd131267.96278830.jpg', '2026-09-07 03:47:41'),
(87, 'Fredirch Eusoya - RDR Visit', '2026-09-07', '13:00:00', 'Jay-r Robielos', '', '2026-09-07 03:48:34'),
(88, 'Bernadeth Layo - RDR Live Selling', '2026-09-07', '14:00:00', 'Jay-r Robielos', 'uploads/seller_6a9e373ce1dc85.77905471.jpg', '2026-09-07 04:02:04'),
(89, 'Zeenat Malino - RDR Visit', '2026-09-09', '20:00:00', 'Jay-r Robielos', '', '2026-09-08 08:40:32'),
(90, 'Remegel Mandahuyan - RDR Visit', '2026-09-09', '13:00:00', 'Jay-r Robielos', '', '2026-09-08 08:43:50'),
(91, 'Stephen and Kiray', '2026-09-14', '13:00:00', 'Jay-r Robielos', 'uploads/seller_6aa0dcf9019d75.55494428.jpg', '2026-09-08 11:01:26'),
(92, 'Lannie Dellosa - RDR live selling', '2026-09-09', '11:00:00', 'Kym', '', '2026-09-09 01:45:01'),
(93, 'A.P Lim - RDR Visit', '2026-09-12', '20:00:00', 'Jay-r Robielos', '', '2026-09-09 06:55:40'),
(94, 'Rochelle Solano - RDR Visit ', '2026-09-10', '11:00:00', 'Jay-r Robielos', '', '2026-09-09 10:13:48'),
(95, 'Bernadeth Layo - RDR Live Selling', '2026-09-12', '19:00:00', 'Jay-r Robielos', 'uploads/seller_6aa139cf34d1e8.38949886.jpg', '2026-09-09 10:49:51'),
(96, 'Sophia Mara - RDR Visit', '2026-09-10', '15:00:00', 'Jay-r Robielos', '', '2026-09-09 10:51:16'),
(97, 'Darrell Llesis - RDR Visit', '2026-09-12', '15:00:00', 'Jay-r Robielos', '', '2026-09-10 09:55:02'),
(99, 'Marissa Fabio - Onelive Live Selling', '2026-09-12', '15:00:00', 'Jay-r Robielos', '', '2026-09-11 07:53:57'),
(100, 'Aileen Manalo - RDR Visit', '2026-09-12', '14:00:00', 'Jay-r Robielos', '', '2026-09-11 10:05:39'),
(102, 'Marichu Gabrillo - RDR Visit', '2026-09-12', '16:00:00', 'Jay-r Robielos', '', '2026-09-12 04:57:25'),
(103, 'Ma. Christina Manalo - RDR Visit', '2026-09-12', '16:00:00', 'Jay-r Robielos', '', '2026-09-12 04:58:22'),
(104, 'Jhersy Malasan - RDR Visit', '2026-09-12', '10:00:00', 'Jay-r Robielos', '', '2026-09-12 04:58:52'),
(105, 'Fe Holasca - RDR Visit', '2026-09-12', '15:00:00', 'Jay-r Robielos', '', '2026-09-12 05:00:00'),
(106, 'Marge Padua', '2026-09-12', '15:00:00', 'Jay-r Robielos', '', '2026-09-12 05:00:21'),
(107, 'C&M jewelry - visit picture', '2026-09-14', '11:30:00', 'Kym', '', '2026-09-14 05:02:49'),
(108, 'Salve Bautista - RDR visit ', '2026-09-14', '13:30:00', 'Kym', '', '2026-09-14 05:03:18'),
(109, 'Elicar Padua - sister Marge / Live Selling', '2026-09-14', '16:00:00', 'Kym', '', '2026-09-14 05:04:25'),
(110, 'Cathy Pullan - RDR live selling ', '2026-09-17', '15:00:00', 'Kym', '', '2026-09-14 07:06:15'),
(111, 'Cathy Pullan- RDR live selling', '2026-09-19', '15:00:00', 'Kym', '', '2026-09-14 07:06:33'),
(112, 'Paul Steven Lim - RDR Visit', '2026-09-14', '20:00:00', 'Kym', '', '2026-09-14 07:22:06'),
(113, 'China Roces / Grace - Live Selling', '2026-09-14', '16:00:00', 'Kym', '', '2026-09-14 07:46:17'),
(114, 'Emak Co - RDR Visit', '2026-09-15', '11:00:00', 'Kym', '', '2026-09-15 01:29:27'),
(115, 'Rhoda May Conde - Conde RDR Visit', '2026-09-15', '13:00:00', 'Kym', '', '2026-09-15 01:30:10'),
(116, 'Rove Tulay Fortaleza - RDR live selling', '2026-09-16', '16:00:00', 'Kym', '', '2026-09-15 01:30:59'),
(117, 'Ann C. Yap - RDR visit', '2026-09-21', '13:00:00', 'Kym', '', '2026-09-15 01:31:56'),
(118, 'Lourdes Francisco - RDR Visit', '2026-09-15', '11:30:00', 'Kym', '', '2026-09-15 02:08:24'),
(119, 'Katrina Medina - RDR Visit', '2026-09-15', '13:00:00', 'Jay-r Robielos', '', '2026-09-15 02:13:22'),
(120, 'Kitty Castro Uy - RDR visit', '2026-09-19', '10:00:00', 'Kym', '', '2026-09-15 03:14:25'),
(121, 'Girlie Salvadora - RDR Visit', '2026-09-18', '14:00:00', 'Jay-r Robielos', '', '2026-09-15 05:10:04'),
(122, 'Erlyn Abesamis - RDR Live Selling', '2026-09-16', '12:00:00', 'Kym', '', '2026-09-16 02:04:47'),
(123, 'Rosemarie - Live Selling', '2026-10-16', '12:00:00', 'Kym', '', '2026-09-16 02:05:40'),
(124, 'Rose Marie RDR - Live Selling', '2026-10-17', '12:00:00', 'Kym', '', '2026-09-16 02:06:04'),
(126, 'Matthew Viñas - RDR Visit', '2026-10-08', '13:00:00', 'Jay-r Robielos', '', '2026-09-16 07:29:25'),
(127, 'Rhulyn Hijos - RDR Live Selling', '2026-09-17', '17:00:00', 'Jay-r Robielos', '', '2026-09-16 09:58:14'),
(128, 'Renz Mark Villaseñor - RDR Visit', '2026-09-17', '21:00:00', 'Jay-r Robielos', '', '2026-09-17 02:55:57'),
(129, 'Cez Aguilar - RDR Visit', '2026-09-18', '13:00:00', 'Jay-r Robielos', '', '2026-09-17 08:58:52'),
(130, 'Venus Dela Paz - RDR VIsit', '2026-09-24', '14:00:00', 'Jay-r Robielos', '', '2026-09-17 10:25:43'),
(131, 'Lemon Abegail Martes- RDR Visit', '2026-09-26', '12:00:00', 'Jay-r Robielos', '', '2026-09-17 10:50:00'),
(132, 'Darrell Llesis - RDR Visit ', '2026-09-19', '16:00:00', 'Jay-r Robielos', '', '2026-09-17 10:58:04'),
(133, 'Emily Nonato - RDR Visit', '2026-09-19', '11:00:00', 'Jay-r Robielos', '', '2026-09-18 09:09:35'),
(134, 'Jenica Bascon - RDR Visit', '2026-09-24', '13:00:00', 'Jay-r Robielos', '', '2026-09-18 10:50:25'),
(135, 'Susan Algura - RDR Visit', '2026-09-19', '16:00:00', 'Jay-r Robielos', '', '2026-09-19 04:39:26'),
(136, 'Lara Gumpal - Onelive Live Selling', '2026-09-19', '16:00:00', 'Jay-r Robielos', 'uploads/seller_6aae12450e29a0.41348225.jpg', '2026-09-19 04:40:37'),
(137, 'China Roces - Onelive Live Selling', '2026-09-21', '14:00:00', 'Jay-r Robielos', 'uploads/seller_6ab0b38c9460e0.77949849.jpg', '2026-09-21 04:33:16'),
(138, 'Marge Padua- OneLive Live Selling', '2026-09-21', '16:00:00', 'Jay-r Robielos', '', '2026-09-21 04:35:15'),
(139, 'Janice Santos - RDR Live Selling', '2026-10-05', '16:00:00', 'Jay-r Robielos', '', '2026-09-22 09:22:05'),
(140, 'Anna Leah Naviza - Onelive Live Selling', '2026-09-25', '16:00:00', 'Jay-r Robielos', '', '2026-09-23 08:39:57'),
(141, 'Jenelyn Caminade - RDR Visit', '2026-09-26', '15:00:00', 'Jay-r Robielos', '', '2026-09-24 05:56:16'),
(143, 'Lara Gumpal - Onelive Live Selling', '2026-09-25', '15:00:00', 'Reyzel Taruc', '', '2026-09-24 08:56:43'),
(144, 'Susan Algura - RDR Visit', '2026-09-26', '16:00:00', 'Jay-r Robielos', '', '2026-09-24 09:24:55'),
(145, 'Maria Erlinda Laranang - RDR Visit', '2026-09-26', '11:00:00', 'Jay-r Robielos', '', '2026-09-24 09:41:47'),
(146, 'MARGE PADUA', '2026-09-26', '19:00:00', 'Reyzel Taruc', '', '2026-09-25 08:25:13'),
(147, 'Erlyn Abesamis - RDR Live Selling', '2026-09-26', '16:00:00', 'Jay-r Robielos', '', '2026-09-25 08:30:09'),
(148, 'Erlyn Abesamis - RDR Live Selling', '2026-10-01', '14:00:00', 'Jay-r Robielos', '', '2026-09-25 08:30:30'),
(149, 'CLASSY COATS (JULIE) - RDR Live Selling', '2026-09-26', '19:30:00', 'REYZEL TARUC', '', '2026-09-26 03:16:50'),
(151, 'Rhoda May Conde - RDR Live Selling', '2026-10-01', '12:00:00', 'Jay-r Robielos', '', '2026-09-26 05:52:22'),
(152, 'JHAIKA JAPAN - ONELIVE', '2026-09-29', '15:00:00', 'REYZEL', '', '2026-09-29 02:41:55'),
(153, 'Julie Ann Dela Cruz ( Classy Coats)- RDR', '2026-09-29', '13:00:00', 'Jay-r Robielos', '', '2026-09-29 06:08:06'),
(154, 'LEEH LUGSQUARE - ONELIVE', '2026-10-02', '13:00:00', 'REYZEL TARUC', 'uploads/seller_6abb89503d8404.06529646.png', '2026-09-29 09:48:00'),
(155, 'ALNIE SHAYNE DELIMA - RDR LIVE SELLING', '2026-09-29', '19:00:00', 'Jay-r Robielos', 'uploads/seller_6abb9328da09b7.51974056.jpg', '2026-09-29 10:30:00'),
(156, 'Karen Gapasin  - RDR Live Selling', '2026-10-01', '11:00:00', 'Jay-r Robielos', '', '2026-09-30 02:35:41'),
(157, 'Shammah Yabut ( Coco Koronadal) - RDR Live Selling', '2026-09-30', '13:00:00', 'Jay-r Robielos', '', '2026-09-30 02:37:36'),
(158, 'JHAIKA JAPAN - ONELIVE', '2026-09-30', '15:00:00', 'REYZEL TARUC', '', '2026-09-30 05:40:52'),
(160, 'Rose Marie Cancino - RDR Live Selling', '2026-10-16', '15:00:00', 'Jay-r Robielos', '', '2026-09-30 10:10:28'),
(161, 'Rose Marie Cancino - RDR Live Selling', '2026-10-17', '15:00:00', 'Jay-r Robielos', '', '2026-09-30 10:10:46'),
(162, 'Maria Erlinda Laranang - RDR Visit', '2026-10-03', '11:00:00', 'Jay-r Robielos', '', '2026-10-01 06:29:50'),
(163, 'Bart Domingo - RDR Visit', '2026-10-03', '14:00:00', 'Jay-r Robielos', '', '2026-10-01 07:02:24'),
(164, 'MSD (Diana Banilla) ONELIVE', '2026-10-03', '16:00:00', 'REYZEL TARUC', '', '2026-10-02 02:44:27'),
(165, 'Marissa Fabio ONLIVE', '2026-10-02', '15:00:00', 'REYZEL TARUC', '', '2026-10-02 03:00:12'),
(166, 'Erlyn Abesamis - RDR Live Selling', '2026-10-03', '11:00:00', 'Jay-r Robielos', '', '2026-10-03 02:26:04'),
(167, 'Shammah Yabut ( Coco Koronadal) - RDR Live Selling', '2026-10-03', '19:00:00', 'Jay-r Robielos', '', '2026-10-03 02:26:22'),
(168, 'MSD (Diana Banilla) ONELIVE', '2026-10-06', '16:00:00', 'REYZEL TARUC', '', '2026-10-05 05:56:22'),
(169, 'Jean Rose Barangay - RDR Visit', '2026-10-05', '14:00:00', 'jay', '', '2026-10-05 07:04:22'),
(170, 'Maezil Celis - RDR Visit', '2026-10-05', '16:00:00', 'Jay-r Robielos', '', '2026-10-05 07:04:56'),
(171, 'Julie Ann Dela Cruz ( Classy Coats)- RDR Live Selling', '2026-09-05', '16:00:00', 'Jay-r Robielos', '', '2026-10-05 07:05:34'),
(172, 'Shammah Yabut ( Coco Koronadal) - RDR Live Selling', '2026-10-05', '15:00:00', 'Jay-r Robielos', '', '2026-10-05 07:05:56'),
(173, 'Julie Ann Dela Cruz ( Classy Coats)- RDR Live Selling', '2026-10-05', '16:00:00', 'Jay-r Robielos', '', '2026-10-05 07:07:50'),
(174, 'Carol Arcilla - RDR Visit', '2026-10-05', '17:00:00', 'Jay-r Robielos', '', '2026-10-05 07:10:04'),
(175, 'JN JEWELRY-ONELIVE', '2026-10-08', '10:00:00', 'REYZEL TARUC', '', '2026-10-05 09:30:24'),
(176, 'LAURICE LEEH LUGLUG - ONLIVE', '2026-10-06', '19:00:00', 'REYZEL TARUC', '', '2026-10-05 11:03:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `queue`
--
ALTER TABLE `queue`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `queue`
--
ALTER TABLE `queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=177;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
