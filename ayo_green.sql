

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";




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
(5, 'Malvaceae', 'The mallow family including cotton and okra', '2025-08-23 18:14:46', '2025-08-23 18:14:46');

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
(4, 2, 'assets/images/qr_codes/tree_2_1756930976.png', '2025-09-03 20:22:56', 1);

-- --------------------------------------------------------

--
-- Table structure for table `trees`
--

CREATE TABLE `trees` (
  `tree_id` int(11) NOT NULL,
  `scientific_name` varchar(100) NOT NULL,
  `common_names` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`common_names`)),
  `family_id` int(11) NOT NULL,
  `origin_distribution` text DEFAULT NULL,
  `physical_description` text DEFAULT NULL,
  `ecological_info` text DEFAULT NULL,
  `conservation_status` enum('Least Concern','Vulnerable','Endangered') DEFAULT 'Least Concern',
  `uses_economic` text DEFAULT NULL,
  `geotag_lat` decimal(10,8) DEFAULT NULL,
  `geotag_lng` decimal(11,8) DEFAULT NULL,
  `tree_code` varchar(20) NOT NULL,
  `health_status` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `qr_code_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trees`
--

INSERT INTO `trees` (`tree_id`, `scientific_name`, `common_names`, `family_id`, `origin_distribution`, `physical_description`, `ecological_info`, `conservation_status`, `uses_economic`, `geotag_lat`, `geotag_lng`, `tree_code`, `health_status`, `remarks`, `qr_code_path`, `created_at`, `updated_at`) VALUES
(1, 'Khaya senegalensis', '[\"African mahogany\", \"Dry zone mahogany\"]', 1, 'Native to Africa, from Senegal east to Sudan and south to Zimbabwe', 'Large deciduous tree growing to 30 m tall, with a trunk up to 1 m diameter. Bark dark grey, fissured. Leaves pinnate, with 3-6 pairs of leaflets. Flowers small, white, in large panicles.', 'Grows in savanna woodlands and along rivers. Important food source for elephants which eat the bark.', 'Least Concern', 'Timber highly valued for furniture, boat building and construction. Bark used in traditional medicine.', 7.44560000, 3.89450000, 'UI-BG-TS-001', 'Healthy', 'Planted in 1985. One of the largest trees in the garden.', 'assets/images/qr_codes/tree_1_1756930968.png', '2025-08-23 18:14:46', '2025-09-03 20:22:49'),
(2, 'Mangifera indica', '[\"Mango tree\"]', 2, 'Native to South Asia, now cultivated worldwide in tropical regions', 'Large evergreen tree growing to 30-40 m tall. Leaves dark green, glossy, lanceolate. Flowers small, pinkish-white, in panicles. Fruit a large drupe with sweet, juicy flesh.', 'Prefers well-drained soils in tropical climates. Important food source for various animals and insects.', 'Least Concern', 'Fruit widely consumed fresh or processed. Wood used for furniture and construction.', 7.44620000, 3.89510000, 'UI-BG-TS-002', 'Excellent', 'Produces abundant fruit annually. Popular with visitors.', 'assets/images/qr_codes/tree_2_1756930976.png', '2025-08-23 18:14:46', '2025-09-03 20:22:56');

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
(1, 1, 'tree_1_primary.jpg', 'Full view of African Mahogany tree', 1, '2025-08-23 18:14:46'),
(2, 1, 'tree_1_bark.jpg', 'Close-up of bark texture', 0, '2025-08-23 18:14:46'),
(3, 2, 'tree_2_primary.jpg', 'Mango tree in full bloom', 1, '2025-08-23 18:14:46'),
(4, 2, 'tree_2_fruit.jpg', 'Ripe mango fruits', 0, '2025-08-23 18:14:46');

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
  ADD KEY `idx_trees_family` (`family_id`),
  ADD KEY `idx_trees_scientific_name` (`scientific_name`),
  ADD KEY `idx_trees_conservation` (`conservation_status`);

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
  MODIFY `family_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `qr_codes`
--
ALTER TABLE `qr_codes`
  MODIFY `qr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `trees`
--
ALTER TABLE `trees`
  MODIFY `tree_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tree_photos`
--
ALTER TABLE `tree_photos`
  MODIFY `photo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD CONSTRAINT `qr_codes_ibfk_1` FOREIGN KEY (`tree_id`) REFERENCES `trees` (`tree_id`) ON DELETE CASCADE;

--
-- Constraints for table `trees`
--
ALTER TABLE `trees`
  ADD CONSTRAINT `trees_ibfk_1` FOREIGN KEY (`family_id`) REFERENCES `families` (`family_id`);

--
-- Constraints for table `tree_photos`
--
ALTER TABLE `tree_photos`
  ADD CONSTRAINT `tree_photos_ibfk_1` FOREIGN KEY (`tree_id`) REFERENCES `trees` (`tree_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
