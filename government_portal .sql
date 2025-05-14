-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 30, 2025 at 07:19 AM
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
-- Database: `government_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `citizens`
--

CREATE TABLE `citizens` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `national_id` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL CHECK (octet_length(`phone`) >= 10),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `citizens`
--

INSERT INTO `citizens` (`id`, `name`, `email`, `password`, `national_id`, `phone`, `created_at`, `updated_at`) VALUES
(1, 'John Doe', 'john@example.com', 'hashed_password_here', '12345678', '1234567890', '2025-04-13 23:36:45', '2025-04-13 23:36:45'),
(2, 'Antoney ogembo ouko', 'zbntregov@gmail.com', '$2y$10$3dx.dk/f/rcPibWwtygCP.ansoL9Y.y3XujzkJEL0rKzcPOjLRKpi', '428105656', '0768343558', '2025-04-13 23:37:48', '2025-04-13 23:37:48'),
(4, 'joyce', 'jov@gmail.com', '$2y$10$Mg4wYYPrA.2DloA.swCMo.lJSqpVjbHV7LgPKTVU3ESUs6tzzNjq.', '235756797', '0768343558', '2025-04-14 01:29:56', '2025-04-14 01:29:56'),
(5, 'antotech', 'teell@gmail.com', '$2y$10$SFcA0pPM/kHXnJrz.KX.JuQmmFEejsslSd7U479E21p1PvsIZ6HfK', '24763746', '0768343558', '2025-04-14 14:27:52', '2025-04-14 14:27:52'),
(6, 'Antoney ouko', 'aouko178@gmail.com', '$2y$10$8NKsem741W2IxcQ/r8FxDeWvL/MfHAQDPYGFd8B6N2.wEC4zQsQt.', '42810562', '0768343558', '2025-04-25 20:14:41', '2025-04-25 20:14:41'),
(7, 'Antoney ogembo ouko', 'aouko176@gmail.com', '$2y$10$KMl516BphnRAg58Ul85/mOVyCO7UJndyGEKAsRLZk1GwZMdg82q7O', '12335355', '0768343558', '2025-04-25 20:18:34', '2025-04-25 20:18:34'),
(8, 'James gesegeti', 'gesegeti@gmail.com', '$2y$10$DE3VqUliZ0cjJlFNCUwkMOJuqrA845NWoy.0FtcJK1j2Dl.2CaYIq', '435233', '0768343558', '2025-04-30 04:45:44', '2025-04-30 04:45:44');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` varchar(20) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `user_location` varchar(100) NOT NULL,
  `user_avatar` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `category` enum('infrastructure','education','healthcare','transport') NOT NULL,
  `status` enum('pending','under-review','in-progress','implemented','declined') DEFAULT 'pending',
  `votes` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_name`, `user_email`, `user_location`, `user_avatar`, `title`, `message`, `category`, `status`, `votes`, `created_at`, `updated_at`, `category_id`) VALUES
('FB-2023-0140', 'Michael Omondi', 'michael.omondi@example.com', 'Kisumu, Kenya', 'Photos/h3.jpg', 'Healthcare Facilities Need Better Equipment', 'The current state of medical equipment in our local hospitals is concerning.', 'healthcare', 'implemented', 58, '2025-04-22 18:25:50', '2025-04-30 04:19:26', 3),
('FB-2023-0141', 'Will Smith', 'will.smith@example.com', 'Mombasa, Kenya', 'Photos/h2.jpg', 'Need More Public Libraries in Eastern Suburbs', 'The nearest library is over 10 kilometers away, making it inaccessible for many residents.', 'education', 'pending', 36, '2025-04-22 18:25:50', '2025-04-30 04:19:26', 2),
('FB-2023-0142', 'John Doe', 'john.doe@example.com', 'Nairobi, Kenya', 'Photos/h1.jpg', 'Improve Street Lighting in Westlands Area', 'The street lighting in the Westlands area is inadequate and poses a safety risk for pedestrians and drivers at night.', 'infrastructure', 'under-review', 42, '2025-04-22 18:25:50', '2025-04-30 04:19:26', 1);

-- --------------------------------------------------------

--
-- Table structure for table `government_representatives`
--

CREATE TABLE `government_representatives` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `employee_id` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `department` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `region` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `government_representatives`
--

INSERT INTO `government_representatives` (`id`, `name`, `employee_id`, `email`, `department`, `position`, `region`, `password`, `registration_date`) VALUES
(1, 'Antoney ogembo ouko', 'gov@gmail.com', 'aouko178@gmail.com', 'health', 'Manager', 'Isiolo', '$2y$10$8VXzrSPSpUc/3gUHA5EAF.4HVr39vrp.KE08ogkEa55fx7v5T1Gpq', '2025-04-13 19:58:45'),
(2, 'Antoney ogembo ouko', 'tregov@gmail.com', 'treaouko178@gmail.com', 'education', 'Manager', 'Kajiado', '$2y$10$86bYTfCLVld90gndugWOUeYKayTEF17sXkcQW0j9GPyIUCvImqxrO', '2025-04-13 20:33:24'),
(3, 'tech127', 'zbntregov@gmail.com', 'antoneyouko@gmail.com', 'health', 'Manager', 'Kakamega', '$2y$10$0..515l8r3aGI4dh6aO1Ve2CM6uf/x7XvaOtM26p4J6m7vrxPttZ2', '2025-04-14 00:21:43'),
(4, 'tech127', 'rzbntregov@gmail.com', 'rantoneyouko@gmail.com', 'health', 'Manager', 'Kakamega', '$2y$10$.ikSi0dPi0ldDqj.oWQVk.DY/q/dRJi5SWuhEsZLssshi1b5moOue', '2025-04-14 00:25:55'),
(6, 'Antoney ogembo ouko', 'bntregov@gmail.com', 'ouko178@gmail.com', 'transport', 'Manager', 'Kiambu', '$2y$10$nlziPcGAq1tH24lLlhUzGO6d.d.ZbKiISh/6FArimknKylec6SrGS', '2025-04-14 01:28:34'),
(7, 'Techs archs', 'Archs@gmail.com', 'ack23@gmail.com', 'education', 'Manager', 'Kilifi', '$2y$10$3eE8mMDCzfululERGrHWIOSWt9anrHHV.TiCVRh608bz2hpYgMsgG', '2025-04-14 01:39:26'),
(8, 'zetech university', 'Gov-zetech@gmail.ac.ke', 'zetech@gmail.ac.ke', 'education', 'Director', '', '$2y$10$7hyUaHKRF1wLrSPXTR5Ya.HHAGHrxwthJHn3jRAE/wnGuwSoO4EuK', '2025-04-24 23:41:52'),
(9, 'uchumi ke', '235363', 'uchumi@gmail.com', 'health', 'Manager', '', '$2y$10$afD53IYMaWm56ChvObU0.uR620UQjVe0SiyyjxPTCCOfb8YQ2BO3S', '2025-04-30 04:48:10');

-- --------------------------------------------------------

--
-- Table structure for table `inquiries`
--

CREATE TABLE `inquiries` (
  `id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','replied','closed') NOT NULL DEFAULT 'new',
  `created_at` datetime NOT NULL,
  `citizen_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inquiries`
--

INSERT INTO `inquiries` (`id`, `user_name`, `user_email`, `subject`, `message`, `status`, `created_at`, `citizen_id`) VALUES
(1, 'Antoney ouko', 'aouko178@gmail.com', 'helb', 'i need helb', 'replied', '2025-04-25 10:22:55', NULL),
(2, 'Antoney ouko', 'aouko178@gmail.com', 'helb', 'so when should I start applying', 'replied', '2025-04-25 21:53:29', NULL),
(3, 'Antoney ouko', 'aouko178@gmail.com', 'helb', 'Three months could be too longer for me', 'replied', '2025-04-25 23:00:25', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `replies`
--

CREATE TABLE `replies` (
  `id` int(11) NOT NULL,
  `inquiry_id` int(11) NOT NULL,
  `reply_message` text NOT NULL,
  `replied_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `replies`
--

INSERT INTO `replies` (`id`, `inquiry_id`, `reply_message`, `replied_at`) VALUES
(1, 1, 'ok we got you', '2025-04-25 21:47:05'),
(2, 2, 'we will send you an email', '2025-04-25 21:57:24'),
(3, 2, 'This will take just a period of three months', '2025-04-25 22:58:24'),
(4, 3, 'Ok we will discuss you prefferences and we will contact in a week', '2025-04-25 23:03:40');

-- --------------------------------------------------------

--
-- Table structure for table `responses`
--

CREATE TABLE `responses` (
  `id` int(11) NOT NULL,
  `feedback_id` varchar(20) NOT NULL,
  `status` enum('pending','under-review','in-progress','implemented','declined') NOT NULL,
  `public_response` text NOT NULL,
  `internal_notes` text DEFAULT NULL,
  `responded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `citizens`
--
ALTER TABLE `citizens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `national_id` (`national_id`),
  ADD KEY `idx_citizens_email` (`email`),
  ADD KEY `idx_citizens_national_id` (`national_id`),
  ADD KEY `idx_citizens_name` (`name`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_feedback_category` (`category_id`),
  ADD KEY `idx_feedback_status` (`status`);

--
-- Indexes for table `government_representatives`
--
ALTER TABLE `government_representatives`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_email` (`user_email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `replies`
--
ALTER TABLE `replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inquiry_id` (`inquiry_id`),
  ADD KEY `idx_replied_at` (`replied_at`);

--
-- Indexes for table `responses`
--
ALTER TABLE `responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `feedback_id` (`feedback_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `citizens`
--
ALTER TABLE `citizens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `government_representatives`
--
ALTER TABLE `government_representatives`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `inquiries`
--
ALTER TABLE `inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `replies`
--
ALTER TABLE `replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `responses`
--
ALTER TABLE `responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk_feedback_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `replies`
--
ALTER TABLE `replies`
  ADD CONSTRAINT `replies_ibfk_1` FOREIGN KEY (`inquiry_id`) REFERENCES `inquiries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `responses`
--
ALTER TABLE `responses`
  ADD CONSTRAINT `responses_ibfk_1` FOREIGN KEY (`feedback_id`) REFERENCES `feedback` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
