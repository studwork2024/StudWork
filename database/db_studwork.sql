-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 14, 2025 at 03:11 AM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_studwork`
--

-- --------------------------------------------------------

--
-- Table structure for table `employer_documents`
--

DROP TABLE IF EXISTS `employer_documents`;
CREATE TABLE IF NOT EXISTS `employer_documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employer_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employer_email` (`employer_email`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

DROP TABLE IF EXISTS `job_applications`;
CREATE TABLE IF NOT EXISTS `job_applications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `job_id` int NOT NULL,
  `employer_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `student_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `applied_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','accepted','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_posts`
--

DROP TABLE IF EXISTS `job_posts`;
CREATE TABLE IF NOT EXISTS `job_posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employer_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_type` enum('Part-time','Freelance') COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `posted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employer_email` (`employer_email`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_submissions`
--

DROP TABLE IF EXISTS `student_submissions`;
CREATE TABLE IF NOT EXISTS `student_submissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `resume` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valid_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_email` (`student_email`(250))
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fullname` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('student','employer','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `role`, `company_name`, `created_at`) VALUES
(11, 'Administrator', 'admin@studwork.ph', '$2y$10$yLrTLcSAF7RtoylMh/PHIO1J2punXX4KlxJh6ZT55at.WryqOKIMW', 'admin', NULL, '2025-12-13 19:24:33');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
