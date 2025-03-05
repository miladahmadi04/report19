-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 03, 2025 at 09:10 PM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `company_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'miladahmadi04', '$2y$10$o5o4zmdoIWiJ/CH7C4zCNOAr2mbmHkV0Aj03r4fgEoeCcXBh.qifW', '2025-03-01 06:11:46');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`) VALUES
(1, 'فیلمسازی', '2025-03-01 06:14:35'),
(2, 'عکاسی', '2025-03-01 06:14:40'),
(3, 'طراحی وب', '2025-03-01 06:14:47'),
(4, 'طراحی صفحه محصول', '2025-03-01 06:14:56'),
(5, 'SEO', '2025-03-03 17:19:22');

-- --------------------------------------------------------

--
-- Table structure for table `coach_reports`
--

CREATE TABLE `coach_reports` (
  `id` int(11) NOT NULL,
  `coach_id` int(11) NOT NULL,
  `personnel_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `report_date` date NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `team_name` varchar(100) DEFAULT NULL,
  `general_comments` text DEFAULT NULL,
  `coach_comment` text DEFAULT NULL,
  `coach_score` decimal(3,1) DEFAULT NULL,
  `statistics_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`statistics_json`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `coach_reports`
--

INSERT INTO `coach_reports` (`id`, `coach_id`, `personnel_id`, `receiver_id`, `company_id`, `report_date`, `date_from`, `date_to`, `team_name`, `general_comments`, `coach_comment`, `coach_score`, `statistics_json`, `created_at`) VALUES
(1, 1, 2, 0, 0, '2025-03-02', '2025-01-04', '2025-03-17', 'تست', NULL, NULL, NULL, NULL, '2025-03-02 08:00:10'),
(2, 1, 1, 0, 0, '2025-03-02', '2025-01-04', '2025-03-17', 'تست', NULL, NULL, NULL, NULL, '2025-03-02 08:00:10'),
(3, 1, 2, 0, 0, '2025-03-02', '2025-01-04', '2025-03-17', 'تست', NULL, NULL, NULL, NULL, '2025-03-02 08:02:02'),
(4, 1, 1, 0, 0, '2025-03-02', '2025-01-04', '2025-03-17', 'تست', NULL, NULL, NULL, NULL, '2025-03-02 08:02:02'),
(5, 1, 2, 0, 0, '2025-03-02', '2025-01-31', '2025-03-02', 'تست', NULL, NULL, NULL, NULL, '2025-03-02 08:03:04'),
(6, 1, 1, 0, 0, '2025-03-02', '2025-01-31', '2025-03-02', 'تست', NULL, NULL, NULL, NULL, '2025-03-02 08:03:04'),
(7, 1, 2, 0, 0, '2025-03-02', '2025-01-31', '2025-03-02', '', NULL, NULL, NULL, NULL, '2025-03-02 08:04:11'),
(8, 1, 2, 0, 0, '2025-03-02', '2025-01-31', '2025-03-02', '', NULL, NULL, NULL, NULL, '2025-03-02 08:09:13'),
(9, 1, 2, 0, 0, '2025-03-02', '2025-01-31', '2025-03-02', 'دیجیتال', NULL, NULL, NULL, NULL, '2025-03-02 08:12:52'),
(10, 1, 1, 0, 0, '2025-03-02', '2025-01-31', '2025-03-02', 'دیجیتال', NULL, NULL, NULL, NULL, '2025-03-02 08:12:52'),
(11, 1, 2, 0, 0, '2025-03-02', '2025-01-31', '2025-03-02', '', NULL, NULL, NULL, NULL, '2025-03-02 08:16:36'),
(12, 1, 1, 0, 0, '2025-03-02', '2025-01-31', '2025-03-02', '', NULL, NULL, NULL, NULL, '2025-03-02 08:16:36'),
(13, 1, 2, 0, 0, '2025-03-02', '2025-01-31', '2025-03-02', '', NULL, 'قفقفقف', '1.0', NULL, '2025-03-02 08:21:30'),
(14, 1, 1, 0, 0, '2025-03-02', '2025-01-31', '2025-03-02', '', NULL, 'قفقفقف', '2.0', NULL, '2025-03-02 08:21:30'),
(30, 1, 2, 1, 1, '2025-03-03', '2025-02-01', '2025-03-03', NULL, 'nmn', NULL, NULL, NULL, '2025-03-03 12:57:54');

-- --------------------------------------------------------

--
-- Table structure for table `coach_report_access`
--

CREATE TABLE `coach_report_access` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `personnel_id` int(11) NOT NULL,
  `can_view` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `coach_report_personnel`
--

CREATE TABLE `coach_report_personnel` (
  `id` int(11) NOT NULL,
  `coach_report_id` int(11) NOT NULL,
  `personnel_id` int(11) NOT NULL,
  `coach_comment` text DEFAULT NULL,
  `coach_score` decimal(3,1) DEFAULT NULL,
  `statistics_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`statistics_json`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `coach_report_personnel`
--

INSERT INTO `coach_report_personnel` (`id`, `coach_report_id`, `personnel_id`, `coach_comment`, `coach_score`, `statistics_json`, `created_at`) VALUES
(1, 1, 2, NULL, NULL, NULL, '2025-03-03 12:48:05'),
(2, 2, 1, NULL, NULL, NULL, '2025-03-03 12:48:05'),
(3, 3, 2, NULL, NULL, NULL, '2025-03-03 12:48:05'),
(4, 4, 1, NULL, NULL, NULL, '2025-03-03 12:48:05'),
(5, 5, 2, NULL, NULL, NULL, '2025-03-03 12:48:05'),
(6, 6, 1, NULL, NULL, NULL, '2025-03-03 12:48:05'),
(7, 7, 2, NULL, NULL, NULL, '2025-03-03 12:48:05'),
(8, 8, 2, NULL, NULL, NULL, '2025-03-03 12:48:05'),
(9, 9, 2, NULL, NULL, NULL, '2025-03-03 12:48:05'),
(10, 10, 1, NULL, NULL, NULL, '2025-03-03 12:48:05'),
(11, 11, 2, NULL, NULL, NULL, '2025-03-03 12:48:05'),
(12, 12, 1, NULL, NULL, NULL, '2025-03-03 12:48:05'),
(13, 13, 2, 'قفقفقف', '1.0', NULL, '2025-03-03 12:48:05'),
(14, 14, 1, 'قفقفقف', '2.0', NULL, '2025-03-03 12:48:05'),
(34, 30, 2, 'cdfdfd dfd', '2.0', '{\"report_count\":\"1\",\"categories\":[\"\\u0639\\u06a9\\u0627\\u0633\\u06cc\"],\"top_categories\":[{\"name\":\"\\u0639\\u06a9\\u0627\\u0633\\u06cc\",\"count\":\"1\"}]}', '2025-03-03 12:57:54'),
(35, 30, 5, 'dfdf dfd', '3.0', '{\"report_count\":\"0\",\"categories\":[],\"top_categories\":[]}', '2025-03-03 12:57:54');

-- --------------------------------------------------------

--
-- Table structure for table `coach_report_social_reports`
--

CREATE TABLE `coach_report_social_reports` (
  `coach_report_id` int(11) NOT NULL,
  `social_report_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `coach_report_social_reports`
--

INSERT INTO `coach_report_social_reports` (`coach_report_id`, `social_report_id`) VALUES
(13, 2),
(13, 3),
(14, 2),
(30, 2);

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_persian_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `is_active`, `created_at`) VALUES
(1, 'پیروز پک', 1, '2025-03-03 17:19:32'),
(2, 'کیان ورنا', 1, '2025-03-03 17:19:39'),
(3, 'پتروشیمی مالیا', 1, '2025-03-03 17:19:52'),
(4, 'دکتر الهه نصری', 1, '2025-03-03 17:20:05'),
(5, 'تعاونی مسکن بهداشت و درمان', 1, '2025-03-03 17:20:21'),
(6, 'گروه میلاد احمدی', 1, '2025-03-03 17:20:52');

-- --------------------------------------------------------

--
-- Table structure for table `contents`
--

CREATE TABLE `contents` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_persian_ci NOT NULL,
  `scenario` text COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `production_status_id` int(11) NOT NULL,
  `publish_status_id` int(11) NOT NULL,
  `content_format_id` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `publish_date` date NOT NULL,
  `publish_time` time DEFAULT '10:00:00',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `contents`
--

INSERT INTO `contents` (`id`, `company_id`, `title`, `scenario`, `description`, `production_status_id`, `publish_status_id`, `content_format_id`, `created_by`, `publish_date`, `publish_time`, `created_at`) VALUES
(2, 1, 'سییسی', 'سیس', 'یسی', 3, 2, NULL, 1, '2025-03-11', '10:00:00', '2025-03-03 19:08:36'),
(3, 1, 'لالا', 'للالا', 'لالا', 1, 1, NULL, 1, '2025-03-12', '10:00:00', '2025-03-03 19:13:35'),
(7, 1, 'تات', 'لالا', 'الا', 2, 2, NULL, 1, '2025-03-04', '10:00:00', '2025-03-03 19:35:09'),
(8, 1, 'میلاد', 'بل', 'بل', 1, 2, NULL, 1, '2025-03-13', '10:00:00', '2025-03-03 19:35:49'),
(9, 1, 'ننن', 'ننن', 'نن', 3, 2, NULL, 1, '2025-03-03', '10:00:00', '2025-03-03 19:44:46'),
(10, 1, 'وتنتن', 'نتن', 'تنتن', 1, 2, NULL, 1, '2025-03-12', '10:00:00', '2025-03-03 19:46:12'),
(11, 1, 'غعغ', '', '', 2, 2, NULL, 1, '2025-03-19', '10:00:00', '2025-03-03 19:47:49'),
(12, 1, 'قبلبلبل', '', '', 3, 1, NULL, 1, '2025-03-10', '10:00:00', '2025-03-03 19:49:08'),
(13, 1, 'یبیبیب', 'یبی', 'یبی', 1, 2, NULL, 1, '2025-03-18', '10:00:00', '2025-03-03 19:50:03'),
(14, 1, 'توت22فغفغ', '', '', 1, 2, NULL, 1, '2025-03-18', '10:00:00', '2025-03-03 19:51:08');

-- --------------------------------------------------------

--
-- Table structure for table `content_audiences`
--

CREATE TABLE `content_audiences` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `content_audiences`
--

INSERT INTO `content_audiences` (`id`, `company_id`, `name`, `description`, `created_at`) VALUES
(2, 3, 'شرکت‌های پتروشیمی', NULL, '2025-03-03 17:33:03'),
(4, 1, 'ببببب', NULL, '2025-03-03 19:18:56'),
(5, 1, 'عموم مردم', NULL, '2025-03-03 19:31:43'),
(6, 1, 'جوانان', NULL, '2025-03-03 19:31:43'),
(7, 1, 'نوجوانان', NULL, '2025-03-03 19:31:43'),
(8, 1, 'کودکان', NULL, '2025-03-03 19:31:43'),
(9, 1, 'بزرگسالان', NULL, '2025-03-03 19:31:43'),
(10, 1, 'سالمندان', NULL, '2025-03-03 19:31:43'),
(11, 1, 'دانشجویان', NULL, '2025-03-03 19:31:43'),
(12, 1, 'دانش‌آموزان', NULL, '2025-03-03 19:31:43'),
(13, 1, 'متخصصان', NULL, '2025-03-03 19:31:43'),
(14, 2, 'عموم مردم', NULL, '2025-03-03 19:31:43'),
(15, 2, 'جوانان', NULL, '2025-03-03 19:31:43'),
(16, 2, 'نوجوانان', NULL, '2025-03-03 19:31:43'),
(17, 2, 'کودکان', NULL, '2025-03-03 19:31:43'),
(18, 2, 'بزرگسالان', NULL, '2025-03-03 19:31:43'),
(19, 2, 'سالمندان', NULL, '2025-03-03 19:31:43'),
(20, 2, 'دانشجویان', NULL, '2025-03-03 19:31:43'),
(21, 2, 'دانش‌آموزان', NULL, '2025-03-03 19:31:43'),
(22, 2, 'متخصصان', NULL, '2025-03-03 19:31:43'),
(23, 3, 'عموم مردم', NULL, '2025-03-03 19:31:43'),
(24, 3, 'جوانان', NULL, '2025-03-03 19:31:43'),
(25, 3, 'نوجوانان', NULL, '2025-03-03 19:31:43'),
(26, 3, 'کودکان', NULL, '2025-03-03 19:31:43'),
(27, 3, 'بزرگسالان', NULL, '2025-03-03 19:31:44'),
(28, 3, 'سالمندان', NULL, '2025-03-03 19:31:44'),
(29, 3, 'دانشجویان', NULL, '2025-03-03 19:31:44'),
(30, 3, 'دانش‌آموزان', NULL, '2025-03-03 19:31:44'),
(31, 3, 'متخصصان', NULL, '2025-03-03 19:31:44'),
(32, 4, 'عموم مردم', NULL, '2025-03-03 19:31:44'),
(33, 4, 'جوانان', NULL, '2025-03-03 19:31:44'),
(34, 4, 'نوجوانان', NULL, '2025-03-03 19:31:44'),
(35, 4, 'کودکان', NULL, '2025-03-03 19:31:44'),
(36, 4, 'بزرگسالان', NULL, '2025-03-03 19:31:44'),
(37, 4, 'سالمندان', NULL, '2025-03-03 19:31:44'),
(38, 4, 'دانشجویان', NULL, '2025-03-03 19:31:44'),
(39, 4, 'دانش‌آموزان', NULL, '2025-03-03 19:31:44'),
(40, 4, 'متخصصان', NULL, '2025-03-03 19:31:44'),
(41, 5, 'عموم مردم', NULL, '2025-03-03 19:31:44'),
(42, 5, 'جوانان', NULL, '2025-03-03 19:31:44'),
(43, 5, 'نوجوانان', NULL, '2025-03-03 19:31:44'),
(44, 5, 'کودکان', NULL, '2025-03-03 19:31:44'),
(45, 5, 'بزرگسالان', NULL, '2025-03-03 19:31:44'),
(46, 5, 'سالمندان', NULL, '2025-03-03 19:31:44'),
(47, 5, 'دانشجویان', NULL, '2025-03-03 19:31:44'),
(48, 5, 'دانش‌آموزان', NULL, '2025-03-03 19:31:44'),
(49, 5, 'متخصصان', NULL, '2025-03-03 19:31:44'),
(50, 6, 'عموم مردم', NULL, '2025-03-03 19:31:44'),
(51, 6, 'جوانان', NULL, '2025-03-03 19:31:44'),
(52, 6, 'نوجوانان', NULL, '2025-03-03 19:31:44'),
(53, 6, 'کودکان', NULL, '2025-03-03 19:31:44'),
(54, 6, 'بزرگسالان', NULL, '2025-03-03 19:31:44'),
(55, 6, 'سالمندان', NULL, '2025-03-03 19:31:44'),
(56, 6, 'دانشجویان', NULL, '2025-03-03 19:31:44'),
(57, 6, 'دانش‌آموزان', NULL, '2025-03-03 19:31:44'),
(58, 6, 'متخصصان', NULL, '2025-03-03 19:31:44');

-- --------------------------------------------------------

--
-- Table structure for table `content_audience_content`
--

CREATE TABLE `content_audience_content` (
  `content_id` int(11) NOT NULL,
  `audience_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `content_audience_content`
--

INSERT INTO `content_audience_content` (`content_id`, `audience_id`) VALUES
(7, 4),
(9, 4),
(10, 4),
(11, 11),
(12, 4),
(13, 4),
(14, 4);

-- --------------------------------------------------------

--
-- Table structure for table `content_calendar_settings`
--

CREATE TABLE `content_calendar_settings` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `field_name` varchar(50) NOT NULL,
  `is_visible` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `content_calendar_settings`
--

INSERT INTO `content_calendar_settings` (`id`, `company_id`, `field_name`, `is_visible`, `display_order`, `created_at`) VALUES
(6, 1, 'title', 1, 1, '2025-03-03 20:08:14'),
(7, 1, 'publish_date', 1, 2, '2025-03-03 20:08:14'),
(8, 1, 'publish_time', 1, 3, '2025-03-03 20:08:14'),
(9, 1, 'production_status', 1, 4, '2025-03-03 20:08:14'),
(10, 1, 'publish_status', 1, 5, '2025-03-03 20:08:14'),
(11, 1, 'topics', 1, 6, '2025-03-03 20:08:14'),
(12, 1, 'audiences', 1, 7, '2025-03-03 20:08:14'),
(13, 1, 'types', 1, 8, '2025-03-03 20:08:14'),
(14, 1, 'platforms', 1, 9, '2025-03-03 20:08:14'),
(15, 1, 'responsible', 1, 10, '2025-03-03 20:08:14'),
(16, 1, 'scenario', 1, 11, '2025-03-03 20:08:14'),
(17, 1, 'description', 1, 12, '2025-03-03 20:08:14');

-- --------------------------------------------------------

--
-- Table structure for table `content_formats`
--

CREATE TABLE `content_formats` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `can_delete` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_system` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `content_formats`
--

INSERT INTO `content_formats` (`id`, `company_id`, `name`, `is_default`, `can_delete`, `created_at`, `is_system`) VALUES
(4, 1, 'خلاصه', 0, 1, '2025-03-03 18:55:45', 1),
(5, 1, 'کامل', 0, 1, '2025-03-03 18:55:45', 1),
(6, 1, 'تیزر', 0, 1, '2025-03-03 18:55:45', 1),
(7, 2, 'خلاصه', 0, 1, '2025-03-03 18:55:46', 1),
(8, 2, 'کامل', 0, 1, '2025-03-03 18:55:46', 1),
(9, 2, 'تیزر', 0, 1, '2025-03-03 18:55:46', 1),
(10, 3, 'خلاصه', 0, 1, '2025-03-03 18:55:46', 1),
(11, 3, 'کامل', 0, 1, '2025-03-03 18:55:46', 1),
(12, 3, 'تیزر', 0, 1, '2025-03-03 18:55:46', 1),
(13, 4, 'خلاصه', 0, 1, '2025-03-03 18:55:46', 1),
(14, 4, 'کامل', 0, 1, '2025-03-03 18:55:46', 1),
(15, 4, 'تیزر', 0, 1, '2025-03-03 18:55:46', 1),
(16, 5, 'خلاصه', 0, 1, '2025-03-03 18:55:46', 1),
(17, 5, 'کامل', 0, 1, '2025-03-03 18:55:46', 1),
(18, 5, 'تیزر', 0, 1, '2025-03-03 18:55:46', 1),
(19, 6, 'خلاصه', 0, 1, '2025-03-03 18:55:46', 1),
(20, 6, 'کامل', 0, 1, '2025-03-03 18:55:46', 1),
(21, 6, 'تیزر', 0, 1, '2025-03-03 18:55:46', 1),
(22, 5, 'تست2', 0, 1, '2025-03-03 19:07:12', 0),
(23, 1, 'تستسس', 0, 1, '2025-03-03 19:09:32', 0);

-- --------------------------------------------------------

--
-- Table structure for table `content_platforms`
--

CREATE TABLE `content_platforms` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `content_platforms`
--

INSERT INTO `content_platforms` (`id`, `company_id`, `name`, `is_default`, `created_at`) VALUES
(1, 1, 'Instagram', 0, '2025-03-03 15:08:15'),
(2, 1, 'Youtube', 0, '2025-03-03 15:08:29'),
(3, 1, 'اینستاگرام', 0, '2025-03-03 19:21:14'),
(4, 1, 'تلگرام', 0, '2025-03-03 19:21:14'),
(5, 1, 'لینکدین', 0, '2025-03-03 19:21:14'),
(6, 1, 'توییتر', 0, '2025-03-03 19:21:14'),
(7, 1, 'یوتیوب', 0, '2025-03-03 19:21:14'),
(8, 1, 'وب‌سایت', 0, '2025-03-03 19:21:14'),
(9, 1, 'واتساپ', 0, '2025-03-03 19:21:14'),
(10, 2, 'اینستاگرام', 0, '2025-03-03 19:21:14'),
(11, 2, 'تلگرام', 0, '2025-03-03 19:21:14'),
(12, 2, 'لینکدین', 0, '2025-03-03 19:21:14'),
(13, 2, 'توییتر', 0, '2025-03-03 19:21:14'),
(14, 2, 'یوتیوب', 0, '2025-03-03 19:21:14'),
(15, 2, 'وب‌سایت', 0, '2025-03-03 19:21:14'),
(16, 2, 'واتساپ', 0, '2025-03-03 19:21:14'),
(17, 3, 'اینستاگرام', 0, '2025-03-03 19:21:14'),
(18, 3, 'تلگرام', 0, '2025-03-03 19:21:14'),
(19, 3, 'لینکدین', 0, '2025-03-03 19:21:14'),
(20, 3, 'توییتر', 0, '2025-03-03 19:21:14'),
(21, 3, 'یوتیوب', 0, '2025-03-03 19:21:14'),
(22, 3, 'وب‌سایت', 0, '2025-03-03 19:21:14'),
(23, 3, 'واتساپ', 0, '2025-03-03 19:21:14'),
(24, 4, 'اینستاگرام', 0, '2025-03-03 19:21:14'),
(25, 4, 'تلگرام', 0, '2025-03-03 19:21:14'),
(26, 4, 'لینکدین', 0, '2025-03-03 19:21:14'),
(27, 4, 'توییتر', 0, '2025-03-03 19:21:14'),
(28, 4, 'یوتیوب', 0, '2025-03-03 19:21:14'),
(29, 4, 'وب‌سایت', 0, '2025-03-03 19:21:14'),
(30, 4, 'واتساپ', 0, '2025-03-03 19:21:14'),
(31, 5, 'اینستاگرام', 0, '2025-03-03 19:21:14'),
(32, 5, 'تلگرام', 0, '2025-03-03 19:21:14'),
(33, 5, 'لینکدین', 0, '2025-03-03 19:21:14'),
(34, 5, 'توییتر', 0, '2025-03-03 19:21:14'),
(35, 5, 'یوتیوب', 0, '2025-03-03 19:21:14'),
(36, 5, 'وب‌سایت', 0, '2025-03-03 19:21:14'),
(37, 5, 'واتساپ', 0, '2025-03-03 19:21:14'),
(38, 6, 'اینستاگرام', 0, '2025-03-03 19:21:15'),
(39, 6, 'تلگرام', 0, '2025-03-03 19:21:15'),
(40, 6, 'لینکدین', 0, '2025-03-03 19:21:15'),
(41, 6, 'توییتر', 0, '2025-03-03 19:21:15'),
(42, 6, 'یوتیوب', 0, '2025-03-03 19:21:15'),
(43, 6, 'وب‌سایت', 0, '2025-03-03 19:21:15'),
(44, 6, 'واتساپ', 0, '2025-03-03 19:21:15');

-- --------------------------------------------------------

--
-- Table structure for table `content_platform_relations`
--

CREATE TABLE `content_platform_relations` (
  `content_id` int(11) NOT NULL,
  `platform_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `content_platform_relations`
--

INSERT INTO `content_platform_relations` (`content_id`, `platform_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(7, 1),
(9, 1),
(10, 1),
(11, 4),
(12, 1),
(13, 1),
(14, 4);

-- --------------------------------------------------------

--
-- Table structure for table `content_post_publish_processes`
--

CREATE TABLE `content_post_publish_processes` (
  `id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `format_id` int(11) NOT NULL,
  `days_after` int(11) NOT NULL DEFAULT 0,
  `publish_time` time DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `content_production_statuses`
--

CREATE TABLE `content_production_statuses` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_persian_ci NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `is_system` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `content_production_statuses`
--

INSERT INTO `content_production_statuses` (`id`, `company_id`, `name`, `is_default`, `is_system`, `created_at`) VALUES
(1, 1, 'محتوا تولید نشده', 1, 1, '2025-03-03 18:37:52'),
(2, 1, 'محتوا در حال تولید است', 0, 1, '2025-03-03 18:37:52'),
(3, 1, 'محتوا تولید شده', 0, 1, '2025-03-03 18:37:52'),
(4, 2, 'محتوا تولید نشده', 1, 1, '2025-03-03 18:37:52'),
(5, 2, 'محتوا در حال تولید است', 0, 1, '2025-03-03 18:37:52'),
(6, 2, 'محتوا تولید شده', 0, 1, '2025-03-03 18:37:52'),
(7, 3, 'محتوا تولید نشده', 1, 1, '2025-03-03 18:37:52'),
(8, 3, 'محتوا در حال تولید است', 0, 1, '2025-03-03 18:37:52'),
(9, 3, 'محتوا تولید شده', 0, 1, '2025-03-03 18:37:52'),
(10, 4, 'محتوا تولید نشده', 1, 1, '2025-03-03 18:37:52'),
(11, 4, 'محتوا در حال تولید است', 0, 1, '2025-03-03 18:37:52'),
(12, 4, 'محتوا تولید شده', 0, 1, '2025-03-03 18:37:52'),
(13, 5, 'محتوا تولید نشده', 1, 1, '2025-03-03 18:37:52'),
(14, 5, 'محتوا در حال تولید است', 0, 1, '2025-03-03 18:37:52'),
(15, 5, 'محتوا تولید شده', 0, 1, '2025-03-03 18:37:52'),
(16, 6, 'محتوا تولید نشده', 1, 1, '2025-03-03 18:37:52'),
(17, 6, 'محتوا در حال تولید است', 0, 1, '2025-03-03 18:37:52'),
(18, 6, 'محتوا تولید شده', 0, 1, '2025-03-03 18:37:52');

-- --------------------------------------------------------

--
-- Table structure for table `content_publish_statuses`
--

CREATE TABLE `content_publish_statuses` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_persian_ci NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `is_system` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `content_publish_statuses`
--

INSERT INTO `content_publish_statuses` (`id`, `company_id`, `name`, `is_default`, `is_system`, `created_at`) VALUES
(1, 1, 'منتشر نشده', 1, 1, '2025-03-03 18:37:52'),
(2, 1, 'منتشر شده', 0, 1, '2025-03-03 18:37:52'),
(3, 2, 'منتشر نشده', 1, 1, '2025-03-03 18:37:52'),
(4, 2, 'منتشر شده', 0, 1, '2025-03-03 18:37:52'),
(5, 3, 'منتشر نشده', 1, 1, '2025-03-03 18:37:52'),
(6, 3, 'منتشر شده', 0, 1, '2025-03-03 18:37:52'),
(7, 4, 'منتشر نشده', 1, 1, '2025-03-03 18:37:52'),
(8, 4, 'منتشر شده', 0, 1, '2025-03-03 18:37:52'),
(9, 5, 'منتشر نشده', 1, 1, '2025-03-03 18:37:52'),
(10, 5, 'منتشر شده', 0, 1, '2025-03-03 18:37:52'),
(11, 6, 'منتشر نشده', 1, 1, '2025-03-03 18:37:52'),
(12, 6, 'منتشر شده', 0, 1, '2025-03-03 18:37:52');

-- --------------------------------------------------------

--
-- Table structure for table `content_tasks`
--

CREATE TABLE `content_tasks` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `can_delete` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `content_tasks`
--

INSERT INTO `content_tasks` (`id`, `company_id`, `name`, `is_default`, `can_delete`, `created_at`) VALUES
(1, 1, 'وظیفه اصلی', 1, 0, '2025-03-03 13:07:17'),
(2, 1, 'فرآیند پس از انتشار', 1, 0, '2025-03-03 13:07:17'),
(3, 3, 'وظیفه اصلی', 1, 0, '2025-03-03 18:43:11'),
(4, 3, 'فرآیند پس از انتشار', 1, 0, '2025-03-03 18:43:11'),
(5, 1, 'تولید محتوا', 0, 1, '2025-03-03 19:21:14'),
(6, 1, 'ویرایش محتوا', 0, 1, '2025-03-03 19:21:14'),
(7, 1, 'بازبینی محتوا', 0, 1, '2025-03-03 19:21:14'),
(8, 1, 'تأیید نهایی', 0, 1, '2025-03-03 19:21:14'),
(9, 1, 'انتشار محتوا', 0, 1, '2025-03-03 19:21:14'),
(10, 1, 'پیگیری بازخوردها', 0, 1, '2025-03-03 19:21:14'),
(11, 2, 'تولید محتوا', 0, 1, '2025-03-03 19:21:14'),
(12, 2, 'ویرایش محتوا', 0, 1, '2025-03-03 19:21:14'),
(13, 2, 'بازبینی محتوا', 0, 1, '2025-03-03 19:21:14'),
(14, 2, 'تأیید نهایی', 0, 1, '2025-03-03 19:21:14'),
(15, 2, 'انتشار محتوا', 0, 1, '2025-03-03 19:21:14'),
(16, 2, 'پیگیری بازخوردها', 0, 1, '2025-03-03 19:21:14'),
(17, 3, 'تولید محتوا', 0, 1, '2025-03-03 19:21:14'),
(18, 3, 'ویرایش محتوا', 0, 1, '2025-03-03 19:21:14'),
(19, 3, 'بازبینی محتوا', 0, 1, '2025-03-03 19:21:14'),
(20, 3, 'تأیید نهایی', 0, 1, '2025-03-03 19:21:14'),
(21, 3, 'انتشار محتوا', 0, 1, '2025-03-03 19:21:14'),
(22, 3, 'پیگیری بازخوردها', 0, 1, '2025-03-03 19:21:14'),
(23, 4, 'تولید محتوا', 0, 1, '2025-03-03 19:21:14'),
(24, 4, 'ویرایش محتوا', 0, 1, '2025-03-03 19:21:14'),
(25, 4, 'بازبینی محتوا', 0, 1, '2025-03-03 19:21:14'),
(26, 4, 'تأیید نهایی', 0, 1, '2025-03-03 19:21:14'),
(27, 4, 'انتشار محتوا', 0, 1, '2025-03-03 19:21:14'),
(28, 4, 'پیگیری بازخوردها', 0, 1, '2025-03-03 19:21:14'),
(29, 5, 'تولید محتوا', 0, 1, '2025-03-03 19:21:15'),
(30, 5, 'ویرایش محتوا', 0, 1, '2025-03-03 19:21:15'),
(31, 5, 'بازبینی محتوا', 0, 1, '2025-03-03 19:21:15'),
(32, 5, 'تأیید نهایی', 0, 1, '2025-03-03 19:21:15'),
(33, 5, 'انتشار محتوا', 0, 1, '2025-03-03 19:21:15'),
(34, 5, 'پیگیری بازخوردها', 0, 1, '2025-03-03 19:21:15'),
(35, 6, 'تولید محتوا', 0, 1, '2025-03-03 19:21:15'),
(36, 6, 'ویرایش محتوا', 0, 1, '2025-03-03 19:21:15'),
(37, 6, 'بازبینی محتوا', 0, 1, '2025-03-03 19:21:15'),
(38, 6, 'تأیید نهایی', 0, 1, '2025-03-03 19:21:15'),
(39, 6, 'انتشار محتوا', 0, 1, '2025-03-03 19:21:15'),
(40, 6, 'پیگیری بازخوردها', 0, 1, '2025-03-03 19:21:15');

-- --------------------------------------------------------

--
-- Table structure for table `content_task_assignments`
--

CREATE TABLE `content_task_assignments` (
  `id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `personnel_id` int(11) NOT NULL,
  `is_completed` tinyint(1) DEFAULT 0,
  `completion_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `content_task_assignments`
--

INSERT INTO `content_task_assignments` (`id`, `content_id`, `task_id`, `personnel_id`, `is_completed`, `completion_date`) VALUES
(1, 1, 2, 2, 0, NULL),
(2, 1, 1, 5, 0, NULL),
(3, 1, 2, 3, 0, NULL),
(4, 1, 1, 3, 0, NULL),
(5, 2, 2, 3, 0, NULL),
(6, 2, 1, 3, 0, NULL),
(7, 3, 2, 3, 0, NULL),
(8, 3, 1, 3, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `content_task_relations`
--

CREATE TABLE `content_task_relations` (
  `content_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `responsible_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `content_templates`
--

CREATE TABLE `content_templates` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `scenario` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `content_topics`
--

CREATE TABLE `content_topics` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `content_topics`
--

INSERT INTO `content_topics` (`id`, `company_id`, `name`, `is_default`, `created_at`) VALUES
(1, 1, 'دستگاه بسته بندی', 0, '2025-03-03 16:13:25'),
(2, 1, 'آموزشی', 0, '2025-03-03 19:21:14'),
(3, 1, 'خبری', 0, '2025-03-03 19:21:14'),
(4, 1, 'سرگرمی', 0, '2025-03-03 19:21:14'),
(5, 1, 'تبلیغاتی', 0, '2025-03-03 19:21:14'),
(6, 1, 'اطلاع‌رسانی', 0, '2025-03-03 19:21:14'),
(7, 2, 'آموزشی', 0, '2025-03-03 19:21:14'),
(8, 2, 'خبری', 0, '2025-03-03 19:21:14'),
(9, 2, 'سرگرمی', 0, '2025-03-03 19:21:14'),
(10, 2, 'تبلیغاتی', 0, '2025-03-03 19:21:14'),
(11, 2, 'اطلاع‌رسانی', 0, '2025-03-03 19:21:14'),
(12, 3, 'آموزشی', 0, '2025-03-03 19:21:14'),
(13, 3, 'خبری', 0, '2025-03-03 19:21:14'),
(14, 3, 'سرگرمی', 0, '2025-03-03 19:21:14'),
(15, 3, 'تبلیغاتی', 0, '2025-03-03 19:21:14'),
(16, 3, 'اطلاع‌رسانی', 0, '2025-03-03 19:21:14'),
(17, 4, 'آموزشی', 0, '2025-03-03 19:21:14'),
(18, 4, 'خبری', 0, '2025-03-03 19:21:14'),
(19, 4, 'سرگرمی', 0, '2025-03-03 19:21:14'),
(20, 4, 'تبلیغاتی', 0, '2025-03-03 19:21:14'),
(21, 4, 'اطلاع‌رسانی', 0, '2025-03-03 19:21:14'),
(22, 5, 'آموزشی', 0, '2025-03-03 19:21:14'),
(23, 5, 'خبری', 0, '2025-03-03 19:21:14'),
(24, 5, 'سرگرمی', 0, '2025-03-03 19:21:14'),
(25, 5, 'تبلیغاتی', 0, '2025-03-03 19:21:14'),
(26, 5, 'اطلاع‌رسانی', 0, '2025-03-03 19:21:14'),
(27, 6, 'آموزشی', 0, '2025-03-03 19:21:15'),
(28, 6, 'خبری', 0, '2025-03-03 19:21:15'),
(29, 6, 'سرگرمی', 0, '2025-03-03 19:21:15'),
(30, 6, 'تبلیغاتی', 0, '2025-03-03 19:21:15'),
(31, 6, 'اطلاع‌رسانی', 0, '2025-03-03 19:21:15');

-- --------------------------------------------------------

--
-- Table structure for table `content_topic_relations`
--

CREATE TABLE `content_topic_relations` (
  `content_id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `content_topic_relations`
--

INSERT INTO `content_topic_relations` (`content_id`, `topic_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(7, 2),
(8, 2),
(9, 2),
(10, 2),
(11, 2),
(12, 2),
(13, 2),
(14, 2),
(14, 3);

-- --------------------------------------------------------

--
-- Table structure for table `content_types`
--

CREATE TABLE `content_types` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `content_types`
--

INSERT INTO `content_types` (`id`, `company_id`, `name`, `is_default`, `created_at`) VALUES
(1, 3, 'gilsonite', 0, '2025-03-03 18:10:08'),
(2, 1, 'فیلم', 0, '2025-03-03 18:11:27');

-- --------------------------------------------------------

--
-- Table structure for table `content_type_relations`
--

CREATE TABLE `content_type_relations` (
  `content_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `content_type_relations`
--

INSERT INTO `content_type_relations` (`content_id`, `type_id`) VALUES
(2, 2),
(3, 2),
(7, 2),
(8, 2),
(9, 2),
(10, 2),
(11, 2),
(12, 2),
(13, 2),
(14, 2);

-- --------------------------------------------------------

--
-- Table structure for table `kpi_models`
--

CREATE TABLE `kpi_models` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `model_type` enum('growth_over_time','percentage_of_field') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `kpi_models`
--

INSERT INTO `kpi_models` (`id`, `name`, `description`, `model_type`, `created_at`) VALUES
(1, 'رشد زمانی', 'انتظار دارم فیلد X هر Y روز به مقدار N رشد کند', 'growth_over_time', '2025-03-01 06:11:46'),
(2, 'درصد از فیلد دیگر', 'انتظار دارم فیلد X به مقدار N درصد از فیلد دیگر باشد', 'percentage_of_field', '2025-03-01 06:11:46');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `scheduled_time` datetime DEFAULT NULL,
  `is_sent` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `monthly_reports`
--

CREATE TABLE `monthly_reports` (
  `id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `creator_id` int(11) DEFAULT NULL,
  `report_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `monthly_reports`
--

INSERT INTO `monthly_reports` (`id`, `page_id`, `creator_id`, `report_date`, `created_at`) VALUES
(2, 1, NULL, '2025-02-01', '2025-03-01 06:41:36'),
(3, 1, NULL, '2025-03-01', '2025-03-01 06:43:34');

-- --------------------------------------------------------

--
-- Table structure for table `monthly_report_values`
--

CREATE TABLE `monthly_report_values` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `field_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `monthly_report_values`
--

INSERT INTO `monthly_report_values` (`id`, `report_id`, `field_id`, `field_value`, `created_at`) VALUES
(7, 2, 1, 'http://localhost/report2/social_pages.php', '2025-03-01 06:41:36'),
(8, 2, 2, '500', '2025-03-01 06:41:36'),
(9, 2, 3, '20', '2025-03-01 06:41:36'),
(10, 2, 4, '50', '2025-03-01 06:41:36'),
(11, 2, 5, '20', '2025-03-01 06:41:36'),
(12, 2, 6, '55', '2025-03-01 06:41:36'),
(13, 3, 1, 'http://localhost/report2/social_pages.php', '2025-03-01 06:43:34'),
(14, 3, 2, '560', '2025-03-01 06:43:34'),
(15, 3, 3, '5', '2025-03-01 06:43:34'),
(16, 3, 4, '66', '2025-03-01 06:43:34'),
(17, 3, 5, '20', '2025-03-01 06:43:34'),
(18, 3, 6, '500', '2025-03-01 06:43:34');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, 2, 'پیام جدید از miladahmadi04: تست', 'view_message.php?id=0', 0, '2025-03-01 17:11:13'),
(2, 1, 'پیام جدید از عرفان عباسپور: RE: تست', 'view_message.php?id=0', 0, '2025-03-01 17:13:21');

-- --------------------------------------------------------

--
-- Table structure for table `page_kpis`
--

CREATE TABLE `page_kpis` (
  `id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `kpi_model_id` int(11) NOT NULL,
  `related_field_id` int(11) DEFAULT NULL,
  `growth_value` decimal(10,2) DEFAULT NULL,
  `growth_period_days` int(11) DEFAULT NULL,
  `percentage_value` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `page_kpis`
--

INSERT INTO `page_kpis` (`id`, `page_id`, `field_id`, `kpi_model_id`, `related_field_id`, `growth_value`, `growth_period_days`, `percentage_value`, `created_at`) VALUES
(1, 1, 2, 1, NULL, '10.00', 30, NULL, '2025-03-01 06:36:18'),
(2, 1, 3, 1, NULL, '10.00', 30, NULL, '2025-03-01 06:36:32'),
(3, 1, 4, 1, NULL, '10.00', 30, NULL, '2025-03-01 06:37:54'),
(4, 1, 5, 2, 2, NULL, NULL, '10.00', '2025-03-01 06:38:23'),
(5, 1, 6, 2, 5, NULL, NULL, '10.00', '2025-03-01 06:38:36');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_persian_ci NOT NULL,
  `code` varchar(100) COLLATE utf8mb4_persian_ci NOT NULL,
  `description` text COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `code`, `description`, `created_at`) VALUES
(1, 'مشاهده داشبورد', 'view_dashboard', 'دسترسی به صفحه داشبورد', '2025-03-03 17:45:58'),
(2, 'مشاهده شرکت‌ها', 'view_companies', 'مشاهده لیست شرکت‌ها', '2025-03-03 17:45:58'),
(3, 'افزودن شرکت', 'add_company', 'افزودن شرکت جدید', '2025-03-03 17:45:58'),
(4, 'ویرایش شرکت', 'edit_company', 'ویرایش اطلاعات شرکت', '2025-03-03 17:45:58'),
(5, 'حذف شرکت', 'delete_company', 'حذف شرکت', '2025-03-03 17:45:58'),
(6, 'تغییر وضعیت شرکت', 'toggle_company', 'فعال/غیرفعال کردن شرکت', '2025-03-03 17:45:58'),
(7, 'مشاهده پرسنل', 'view_personnel', 'مشاهده لیست پرسنل', '2025-03-03 17:45:58'),
(8, 'افزودن پرسنل', 'add_personnel', 'افزودن پرسنل جدید', '2025-03-03 17:45:58'),
(9, 'ویرایش پرسنل', 'edit_personnel', 'ویرایش اطلاعات پرسنل', '2025-03-03 17:45:58'),
(10, 'حذف پرسنل', 'delete_personnel', 'حذف پرسنل', '2025-03-03 17:45:58'),
(11, 'تغییر وضعیت پرسنل', 'toggle_personnel', 'فعال/غیرفعال کردن پرسنل', '2025-03-03 17:45:58'),
(12, 'بازنشانی رمز عبور', 'reset_password', 'بازنشانی رمز عبور پرسنل', '2025-03-03 17:45:58'),
(13, 'مشاهده نقش‌ها', 'view_roles', 'مشاهده لیست نقش‌ها', '2025-03-03 17:45:58'),
(14, 'افزودن نقش', 'add_role', 'افزودن نقش جدید', '2025-03-03 17:45:58'),
(15, 'ویرایش نقش', 'edit_role', 'ویرایش اطلاعات نقش', '2025-03-03 17:45:58'),
(16, 'حذف نقش', 'delete_role', 'حذف نقش', '2025-03-03 17:45:58'),
(17, 'مدیریت دسترسی‌ها', 'manage_permissions', 'تنظیم دسترسی‌های هر نقش', '2025-03-03 17:45:58'),
(18, 'مشاهده دسته‌بندی‌ها', 'view_categories', 'مشاهده لیست دسته‌بندی‌ها', '2025-03-03 17:45:58'),
(19, 'افزودن دسته‌بندی', 'add_category', 'افزودن دسته‌بندی جدید', '2025-03-03 17:45:58'),
(20, 'ویرایش دسته‌بندی', 'edit_category', 'ویرایش اطلاعات دسته‌بندی', '2025-03-03 17:45:58'),
(21, 'حذف دسته‌بندی', 'delete_category', 'حذف دسته‌بندی', '2025-03-03 17:45:58'),
(22, 'مشاهده گزارش‌های روزانه', 'view_daily_reports', 'مشاهده لیست گزارش‌های روزانه', '2025-03-03 17:45:58'),
(23, 'افزودن گزارش روزانه', 'add_daily_report', 'ثبت گزارش روزانه جدید', '2025-03-03 17:45:58'),
(24, 'ویرایش گزارش روزانه', 'edit_daily_report', 'ویرایش گزارش روزانه', '2025-03-03 17:45:58'),
(25, 'حذف گزارش روزانه', 'delete_daily_report', 'حذف گزارش روزانه', '2025-03-03 17:45:58'),
(26, 'مشاهده گزارش‌های ماهانه', 'view_monthly_reports', 'مشاهده لیست گزارش‌های ماهانه', '2025-03-03 17:45:58'),
(27, 'افزودن گزارش ماهانه', 'add_monthly_report', 'ثبت گزارش ماهانه جدید', '2025-03-03 17:45:58'),
(28, 'ویرایش گزارش ماهانه', 'edit_monthly_report', 'ویرایش گزارش ماهانه', '2025-03-03 17:45:58'),
(29, 'حذف گزارش ماهانه', 'delete_monthly_report', 'حذف گزارش ماهانه', '2025-03-03 17:45:58'),
(30, 'مشاهده گزارش‌های کوچ', 'view_coach_reports', 'مشاهده لیست گزارش‌های کوچ', '2025-03-03 17:45:58'),
(31, 'افزودن گزارش کوچ', 'add_coach_report', 'ثبت گزارش کوچ جدید', '2025-03-03 17:45:58'),
(32, 'ویرایش گزارش کوچ', 'edit_coach_report', 'ویرایش گزارش کوچ', '2025-03-03 17:45:58'),
(33, 'حذف گزارش کوچ', 'delete_coach_report', 'حذف گزارش کوچ', '2025-03-03 17:45:58'),
(34, 'مشاهده شبکه‌های اجتماعی', 'view_social_networks', 'مشاهده لیست شبکه‌های اجتماعی', '2025-03-03 17:45:58'),
(35, 'افزودن شبکه اجتماعی', 'add_social_network', 'افزودن شبکه اجتماعی جدید', '2025-03-03 17:45:58'),
(36, 'ویرایش شبکه اجتماعی', 'edit_social_network', 'ویرایش اطلاعات شبکه اجتماعی', '2025-03-03 17:45:58'),
(37, 'حذف شبکه اجتماعی', 'delete_social_network', 'حذف شبکه اجتماعی', '2025-03-03 17:45:58'),
(38, 'مشاهده صفحات اجتماعی', 'view_social_pages', 'مشاهده لیست صفحات اجتماعی', '2025-03-03 17:45:58'),
(39, 'افزودن صفحه اجتماعی', 'add_social_page', 'افزودن صفحه اجتماعی جدید', '2025-03-03 17:45:58'),
(40, 'ویرایش صفحه اجتماعی', 'edit_social_page', 'ویرایش اطلاعات صفحه اجتماعی', '2025-03-03 17:45:58'),
(41, 'حذف صفحه اجتماعی', 'delete_social_page', 'حذف صفحه اجتماعی', '2025-03-03 17:45:58'),
(42, 'مشاهده محتواها', 'view_contents', 'مشاهده لیست محتواها', '2025-03-03 17:45:58'),
(43, 'افزودن محتوا', 'add_content', 'افزودن محتوای جدید', '2025-03-03 17:45:58'),
(44, 'ویرایش محتوا', 'edit_content', 'ویرایش محتوا', '2025-03-03 17:45:58'),
(45, 'حذف محتوا', 'delete_content', 'حذف محتوا', '2025-03-03 17:45:58'),
(46, 'مشاهده تقویم محتوا', 'view_content_calendar', 'مشاهده تقویم محتوایی', '2025-03-03 17:45:58'),
(47, 'مدیریت قالب‌های محتوا', 'manage_content_templates', 'مدیریت قالب‌های محتوایی', '2025-03-03 17:45:58'),
(48, 'مشاهده KPI', 'view_kpis', 'مشاهده شاخص‌های کلیدی عملکرد', '2025-03-03 17:45:58'),
(49, 'افزودن KPI', 'add_kpi', 'افزودن شاخص جدید', '2025-03-03 17:45:58'),
(50, 'ویرایش KPI', 'edit_kpi', 'ویرایش شاخص', '2025-03-03 17:45:58'),
(51, 'حذف KPI', 'delete_kpi', 'حذف شاخص', '2025-03-03 17:45:58'),
(52, 'مشاهده عملکرد', 'view_performance', 'مشاهده گزارش‌های عملکرد', '2025-03-03 17:45:58'),
(53, 'ثبت عملکرد', 'add_performance', 'ثبت عملکرد جدید', '2025-03-03 17:45:58'),
(54, 'ویرایش عملکرد', 'edit_performance', 'ویرایش عملکرد', '2025-03-03 17:45:58'),
(55, 'مشاهده تنظیمات', 'view_settings', 'مشاهده تنظیمات سیستم', '2025-03-03 17:45:58'),
(56, 'ویرایش تنظیمات', 'edit_settings', 'ویرایش تنظیمات سیستم', '2025-03-03 17:45:58');

-- --------------------------------------------------------

--
-- Table structure for table `personnel`
--

CREATE TABLE `personnel` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_persian_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_persian_ci NOT NULL,
  `gender` enum('male','female') COLLATE utf8mb4_persian_ci NOT NULL DEFAULT 'male',
  `email` varchar(100) COLLATE utf8mb4_persian_ci NOT NULL,
  `mobile` varchar(20) COLLATE utf8mb4_persian_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_persian_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_persian_ci NOT NULL,
  `position` varchar(100) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `personnel`
--

INSERT INTO `personnel` (`id`, `user_id`, `company_id`, `role_id`, `first_name`, `last_name`, `gender`, `email`, `mobile`, `username`, `password`, `position`, `is_active`, `created_at`) VALUES
(2, 3, 6, 1, 'dfdf', 'dfdf', 'male', 'dfd@fg.com', '54', 'dfdfdfdf558', '$2y$10$31cMKsfNRzTV6v9kJAnlIeRU9nm.j.gxmr31XYJIF.UXCl9S5rUJG', NULL, 1, '2025-03-03 17:29:58'),
(3, 4, 1, 2, 'علیرضا', 'ترابی', 'male', 'ceo@milad-ahmadi.com', '09330073533', '242', '$2y$10$C562jwEpFckIm6Txa04sPexQEOdXeeolUCMWQYItTxlPLvewyTfiG', NULL, 1, '2025-03-03 18:12:31');

-- --------------------------------------------------------

--
-- Table structure for table `post_publish_platform_relations`
--

CREATE TABLE `post_publish_platform_relations` (
  `process_id` int(11) NOT NULL,
  `platform_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `personnel_id` int(11) NOT NULL,
  `report_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `personnel_id`, `report_date`, `created_at`) VALUES
(1, 2, '2025-03-01', '2025-03-01 06:48:01');

-- --------------------------------------------------------

--
-- Table structure for table `report_items`
--

CREATE TABLE `report_items` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `report_items`
--

INSERT INTO `report_items` (`id`, `report_id`, `content`, `created_at`) VALUES
(1, 1, 'طراحی پوستر', '2025-03-01 06:48:01');

-- --------------------------------------------------------

--
-- Table structure for table `report_item_categories`
--

CREATE TABLE `report_item_categories` (
  `item_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `report_item_categories`
--

INSERT INTO `report_item_categories` (`item_id`, `category_id`) VALUES
(1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `report_scores`
--

CREATE TABLE `report_scores` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `expected_value` decimal(10,2) NOT NULL,
  `actual_value` decimal(10,2) NOT NULL,
  `score` decimal(3,1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `report_scores`
--

INSERT INTO `report_scores` (`id`, `report_id`, `field_id`, `expected_value`, `actual_value`, `score`, `created_at`) VALUES
(1, 2, 2, '546.52', '500.00', '6.4', '2025-03-01 06:41:36'),
(2, 2, 3, '21.86', '20.00', '6.4', '2025-03-01 06:41:36'),
(3, 2, 4, '54.65', '50.00', '6.4', '2025-03-01 06:41:36'),
(4, 2, 5, '50.00', '20.00', '2.8', '2025-03-01 06:41:36'),
(5, 2, 6, '2.00', '55.00', '7.0', '2025-03-01 06:41:36'),
(6, 3, 2, '500.00', '560.00', '7.0', '2025-03-01 06:43:34'),
(7, 3, 3, '20.00', '5.00', '1.8', '2025-03-01 06:43:34'),
(8, 3, 4, '50.00', '66.00', '7.0', '2025-03-01 06:43:34'),
(9, 3, 5, '50.00', '20.00', '2.8', '2025-03-01 06:43:34'),
(10, 3, 6, '2.00', '500.00', '7.0', '2025-03-01 06:43:34');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_persian_ci NOT NULL,
  `description` text COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `is_ceo` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `is_ceo`, `created_at`) VALUES
(1, 'مدیر سیستم', 'دسترسی کامل به تمام بخش‌های سیستم', 1, '2025-03-03 17:18:36'),
(2, 'مدیر عامل', NULL, 1, '2025-03-03 17:18:53');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 16),
(1, 17),
(1, 18),
(1, 19),
(1, 20),
(1, 21),
(1, 22),
(1, 23),
(1, 24),
(1, 25),
(1, 26),
(1, 27),
(1, 28),
(1, 29),
(1, 30),
(1, 31),
(1, 32),
(1, 33),
(1, 34),
(1, 35),
(1, 36),
(1, 37),
(1, 38),
(1, 39),
(1, 40),
(1, 41),
(1, 42),
(1, 43),
(1, 44),
(1, 45),
(1, 46),
(1, 47),
(1, 48),
(1, 49),
(1, 50),
(1, 51),
(1, 52),
(1, 53),
(1, 54),
(1, 55),
(1, 56),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(2, 7),
(2, 8),
(2, 9),
(2, 10),
(2, 11),
(2, 12),
(2, 13),
(2, 14),
(2, 15),
(2, 16),
(2, 17),
(2, 18),
(2, 19),
(2, 20),
(2, 21),
(2, 22),
(2, 23),
(2, 24),
(2, 25),
(2, 26),
(2, 27),
(2, 28),
(2, 29),
(2, 30),
(2, 31),
(2, 32),
(2, 33),
(2, 34),
(2, 35),
(2, 36),
(2, 37),
(2, 38),
(2, 39),
(2, 40),
(2, 41),
(2, 42),
(2, 43),
(2, 44),
(2, 45),
(2, 46),
(2, 47),
(2, 48),
(2, 49),
(2, 50),
(2, 51),
(2, 52),
(2, 53),
(2, 54),
(2, 55),
(2, 56);

-- --------------------------------------------------------

--
-- Table structure for table `social_networks`
--

CREATE TABLE `social_networks` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `social_networks`
--

INSERT INTO `social_networks` (`id`, `name`, `icon`, `created_at`) VALUES
(1, 'Instagram', 'fab fa-instagram', '2025-03-01 06:11:46'),
(2, 'Twitter', 'fab fa-twitter', '2025-03-01 06:11:46'),
(3, 'Facebook', 'fab fa-facebook', '2025-03-01 06:11:46'),
(4, 'LinkedIn', 'fab fa-linkedin', '2025-03-01 06:11:46'),
(5, 'YouTube', 'fab fa-youtube', '2025-03-01 06:11:46'),
(6, 'TikTok', 'fab fa-tiktok', '2025-03-01 06:11:46'),
(7, 'Pinterest', 'fab fa-pinterest', '2025-03-01 06:11:46');

-- --------------------------------------------------------

--
-- Table structure for table `social_network_fields`
--

CREATE TABLE `social_network_fields` (
  `id` int(11) NOT NULL,
  `social_network_id` int(11) NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `field_label` varchar(100) NOT NULL,
  `field_type` enum('text','number','date','url') NOT NULL,
  `is_required` tinyint(1) DEFAULT 0,
  `is_kpi` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `social_network_fields`
--

INSERT INTO `social_network_fields` (`id`, `social_network_id`, `field_name`, `field_label`, `field_type`, `is_required`, `is_kpi`, `sort_order`, `created_at`) VALUES
(1, 1, 'instagram_url', 'آدرس اینستاگرام', 'text', 1, 0, 1, '2025-03-01 06:11:46'),
(2, 1, 'followers', 'تعداد فالوور', 'number', 1, 1, 2, '2025-03-01 06:11:46'),
(3, 1, 'engagement', 'تعداد تعامل', 'number', 1, 1, 3, '2025-03-01 06:11:46'),
(4, 1, 'views', 'تعداد بازدید', 'number', 1, 1, 4, '2025-03-01 06:11:46'),
(5, 1, 'leads', 'تعداد لید', 'number', 0, 1, 5, '2025-03-01 06:11:46'),
(6, 1, 'customers', 'تعداد مشتری', 'number', 0, 1, 6, '2025-03-01 06:11:46');

-- --------------------------------------------------------

--
-- Table structure for table `social_pages`
--

CREATE TABLE `social_pages` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `social_network_id` int(11) NOT NULL,
  `page_name` varchar(100) NOT NULL,
  `page_url` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `social_pages`
--

INSERT INTO `social_pages` (`id`, `company_id`, `social_network_id`, `page_name`, `page_url`, `start_date`, `created_at`) VALUES
(1, 1, 1, 'اینستاگرام پیروز پک', 'http://localhost/report2/social_pages.php', '2025-03-01', '2025-03-01 06:33:47');

-- --------------------------------------------------------

--
-- Table structure for table `social_page_fields`
--

CREATE TABLE `social_page_fields` (
  `id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `field_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `social_page_fields`
--

INSERT INTO `social_page_fields` (`id`, `page_id`, `field_id`, `field_value`, `created_at`) VALUES
(1, 1, 1, 'http://localhost/report2/social_pages.php', '2025-03-01 06:33:47'),
(2, 1, 2, '500', '2025-03-01 06:33:47'),
(3, 1, 3, '20', '2025-03-01 06:33:47'),
(4, 1, 4, '50', '2025-03-01 06:33:47'),
(5, 1, 5, '20', '2025-03-01 06:33:47'),
(6, 1, 6, '55', '2025-03-01 06:33:47');

-- --------------------------------------------------------

--
-- Table structure for table `template_audience_relations`
--

CREATE TABLE `template_audience_relations` (
  `template_id` int(11) NOT NULL,
  `audience_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `template_platform_relations`
--

CREATE TABLE `template_platform_relations` (
  `template_id` int(11) NOT NULL,
  `platform_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `template_task_relations`
--

CREATE TABLE `template_task_relations` (
  `template_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `template_topic_relations`
--

CREATE TABLE `template_topic_relations` (
  `template_id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `template_type_relations`
--

CREATE TABLE `template_type_relations` (
  `template_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_persian_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_persian_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_persian_ci NOT NULL,
  `user_type` enum('admin','user') COLLATE utf8mb4_persian_ci NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `user_type`, `is_active`, `created_at`) VALUES
(1, 'admin', '$2y$10$9lVwAvUhcVkvS5T8tR37b.XOwATEyjwvUWfch6Fi3upK/51.fdBcK', 'admin@example.com', 'admin', 1, '2025-03-03 17:17:01'),
(3, 'dfdfdfdf558', '$2y$10$31cMKsfNRzTV6v9kJAnlIeRU9nm.j.gxmr31XYJIF.UXCl9S5rUJG', 'dfd@fg.com', 'user', 1, '2025-03-03 17:29:58'),
(4, '242', '$2y$10$C562jwEpFckIm6Txa04sPexQEOdXeeolUCMWQYItTxlPLvewyTfiG', 'ceo@milad-ahmadi.com', 'user', 1, '2025-03-03 18:12:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coach_reports`
--
ALTER TABLE `coach_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coach_id` (`coach_id`),
  ADD KEY `personnel_id` (`personnel_id`);

--
-- Indexes for table `coach_report_access`
--
ALTER TABLE `coach_report_access`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `personnel_id` (`personnel_id`);

--
-- Indexes for table `coach_report_personnel`
--
ALTER TABLE `coach_report_personnel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coach_report_id` (`coach_report_id`,`personnel_id`),
  ADD KEY `personnel_id` (`personnel_id`);

--
-- Indexes for table `coach_report_social_reports`
--
ALTER TABLE `coach_report_social_reports`
  ADD PRIMARY KEY (`coach_report_id`,`social_report_id`),
  ADD KEY `social_report_id` (`social_report_id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contents`
--
ALTER TABLE `contents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `production_status_id` (`production_status_id`),
  ADD KEY `publish_status_id` (`publish_status_id`),
  ADD KEY `content_format_id` (`content_format_id`);

--
-- Indexes for table `content_audiences`
--
ALTER TABLE `content_audiences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `content_audience_content`
--
ALTER TABLE `content_audience_content`
  ADD PRIMARY KEY (`content_id`,`audience_id`),
  ADD KEY `audience_id` (`audience_id`);

--
-- Indexes for table `content_calendar_settings`
--
ALTER TABLE `content_calendar_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `company_id` (`company_id`,`field_name`);

--
-- Indexes for table `content_formats`
--
ALTER TABLE `content_formats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `content_platforms`
--
ALTER TABLE `content_platforms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `content_platform_relations`
--
ALTER TABLE `content_platform_relations`
  ADD PRIMARY KEY (`content_id`,`platform_id`),
  ADD KEY `platform_id` (`platform_id`);

--
-- Indexes for table `content_post_publish_processes`
--
ALTER TABLE `content_post_publish_processes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `content_id` (`content_id`),
  ADD KEY `format_id` (`format_id`);

--
-- Indexes for table `content_production_statuses`
--
ALTER TABLE `content_production_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_company_status` (`company_id`,`name`);

--
-- Indexes for table `content_publish_statuses`
--
ALTER TABLE `content_publish_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_company_status` (`company_id`,`name`);

--
-- Indexes for table `content_tasks`
--
ALTER TABLE `content_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `content_task_assignments`
--
ALTER TABLE `content_task_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `content_id` (`content_id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `personnel_id` (`personnel_id`);

--
-- Indexes for table `content_task_relations`
--
ALTER TABLE `content_task_relations`
  ADD PRIMARY KEY (`content_id`,`task_id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `responsible_id` (`responsible_id`);

--
-- Indexes for table `content_templates`
--
ALTER TABLE `content_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `content_topics`
--
ALTER TABLE `content_topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `content_topic_relations`
--
ALTER TABLE `content_topic_relations`
  ADD PRIMARY KEY (`content_id`,`topic_id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- Indexes for table `content_types`
--
ALTER TABLE `content_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `content_type_relations`
--
ALTER TABLE `content_type_relations`
  ADD PRIMARY KEY (`content_id`,`type_id`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `kpi_models`
--
ALTER TABLE `kpi_models`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `scheduled_time` (`scheduled_time`),
  ADD KEY `is_sent` (`is_sent`);

--
-- Indexes for table `monthly_reports`
--
ALTER TABLE `monthly_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `page_id` (`page_id`),
  ADD KEY `creator_id` (`creator_id`);

--
-- Indexes for table `monthly_report_values`
--
ALTER TABLE `monthly_report_values`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_id` (`report_id`),
  ADD KEY `field_id` (`field_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `is_read` (`is_read`);

--
-- Indexes for table `page_kpis`
--
ALTER TABLE `page_kpis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `page_id` (`page_id`),
  ADD KEY `field_id` (`field_id`),
  ADD KEY `kpi_model_id` (`kpi_model_id`),
  ADD KEY `related_field_id` (`related_field_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `personnel`
--
ALTER TABLE `personnel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `post_publish_platform_relations`
--
ALTER TABLE `post_publish_platform_relations`
  ADD PRIMARY KEY (`process_id`,`platform_id`),
  ADD KEY `platform_id` (`platform_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `personnel_id` (`personnel_id`);

--
-- Indexes for table `report_items`
--
ALTER TABLE `report_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_id` (`report_id`);

--
-- Indexes for table `report_item_categories`
--
ALTER TABLE `report_item_categories`
  ADD PRIMARY KEY (`item_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `report_scores`
--
ALTER TABLE `report_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_id` (`report_id`),
  ADD KEY `field_id` (`field_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `social_networks`
--
ALTER TABLE `social_networks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `social_network_fields`
--
ALTER TABLE `social_network_fields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `social_network_id` (`social_network_id`);

--
-- Indexes for table `social_pages`
--
ALTER TABLE `social_pages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `social_network_id` (`social_network_id`);

--
-- Indexes for table `social_page_fields`
--
ALTER TABLE `social_page_fields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `page_id` (`page_id`),
  ADD KEY `field_id` (`field_id`);

--
-- Indexes for table `template_audience_relations`
--
ALTER TABLE `template_audience_relations`
  ADD PRIMARY KEY (`template_id`,`audience_id`),
  ADD KEY `audience_id` (`audience_id`);

--
-- Indexes for table `template_platform_relations`
--
ALTER TABLE `template_platform_relations`
  ADD PRIMARY KEY (`template_id`,`platform_id`),
  ADD KEY `platform_id` (`platform_id`);

--
-- Indexes for table `template_task_relations`
--
ALTER TABLE `template_task_relations`
  ADD PRIMARY KEY (`template_id`,`task_id`),
  ADD KEY `task_id` (`task_id`);

--
-- Indexes for table `template_topic_relations`
--
ALTER TABLE `template_topic_relations`
  ADD PRIMARY KEY (`template_id`,`topic_id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- Indexes for table `template_type_relations`
--
ALTER TABLE `template_type_relations`
  ADD PRIMARY KEY (`template_id`,`type_id`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `coach_reports`
--
ALTER TABLE `coach_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `coach_report_access`
--
ALTER TABLE `coach_report_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coach_report_personnel`
--
ALTER TABLE `coach_report_personnel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `contents`
--
ALTER TABLE `contents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `content_audiences`
--
ALTER TABLE `content_audiences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `content_calendar_settings`
--
ALTER TABLE `content_calendar_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `content_formats`
--
ALTER TABLE `content_formats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `content_platforms`
--
ALTER TABLE `content_platforms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `content_post_publish_processes`
--
ALTER TABLE `content_post_publish_processes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `content_production_statuses`
--
ALTER TABLE `content_production_statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `content_publish_statuses`
--
ALTER TABLE `content_publish_statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `content_tasks`
--
ALTER TABLE `content_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `content_task_assignments`
--
ALTER TABLE `content_task_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `content_templates`
--
ALTER TABLE `content_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `content_topics`
--
ALTER TABLE `content_topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `content_types`
--
ALTER TABLE `content_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `kpi_models`
--
ALTER TABLE `kpi_models`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `monthly_reports`
--
ALTER TABLE `monthly_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `monthly_report_values`
--
ALTER TABLE `monthly_report_values`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `page_kpis`
--
ALTER TABLE `page_kpis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `personnel`
--
ALTER TABLE `personnel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `report_items`
--
ALTER TABLE `report_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `report_scores`
--
ALTER TABLE `report_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `social_networks`
--
ALTER TABLE `social_networks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `social_network_fields`
--
ALTER TABLE `social_network_fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `social_pages`
--
ALTER TABLE `social_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `social_page_fields`
--
ALTER TABLE `social_page_fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `coach_reports`
--
ALTER TABLE `coach_reports`
  ADD CONSTRAINT `coach_reports_ibfk_1` FOREIGN KEY (`coach_id`) REFERENCES `personnel` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coach_reports_ibfk_2` FOREIGN KEY (`personnel_id`) REFERENCES `personnel` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `coach_report_access`
--
ALTER TABLE `coach_report_access`
  ADD CONSTRAINT `coach_report_access_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coach_report_access_ibfk_2` FOREIGN KEY (`personnel_id`) REFERENCES `personnel` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `coach_report_personnel`
--
ALTER TABLE `coach_report_personnel`
  ADD CONSTRAINT `coach_report_personnel_ibfk_1` FOREIGN KEY (`coach_report_id`) REFERENCES `coach_reports` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coach_report_personnel_ibfk_2` FOREIGN KEY (`personnel_id`) REFERENCES `personnel` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `coach_report_social_reports`
--
ALTER TABLE `coach_report_social_reports`
  ADD CONSTRAINT `coach_report_social_reports_ibfk_1` FOREIGN KEY (`coach_report_id`) REFERENCES `coach_reports` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coach_report_social_reports_ibfk_2` FOREIGN KEY (`social_report_id`) REFERENCES `monthly_reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contents`
--
ALTER TABLE `contents`
  ADD CONSTRAINT `contents_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contents_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contents_ibfk_3` FOREIGN KEY (`production_status_id`) REFERENCES `content_production_statuses` (`id`),
  ADD CONSTRAINT `contents_ibfk_4` FOREIGN KEY (`publish_status_id`) REFERENCES `content_publish_statuses` (`id`),
  ADD CONSTRAINT `contents_ibfk_5` FOREIGN KEY (`content_format_id`) REFERENCES `content_formats` (`id`) ON DELETE NO ACTION;

--
-- Constraints for table `content_audiences`
--
ALTER TABLE `content_audiences`
  ADD CONSTRAINT `content_audiences_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_audiences_ibfk_10` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_audiences_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_audiences_ibfk_3` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_audiences_ibfk_4` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_audiences_ibfk_5` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_audiences_ibfk_6` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_audiences_ibfk_7` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_audiences_ibfk_8` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_audiences_ibfk_9` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `content_audience_content`
--
ALTER TABLE `content_audience_content`
  ADD CONSTRAINT `content_audience_content_ibfk_1` FOREIGN KEY (`content_id`) REFERENCES `contents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_audience_content_ibfk_2` FOREIGN KEY (`audience_id`) REFERENCES `content_audiences` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `content_calendar_settings`
--
ALTER TABLE `content_calendar_settings`
  ADD CONSTRAINT `content_calendar_settings_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `content_formats`
--
ALTER TABLE `content_formats`
  ADD CONSTRAINT `content_formats_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `content_platforms`
--
ALTER TABLE `content_platforms`
  ADD CONSTRAINT `content_platforms_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `content_platform_relations`
--
ALTER TABLE `content_platform_relations`
  ADD CONSTRAINT `content_platform_relations_ibfk_1` FOREIGN KEY (`content_id`) REFERENCES `contents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_platform_relations_ibfk_2` FOREIGN KEY (`platform_id`) REFERENCES `content_platforms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `content_post_publish_processes`
--
ALTER TABLE `content_post_publish_processes`
  ADD CONSTRAINT `content_post_publish_processes_ibfk_1` FOREIGN KEY (`content_id`) REFERENCES `contents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_post_publish_processes_ibfk_2` FOREIGN KEY (`format_id`) REFERENCES `content_formats` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `content_production_statuses`
--
ALTER TABLE `content_production_statuses`
  ADD CONSTRAINT `content_production_statuses_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `content_publish_statuses`
--
ALTER TABLE `content_publish_statuses`
  ADD CONSTRAINT `content_publish_statuses_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `content_tasks`
--
ALTER TABLE `content_tasks`
  ADD CONSTRAINT `content_tasks_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `content_task_assignments`
--
ALTER TABLE `content_task_assignments`
  ADD CONSTRAINT `content_task_assignments_ibfk_1` FOREIGN KEY (`content_id`) REFERENCES `contents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_task_assignments_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `content_tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_task_assignments_ibfk_3` FOREIGN KEY (`personnel_id`) REFERENCES `personnel` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `content_task_relations`
--
ALTER TABLE `content_task_relations`
  ADD CONSTRAINT `content_task_relations_ibfk_1` FOREIGN KEY (`content_id`) REFERENCES `contents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_task_relations_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `content_tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_task_relations_ibfk_3` FOREIGN KEY (`responsible_id`) REFERENCES `personnel` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `content_templates`
--
ALTER TABLE `content_templates`
  ADD CONSTRAINT `content_templates_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_templates_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `personnel` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `content_topics`
--
ALTER TABLE `content_topics`
  ADD CONSTRAINT `content_topics_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `content_topic_relations`
--
ALTER TABLE `content_topic_relations`
  ADD CONSTRAINT `content_topic_relations_ibfk_1` FOREIGN KEY (`content_id`) REFERENCES `contents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_topic_relations_ibfk_2` FOREIGN KEY (`topic_id`) REFERENCES `content_topics` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `content_types`
--
ALTER TABLE `content_types`
  ADD CONSTRAINT `content_types_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `content_type_relations`
--
ALTER TABLE `content_type_relations`
  ADD CONSTRAINT `content_type_relations_ibfk_1` FOREIGN KEY (`content_id`) REFERENCES `contents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_type_relations_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `content_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `monthly_reports`
--
ALTER TABLE `monthly_reports`
  ADD CONSTRAINT `monthly_reports_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `social_pages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `monthly_report_values`
--
ALTER TABLE `monthly_report_values`
  ADD CONSTRAINT `monthly_report_values_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `monthly_reports` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `monthly_report_values_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `social_network_fields` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `page_kpis`
--
ALTER TABLE `page_kpis`
  ADD CONSTRAINT `page_kpis_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `social_pages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `page_kpis_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `social_network_fields` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `page_kpis_ibfk_3` FOREIGN KEY (`kpi_model_id`) REFERENCES `kpi_models` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `page_kpis_ibfk_4` FOREIGN KEY (`related_field_id`) REFERENCES `social_network_fields` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `personnel`
--
ALTER TABLE `personnel`
  ADD CONSTRAINT `personnel_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `personnel_ibfk_10` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `personnel_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `personnel_ibfk_3` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `personnel_ibfk_4` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `personnel_ibfk_5` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `personnel_ibfk_6` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `personnel_ibfk_7` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `personnel_ibfk_8` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `personnel_ibfk_9` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_publish_platform_relations`
--
ALTER TABLE `post_publish_platform_relations`
  ADD CONSTRAINT `post_publish_platform_relations_ibfk_1` FOREIGN KEY (`process_id`) REFERENCES `content_post_publish_processes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_publish_platform_relations_ibfk_2` FOREIGN KEY (`platform_id`) REFERENCES `content_platforms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`personnel_id`) REFERENCES `personnel` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `report_items`
--
ALTER TABLE `report_items`
  ADD CONSTRAINT `report_items_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `report_item_categories`
--
ALTER TABLE `report_item_categories`
  ADD CONSTRAINT `report_item_categories_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `report_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_item_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `report_scores`
--
ALTER TABLE `report_scores`
  ADD CONSTRAINT `report_scores_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `monthly_reports` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_scores_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `social_network_fields` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `social_network_fields`
--
ALTER TABLE `social_network_fields`
  ADD CONSTRAINT `social_network_fields_ibfk_1` FOREIGN KEY (`social_network_id`) REFERENCES `social_networks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `social_pages`
--
ALTER TABLE `social_pages`
  ADD CONSTRAINT `social_pages_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `social_pages_ibfk_2` FOREIGN KEY (`social_network_id`) REFERENCES `social_networks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `social_page_fields`
--
ALTER TABLE `social_page_fields`
  ADD CONSTRAINT `social_page_fields_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `social_pages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `social_page_fields_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `social_network_fields` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `template_audience_relations`
--
ALTER TABLE `template_audience_relations`
  ADD CONSTRAINT `template_audience_relations_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `content_templates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `template_audience_relations_ibfk_2` FOREIGN KEY (`audience_id`) REFERENCES `content_target_audiences` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `template_platform_relations`
--
ALTER TABLE `template_platform_relations`
  ADD CONSTRAINT `template_platform_relations_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `content_templates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `template_platform_relations_ibfk_2` FOREIGN KEY (`platform_id`) REFERENCES `content_platforms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `template_task_relations`
--
ALTER TABLE `template_task_relations`
  ADD CONSTRAINT `template_task_relations_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `content_templates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `template_task_relations_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `content_tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `template_topic_relations`
--
ALTER TABLE `template_topic_relations`
  ADD CONSTRAINT `template_topic_relations_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `content_templates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `template_topic_relations_ibfk_2` FOREIGN KEY (`topic_id`) REFERENCES `content_topics` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `template_type_relations`
--
ALTER TABLE `template_type_relations`
  ADD CONSTRAINT `template_type_relations_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `content_templates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `template_type_relations_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `content_types` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
