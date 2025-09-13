-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 11, 2025 at 11:36 PM
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
-- Database: `ayo_green`
--

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `message_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `families`
--

CREATE TABLE `families` (
  `family_id` int(11) NOT NULL,
  `family_name` varchar(100) NOT NULL,
  `family_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `families`
--

INSERT INTO `families` (`family_id`, `family_name`, `family_description`, `created_at`, `updated_at`) VALUES
(1, 'Fabaceae', 'The legume, pea, or bean family - one of the largest plant families', '2025-08-23 18:14:46', '2025-08-23 18:14:46'),
(2, 'Moraceae', 'The mulberry family including figs and breadfruit', '2025-08-23 18:14:46', '2025-08-23 18:14:46'),
(3, 'Arecaceae', 'The palm family with over 2,500 species', '2025-08-23 18:14:46', '2025-08-23 18:14:46'),
(4, 'Rutaceae', 'The rue or citrus family including oranges and lemons', '2025-08-23 18:14:46', '2025-08-23 18:14:46'),
(5, 'Malvaceae', 'The mallow family including cotton and okra', '2025-08-23 18:14:46', '2025-08-23 18:14:46'),
(6, 'Combretaceae', NULL, '2025-09-11 20:08:54', '2025-09-11 20:08:54');

-- --------------------------------------------------------

--
-- Table structure for table `qr_codes`
--

CREATE TABLE `qr_codes` (
  `qr_id` int(11) NOT NULL,
  `tree_id` int(11) NOT NULL,
  `qr_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qr_codes`
--

INSERT INTO `qr_codes` (`qr_id`, `tree_id`, `qr_path`, `created_at`, `is_active`) VALUES
(1, 1, 'assets/images/qr_codes/tree_1_1234567890.png', '2025-08-23 18:14:46', 0),
(2, 2, 'assets/images/qr_codes/tree_2_0987654321.png', '2025-08-23 18:14:46', 0),
(3, 1, 'assets/images/qr_codes/tree_1_1756930968.png', '2025-09-03 20:22:49', 1),
(4, 2, 'assets/images/qr_codes/tree_2_1756930976.png', '2025-09-03 20:22:56', 1),
(5, 3, 'assets/images/qr_codes/tree_3_1757625999.png', '2025-09-11 21:26:39', 1);

-- --------------------------------------------------------

--
-- Table structure for table `trees`
--

CREATE TABLE `trees` (
  `tree_id` int(11) NOT NULL,
  `scientific_name` varchar(255) NOT NULL,
  `common_name` varchar(255) DEFAULT NULL,
  `family_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `ecological_info` text DEFAULT NULL,
  `conservation_status` enum('Least Concern','Vulnerable','Endangered','Critically Endangered') DEFAULT 'Least Concern',
  `uses_importance` text DEFAULT NULL,
  `origin_distribution` text DEFAULT NULL,
  `gps_coordinates` varchar(255) DEFAULT NULL,
  `qr_code_path` varchar(500) DEFAULT NULL,
  `tree_code` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trees`
--

INSERT INTO `trees` (`tree_id`, `scientific_name`, `common_name`, `family_id`, `description`, `ecological_info`, `conservation_status`, `uses_importance`, `origin_distribution`, `gps_coordinates`, `qr_code_path`, `tree_code`, `created_at`, `updated_at`) VALUES
(1, 'Enterolobium cyclocarpum (Jacq.) Griseb.', 'Ear Pod Tree', 1, 'A Large deciduous tree that can reach 20–30 meters in height with a thick trunk which is often buttressed at the base; bark grey to light brown, rough with fissures. Leaves are bipinnately compound, with small leaflets that close at night or during drought. Produces distinctive, large, dark brown, ear-shaped pods (up to 12 cm wide) containing several glossy brown seeds. Canopy is broad and spreading, providing extensive shade.', 'Thrives in well-drained soils, tolerates a variety of soil types including sandy and clay soils. Adapted to both wet and dry tropical climates. Nitrogen-fixing species, improving soil fertility. Flowering occurs between December and March; fruits mature between April and June in Nigeria.', 'Least Concern', 'Ornamental and shade tree in parks, gardens, and avenues. Timber is moderately heavy and used for furniture, carpentry, and light construction. Pods are sometimes used as livestock fodder. Seeds have been used in traditional crafts and jewelry. Plays a role in soil enrichment through nitrogen fixation.', 'Native to tropical regions of Central and South America, particularly Mexico, Costa Rica, and Venezuela. Widely introduced and naturalised in tropical Africa, including Nigeria, for ornamental and shade purposes.', '7.4456, 3.8945', 'assets/images/qr_codes/tree_1_1756930968.png', 'UI-BG-TS-001', '2025-09-11 20:08:54', '2025-09-11 20:58:40'),
(2, 'Terminalia superba Engl. & Diels', 'White afara, Limba', 6, 'A tall deciduous tree reaching 30–45 meters in height. Straight, cylindrical bole often branchless up to 20 meters. The Bark is smooth to slightly fissured, greyish-brown in color. Leaves are simple, arranged in whorls near branch tips, elliptical in shape. Flowers are small, creamy-white, and borne in slender spikes. Fruits are winged, resembling a small propeller.', 'Prefers well-drained, deep loamy soils. Grows best in humid lowland forests with full sunlight. Tolerates secondary forest conditions. Flowers around February to April and fruits between May and July. Provides shade and helps reduce soil erosion.', 'Least Concern', 'Highly valued for timber—lightweight and easy to work with. Used in furniture, veneer, plywood, and interior finishing. Bark is used in traditional medicine for treating cough and infections. Culturally significant in some communities as a symbol of prestige. Often planted for reforestation and ornamental purposes.', 'Native to West and Central Africa. Found in tropical rainforests across Nigeria, Ghana, Cameroon, and Congo. Commonly cultivated in forest reserves and botanical gardens in Nigeria.', NULL, 'assets/images/qr_codes/tree_2_1756930976.pngassets/images/qr_codes/tree_2_1756930976.png', 'UI-BG-TS-002', '2025-09-11 20:08:54', '2025-09-11 20:35:25'),
(3, 'Roystonea regia (Kunth) O.F.Cook', 'Royal Palm', 3, 'Tall, solitary palm reaching 20–30 meters in height. Smooth, light gray to whitish trunk, swollen at the base and tapering upward. A distinct green crownshaft (smooth, waxy stem section below the leaves) tops the trunk. Crown consists of 15–20 long, pinnate fronds, each up to 4–6 meters long. Inflorescences emerge below the crownshaft, bearing small, creamy-white flowers. Fruits are oval, purplish-black when mature, about 1–2 cm in diameter.', 'Prefers full sunlight and well-drained soils; tolerates a range of soil types from sandy to loamy. Thrives in humid tropical climates but is also drought-tolerant once established. Flowering can occur year-round in Nigeria, but peak fruiting is often during the dry season. Provides perching and feeding sites for birds and bats.', 'Least Concern', 'Highly valued as an ornamental tree for landscaping in gardens, avenues, and institutional grounds. Trunk sometimes used in rural areas for construction. Fruits are eaten by birds and sometimes livestock. Cultural symbol in Cuba and parts of the Caribbean.', 'Native to southern Florida, the Caribbean (especially Cuba), and parts of Central America. Widely cultivated in tropical and subtropical regions worldwide as an ornamental palm, including Nigeria.', NULL, 'assets/images/qr_codes/tree_3_1757625999.png', 'UI-BG-TS-003', '2025-09-11 20:08:54', '2025-09-11 21:26:39');

-- --------------------------------------------------------

--
-- Table structure for table `tree_photos`
--

CREATE TABLE `tree_photos` (
  `photo_id` int(11) NOT NULL,
  `tree_id` int(11) NOT NULL,
  `photo_path` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tree_photos`
--

INSERT INTO `tree_photos` (`photo_id`, `tree_id`, `photo_path`, `caption`, `is_primary`, `created_at`) VALUES
(3, 2, 'tree_2_primary.jpg', 'Mango tree in full bloom', 1, '2025-08-23 18:14:46'),
(4, 2, 'tree_2_fruit.jpg', 'Ripe mango fruits', 0, '2025-08-23 18:14:46'),
(5, 1, 'tree_1_68c3382aab6c9.jpg', '', 0, '2025-09-11 20:59:22'),
(6, 1, 'tree_1_68c33dfc51be6.jpg', '', 0, '2025-09-11 21:24:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','editor') DEFAULT 'editor',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `email`, `role`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$nrGA5gYh4Q/rPwd54Z/s2..N.tgJmrTQOsI373mMV7BKffXJktzZy', 'admin@botanicalgarden.ui.edu.ng', 'admin', NULL, '2025-08-23 18:14:46', '2025-08-23 18:16:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `email` (`email`,`created_at`);

--
-- Indexes for table `families`
--
ALTER TABLE `families`
  ADD PRIMARY KEY (`family_id`),
  ADD UNIQUE KEY `family_name` (`family_name`);

--
-- Indexes for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD PRIMARY KEY (`qr_id`),
  ADD KEY `tree_id` (`tree_id`,`is_active`),
  ADD KEY `idx_qr_codes_active` (`is_active`);

--
-- Indexes for table `trees`
--
ALTER TABLE `trees`
  ADD PRIMARY KEY (`tree_id`),
  ADD UNIQUE KEY `tree_code` (`tree_code`),
  ADD KEY `idx_tree_code` (`tree_code`),
  ADD KEY `idx_scientific_name` (`scientific_name`),
  ADD KEY `idx_common_name` (`common_name`),
  ADD KEY `idx_family_id` (`family_id`),
  ADD KEY `idx_conservation_status` (`conservation_status`);

--
-- Indexes for table `tree_photos`
--
ALTER TABLE `tree_photos`
  ADD PRIMARY KEY (`photo_id`),
  ADD KEY `tree_id` (`tree_id`,`is_primary`),
  ADD KEY `idx_photos_tree` (`tree_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `families`
--
ALTER TABLE `families`
  MODIFY `family_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `qr_codes`
--
ALTER TABLE `qr_codes`
  MODIFY `qr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `trees`
--
ALTER TABLE `trees`
  MODIFY `tree_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tree_photos`
--
ALTER TABLE `tree_photos`
  MODIFY `photo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `trees`
--
ALTER TABLE `trees`
  ADD CONSTRAINT `trees_ibfk_1` FOREIGN KEY (`family_id`) REFERENCES `families` (`family_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
