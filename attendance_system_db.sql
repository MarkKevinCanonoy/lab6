-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 15, 2026 at 03:03 PM
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
-- Database: `attendance_system_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `status` enum('Present','Absent','Late') NOT NULL,
  `time_logged` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `schedule_id`, `student_id`, `status`, `time_logged`) VALUES
(2, 5, 9, 'Present', '2026-03-15 13:03:05'),
(3, 5, 7, 'Present', '2026-03-15 13:03:12'),
(4, 5, 12, 'Present', '2026-03-15 13:03:19'),
(5, 5, 8, 'Present', '2026-03-15 13:03:26'),
(6, 5, 10, 'Present', '2026-03-15 13:03:32'),
(7, 5, 11, 'Late', '2026-03-15 13:03:53'),
(8, 7, 11, 'Absent', '2026-03-15 13:09:04'),
(9, 8, 11, 'Absent', '2026-03-15 13:09:38'),
(10, 9, 11, 'Absent', '2026-03-15 13:10:04'),
(11, 10, 11, 'Absent', '2026-03-15 13:10:36');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `subject_id`) VALUES
(3, 9, 6),
(4, 12, 6),
(5, 8, 6),
(6, 7, 6),
(7, 10, 6),
(8, 11, 6),
(9, 9, 7),
(10, 12, 7),
(11, 8, 7),
(12, 7, 7),
(13, 10, 7),
(14, 11, 7);

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `class_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `subject_id`, `class_date`) VALUES
(5, 6, '2026-03-16'),
(6, 7, '2026-03-17'),
(7, 6, '2026-03-18'),
(8, 6, '2026-03-19'),
(9, 6, '2026-03-20'),
(10, 6, '2026-03-21'),
(11, 6, '2026-03-23');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `instructor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `code`, `name`, `instructor_id`) VALUES
(6, 'IT 303A', 'Capstone project and research 1', 6),
(7, 'IT 303', 'Information Assurance and Security 2', 5);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Instructor','Student') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin User', 'admin@test.com', '$2y$10$Gm7h4GAfOgbTm5RA02l2E.IM8bCafbTSH6pa75A2f/vbJNVhmqOLC', 'Admin', '2026-03-14 10:11:19'),
(5, 'Hange Zoe', 'hange@gmail.com', '$2y$10$Kvn.OL3pRprQVyjrD303oekx5UwzDkPlNTGZ5wCUX8E6oMNR3LSjq', 'Instructor', '2026-03-15 12:50:52'),
(6, 'Erwin Smith', 'erwin@gmail.com', '$2y$10$04IFsnfK7.elSsqg8qPkY.ecvfxNCYe6XhrQHganj.OFUCWVi7tgG', 'Instructor', '2026-03-15 12:51:46'),
(7, 'Mark Kevin Canonoy', 'markkevin@gmail.com', '$2y$10$.g5IsVKO1bY5X0cD6X0CMe3YG8fc9f9Gs5bkcnHXApCdLBE69ePvG', 'Student', '2026-03-15 12:52:30'),
(8, 'Jorge Mikhael Gubantes', 'jorge@gmail.com', '$2y$10$mw/E4UXui79AoKl./DBpZutxj052phR7zlpMpBsSovhkoLhv9xjXq', 'Student', '2026-03-15 12:52:58'),
(9, 'Ace Lozabe', 'ace@gmail.com', '$2y$10$sxGvh3UWnxTRkVWsY7Kfe.vZc8ese2gD7M8imAqPDfGWetPgt2G7S', 'Student', '2026-03-15 12:54:43'),
(10, 'Menard Baladjay', 'menard@gmail.com', '$2y$10$9hSRZBnZCSeX4zhLGSTjDu2XA5Y3raNS/x6WpQWdeAzaRAtgk6A1y', 'Student', '2026-03-15 12:54:59'),
(11, 'Ronaldo Dasigan', 'ronaldo@gmail.com', '$2y$10$h05jUkdpfsMLj48f1C.seuvqkYNNaSYXSFnHuUldI6YS6LZAo5xq6', 'Student', '2026-03-15 12:55:21'),
(12, 'Jeramae Pace', 'jeramae@gmail.com', '$2y$10$Q5uGjgd2p8q3Ab0CWK0rKeH8PJww3yBBn5i6fHvZENY3IxNyR3sHq', 'Student', '2026-03-15 12:55:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `instructor_id` (`instructor_id`);

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
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
