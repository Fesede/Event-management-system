-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 01, 2026 at 10:53 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `st_raguel`
--

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `event_date` date NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `status` enum('published','draft','rejected') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `Location` varchar(255) NOT NULL,
  `Time` time NOT NULL,
  `category` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `user_name`, `title`, `event_date`, `link`, `description`, `image`, `status`, `created_at`, `Location`, `Time`, `category`) VALUES
(1, 'Dereje Sebsibe', 'Meeting', '2018-01-30', '', 'greet work ok', 'event_1778494714.png', 'rejected', '2026-05-11 10:18:34', 'Addis Ababa,Werda 9', '10:25:21', 'አባላት ጉዳይ ክፍል'),
(2, 'root', 'Meeting', '2026-05-14', '', 'hjasdjkhadksjhka', 'event_1778594649_Capture.PNG', 'published', '2026-05-12 14:04:09', 'Addis ababa, werda 9', '21:02:00', 'ድጋፍና ማስተባበር ንኡስ ክፍል'),
(3, 'Admin User', 'gdggd', '2026-05-22', '', 'bcfwsdölkiojdsm,dcjkl', 'default.jpg', 'draft', '2026-05-18 17:04:03', 'addis ababa,gibi', '12:08:00', 'Admin'),
(4, 'Admin User', 'gdggd', '2026-05-22', '', 'bcfwsdölkiojdsm,dcjkl', 'default.jpg', 'rejected', '2026-05-18 17:05:53', 'addis ababa,gibi', '12:08:00', 'Admin'),
(5, 'Admin User', 'Dereje', '2026-05-06', '', 'asdfgfdger sdfljkksf skdjjldfjids sndkfjiksd', 'default.jpg', 'draft', '2026-05-18 17:13:11', 'addis ababa,gibi', '23:15:00', 'Admin'),
(6, 'Admin User', 'Dereje', '2026-05-06', '', 'asdfgfdger sdfljkksf skdjjldfjids sndkfjiksd', 'default.jpg', 'rejected', '2026-05-18 17:13:59', 'addis ababa,gibi', '23:15:00', 'አባላት ጉዳይ ክፍል');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `item_code` varchar(50) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `item_code`, `item_name`, `category`, `quantity`, `created_at`) VALUES
(1, 'INV-012', 'computer', 'ኤሌክትሮኒክስ', 10, '2026-05-20 10:38:00');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `Photo` varchar(255) DEFAULT '',
  `full_Name` varchar(255) NOT NULL,
  `Phone_no` varchar(50) NOT NULL,
  `kesitena_Name` varchar(255) DEFAULT '',
  `town` varchar(100) NOT NULL,
  `Kefele_Ketema` varchar(100) NOT NULL,
  `Werda` varchar(100) NOT NULL,
  `school_level` varchar(100) NOT NULL,
  `status_work` varchar(100) NOT NULL,
  `type_work` varchar(100) NOT NULL,
  `Service_Selection` text NOT NULL,
  `Emg_name` varchar(255) NOT NULL,
  `Emg_phone` varchar(50) NOT NULL,
  `Relationship` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `Photo`, `full_Name`, `Phone_no`, `kesitena_Name`, `town`, `Kefele_Ketema`, `Werda`, `school_level`, `status_work`, `type_work`, `Service_Selection`, `Emg_name`, `Emg_phone`, `Relationship`, `created_at`) VALUES
(1, 'uploads/raguel_6a0ecd40843d07.16147600.png', 'DERE GOOD', '0945285556', 'ghjkll', 'Addis ababa', 'addis ketema', '09', '12', 'employer', 'negade', 'ማዕከላዊያን ንዑስ ክፍል, ህፃናት ንዑስ ክፍል, ጊቢ ጉባኤያት ክፍል', 'fgjkkk', '0974858525', 'yok,kdxcc', '2026-05-21 09:15:44');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `author` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `status` enum('published','draft','rejected') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `author`, `title`, `content`, `image`, `status`, `created_at`, `category`) VALUES
(1, 'bagere', 'news', 'dsjfkjllflklfjdljlf', 'news_1778513803_Capture.PNG', 'published', '2026-05-11 15:36:43', 'ምዝገባና መረጃ ክፍል'),
(2, 'bagere', 'tap', 'sjhkhehdhk', 'news_1778513930_log-modified.png', 'published', '2026-05-11 15:38:50', 'ልማት ንኡስ ክፍል'),
(3, 'Admin', 'dfjjkdf', 'jhdfjjfh', 'default.jpg', 'draft', '2026-05-19 19:43:56', 'Admin'),
(4, 'bagere', 'go', 'ddfgg', 'news_1779233157_6a0cf185e6685.jpg', 'published', '2026-05-19 23:25:57', 'ንዋይ አስተዳደር ዐቢይ ክፍል'),
(5, 'bagere', 'tap', 'dffff', 'news_1779233921_6a0cf4810ec13.jpg', 'published', '2026-05-19 23:38:41', 'አባላት ጉዳይ ክፍል'),
(6, 'ddd', 'ddd', 'ddd', 'news_1779234874_6a0cf83a6456f.jpg', 'draft', '2026-05-19 23:54:34', 'አባላት ጉዳይ ክፍል');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('primary','success','warning','danger') DEFAULT 'primary',
  `icon` varchar(50) DEFAULT 'fa-bell',
  `status` enum('unread','read') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `title`, `message`, `type`, `icon`, `status`, `created_at`) VALUES
(3, 'jhdjsjd', 'sdjhaukhsdf', '', 'fa-bullhorn', 'read', '2026-05-12 17:56:03'),
(4, 'hhjjhk', 'tfhfjgh', 'warning', 'fa-exclamation-triangle', 'read', '2026-05-17 19:22:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Editor','Super Admin') DEFAULT 'Admin',
  `photo` varchar(255) DEFAULT 'default_avatar.png',
  `status` enum('active','inactive') DEFAULT 'active',
  `last_seen` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category` varchar(255) NOT NULL DEFAULT 'None'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `role`, `photo`, `status`, `last_seen`, `created_at`, `category`) VALUES
(2, 'DEREt', 'GOOD', 'deregood292@gmail.com', '201920', 'Super Admin', '1778609808_log-modified.png', 'inactive', NULL, '2026-05-12 18:16:48', 'ምዝገባና  መረጃ ክፍል'),
(3, 'Dereje', 'Sebsibe', 'deregood29@gmail.com', '$2y$10$AnhxSSIexoScXdyxoUPx6ObkNFVNBYjaAWdTXaRn6Wg7cny1.W85i', 'Super Admin', '1778696126_log-modified.png', 'active', NULL, '2026-05-13 18:15:26', 'ንዋይ አስተዳደር ዐቢይ ክፍል'),
(5, 'Dereje', 'Kebede', 'abebe29@gmail.com', '$2y$10$hEpF8vL7ueCc/NJ3bhYpM.MLktIwUr7NGtxsqVd5YhIyyvsh9gSqe', 'Admin', '1779115755_log.png', 'active', NULL, '2026-05-18 14:49:15', 'ጽህፈት ቤት');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item_code` (`item_code`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
