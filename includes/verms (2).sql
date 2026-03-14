-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 27, 2026 at 06:56 AM
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
-- Database: `verms`
--

-- --------------------------------------------------------

--
-- Table structure for table `birth_events`
--

CREATE TABLE `birth_events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `form_number` varchar(50) DEFAULT NULL,
  `registration_uid` varchar(50) DEFAULT NULL,
  `child_name` varchar(100) DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `grandfather_name` varchar(100) DEFAULT NULL,
  `sex` enum('Male','Female') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `mother_full_name` varchar(100) DEFAULT NULL,
  `father_full_name` varchar(100) DEFAULT NULL,
  `parents_nationality` varchar(100) DEFAULT NULL,
  `registered_date` date DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('Pending','Paid','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `place_of_birth` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `birth_events`
--

INSERT INTO `birth_events` (`id`, `user_id`, `form_number`, `registration_uid`, `child_name`, `father_name`, `grandfather_name`, `sex`, `date_of_birth`, `mother_full_name`, `father_full_name`, `parents_nationality`, `registered_date`, `issue_date`, `photo`, `notes`, `status`, `created_at`, `place_of_birth`) VALUES
(1, 6, 'FORM-mkoa84pf-BP1RF', 'BIRTH-mkoa84pf-SWE09', 'Dawit', 'Mengesha', 'gadefaw', 'Male', '2026-01-15', 'helen', 'ambaw', 'Ethiopian', '2026-01-21', '2026-01-21', 'images/birth_photos/birth_697109d6922088.65048286.jpg', '', 'Approved', '2026-01-21 17:16:06', 1),
(2, 11, 'FORM-mkocfa1t-VYPB5', 'BIRTH-mkocfa1t-QZ0C6', 'mekides', 'Mengesha', 'gadefaw', 'Female', '2026-01-21', 'helen', 'ambaw', 'Ethiopian', '2026-01-21', '2026-01-21', 'images/birth_photos/birth_697117de9ceaf0.88267837.jpg', '', 'Approved', '2026-01-21 18:15:58', 3),
(4, 6, 'FORM-mkqvfwyz-5IEC7', 'BIRTH-mkqvfwyz-0KMTE', 'rediwuan', 'oli', 'moma', 'Male', '2025-12-31', 'Betelhem', 'abebe', 'Ethiopian', '2029-05-23', '2026-01-23', 'images/birth_photos/birth_69736dc2c997f7.00613260.pdf', '', 'Approved', '2026-01-23 12:46:58', 1);

-- --------------------------------------------------------

--
-- Table structure for table `death_events`
--

CREATE TABLE `death_events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `form_number` varchar(50) DEFAULT NULL,
  `registration_uid` varchar(50) DEFAULT NULL,
  `deceased_name` varchar(100) DEFAULT NULL,
  `sex` enum('Male','Female') DEFAULT NULL,
  `date_of_death` date DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `cause_of_death` varchar(200) DEFAULT NULL,
  `reporter_full_name` varchar(100) DEFAULT NULL,
  `relationship` varchar(100) DEFAULT NULL,
  `registered_date` date DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('Pending','Paid','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `place_of_death` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `divorce_events`
--

CREATE TABLE `divorce_events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `form_number` varchar(50) DEFAULT NULL,
  `registration_uid` varchar(50) DEFAULT NULL,
  `husband_name` varchar(100) DEFAULT NULL,
  `wife_name` varchar(100) DEFAULT NULL,
  `divorce_date` date DEFAULT NULL,
  `witness_1` varchar(100) DEFAULT NULL,
  `witness_2` varchar(100) DEFAULT NULL,
  `registered_date` date DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `husband_photo` varchar(255) DEFAULT NULL,
  `wife_photo` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('Pending','Paid','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `place_of_divorce` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kebeles`
--

CREATE TABLE `kebeles` (
  `id` int(11) NOT NULL,
  `zone_id` int(11) NOT NULL,
  `woreda_id` int(11) NOT NULL,
  `kebele_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `kebele_officer_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kebeles`
--

INSERT INTO `kebeles` (`id`, `zone_id`, `woreda_id`, `kebele_name`, `phone`, `kebele_officer_id`, `created_at`) VALUES
(1, 1, 1, '01', '+251940000000', 4, '2026-01-21 17:05:50'),
(2, 1, 1, '02', '+251910000000', 7, '2026-01-21 17:44:31'),
(3, 1, 2, '03', '+251980000000', 10, '2026-01-21 18:11:12');

-- --------------------------------------------------------

--
-- Table structure for table `marriage_events`
--

CREATE TABLE `marriage_events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `form_number` varchar(50) DEFAULT NULL,
  `registration_uid` varchar(50) DEFAULT NULL,
  `husband_name` varchar(100) DEFAULT NULL,
  `wife_name` varchar(100) DEFAULT NULL,
  `marriage_date` date DEFAULT NULL,
  `witness_1` varchar(100) DEFAULT NULL,
  `witness_2` varchar(100) DEFAULT NULL,
  `registered_date` date DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `husband_photo` varchar(255) DEFAULT NULL,
  `wife_photo` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('Pending','Paid','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `place_of_marriage` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marriage_events`
--

INSERT INTO `marriage_events` (`id`, `user_id`, `form_number`, `registration_uid`, `husband_name`, `wife_name`, `marriage_date`, `witness_1`, `witness_2`, `registered_date`, `issue_date`, `husband_photo`, `wife_photo`, `notes`, `status`, `created_at`, `place_of_marriage`) VALUES
(1, 8, 'FORM-mkobhvlc-G7K1U', 'MARRIAGE-mkobhvlc-TXAFC', 'tesfaw', 'helen', '2026-01-21', 'dawit', 'fasika', '2026-01-21', '2026-01-21', 'images/marriage_photos/husband_697111fd1ad605.36466991.jpg', 'images/marriage_photos/wife_697111fd1b1973.91987245.jpg', '', 'Approved', '2026-01-21 17:50:53', '2'),
(2, 6, 'FORM-mkw66zmf-GLNJE', 'MARRIAGE-mkw66zmf-JEBOR', 'Tesfaw Amare', 'Zewudie Getaye', '2026-01-27', 'Birhanu Moges', 'Eniyew Getinet', '2026-01-27', '2026-01-27', 'images/marriage_photos/husband_697850d2740ea9.64004248.jpg', 'images/marriage_photos/wife_697850d2755bb0.66743533.jpg', 'We Celebrate It !!!', 'Approved', '2026-01-27 05:44:50', '1');

-- --------------------------------------------------------

--
-- Table structure for table `notices`
--

CREATE TABLE `notices` (
  `id` int(11) NOT NULL,
  `woreda_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `event_id` int(11) NOT NULL,
  `service_fee` decimal(10,2) NOT NULL,
  `transaction_code` varchar(100) NOT NULL,
  `paid_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `event_type`, `event_id`, `service_fee`, `transaction_code`, `paid_at`) VALUES
(1, 6, 'Birth', 1, 50.00, '76354623', '2026-01-21 17:16:58'),
(2, 8, 'Marriage', 1, 100.00, '893459672', '2026-01-21 17:52:38'),
(3, 11, 'Birth', 2, 50.00, '78956', '2026-01-21 18:18:03'),
(4, 6, 'Birth', 4, 50.00, '9345T78', '2026-01-23 12:47:41'),
(5, 6, 'Marriage', 2, 100.00, '123456', '2026-01-27 05:45:23');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `citizen_id` int(11) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `details` text NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `citizen_id`, `event_type`, `details`, `status`, `created_at`) VALUES
(1, 6, 'Birth', 'birth', 'Approved', '2026-01-21 17:11:56'),
(2, 8, 'Marriage', 'marriage', 'Pending', '2026-01-21 17:46:52'),
(3, 11, 'Birth', 'birth', 'Pending', '2026-01-21 18:14:27'),
(4, 6, 'Birth', 'Birth', 'Approved', '2026-01-22 12:32:36'),
(5, 6, 'Birth', 'birth', 'Pending', '2026-01-23 12:42:07'),
(6, 6, 'Marriage', 'We celebrate our Marriages!!!', 'Approved', '2026-01-27 05:42:03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `kebele` varchar(100) NOT NULL,
  `woreda` varchar(100) NOT NULL,
  `zone` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('kebele','woreda','zone','admin','citizen','statistician') NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `security_answer` varchar(255) DEFAULT NULL,
  `security_question` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `email`, `phone`, `kebele`, `woreda`, `zone`, `password`, `role`, `status`, `active`, `created_at`, `security_answer`, `security_question`) VALUES
(1, 'System Administrator', 'admin', 'admin@verms.com', '', '', '', '', '$2y$10$hLb7vW8IphoH5R56354pAOF7zwjApN/OeYJ.we.ixPiulRVeDM07e', 'admin', 'active', 1, '2026-01-21 16:53:51', '1234', 'What is your memorable security question?'),
(2, 'Zone1', 'zone1', 'zone1@gmail.com', '', '', '', '', '$2y$10$AW6.22VLsFve.xjP78Y/fe1fMJS5MCuuyweqNCDKA4kfHHeLsUJFW', 'zone', 'active', 1, '2026-01-21 16:57:41', '1234', 'What is your memorable security question?'),
(3, 'Woreda1', 'woreda1', 'woreda1@gmail.com', '', '', '', '', '$2y$10$u07SL/Ejw2e20aS35DIX1e1adKXsqEI/5B8ifV4zU19m4JLXk5Wp6', 'woreda', 'active', 1, '2026-01-21 16:59:24', '1234', 'What is your memorable security question?'),
(4, 'Kebele1', 'kebele1', 'kebele1@gmail.com', '', '', '', '', '$2y$10$ZxBXUz/56gNL6CcGwpRZ8.UiHgHPVtBIDlrV.z7tqA0s07xlV.E2i', 'kebele', 'active', 1, '2026-01-21 17:00:33', '1234', 'What is your memorable security question?'),
(5, 'Statistician1', 'statistician1', 'statistician1@gmail.com', '', '', '', '', '$2y$10$UA4ytLeTzR9lcotifAKLEOxOCgwd0gqF3Gdg4JpcdaRiTaKcuZSby', 'statistician', 'active', 1, '2026-01-21 17:02:13', '1234', 'What is your memorable security question?'),
(6, 'tesfaw', 'Tesfaw', 'tesfaw@gmail.com', '+251930000000', '01', 'Woreda1', 'South Gondar', '$2y$10$xv.sZdr6Nyml5VZtJvBcuOx3NYyrOxgQWA6q2.vXi/mg2RiaJtG06', 'citizen', 'active', 1, '2026-01-21 17:09:46', '1234', 'What is your memorable security question?'),
(7, 'kebele2', 'kebele2', 'kebele2@gmail.com', '', '', '', '', '$2y$10$/fcQ9moz8bi9cLIbEh3cKOF6NcMIdz1DkjniJx4Dt1TTb1Dix5ocu', 'kebele', 'active', 1, '2026-01-21 17:41:15', '1234', 'What is your memorable security question?'),
(8, 'Dawit', 'Dawit', 'dawit@gmail.com', '+251920000000', '02', 'Woreda1', 'South Gondar', '$2y$10$5G1n1reZiIdTL8R7gHXe8.WIwFZyEQCqkEbFXCXRI7YO8cznYkeCS', 'citizen', 'active', 1, '2026-01-21 17:45:55', '1234', 'What is your memorable security question?'),
(9, 'Woreda2', 'woreda2', 'woreda2@gmail.com', '', '', '', '', '$2y$10$dAh9vW8y6TKcCKLRcgZJ4um2sB5RF9uqtOEMSVmSHP919cB96DLxe', 'woreda', 'active', 1, '2026-01-21 18:08:23', '1234', 'What is your memorable security question?'),
(10, 'Kebele3', 'kebele3', 'kebele3@gmail.com', '', '', '', '', '$2y$10$oaVNBYtR5CNFNJCwl2JZQOak9cpmyjv4GIvS.jeBJUD/wlj9wAz6y', 'kebele', 'active', 1, '2026-01-21 18:09:43', '1234', 'What is your memorable security question?'),
(11, 'Fasika', 'Fasika', 'fasika@gmail.com', '+251910000000', '03', 'woreda2', 'South Gondar', '$2y$10$GVx3tMwuQWW3bKEArrl0g.ejJdyF9bMZzQFoLI9NU/FlCEAyzQC/u', 'citizen', 'active', 1, '2026-01-21 18:13:37', '1234', 'What is your memorable security question?');

-- --------------------------------------------------------

--
-- Table structure for table `woredas`
--

CREATE TABLE `woredas` (
  `id` int(11) NOT NULL,
  `zone_id` int(11) NOT NULL,
  `woreda_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `woreda_officer_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `woredas`
--

INSERT INTO `woredas` (`id`, `zone_id`, `woreda_name`, `phone`, `woreda_officer_id`, `created_at`) VALUES
(1, 1, 'Woreda1', '+251910000000', 3, '2026-01-21 17:04:38'),
(2, 1, 'woreda2', '+251990000000', 9, '2026-01-21 18:10:25');

-- --------------------------------------------------------

--
-- Table structure for table `zones`
--

CREATE TABLE `zones` (
  `id` int(11) NOT NULL,
  `zone_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `zone_officer_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `zones`
--

INSERT INTO `zones` (`id`, `zone_name`, `phone`, `zone_officer_id`, `created_at`) VALUES
(1, 'South Gondar', '+251920000000', 2, '2026-01-21 17:03:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `birth_events`
--
ALTER TABLE `birth_events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `form_number` (`form_number`),
  ADD UNIQUE KEY `registration_uid` (`registration_uid`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `place_of_birth` (`place_of_birth`);

--
-- Indexes for table `death_events`
--
ALTER TABLE `death_events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `form_number` (`form_number`),
  ADD UNIQUE KEY `registration_uid` (`registration_uid`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `place_of_death` (`place_of_death`);

--
-- Indexes for table `divorce_events`
--
ALTER TABLE `divorce_events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `form_number` (`form_number`),
  ADD UNIQUE KEY `registration_uid` (`registration_uid`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `place_of_divorce` (`place_of_divorce`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `kebeles`
--
ALTER TABLE `kebeles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `woreda_id` (`woreda_id`,`kebele_name`),
  ADD KEY `zone_id` (`zone_id`),
  ADD KEY `kebele_officer_id` (`kebele_officer_id`);

--
-- Indexes for table `marriage_events`
--
ALTER TABLE `marriage_events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `form_number` (`form_number`),
  ADD UNIQUE KEY `registration_uid` (`registration_uid`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notices`
--
ALTER TABLE `notices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `woreda_id` (`woreda_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `citizen_id` (`citizen_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `woredas`
--
ALTER TABLE `woredas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `zone_id` (`zone_id`,`woreda_name`),
  ADD KEY `woreda_officer_id` (`woreda_officer_id`);

--
-- Indexes for table `zones`
--
ALTER TABLE `zones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `zone_name` (`zone_name`),
  ADD KEY `zone_officer_id` (`zone_officer_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `birth_events`
--
ALTER TABLE `birth_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `death_events`
--
ALTER TABLE `death_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `divorce_events`
--
ALTER TABLE `divorce_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kebeles`
--
ALTER TABLE `kebeles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `marriage_events`
--
ALTER TABLE `marriage_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notices`
--
ALTER TABLE `notices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `woredas`
--
ALTER TABLE `woredas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `zones`
--
ALTER TABLE `zones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `birth_events`
--
ALTER TABLE `birth_events`
  ADD CONSTRAINT `birth_events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `birth_events_ibfk_2` FOREIGN KEY (`place_of_birth`) REFERENCES `kebeles` (`id`);

--
-- Constraints for table `death_events`
--
ALTER TABLE `death_events`
  ADD CONSTRAINT `death_events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `death_events_ibfk_2` FOREIGN KEY (`place_of_death`) REFERENCES `kebeles` (`id`);

--
-- Constraints for table `divorce_events`
--
ALTER TABLE `divorce_events`
  ADD CONSTRAINT `divorce_events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `divorce_events_ibfk_2` FOREIGN KEY (`place_of_divorce`) REFERENCES `kebeles` (`id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `kebeles`
--
ALTER TABLE `kebeles`
  ADD CONSTRAINT `kebeles_ibfk_1` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`),
  ADD CONSTRAINT `kebeles_ibfk_2` FOREIGN KEY (`woreda_id`) REFERENCES `woredas` (`id`),
  ADD CONSTRAINT `kebeles_ibfk_3` FOREIGN KEY (`kebele_officer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `marriage_events`
--
ALTER TABLE `marriage_events`
  ADD CONSTRAINT `marriage_events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notices`
--
ALTER TABLE `notices`
  ADD CONSTRAINT `notices_ibfk_1` FOREIGN KEY (`woreda_id`) REFERENCES `woredas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`citizen_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `woredas`
--
ALTER TABLE `woredas`
  ADD CONSTRAINT `woredas_ibfk_1` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`),
  ADD CONSTRAINT `woredas_ibfk_2` FOREIGN KEY (`woreda_officer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `zones`
--
ALTER TABLE `zones`
  ADD CONSTRAINT `zones_ibfk_1` FOREIGN KEY (`zone_officer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
