-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 18, 2026 at 01:35 PM
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
-- Database: `bloodborne`
--

-- --------------------------------------------------------

--
-- Table structure for table `hunters`
--

CREATE TABLE `hunters` (
  `hunter_id` int(10) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` int(255) NOT NULL,
  `role` varchar(100) NOT NULL,
  `blood_echoes` int(11) NOT NULL,
  `insight` int(11) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hunters`
--

INSERT INTO `hunters` (`hunter_id`, `username`, `email`, `password`, `role`, `blood_echoes`, `insight`, `date_created`) VALUES
(1, 'Von', 'jeremyvon21@gmail.com', 4, 'hunter', 0, 0, '2026-07-02 15:57:47'),
(2, 'jeremy', 'jeremy2121@gmail.com', 4, 'hunter', 0, 0, '2026-07-04 00:59:56'),
(3, 'hehe', 'hehe21@gmail.com', 4, 'gehrman', 0, 0, '2026-07-04 01:33:21'),
(4, 'Hunter', 'von@gmail.com', 5, 'gehrman', 0, 0, '2026-07-05 23:55:38'),
(5, 'heheheheheheh', 'novnov21@gmail.com', 0, 'gehrman', 0, 0, '2026-07-06 00:36:08'),
(6, 't@gmail.com', 't@gmail.com', 0, 'hunter', 2000, 0, '2026-07-16 07:20:22'),
(7, 'Theresa', 'theresagils@gmail.com', 0, 'hunter', 2000, 0, '2026-07-16 07:23:25'),
(8, 'simon', 'simonmigs@gmail.com', 0, 'hunter', 7000, 5, '2026-07-16 07:25:14'),
(9, 'JEREMYVON', 'jeremyvonvon@gmail.com', 4, 'hunter', 9000, 10, '2026-07-17 06:49:54'),
(10, 'vonvonthegreat', 'vonvon@gmail.com', 4, 'hunter', 9000, 10, '2026-07-17 06:56:08'),
(11, 'jerjer', 'jeremysanjuan@gmail.com', 4, 'hunter', 9000, 10, '2026-07-17 07:13:33');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` int(11) NOT NULL,
  `insight_required` int(11) NOT NULL DEFAULT 0,
  `stock` int(11) NOT NULL,
  `img_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `item_name`, `description`, `price`, `insight_required`, `stock`, `img_path`, `created_at`) VALUES
(2, 'Saw Cleaver', 'One of the trick weapons of the workshop, commonly used in the hunting business.\r\nThis saw, effective at drawing the blood of beasts, transforms into a long cleaver that makes use of centrifugal force.\r\nThe saw, with its set of blood-letting teeth, has become a symbol of the hunt, and only grows in effectiveness the more grotesquely transformed the beast\"\r\n\r\n ', 0, 0, 980, 'images/items/1783298206_Saw_Cleaver.webp', '2026-07-06 00:36:46'),
(3, 'Saw Spear', 'One of the trick weapons of the workshop, commonly used by those who dedicate themselves to the hunt. This saw, effective at drawing the blood of beasts, transforms into a medium-range spear. The saw, with its set of blood-letting teeth, has become a symbol of the hunt, and only grows in effectiveness the more grotesquely transformed the beast.', 1000, 0, 7, 'images/items/1784269307_saw_spear.jpg', '2026-07-17 06:21:47'),
(4, 'A Call Beyond', 'Long ago, the Healing Church used phantasms to reach a lofty plane of darkness, but failed to make contact with the outer reaches of the cosmos.', 5000, 10, 10, 'images/items/1784269679_call beyond.webp', '2026-07-17 06:27:59'),
(5, 'Ludwig The Holy Blade', 'Ah, you were at my side all along. My true mentor. My guiding moonlight.', 6000, 15, 2, 'images/items/1784270401_LudwigsHolyBladeThumb.jpg', '2026-07-17 06:40:01'),
(6, 'Kos Parasite', 'A weird alien parasite', 8000, 20, 2, 'images/items/1784271624_Kos_Parasite.jpg', '2026-07-17 07:00:24');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `hunter_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` int(11) NOT NULL,
  `status` int(1) NOT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `hunter_id`, `item_id`, `quantity`, `total_price`, `status`, `created`) VALUES
(1, 1, 2, 2, 0, 1, '2026-07-14 08:49:34'),
(2, 1, 2, 1, 0, 1, '2026-07-14 08:50:52'),
(3, 1, 2, 1, 0, 1, '2026-07-14 20:08:31'),
(4, 6, 2, 13, 0, 1, '2026-07-16 07:21:16'),
(5, 9, 2, 1, 0, 1, '2026-07-17 06:51:12'),
(6, 9, 3, 1, 1000, 1, '2026-07-17 06:51:12'),
(7, 10, 2, 1, 0, 1, '2026-07-17 06:57:24'),
(8, 10, 3, 1, 1000, 1, '2026-07-17 06:57:24'),
(9, 11, 2, 1, 0, 1, '2026-07-17 07:15:16'),
(10, 11, 3, 1, 1000, 1, '2026-07-17 07:15:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `hunters`
--
ALTER TABLE `hunters`
  ADD PRIMARY KEY (`hunter_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `hunter_id` (`hunter_id`),
  ADD KEY `item_id` (`item_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hunters`
--
ALTER TABLE `hunters`
  MODIFY `hunter_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`hunter_id`) REFERENCES `hunters` (`hunter_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
