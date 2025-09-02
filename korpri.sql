-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 02, 2025 at 01:39 PM
-- Server version: 8.0.41-0ubuntu0.22.04.1
-- PHP Version: 8.1.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `korpri`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_id` bigint UNSIGNED DEFAULT NULL,
  `subject_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `properties` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `host` varchar(46) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `description`, `subject_id`, `subject_type`, `user_id`, `properties`, `host`, `created_at`, `updated_at`) VALUES
(1, 'audit:created', 1, 'App\\Models\\Pendaftar#1', NULL, '{\"nama\":\"Runner-0001\",\"nik\":\"1111\",\"email\":\"Runner-0001@gmail.com\",\"no_tiket\":\"0001\",\"total_bayar\":\"200000\",\"updated_at\":\"2025-08-24 07:53:06\",\"created_at\":\"2025-08-24 07:53:06\",\"id\":1}', '127.0.0.1', '2025-08-24 07:53:06', '2025-08-24 07:53:06'),
(2, 'audit:created', 2, 'App\\Models\\Pendaftar#2', NULL, '{\"nama\":\"Runner-02\",\"nik\":\"1111\",\"email\":\"Runner-02@gmail.com\",\"no_tiket\":\"02\",\"total_bayar\":\"200000\",\"updated_at\":\"2025-08-24 07:54:38\",\"created_at\":\"2025-08-24 07:54:38\",\"id\":2}', '127.0.0.1', '2025-08-24 07:54:38', '2025-08-24 07:54:38'),
(3, 'audit:created', 3, 'App\\Models\\Pendaftar#3', NULL, '{\"nama\":\"Runner-03\",\"nik\":\"1111\",\"email\":\"Runner-03@gmail.com\",\"no_tiket\":\"03\",\"total_bayar\":\"200000\",\"updated_at\":\"2025-08-24 07:55:54\",\"created_at\":\"2025-08-24 07:55:54\",\"id\":3}', '127.0.0.1', '2025-08-24 07:55:54', '2025-08-24 07:55:54'),
(4, 'audit:created', 4, 'App\\Models\\Pendaftar#4', NULL, '{\"nama\":\"Runner-04\",\"nik\":\"1111\",\"email\":\"Runner-04@gmail.com\",\"no_tiket\":\"04\",\"total_bayar\":\"200000\",\"updated_at\":\"2025-08-24 07:57:48\",\"created_at\":\"2025-08-24 07:57:48\",\"id\":4}', '127.0.0.1', '2025-08-24 07:57:48', '2025-08-24 07:57:48'),
(5, 'audit:created', 5, 'App\\Models\\Pendaftar#5', NULL, '{\"nama\":\"Runner-05\",\"nik\":\"1111\",\"email\":\"Runner-05@gmail.com\",\"no_tiket\":\"05\",\"total_bayar\":\"200000\",\"updated_at\":\"2025-08-24 07:58:20\",\"created_at\":\"2025-08-24 07:58:20\",\"id\":5}', '127.0.0.1', '2025-08-24 07:58:20', '2025-08-24 07:58:20'),
(6, 'audit:created', 6, 'App\\Models\\Pendaftar#6', NULL, '{\"nama\":\"Runner-06\",\"nik\":\"1111\",\"email\":\"Runner-06@gmail.com\",\"no_tiket\":\"06\",\"total_bayar\":\"200000\",\"updated_at\":\"2025-08-24 07:58:56\",\"created_at\":\"2025-08-24 07:58:56\",\"id\":6}', '127.0.0.1', '2025-08-24 07:58:56', '2025-08-24 07:58:56'),
(7, 'audit:created', 7, 'App\\Models\\Pendaftar#7', NULL, '{\"nama\":\"failamir abdullah\",\"nik\":\"1234567890\",\"email\":\"ifailamir@gmail.com\",\"no_hp\":\"0812312312312\",\"total_bayar\":200000,\"no_tiket\":\"07\",\"status_payment\":\"pending\",\"updated_at\":\"2025-08-24 07:59:11\",\"created_at\":\"2025-08-24 07:59:11\",\"id\":7}', '127.0.0.1', '2025-08-24 07:59:11', '2025-08-24 07:59:11'),
(8, 'audit:created', 8, 'App\\Models\\Pendaftar#8', NULL, '{\"nama\":\"Runner-08\",\"nik\":\"1111\",\"email\":\"Runner-08@gmail.com\",\"no_tiket\":\"08\",\"total_bayar\":\"200000\",\"updated_at\":\"2025-08-24 08:04:03\",\"created_at\":\"2025-08-24 08:04:03\",\"id\":8}', '127.0.0.1', '2025-08-24 08:04:03', '2025-08-24 08:04:03'),
(9, 'audit:created', 9, 'App\\Models\\Pendaftar#9', NULL, '{\"nama\":\"Runner-09\",\"nik\":\"1111\",\"email\":\"Runner-09@gmail.com\",\"no_tiket\":\"09\",\"total_bayar\":\"200000\",\"updated_at\":\"2025-08-24 09:08:25\",\"created_at\":\"2025-08-24 09:08:25\",\"id\":9}', '127.0.0.1', '2025-08-24 09:08:25', '2025-08-24 09:08:25'),
(10, 'audit:created', 3, 'App\\Models\\Transaksi#3', NULL, '{\"invoice\":\"TRX-1N5Q0QS038\",\"event_id\":null,\"amount\":\"200000\",\"note\":\"failamir abdullah\",\"status\":\"pending\",\"updated_at\":\"2025-08-24 09:08:35\",\"created_at\":\"2025-08-24 09:08:35\",\"id\":3}', '127.0.0.1', '2025-08-24 09:08:35', '2025-08-24 09:08:35'),
(11, 'audit:updated', 3, 'App\\Models\\Transaksi#3', NULL, '{\"updated_at\":\"2025-08-24 09:08:36\",\"snap_token\":\"4b0a1920-6fe7-4a4e-91a4-805d86f008b3\",\"id\":3}', '127.0.0.1', '2025-08-24 09:08:36', '2025-08-24 09:08:36'),
(12, 'audit:created', 10, 'App\\Models\\Pendaftar#10', 1, '{\"nama\":\"Runner-010\",\"nik\":\"1111\",\"email\":\"Runner-010@gmail.com\",\"no_tiket\":\"010\",\"total_bayar\":\"200000\",\"updated_at\":\"2025-08-24 10:37:49\",\"created_at\":\"2025-08-24 10:37:49\",\"id\":10}', '127.0.0.1', '2025-08-24 10:37:49', '2025-08-24 10:37:49'),
(13, 'audit:created', 4, 'App\\Models\\Transaksi#4', 1, '{\"invoice\":\"TRX-78Y3Q8Z2ID\",\"event_id\":null,\"amount\":\"200000\",\"note\":\"failamir abdullah\",\"status\":\"pending\",\"updated_at\":\"2025-08-24 10:38:22\",\"created_at\":\"2025-08-24 10:38:22\",\"id\":4}', '127.0.0.1', '2025-08-24 10:38:22', '2025-08-24 10:38:22'),
(14, 'audit:updated', 4, 'App\\Models\\Transaksi#4', 1, '{\"updated_at\":\"2025-08-24 10:38:24\",\"snap_token\":\"d1143135-c756-43bd-8fed-bbb320e362b8\",\"id\":4}', '127.0.0.1', '2025-08-24 10:38:24', '2025-08-24 10:38:24'),
(15, 'audit:created', 11, 'App\\Models\\Pendaftar#11', NULL, '{\"nama\":\"Runner-010\",\"nik\":\"1111\",\"email\":\"Runner-010@gmail.com\",\"no_tiket\":\"010\",\"total_bayar\":\"200000\",\"updated_at\":\"2025-08-24 10:45:38\",\"created_at\":\"2025-08-24 10:45:38\",\"id\":11}', '127.0.0.1', '2025-08-24 10:45:38', '2025-08-24 10:45:38'),
(16, 'audit:created', 12, 'App\\Models\\Pendaftar#12', NULL, '{\"nama\":\"Runner-010\",\"nik\":\"1111\",\"email\":\"Runner-010@gmail.com\",\"no_tiket\":\"010\",\"total_bayar\":\"200000\",\"updated_at\":\"2025-08-24 10:51:40\",\"created_at\":\"2025-08-24 10:51:40\",\"id\":12}', '127.0.0.1', '2025-08-24 10:51:40', '2025-08-24 10:51:40'),
(17, 'audit:created', 5, 'App\\Models\\Transaksi#5', NULL, '{\"invoice\":\"TRX-H540UR6444\",\"event_id\":null,\"amount\":\"200000\",\"note\":\"failamir abdullah\",\"status\":\"pending\",\"updated_at\":\"2025-08-24 10:54:58\",\"created_at\":\"2025-08-24 10:54:58\",\"id\":5}', '127.0.0.1', '2025-08-24 10:54:58', '2025-08-24 10:54:58'),
(18, 'audit:updated', 5, 'App\\Models\\Transaksi#5', NULL, '{\"updated_at\":\"2025-08-24 10:54:59\",\"snap_token\":\"ea937d4b-1dcf-4fcf-863c-4dd9c8b1f18f\",\"id\":5}', '127.0.0.1', '2025-08-24 10:54:59', '2025-08-24 10:54:59'),
(19, 'audit:created', 13, 'App\\Models\\Pendaftar#13', 2, '{\"nama\":\"Runner-10\",\"nik\":\"1111\",\"email\":\"ifailamir@gmail.com\",\"no_tiket\":10,\"total_bayar\":\"200000\",\"updated_at\":\"2025-08-25 13:21:52\",\"created_at\":\"2025-08-25 13:21:52\",\"id\":13}', '127.0.0.1', '2025-08-25 13:21:52', '2025-08-25 13:21:52'),
(20, 'audit:created', 14, 'App\\Models\\Pendaftar#14', 2, '{\"nama\":\"Runner-11\",\"nik\":\"1111\",\"email\":\"ifailamir@gmail.com\",\"no_tiket\":11,\"total_bayar\":\"200000\",\"updated_at\":\"2025-08-25 13:27:30\",\"created_at\":\"2025-08-25 13:27:30\",\"id\":14}', '127.0.0.1', '2025-08-25 13:27:30', '2025-08-25 13:27:30'),
(21, 'audit:created', 15, 'App\\Models\\Pendaftar#15', 2, '{\"nama\":\"Runner-12\",\"nik\":\"1111\",\"email\":\"ifailamir@gmail.com\",\"no_tiket\":12,\"total_bayar\":\"200000\",\"updated_at\":\"2025-08-25 13:28:07\",\"created_at\":\"2025-08-25 13:28:07\",\"id\":15}', '127.0.0.1', '2025-08-25 13:28:08', '2025-08-25 13:28:08'),
(22, 'audit:created', 16, 'App\\Models\\Pendaftar#16', 2, '{\"nama\":\"Runner-13\",\"nik\":\"1111\",\"email\":\"ifailamir@gmail.com\",\"no_tiket\":13,\"total_bayar\":\"200000\",\"updated_at\":\"2025-08-25 13:32:37\",\"created_at\":\"2025-08-25 13:32:37\",\"id\":16}', '127.0.0.1', '2025-08-25 13:32:37', '2025-08-25 13:32:37'),
(23, 'audit:created', 17, 'App\\Models\\Pendaftar#17', 2, '{\"nama\":\"Runner-14\",\"nik\":\"1111\",\"email\":\"ifailamir@gmail.com\",\"no_tiket\":14,\"total_bayar\":\"200000\",\"updated_at\":\"2025-08-25 13:34:49\",\"created_at\":\"2025-08-25 13:34:49\",\"id\":17}', '127.0.0.1', '2025-08-25 13:34:49', '2025-08-25 13:34:49'),
(24, 'audit:created', 18, 'App\\Models\\Pendaftar#18', 2, '{\"nama\":\"Runner-00015\",\"nik\":\"1111\",\"email\":\"ifailamir@gmail.com\",\"no_tiket\":\"00015\",\"total_bayar\":\"200000\",\"updated_at\":\"2025-08-25 13:41:37\",\"created_at\":\"2025-08-25 13:41:37\",\"id\":18}', '127.0.0.1', '2025-08-25 13:41:37', '2025-08-25 13:41:37'),
(25, 'audit:created', 19, 'App\\Models\\Pendaftar#19', 2, '{\"nama\":\"Runner-00016\",\"nik\":\"1111\",\"email\":\"ifailamir@gmail.com\",\"no_tiket\":\"00016\",\"total_bayar\":\"200000\",\"updated_at\":\"2025-08-25 13:46:24\",\"created_at\":\"2025-08-25 13:46:24\",\"id\":19}', '127.0.0.1', '2025-08-25 13:46:25', '2025-08-25 13:46:25'),
(26, 'audit:created', 20, 'App\\Models\\Pendaftar#20', 2, '{\"nama\":\"Runner-00017\",\"nik\":\"1111\",\"email\":\"ifailamir@gmail.com\",\"no_tiket\":\"00017\",\"total_bayar\":\"200000\",\"updated_at\":\"2025-08-25 13:46:43\",\"created_at\":\"2025-08-25 13:46:43\",\"id\":20}', '127.0.0.1', '2025-08-25 13:46:43', '2025-08-25 13:46:43'),
(27, 'audit:created', 21, 'App\\Models\\Pendaftar#21', 2, '{\"nama\":\"Runner-00018\",\"nik\":\"1111\",\"email\":\"ifailamir@gmail.com\",\"no_tiket\":\"00018\",\"total_bayar\":\"200000\",\"updated_at\":\"2025-08-25 13:47:02\",\"created_at\":\"2025-08-25 13:47:02\",\"id\":21}', '127.0.0.1', '2025-08-25 13:47:02', '2025-08-25 13:47:02'),
(28, 'audit:created', 22, 'App\\Models\\Pendaftar#22', 2, '{\"nama\":\"Runner-00019\",\"nik\":\"1111\",\"email\":\"ifailamir@gmail.com\",\"no_tiket\":\"00019\",\"total_bayar\":\"200000\",\"updated_at\":\"2025-08-25 13:47:13\",\"created_at\":\"2025-08-25 13:47:13\",\"id\":22}', '127.0.0.1', '2025-08-25 13:47:13', '2025-08-25 13:47:13'),
(29, 'audit:created', 23, 'App\\Models\\Pendaftar#23', 2, '{\"nama\":\"Runner-00020\",\"nik\":\"1111\",\"email\":\"ifailamir@gmail.com\",\"no_tiket\":\"00020\",\"total_bayar\":\"200000\",\"updated_at\":\"2025-08-25 13:47:21\",\"created_at\":\"2025-08-25 13:47:21\",\"id\":23}', '127.0.0.1', '2025-08-25 13:47:21', '2025-08-25 13:47:21'),
(30, 'audit:created', 24, 'App\\Models\\Pendaftar#24', 2, '{\"nama\":\"Runner-00021\",\"nik\":\"1111\",\"email\":\"ifailamir@gmail.com\",\"no_tiket\":\"00021\",\"total_bayar\":\"200000\",\"updated_at\":\"2025-08-25 13:47:39\",\"created_at\":\"2025-08-25 13:47:39\",\"id\":24}', '127.0.0.1', '2025-08-25 13:47:39', '2025-08-25 13:47:39'),
(31, 'audit:created', 25, 'App\\Models\\Pendaftar#25', 2, '{\"nama\":\"Runner-00022\",\"nik\":\"1111\",\"email\":\"ifailamir@gmail.com\",\"no_tiket\":\"00022\",\"total_bayar\":\"200000\",\"updated_at\":\"2025-08-25 14:02:01\",\"created_at\":\"2025-08-25 14:02:01\",\"id\":25}', '127.0.0.1', '2025-08-25 14:02:01', '2025-08-25 14:02:01'),
(32, 'audit:created', 6, 'App\\Models\\Transaksi#6', 2, '{\"invoice\":\"TRX-25DBPA44L7\",\"event_id\":null,\"amount\":\"200000\",\"note\":\"failamir abdullah\",\"status\":\"pending\",\"created_by_id\":2,\"updated_at\":\"2025-08-25 14:05:28\",\"created_at\":\"2025-08-25 14:05:28\",\"id\":6}', '127.0.0.1', '2025-08-25 14:05:28', '2025-08-25 14:05:28'),
(33, 'audit:updated', 6, 'App\\Models\\Transaksi#6', 2, '{\"updated_at\":\"2025-08-25 14:05:29\",\"snap_token\":\"4950ed2d-a724-4709-9d73-57484f9f94fc\",\"id\":6}', '127.0.0.1', '2025-08-25 14:05:29', '2025-08-25 14:05:29'),
(34, 'audit:created', 26, 'App\\Models\\Pendaftar#26', NULL, '{\"nama\":\"lombok\",\"email\":\"lombok2@cantik.com\",\"no_hp\":null,\"no_tiket\":\"015\",\"total_bayar\":\"200000\",\"event_id\":1,\"nik\":\"u67ty3tw34w535433q432c54353453\",\"updated_at\":\"2025-08-25 17:37:46\",\"created_at\":\"2025-08-25 17:37:46\",\"id\":26}', '127.0.0.1', '2025-08-25 17:37:46', '2025-08-25 17:37:46'),
(35, 'audit:created', 27, 'App\\Models\\Pendaftar#27', NULL, '{\"nama\":\"lombok\",\"email\":\"lombok2@cantik.com\",\"no_hp\":null,\"no_tiket\":\"015\",\"total_bayar\":\"200000\",\"event_id\":1,\"nik\":\"u67ty3tw34w535433q432c54353453\",\"updated_at\":\"2025-08-25 17:41:57\",\"created_at\":\"2025-08-25 17:41:57\",\"id\":27}', '127.0.0.1', '2025-08-25 17:41:57', '2025-08-25 17:41:57'),
(36, 'audit:created', 28, 'App\\Models\\Pendaftar#28', NULL, '{\"nama\":\"Admin\",\"email\":\"admin@admin.com\",\"no_hp\":null,\"no_tiket\":\"015\",\"total_bayar\":\"200000\",\"event_id\":1,\"nik\":\"1121212121\",\"updated_at\":\"2025-08-25 18:03:47\",\"created_at\":\"2025-08-25 18:03:47\",\"id\":28}', '127.0.0.1', '2025-08-25 18:03:47', '2025-08-25 18:03:47'),
(37, 'audit:created', 29, 'App\\Models\\Pendaftar#29', NULL, '{\"nama\":\"Admin\",\"email\":\"admin@admin.com\",\"no_hp\":null,\"no_tiket\":\"015\",\"total_bayar\":\"200000\",\"event_id\":1,\"nik\":\"1121212121\",\"updated_at\":\"2025-08-25 18:04:40\",\"created_at\":\"2025-08-25 18:04:40\",\"id\":29}', '127.0.0.1', '2025-08-25 18:04:40', '2025-08-25 18:04:40'),
(38, 'audit:created', 30, 'App\\Models\\Pendaftar#30', NULL, '{\"nama\":\"Admin\",\"email\":\"admin@admin.com\",\"no_hp\":null,\"no_tiket\":\"015\",\"total_bayar\":\"200000\",\"event_id\":1,\"nik\":\"1121212121\",\"updated_at\":\"2025-08-25 18:05:13\",\"created_at\":\"2025-08-25 18:05:13\",\"id\":30}', '127.0.0.1', '2025-08-25 18:05:13', '2025-08-25 18:05:13'),
(39, 'audit:created', 31, 'App\\Models\\Pendaftar#31', NULL, '{\"nama\":\"Admin\",\"email\":\"admin@admin.com\",\"no_hp\":null,\"no_tiket\":\"015\",\"total_bayar\":\"200000\",\"event_id\":1,\"nik\":\"1121212121\",\"updated_at\":\"2025-08-25 18:05:54\",\"created_at\":\"2025-08-25 18:05:54\",\"id\":31}', '127.0.0.1', '2025-08-25 18:05:54', '2025-08-25 18:05:54'),
(40, 'audit:created', 7, 'App\\Models\\Transaksi#7', NULL, '{\"invoice\":\"TRX-O971CG3M49\",\"events\":\"s:3:\\\"015\\\";\",\"peserta_id\":1,\"amount\":200000,\"note\":\"Admin\",\"status\":\"pending\",\"updated_at\":\"2025-08-25 18:05:54\",\"created_at\":\"2025-08-25 18:05:54\",\"id\":7}', '127.0.0.1', '2025-08-25 18:05:54', '2025-08-25 18:05:54'),
(41, 'audit:created', 32, 'App\\Models\\Pendaftar#32', NULL, '{\"nama\":\"Admin\",\"email\":\"admin@admin.com\",\"no_hp\":null,\"no_tiket\":\"015\",\"total_bayar\":\"200000\",\"event_id\":1,\"nik\":\"1121212121\",\"updated_at\":\"2025-08-25 18:32:55\",\"created_at\":\"2025-08-25 18:32:55\",\"id\":32}', '127.0.0.1', '2025-08-25 18:32:55', '2025-08-25 18:32:55'),
(42, 'audit:created', 8, 'App\\Models\\Transaksi#8', NULL, '{\"invoice\":\"TRX-H2B29F86YW\",\"events\":\"s:3:\\\"015\\\";\",\"peserta_id\":1,\"amount\":200000,\"note\":\"Admin\",\"status\":\"pending\",\"updated_at\":\"2025-08-25 18:32:55\",\"created_at\":\"2025-08-25 18:32:55\",\"id\":8}', '127.0.0.1', '2025-08-25 18:32:55', '2025-08-25 18:32:55'),
(43, 'audit:created', 33, 'App\\Models\\Pendaftar#33', NULL, '{\"email\":\"lombok2@cantik.com\",\"nama\":\"lombok\",\"nik\":\"123871293871238912312\",\"no_hp\":\"08213123123213\",\"event_id\":\"1\",\"no_tiket\":\"015\",\"total_bayar\":\"200000\",\"status_payment\":\"pending\",\"updated_at\":\"2025-08-25 20:26:24\",\"created_at\":\"2025-08-25 20:26:24\",\"id\":33}', '127.0.0.1', '2025-08-25 20:26:24', '2025-08-25 20:26:24'),
(44, 'audit:created', 9, 'App\\Models\\Transaksi#9', NULL, '{\"invoice\":\"TRX-YMRIYUJ61K\",\"events\":\"s:3:\\\"015\\\";\",\"peserta_id\":6,\"amount\":200000,\"note\":\"lombok\",\"status\":\"pending\",\"updated_at\":\"2025-08-25 20:26:24\",\"created_at\":\"2025-08-25 20:26:24\",\"id\":9}', '127.0.0.1', '2025-08-25 20:26:24', '2025-08-25 20:26:24'),
(45, 'audit:created', 34, 'App\\Models\\Pendaftar#34', NULL, '{\"no_tiket\":\"15\",\"nama\":\"Test Runner\",\"nik\":\"1234567890123456\",\"email\":\"testrunner@example.com\",\"no_hp\":\"08123456789\",\"province\":\"NTB\",\"city\":\"Lombok Tengah\",\"address\":\"Jl. Contoh 123\",\"event_id\":1,\"total_bayar\":\"200000\",\"status_payment\":\"pending\",\"updated_at\":\"2025-08-26 14:07:34\",\"created_at\":\"2025-08-26 14:07:34\",\"id\":34}', '127.0.0.1', '2025-08-26 14:07:34', '2025-08-26 14:07:34'),
(46, 'audit:created', 10, 'App\\Models\\Transaksi#10', NULL, '{\"invoice\":\"TRX-PGYXDZTHN5\",\"events\":\"s:2:\\\"15\\\";\",\"event_id\":1,\"amount\":\"200000\",\"note\":\"Test Runner\",\"status\":\"pending\",\"updated_at\":\"2025-08-26 14:07:34\",\"created_at\":\"2025-08-26 14:07:34\",\"id\":10}', '127.0.0.1', '2025-08-26 14:07:34', '2025-08-26 14:07:34'),
(47, 'audit:created', 35, 'App\\Models\\Pendaftar#35', NULL, '{\"no_tiket\":\"16\",\"nama\":\"LT Runner\",\"nik\":\"9876543210987654\",\"email\":\"ltrunner@example.com\",\"no_hp\":\"081200000000\",\"province\":\"NTB\",\"city\":\"Mataram\",\"address\":\"Jl. Tunnel 1\",\"event_id\":1,\"total_bayar\":\"200000\",\"status_payment\":\"pending\",\"updated_at\":\"2025-08-26 15:03:41\",\"created_at\":\"2025-08-26 15:03:41\",\"id\":35}', '127.0.0.1', '2025-08-26 15:03:41', '2025-08-26 15:03:41'),
(48, 'audit:created', 11, 'App\\Models\\Transaksi#11', NULL, '{\"invoice\":\"TRX-P09YOWTAAT\",\"events\":\"s:2:\\\"16\\\";\",\"event_id\":1,\"amount\":\"200000\",\"note\":\"LT Runner\",\"status\":\"pending\",\"updated_at\":\"2025-08-26 15:03:41\",\"created_at\":\"2025-08-26 15:03:41\",\"id\":11}', '127.0.0.1', '2025-08-26 15:03:41', '2025-08-26 15:03:41'),
(49, 'audit:created', 36, 'App\\Models\\Pendaftar#36', NULL, '{\"email\":\"lombok2@cantik.com\",\"nama\":\"lombok\",\"nik\":\"123871293871238912312\",\"no_hp\":\"08213123123213\",\"address\":\"jln sade\",\"province\":\"NTB\",\"city\":\"mataram\",\"event_id\":\"1\",\"no_tiket\":\"017\",\"total_bayar\":\"200000\",\"status_payment\":\"pending\",\"updated_at\":\"2025-08-27 08:06:20\",\"created_at\":\"2025-08-27 08:06:20\",\"id\":36}', '182.253.58.99', '2025-08-27 08:06:20', '2025-08-27 08:06:20'),
(50, 'audit:created', 12, 'App\\Models\\Transaksi#12', NULL, '{\"invoice\":\"TRX-766KJ7TD5U\",\"events\":\"s:3:\\\"017\\\";\",\"peserta_id\":6,\"amount\":200000,\"note\":\"lombok\",\"status\":\"pending\",\"updated_at\":\"2025-08-27 08:06:20\",\"created_at\":\"2025-08-27 08:06:20\",\"id\":12}', '182.253.58.99', '2025-08-27 08:06:20', '2025-08-27 08:06:20'),
(51, 'audit:created', 37, 'App\\Models\\Pendaftar#37', NULL, '{\"email\":\"lombok2@cantik.com\",\"nama\":\"lombok\",\"nik\":\"123871293871238912312\",\"no_hp\":\"08213123123213\",\"address\":\"jln sade\",\"province\":\"NTB\",\"city\":\"mataram\",\"event_id\":\"1\",\"no_tiket\":\"017\",\"total_bayar\":\"200000\",\"status_payment\":\"pending\",\"updated_at\":\"2025-08-27 08:07:46\",\"created_at\":\"2025-08-27 08:07:46\",\"id\":37}', '182.253.58.99', '2025-08-27 08:07:46', '2025-08-27 08:07:46'),
(52, 'audit:created', 13, 'App\\Models\\Transaksi#13', NULL, '{\"invoice\":\"TRX-5FX5Q5XGM0\",\"events\":\"s:3:\\\"017\\\";\",\"peserta_id\":6,\"amount\":200000,\"note\":\"lombok\",\"status\":\"pending\",\"updated_at\":\"2025-08-27 08:07:46\",\"created_at\":\"2025-08-27 08:07:46\",\"id\":13}', '182.253.58.99', '2025-08-27 08:07:46', '2025-08-27 08:07:46'),
(53, 'audit:created', 14, 'App\\Models\\Transaksi#14', NULL, '{\"invoice\":\"TRX-4754EEQ9R7\",\"events\":\"1\",\"peserta_id\":6,\"amount\":\"200000\",\"note\":\"lombok\",\"status\":\"pending\",\"updated_at\":\"2025-08-27 08:13:32\",\"created_at\":\"2025-08-27 08:13:32\",\"id\":14}', '182.253.58.99', '2025-08-27 08:13:32', '2025-08-27 08:13:32'),
(54, 'audit:created', 15, 'App\\Models\\Transaksi#15', NULL, '{\"invoice\":\"TRX-SKY79T98TO\",\"events\":\"1\",\"peserta_id\":6,\"amount\":\"200000\",\"note\":\"lombok\",\"status\":\"pending\",\"updated_at\":\"2025-08-27 08:14:40\",\"created_at\":\"2025-08-27 08:14:40\",\"id\":15}', '182.253.58.99', '2025-08-27 08:14:40', '2025-08-27 08:14:40'),
(55, 'audit:created', 16, 'App\\Models\\Transaksi#16', NULL, '{\"invoice\":\"TRX-W5ZR9Z5I0G\",\"events\":\"1\",\"peserta_id\":6,\"amount\":\"200000\",\"note\":\"lombok\",\"status\":\"pending\",\"updated_at\":\"2025-08-27 08:18:15\",\"created_at\":\"2025-08-27 08:18:15\",\"id\":16}', '182.253.58.99', '2025-08-27 08:18:15', '2025-08-27 08:18:15'),
(56, 'audit:created', 17, 'App\\Models\\Transaksi#17', NULL, '{\"invoice\":\"TRX-2Q126ALC9R\",\"events\":\"1\",\"peserta_id\":6,\"amount\":\"200000\",\"note\":\"lombok\",\"status\":\"pending\",\"updated_at\":\"2025-08-27 08:18:16\",\"created_at\":\"2025-08-27 08:18:16\",\"id\":17}', '182.253.58.99', '2025-08-27 08:18:16', '2025-08-27 08:18:16'),
(57, 'audit:created', 18, 'App\\Models\\Transaksi#18', NULL, '{\"invoice\":\"TRX-Q298PF7P24\",\"events\":\"1\",\"peserta_id\":6,\"amount\":\"200000\",\"note\":\"lombok\",\"status\":\"pending\",\"updated_at\":\"2025-08-27 08:56:41\",\"created_at\":\"2025-08-27 08:56:41\",\"id\":18}', '182.253.58.99', '2025-08-27 08:56:41', '2025-08-27 08:56:41'),
(58, 'audit:created', 19, 'App\\Models\\Transaksi#19', NULL, '{\"invoice\":\"TRX-L25262KH93\",\"events\":\"1\",\"peserta_id\":22,\"amount\":null,\"note\":\"fafafa\",\"status\":\"pending\",\"updated_at\":\"2025-08-27 09:11:50\",\"created_at\":\"2025-08-27 09:11:50\",\"id\":19}', '182.253.58.99', '2025-08-27 09:11:50', '2025-08-27 09:11:50'),
(59, 'audit:created', 20, 'App\\Models\\Transaksi#20', NULL, '{\"invoice\":\"TRX-9USLXP3YIU\",\"events\":\"1\",\"peserta_id\":23,\"amount\":null,\"note\":\"fafa\",\"status\":\"pending\",\"updated_at\":\"2025-08-27 09:13:40\",\"created_at\":\"2025-08-27 09:13:40\",\"id\":20}', '182.253.58.99', '2025-08-27 09:13:40', '2025-08-27 09:13:40'),
(60, 'audit:created', 21, 'App\\Models\\Transaksi#21', NULL, '{\"invoice\":\"TRX-OJ2Z4I951E\",\"events\":\"1\",\"peserta_id\":26,\"amount\":\"200000\",\"note\":\"afaga\",\"status\":\"pending\",\"updated_at\":\"2025-08-27 09:18:25\",\"created_at\":\"2025-08-27 09:18:25\",\"id\":21}', '182.253.58.99', '2025-08-27 09:18:25', '2025-08-27 09:18:25'),
(61, 'audit:created', 22, 'App\\Models\\Transaksi#22', NULL, '{\"invoice\":\"TRX-5UUKMU4Q34\",\"events\":\"1\",\"peserta_id\":27,\"amount\":\"200000\",\"note\":\"Fail Amirku\",\"status\":\"pending\",\"updated_at\":\"2025-08-27 17:16:02\",\"created_at\":\"2025-08-27 17:16:02\",\"id\":22}', '103.136.59.14', '2025-08-27 17:16:02', '2025-08-27 17:16:02'),
(62, 'audit:created', 23, 'App\\Models\\Transaksi#23', NULL, '{\"invoice\":\"TRX-EA40T3ICWD\",\"events\":\"1\",\"peserta_id\":29,\"amount\":\"200000\",\"note\":\"Test1\",\"status\":\"pending\",\"updated_at\":\"2025-08-27 17:29:16\",\"created_at\":\"2025-08-27 17:29:16\",\"id\":23}', '103.136.59.14', '2025-08-27 17:29:16', '2025-08-27 17:29:16'),
(63, 'audit:updated', 23, 'App\\Models\\Transaksi#23', NULL, '{\"status\":\"success\",\"updated_at\":\"2025-08-27 17:30:43\",\"id\":23}', '8.215.21.228', '2025-08-27 17:30:43', '2025-08-27 17:30:43'),
(64, 'audit:updated', 1, 'App\\Models\\Pendaftar#1', 21, '{\"nomor_punggung\":\"00001\",\"updated_at\":\"2025-08-27 18:40:15\",\"paired_at\":\"2025-08-27T18:40:15.876639Z\",\"id\":1}', '103.136.59.14', '2025-08-27 18:40:15', '2025-08-27 18:40:15'),
(65, 'audit:created', 24, 'App\\Models\\Transaksi#24', NULL, '{\"invoice\":\"TRX-MO7FKUX6KB\",\"events\":\"1\",\"peserta_id\":20,\"amount\":\"200000\",\"note\":\"pail\",\"status\":\"pending\",\"updated_at\":\"2025-08-27 18:43:58\",\"created_at\":\"2025-08-27 18:43:58\",\"id\":24}', '103.136.59.14', '2025-08-27 18:43:58', '2025-08-27 18:43:58'),
(66, 'audit:created', 25, 'App\\Models\\Transaksi#25', NULL, '{\"invoice\":\"TRX-5W5TPUARFV\",\"events\":\"2\",\"peserta_id\":31,\"amount\":\"200000\",\"note\":\"huhhg\",\"status\":\"pending\",\"updated_at\":\"2025-08-27 18:57:31\",\"created_at\":\"2025-08-27 18:57:31\",\"id\":25}', '202.59.201.172', '2025-08-27 18:57:31', '2025-08-27 18:57:31'),
(67, 'audit:created', 26, 'App\\Models\\Transaksi#26', NULL, '{\"invoice\":\"TRX-862PKTI2QW\",\"events\":\"2\",\"peserta_id\":38,\"amount\":\"200000\",\"note\":\"Failku\",\"status\":\"pending\",\"updated_at\":\"2025-08-28 18:54:02\",\"created_at\":\"2025-08-28 18:54:02\",\"id\":26}', '202.59.201.172', '2025-08-28 18:54:02', '2025-08-28 18:54:02'),
(68, 'audit:created', 27, 'App\\Models\\Transaksi#27', NULL, '{\"invoice\":\"TRX-31X42S959P\",\"events\":\"1\",\"peserta_id\":6,\"amount\":\"200000\",\"note\":\"lombok\",\"status\":\"pending\",\"updated_at\":\"2025-08-28 19:04:00\",\"created_at\":\"2025-08-28 19:04:00\",\"id\":27}', '182.253.58.35', '2025-08-28 19:04:00', '2025-08-28 19:04:00'),
(69, 'audit:created', 28, 'App\\Models\\Transaksi#28', NULL, '{\"invoice\":\"TRX-785BM02GS1\",\"events\":\"1\",\"peserta_id\":6,\"amount\":\"200000\",\"note\":\"lombok\",\"status\":\"pending\",\"updated_at\":\"2025-08-28 19:04:09\",\"created_at\":\"2025-08-28 19:04:09\",\"id\":28}', '182.253.58.35', '2025-08-28 19:04:09', '2025-08-28 19:04:09'),
(70, 'audit:created', 29, 'App\\Models\\Transaksi#29', NULL, '{\"invoice\":\"TRX-9117G39XTW\",\"events\":\"1\",\"peserta_id\":6,\"amount\":\"200000\",\"note\":\"lombok\",\"status\":\"pending\",\"updated_at\":\"2025-08-28 19:20:54\",\"created_at\":\"2025-08-28 19:20:54\",\"id\":29}', '182.253.58.35', '2025-08-28 19:20:54', '2025-08-28 19:20:54'),
(71, 'audit:created', 30, 'App\\Models\\Transaksi#30', NULL, '{\"invoice\":\"TRX-A4W94C1461\",\"events\":\"2\",\"peserta_id\":38,\"amount\":\"200000\",\"note\":\"Failku\",\"status\":\"pending\",\"updated_at\":\"2025-08-28 19:24:38\",\"created_at\":\"2025-08-28 19:24:38\",\"id\":30}', '202.59.201.172', '2025-08-28 19:24:38', '2025-08-28 19:24:38'),
(72, 'audit:created', 31, 'App\\Models\\Transaksi#31', NULL, '{\"invoice\":\"TRX-B8IR0EFE60\",\"events\":\"1\",\"peserta_id\":6,\"amount\":\"200000\",\"note\":\"lombok\",\"status\":\"pending\",\"updated_at\":\"2025-08-28 19:31:08\",\"created_at\":\"2025-08-28 19:31:08\",\"id\":31}', '182.253.58.35', '2025-08-28 19:31:08', '2025-08-28 19:31:08'),
(73, 'audit:created', 32, 'App\\Models\\Transaksi#32', NULL, '{\"invoice\":\"TRX-ZZQ4IJ8601\",\"events\":\"1\",\"peserta_id\":6,\"amount\":\"200000\",\"note\":\"lombok\",\"status\":\"pending\",\"updated_at\":\"2025-08-28 19:40:25\",\"created_at\":\"2025-08-28 19:40:25\",\"id\":32}', '182.253.58.35', '2025-08-28 19:40:25', '2025-08-28 19:40:25'),
(74, 'audit:created', 33, 'App\\Models\\Transaksi#33', NULL, '{\"invoice\":\"TRX-IC7H9Q0GQX\",\"events\":\"1\",\"peserta_id\":6,\"amount\":\"200000\",\"note\":\"lombok\",\"status\":\"pending\",\"updated_at\":\"2025-08-28 19:40:27\",\"created_at\":\"2025-08-28 19:40:27\",\"id\":33}', '182.253.58.35', '2025-08-28 19:40:27', '2025-08-28 19:40:27'),
(75, 'audit:created', 34, 'App\\Models\\Transaksi#34', NULL, '{\"invoice\":\"TRX-7E3C8E4562\",\"events\":\"1\",\"peserta_id\":6,\"amount\":\"200000\",\"note\":\"lombok\",\"status\":\"pending\",\"updated_at\":\"2025-08-28 19:50:17\",\"created_at\":\"2025-08-28 19:50:17\",\"id\":34}', '182.253.58.35', '2025-08-28 19:50:17', '2025-08-28 19:50:17'),
(76, 'audit:created', 35, 'App\\Models\\Transaksi#35', NULL, '{\"invoice\":\"TRX-KOTF2Z637X\",\"events\":\"1\",\"peserta_id\":6,\"amount\":\"200000\",\"note\":\"lombok\",\"status\":\"pending\",\"updated_at\":\"2025-08-28 19:50:19\",\"created_at\":\"2025-08-28 19:50:19\",\"id\":35}', '182.253.58.35', '2025-08-28 19:50:19', '2025-08-28 19:50:19'),
(77, 'audit:created', 36, 'App\\Models\\Transaksi#36', NULL, '{\"invoice\":\"TRX-94UH01N2G4\",\"events\":\"1\",\"peserta_id\":6,\"amount\":\"200000\",\"note\":\"lombok\",\"status\":\"pending\",\"updated_at\":\"2025-08-28 19:57:03\",\"created_at\":\"2025-08-28 19:57:03\",\"id\":36}', '182.253.58.35', '2025-08-28 19:57:03', '2025-08-28 19:57:03'),
(78, 'audit:created', 37, 'App\\Models\\Transaksi#37', NULL, '{\"invoice\":\"TRX-HNNKP4985U\",\"events\":\"1\",\"peserta_id\":6,\"amount\":\"200000\",\"note\":\"lombok\",\"status\":\"pending\",\"updated_at\":\"2025-08-28 19:57:28\",\"created_at\":\"2025-08-28 19:57:28\",\"id\":37}', '182.253.58.35', '2025-08-28 19:57:28', '2025-08-28 19:57:28'),
(79, 'audit:created', 38, 'App\\Models\\Transaksi#38', NULL, '{\"invoice\":\"TRX-691464RI57\",\"events\":\"1\",\"peserta_id\":41,\"amount\":\"200000\",\"note\":\"kalisya\",\"status\":\"pending\",\"updated_at\":\"2025-08-28 19:59:49\",\"created_at\":\"2025-08-28 19:59:49\",\"id\":38}', '182.253.58.35', '2025-08-28 19:59:49', '2025-08-28 19:59:49'),
(80, 'audit:created', 39, 'App\\Models\\Transaksi#39', NULL, '{\"invoice\":\"TRX-3GUPJUCKD5\",\"events\":\"1\",\"peserta_id\":41,\"amount\":\"200000\",\"note\":\"kalisya\",\"status\":\"pending\",\"updated_at\":\"2025-08-28 19:59:51\",\"created_at\":\"2025-08-28 19:59:51\",\"id\":39}', '182.253.58.35', '2025-08-28 19:59:51', '2025-08-28 19:59:51'),
(81, 'audit:created', 40, 'App\\Models\\Transaksi#40', NULL, '{\"invoice\":\"TRX-6KC54I3IYQ\",\"events\":\"1\",\"peserta_id\":41,\"amount\":\"200000\",\"note\":\"kalisya\",\"status\":\"pending\",\"province\":\"NTB\",\"city\":\"mataram\",\"address\":\"jln sade\",\"no_hp\":\"08213123123213\",\"nik\":\"123871293871238912312\",\"email\":\"lombok2@cantik.com\",\"nama\":\"lombok\",\"qr\":\"\\/home\\/ifailamir-korpri\\/htdocs\\/mandalikakorprirun.com\\/public\\/transactions\\/TRX-6KC54I3IYQ.png\",\"updated_at\":\"2025-08-28 20:02:12\",\"created_at\":\"2025-08-28 20:02:12\",\"id\":40}', '182.253.58.35', '2025-08-28 20:02:12', '2025-08-28 20:02:12'),
(82, 'audit:created', 41, 'App\\Models\\Transaksi#41', NULL, '{\"invoice\":\"TRX-638RN613C5\",\"events\":\"1\",\"peserta_id\":41,\"amount\":\"200000\",\"note\":\"kalisya\",\"status\":\"pending\",\"province\":\"NTB\",\"city\":\"mataram\",\"address\":\"jln sade\",\"no_hp\":\"08213123123213\",\"nik\":\"123871293871238912312\",\"email\":\"lombok2@cantik.com\",\"nama\":\"lombok\",\"qr\":\"\\/home\\/ifailamir-korpri\\/htdocs\\/mandalikakorprirun.com\\/public\\/transactions\\/TRX-638RN613C5.png\",\"updated_at\":\"2025-08-28 20:02:13\",\"created_at\":\"2025-08-28 20:02:13\",\"id\":41}', '182.253.58.35', '2025-08-28 20:02:13', '2025-08-28 20:02:13'),
(83, 'audit:created', 42, 'App\\Models\\Transaksi#42', NULL, '{\"invoice\":\"TRX-2RRSQB4QUY\",\"events\":\"1\",\"peserta_id\":41,\"amount\":\"200000\",\"note\":\"kalisya\",\"status\":\"pending\",\"province\":\"NTB\",\"city\":\"mataram\",\"address\":\"jln sade\",\"no_hp\":\"08213123123213\",\"nik\":\"123871293871238912312\",\"email\":\"lombok2@cantik.com\",\"nama\":\"lombok\",\"qr\":\"\\/home\\/ifailamir-korpri\\/htdocs\\/mandalikakorprirun.com\\/public\\/transactions\\/TRX-2RRSQB4QUY.png\",\"updated_at\":\"2025-08-28 20:03:43\",\"created_at\":\"2025-08-28 20:03:43\",\"id\":42}', '182.253.58.35', '2025-08-28 20:03:43', '2025-08-28 20:03:43'),
(84, 'audit:created', 43, 'App\\Models\\Transaksi#43', NULL, '{\"invoice\":\"TRX-W5A87UR5TR\",\"events\":\"1\",\"peserta_id\":41,\"amount\":\"200000\",\"note\":\"kalisya\",\"status\":\"pending\",\"province\":\"NTB\",\"city\":\"mataram\",\"address\":\"jln sade\",\"no_hp\":\"08213123123213\",\"nik\":\"123871293871238912312\",\"email\":\"lombok2@cantik.com\",\"nama\":\"lombok\",\"qr\":\"\\/home\\/ifailamir-korpri\\/htdocs\\/mandalikakorprirun.com\\/public\\/transactions\\/TRX-W5A87UR5TR.png\",\"updated_at\":\"2025-08-28 20:04:17\",\"created_at\":\"2025-08-28 20:04:17\",\"id\":43}', '182.253.58.35', '2025-08-28 20:04:17', '2025-08-28 20:04:17'),
(85, 'audit:created', 44, 'App\\Models\\Transaksi#44', NULL, '{\"invoice\":\"TRX-4TF3BLU33J\",\"events\":\"1\",\"peserta_id\":41,\"amount\":\"200000\",\"note\":\"kalisya\",\"status\":\"pending\",\"province\":\"NTB\",\"city\":\"mataram\",\"address\":\"jln sade\",\"no_hp\":\"08213123123213\",\"nik\":\"123871293871238912312\",\"email\":\"lombok2@cantik.com\",\"nama\":\"lombok\",\"qr\":\"\\/home\\/ifailamir-korpri\\/htdocs\\/mandalikakorprirun.com\\/public\\/transactions\\/TRX-4TF3BLU33J.png\",\"updated_at\":\"2025-08-28 20:05:09\",\"created_at\":\"2025-08-28 20:05:09\",\"id\":44}', '182.253.58.35', '2025-08-28 20:05:09', '2025-08-28 20:05:09'),
(86, 'audit:created', 45, 'App\\Models\\Transaksi#45', NULL, '{\"invoice\":\"TRX-OOJ6BWDO69\",\"events\":\"1\",\"peserta_id\":41,\"amount\":\"200000\",\"note\":\"kalisya\",\"status\":\"pending\",\"province\":\"NTB\",\"city\":\"mataram\",\"address\":\"jln sade\",\"no_hp\":\"08213123123213\",\"nik\":\"123871293871238912312\",\"email\":\"lombok2@cantik.com\",\"nama\":\"lombok\",\"qr\":\"\\/home\\/ifailamir-korpri\\/htdocs\\/mandalikakorprirun.com\\/public\\/transactions\\/TRX-OOJ6BWDO69.png\",\"updated_at\":\"2025-08-28 20:07:59\",\"created_at\":\"2025-08-28 20:07:59\",\"id\":45}', '182.253.58.35', '2025-08-28 20:07:59', '2025-08-28 20:07:59'),
(87, 'audit:created', 46, 'App\\Models\\Transaksi#46', NULL, '{\"invoice\":\"TRX-S553SE5NJE\",\"events\":\"1\",\"peserta_id\":41,\"amount\":\"200000\",\"note\":\"kalisya\",\"status\":\"pending\",\"province\":\"NTB\",\"city\":\"mataram\",\"address\":\"jln sade\",\"no_hp\":\"08213123123213\",\"nik\":\"123871293871238912312\",\"email\":\"lombok2@cantik.com\",\"nama\":\"lombok\",\"qr\":\"\\/home\\/ifailamir-korpri\\/htdocs\\/mandalikakorprirun.com\\/public\\/transactions\\/TRX-S553SE5NJE.png\",\"updated_at\":\"2025-08-28 20:11:17\",\"created_at\":\"2025-08-28 20:11:17\",\"id\":46}', '182.253.58.35', '2025-08-28 20:11:17', '2025-08-28 20:11:17'),
(88, 'audit:updated', 30, 'App\\Models\\Transaksi#30', NULL, '{\"status\":\"success\",\"updated_at\":\"2025-08-28 22:00:53\",\"id\":30}', '147.139.132.215', '2025-08-28 22:00:53', '2025-08-28 22:00:53'),
(89, 'audit:created', 47, 'App\\Models\\Transaksi#47', NULL, '{\"invoice\":\"TRX-46LR1WMU8A\",\"events\":\"2\",\"peserta_id\":38,\"amount\":\"200000\",\"note\":\"Failku\",\"status\":\"pending\",\"province\":null,\"city\":null,\"address\":\"dwwdwddw\",\"no_hp\":\"3324342\",\"nik\":\"2434324423432243\",\"email\":\"fail@amir.com\",\"nama\":\"ddwwd\",\"qr\":\"\\/home\\/ifailamir-korpri\\/htdocs\\/mandalikakorprirun.com\\/public\\/transactions\\/TRX-46LR1WMU8A.png\",\"updated_at\":\"2025-08-28 23:28:09\",\"created_at\":\"2025-08-28 23:28:09\",\"id\":47}', '103.136.59.14', '2025-08-28 23:28:09', '2025-08-28 23:28:09'),
(90, 'audit:created', 48, 'App\\Models\\Transaksi#48', NULL, '{\"invoice\":\"TRX-DR8NJ5T5GZ\",\"events\":\"2\",\"peserta_id\":38,\"amount\":\"200000\",\"note\":\"Failku\",\"status\":\"pending\",\"province\":null,\"city\":null,\"address\":\"ddwdwddw\",\"no_hp\":\"0809809\",\"nik\":\"0808098232323323\",\"email\":\"fail@amir.com\",\"nama\":\"Fail\",\"qr\":\"\\/home\\/ifailamir-korpri\\/htdocs\\/mandalikakorprirun.com\\/public\\/transactions\\/TRX-DR8NJ5T5GZ.png\",\"updated_at\":\"2025-08-28 23:36:17\",\"created_at\":\"2025-08-28 23:36:17\",\"id\":48}', '103.136.59.14', '2025-08-28 23:36:17', '2025-08-28 23:36:17'),
(91, 'audit:created', 49, 'App\\Models\\Transaksi#49', NULL, '{\"invoice\":\"TRX-8BCGP2J8AL\",\"events\":\"2\",\"peserta_id\":38,\"amount\":\"200000\",\"note\":\"Failku\",\"status\":\"pending\",\"province\":null,\"city\":null,\"address\":\"efeffe\",\"no_hp\":\"34334\",\"nik\":\"4355434354443455\",\"email\":\"fail@amir.com\",\"nama\":\"feefe\",\"qr\":\"\\/home\\/ifailamir-korpri\\/htdocs\\/mandalikakorprirun.com\\/public\\/transactions\\/TRX-8BCGP2J8AL.png\",\"updated_at\":\"2025-08-28 23:41:29\",\"created_at\":\"2025-08-28 23:41:29\",\"id\":49}', '103.136.59.14', '2025-08-28 23:41:29', '2025-08-28 23:41:29'),
(92, 'audit:created', 50, 'App\\Models\\Transaksi#50', NULL, '{\"invoice\":\"TRX-32Q6R240O8\",\"events\":\"2\",\"peserta_id\":38,\"amount\":\"200000\",\"note\":\"Failku\",\"status\":\"pending\",\"province\":null,\"city\":null,\"address\":\"ddedddededwwwd\",\"no_hp\":\"324322342\",\"nik\":\"2334432343343423\",\"email\":\"fail@amir.com\",\"nama\":\"dedeed\",\"qr\":\"\\/home\\/ifailamir-korpri\\/htdocs\\/mandalikakorprirun.com\\/public\\/transactions\\/TRX-32Q6R240O8.png\",\"updated_at\":\"2025-08-28 23:44:25\",\"created_at\":\"2025-08-28 23:44:25\",\"id\":50}', '103.136.59.14', '2025-08-28 23:44:25', '2025-08-28 23:44:25'),
(93, 'audit:created', 51, 'App\\Models\\Transaksi#51', NULL, '{\"invoice\":\"TRX-7083941Q6M\",\"events\":\"2\",\"peserta_id\":38,\"amount\":\"200000\",\"note\":\"Failku\",\"status\":\"pending\",\"province\":null,\"city\":null,\"address\":\"wddwdwdw\",\"no_hp\":\"09090\",\"nik\":\"1233213132212323\",\"email\":\"fail@amir.com\",\"nama\":\"dwddwdw\",\"qr\":\"\\/home\\/ifailamir-korpri\\/htdocs\\/mandalikakorprirun.com\\/public\\/transactions\\/TRX-7083941Q6M.png\",\"updated_at\":\"2025-08-28 23:55:37\",\"created_at\":\"2025-08-28 23:55:37\",\"id\":51}', '103.136.59.14', '2025-08-28 23:55:37', '2025-08-28 23:55:37'),
(94, 'audit:updated', 51, 'App\\Models\\Transaksi#51', NULL, '{\"status\":\"success\",\"updated_at\":\"2025-08-28 23:56:29\",\"id\":51}', '147.139.209.91', '2025-08-28 23:56:29', '2025-08-28 23:56:29'),
(95, 'audit:created', 52, 'App\\Models\\Transaksi#52', NULL, '{\"invoice\":\"TRX-1R1SP1763E\",\"events\":\"2\",\"peserta_id\":38,\"amount\":\"200000\",\"note\":\"Failku\",\"status\":\"pending\",\"province\":null,\"city\":null,\"address\":\"dede\",\"no_hp\":\"4223\",\"nik\":\"3434342343432432\",\"email\":\"fail@amir.com\",\"nama\":\"dd\",\"qr\":\"\\/home\\/ifailamir-korpri\\/htdocs\\/mandalikakorprirun.com\\/public\\/transactions\\/TRX-1R1SP1763E.png\",\"updated_at\":\"2025-08-29 00:28:11\",\"created_at\":\"2025-08-29 00:28:11\",\"id\":52}', '103.136.59.14', '2025-08-29 00:28:11', '2025-08-29 00:28:11'),
(96, 'audit:created', 53, 'App\\Models\\Transaksi#53', NULL, '{\"invoice\":\"TRX-D482W28KGD\",\"events\":\"2\",\"peserta_id\":42,\"amount\":\"200000\",\"note\":\"Kezia\",\"status\":\"pending\",\"province\":null,\"city\":null,\"address\":\"feefeef\",\"no_hp\":\"5345344553\",\"nik\":\"5553434543453454\",\"email\":\"kezia@gmail.com\",\"nama\":\"345ede\",\"qr\":\"\\/home\\/ifailamir-korpri\\/htdocs\\/mandalikakorprirun.com\\/public\\/transactions\\/TRX-D482W28KGD.png\",\"updated_at\":\"2025-08-29 04:16:00\",\"created_at\":\"2025-08-29 04:16:00\",\"id\":53}', '104.28.236.82', '2025-08-29 04:16:00', '2025-08-29 04:16:00'),
(97, 'audit:created', 54, 'App\\Models\\Transaksi#54', NULL, '{\"invoice\":\"TRX-V7TF31IYPS\",\"events\":\"2\",\"peserta_id\":43,\"amount\":\"200000\",\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":null,\"city\":null,\"address\":\"kljojiojio\",\"no_hp\":\"0980980980980980\",\"nik\":\"8098098098098098\",\"email\":\"jkljklj\",\"nama\":\"ihihiuh\",\"qr\":\"\\/home\\/ifailamir-korpri\\/htdocs\\/mandalikakorprirun.com\\/public\\/transactions\\/TRX-V7TF31IYPS.png\",\"updated_at\":\"2025-08-29 04:44:18\",\"created_at\":\"2025-08-29 04:44:18\",\"id\":54}', '104.28.236.82', '2025-08-29 04:44:18', '2025-08-29 04:44:18'),
(98, 'audit:updated', 54, 'App\\Models\\Transaksi#54', NULL, '{\"status\":\"success\",\"updated_at\":\"2025-08-29 05:59:15\",\"id\":54}', '147.139.173.83', '2025-08-29 05:59:15', '2025-08-29 05:59:15'),
(99, 'audit:created', 55, 'App\\Models\\Transaksi#55', NULL, '{\"invoice\":\"TRX-YCO07JKU4T\",\"events\":\"1\",\"peserta_id\":43,\"amount\":\"200000\",\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"11\",\"city\":\"11.14\",\"address\":\"wdwddwdwdw\",\"no_hp\":\"335543354\",\"nik\":\"9798908099080980\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Fail\",\"qr\":\"\\/home\\/ifailamir-korpri\\/htdocs\\/mandalikakorprirun.com\\/public\\/transactions\\/TRX-YCO07JKU4T.png\",\"updated_at\":\"2025-08-29 15:47:41\",\"created_at\":\"2025-08-29 15:47:41\",\"id\":55}', '103.136.59.14', '2025-08-29 15:47:41', '2025-08-29 15:47:41'),
(100, 'audit:created', 56, 'App\\Models\\Transaksi#56', NULL, '{\"invoice\":\"TRX-UG4RE3O3V8\",\"events\":\"2\",\"peserta_id\":43,\"amount\":\"200000\",\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":null,\"city\":null,\"address\":\"hiuhiuhiuhiuhiu\",\"no_hp\":\"98098098908098\",\"nik\":\"0980980980980980\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"qr\":\"\\/home\\/ifailamir-korpri\\/htdocs\\/mandalikakorprirun.com\\/public\\/transactions\\/TRX-UG4RE3O3V8.png\",\"updated_at\":\"2025-08-29 15:50:28\",\"created_at\":\"2025-08-29 15:50:28\",\"id\":56}', '103.136.59.14', '2025-08-29 15:50:28', '2025-08-29 15:50:28'),
(101, 'audit:updated', 56, 'App\\Models\\Transaksi#56', NULL, '{\"status\":\"success\",\"updated_at\":\"2025-08-29 15:51:24\",\"id\":56}', '8.215.23.167', '2025-08-29 15:51:24', '2025-08-29 15:51:24'),
(102, 'audit:created', 57, 'App\\Models\\Transaksi#57', NULL, '{\"invoice\":\"TRX-H67142HWFZ\",\"events\":\"2\",\"peserta_id\":43,\"amount\":\"200000\",\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":null,\"city\":null,\"address\":\"fefefefefefe\",\"no_hp\":\"325524254224\",\"nik\":\"3234324342324334\",\"email\":\"kezia1@gmail.com\",\"nama\":\"ddsscs\",\"qr\":\"\\/home\\/ifailamir-korpri\\/htdocs\\/mandalikakorprirun.com\\/public\\/transactions\\/TRX-H67142HWFZ.png\",\"updated_at\":\"2025-08-29 16:18:27\",\"created_at\":\"2025-08-29 16:18:27\",\"id\":57}', '103.136.59.14', '2025-08-29 16:18:27', '2025-08-29 16:18:27'),
(103, 'audit:updated', 57, 'App\\Models\\Transaksi#57', NULL, '{\"status\":\"success\",\"updated_at\":\"2025-08-29 16:20:58\",\"id\":57}', '147.139.173.83', '2025-08-29 16:20:58', '2025-08-29 16:20:58'),
(104, 'audit:created', 58, 'App\\Models\\Transaksi#58', NULL, '{\"invoice\":\"TRX-W9DR568V12\",\"events\":\"1\",\"peserta_id\":39,\"amount\":\"200000\",\"note\":\"kalisya\",\"status\":\"pending\",\"province\":\"NTB\",\"city\":\"mataram\",\"address\":\"jln sade\",\"no_hp\":\"08213123123213\",\"nik\":\"123871293871238912312\",\"email\":\"lombok2@cantik.com\",\"nama\":\"lombok\",\"qr\":\"\\/home\\/ifailamir-korpri\\/htdocs\\/mandalikakorprirun.com\\/public\\/transactions\\/TRX-W9DR568V12.png\",\"updated_at\":\"2025-08-29 16:28:56\",\"created_at\":\"2025-08-29 16:28:56\",\"id\":58}', '182.253.58.35', '2025-08-29 16:28:56', '2025-08-29 16:28:56'),
(105, 'audit:created', 59, 'App\\Models\\Transaksi#59', NULL, '{\"invoice\":\"TRX-20V1P44V55\",\"events\":\"1\",\"peserta_id\":39,\"amount\":\"200000\",\"note\":\"kalisya\",\"status\":\"pending\",\"province\":\"NTB\",\"city\":\"mataram\",\"address\":\"jln sade\",\"no_hp\":\"08213123123213\",\"nik\":\"123871293871238912312\",\"email\":\"lombok2@cantik.com\",\"nama\":\"kalisya ku\",\"qr\":\"\\/home\\/ifailamir-korpri\\/htdocs\\/mandalikakorprirun.com\\/public\\/transactions\\/TRX-20V1P44V55.png\",\"updated_at\":\"2025-08-29 16:29:44\",\"created_at\":\"2025-08-29 16:29:44\",\"id\":59}', '182.253.58.35', '2025-08-29 16:29:44', '2025-08-29 16:29:44'),
(106, 'audit:created', 60, 'App\\Models\\Transaksi#60', NULL, '{\"invoice\":\"TRX-0LOL2O2AA0\",\"events\":\"1\",\"peserta_id\":39,\"amount\":\"200000\",\"note\":\"kalisya\",\"status\":\"pending\",\"province\":\"NTB\",\"city\":\"mataram\",\"address\":\"jln sade\",\"no_hp\":\"08213123123213\",\"nik\":\"123871293871238912312\",\"email\":\"lombok2@cantik.com\",\"nama\":\"kalisya ku\",\"qr\":\"https:\\/\\/mandalikakorprirun.com\\/transactions\\/TRX-0LOL2O2AA0.png\",\"updated_at\":\"2025-08-29 16:30:40\",\"created_at\":\"2025-08-29 16:30:40\",\"id\":60}', '182.253.58.35', '2025-08-29 16:30:40', '2025-08-29 16:30:40'),
(107, 'audit:created', 61, 'App\\Models\\Transaksi#61', NULL, '{\"invoice\":\"TRX-848T12K1C3\",\"events\":\"2\",\"peserta_id\":43,\"amount\":\"200000\",\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"15\",\"city\":\"15.05\",\"address\":\"efeeffe\",\"no_hp\":\"432234555\",\"nik\":\"4535435453453445\",\"email\":\"kezia1@gmail.com\",\"nama\":\"rerereffe\",\"qr\":\"https:\\/\\/mandalikakorprirun.com\\/transactions\\/TRX-848T12K1C3.png\",\"updated_at\":\"2025-08-29 20:52:37\",\"created_at\":\"2025-08-29 20:52:37\",\"id\":61}', '103.136.59.14', '2025-08-29 20:52:37', '2025-08-29 20:52:37'),
(108, 'audit:created', 62, 'App\\Models\\Transaksi#62', NULL, '{\"invoice\":\"TRX-N4720UI287\",\"events\":\"2\",\"peserta_id\":43,\"amount\":\"200000\",\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"75\",\"city\":\"75.02\",\"address\":\"ggcf ga bhht to\",\"no_hp\":\"869678788888\",\"nik\":\"5558522555555555\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"qr\":\"https:\\/\\/mandalikakorprirun.com\\/transactions\\/TRX-N4720UI287.png\",\"updated_at\":\"2025-08-29 21:17:32\",\"created_at\":\"2025-08-29 21:17:32\",\"id\":62}', '202.59.201.172', '2025-08-29 21:17:32', '2025-08-29 21:17:32'),
(109, 'audit:updated', 62, 'App\\Models\\Transaksi#62', NULL, '{\"status\":\"expired\",\"updated_at\":\"2025-08-29 21:34:13\",\"id\":62}', '147.139.213.108', '2025-08-29 21:34:13', '2025-08-29 21:34:13'),
(110, 'audit:updated', 52, 'App\\Models\\Transaksi#52', NULL, '{\"status\":\"expired\",\"updated_at\":\"2025-08-30 00:29:25\",\"id\":52}', '149.129.227.68', '2025-08-30 00:29:25', '2025-08-30 00:29:25'),
(111, 'audit:created', 63, 'App\\Models\\Transaksi#63', NULL, '{\"invoice\":\"TRX-MOXV0823U1\",\"events\":\"2\",\"peserta_id\":45,\"amount\":\"200000\",\"note\":\"Candra\",\"status\":\"pending\",\"province\":null,\"city\":null,\"address\":\"iuoiuouio\",\"no_hp\":\"980980980\",\"nik\":\"0890809890890809\",\"email\":\"candra@candra.com\",\"nama\":\"Candra\",\"qr\":\"https:\\/\\/mandalikakorprirun.com\\/transactions\\/TRX-MOXV0823U1.png\",\"updated_at\":\"2025-08-30 06:19:34\",\"created_at\":\"2025-08-30 06:19:34\",\"id\":63}', '202.59.201.172', '2025-08-30 06:19:34', '2025-08-30 06:19:34'),
(112, 'audit:updated', 63, 'App\\Models\\Transaksi#63', NULL, '{\"status\":\"success\",\"updated_at\":\"2025-08-30 06:20:04\",\"id\":63}', '147.139.132.215', '2025-08-30 06:20:04', '2025-08-30 06:20:04'),
(113, 'audit:created', 64, 'App\\Models\\Transaksi#64', NULL, '{\"invoice\":\"TRX-Q8T5CC8X14\",\"events\":\"2\",\"peserta_id\":45,\"amount\":\"200000\",\"note\":\"Candra\",\"status\":\"pending\",\"province\":\"51\",\"city\":\"51.06\",\"address\":\"piopoiopi\",\"no_hp\":\"33243234\",\"nik\":\"3434534523343443\",\"email\":\"candra@candra.com\",\"nama\":\"eeffeef\",\"qr\":\"https:\\/\\/mandalikakorprirun.com\\/transactions\\/TRX-Q8T5CC8X14.png\",\"updated_at\":\"2025-08-30 07:31:35\",\"created_at\":\"2025-08-30 07:31:35\",\"id\":64}', '202.59.201.172', '2025-08-30 07:31:35', '2025-08-30 07:31:35'),
(114, 'audit:updated', 55, 'App\\Models\\Transaksi#55', NULL, '{\"status\":\"expired\",\"updated_at\":\"2025-08-30 15:48:52\",\"id\":55}', '147.139.206.250', '2025-08-30 15:48:52', '2025-08-30 15:48:52'),
(115, 'audit:created', 65, 'App\\Models\\Transaksi#65', 43, '{\"invoice\":\"TRX-S314IJT53L\",\"events\":\"[1,2]\",\"peserta_id\":43,\"amount\":275000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Jawa Barat\",\"city\":\"Bandung\",\"address\":\"Jl. ... No. ...\",\"no_hp\":\"0811...\",\"nik\":\"317...\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"created_by_id\":43,\"updated_at\":\"2025-08-30 18:54:15\",\"created_at\":\"2025-08-30 18:54:15\",\"id\":65}', '202.59.201.172', '2025-08-30 18:54:15', '2025-08-30 18:54:15'),
(116, 'audit:created', 66, 'App\\Models\\Transaksi#66', 43, '{\"invoice\":\"TRX-8136S5LF2P\",\"events\":\"[1,2]\",\"peserta_id\":43,\"amount\":275000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Jawa Barat\",\"city\":\"Bandung\",\"address\":\"Jl. ... No. ...\",\"no_hp\":\"0811...\",\"nik\":\"317...\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":1,\\\"name\\\":\\\"Teman A\\\",\\\"email\\\":\\\"a@example.com\\\",\\\"phone\\\":\\\"0811...\\\",\\\"nik\\\":\\\"317...\\\",\\\"province\\\":\\\"Jawa Barat\\\",\\\"city\\\":\\\"Bandung\\\",\\\"address\\\":\\\"Jl. ... No. ...\\\"},{\\\"ticketId\\\":2,\\\"name\\\":\\\"Teman B\\\",\\\"email\\\":\\\"b@example.com\\\",\\\"phone\\\":\\\"0812...\\\",\\\"nik\\\":\\\"317...\\\",\\\"province\\\":\\\"DKI Jakarta\\\",\\\"city\\\":\\\"Jakarta\\\",\\\"address\\\":\\\"Jl. ...\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-08-30 18:56:57\",\"created_at\":\"2025-08-30 18:56:57\",\"id\":66}', '202.59.201.172', '2025-08-30 18:56:57', '2025-08-30 18:56:57'),
(117, 'audit:created', 67, 'App\\Models\\Transaksi#67', 43, '{\"invoice\":\"TRX-ZU2B88U81R\",\"events\":\"[1,2]\",\"peserta_id\":43,\"amount\":275000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Jawa Barat\",\"city\":\"Bandung\",\"address\":\"Jl. ... No. ...\",\"no_hp\":\"0811...\",\"nik\":\"317...\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":1,\\\"name\\\":\\\"Teman A\\\",\\\"email\\\":\\\"a@example.com\\\",\\\"phone\\\":\\\"0811...\\\",\\\"nik\\\":\\\"317...\\\",\\\"province\\\":\\\"Jawa Barat\\\",\\\"city\\\":\\\"Bandung\\\",\\\"address\\\":\\\"Jl. ... No. ...\\\"},{\\\"ticketId\\\":2,\\\"name\\\":\\\"Teman B\\\",\\\"email\\\":\\\"b@example.com\\\",\\\"phone\\\":\\\"0812...\\\",\\\"nik\\\":\\\"317...\\\",\\\"province\\\":\\\"DKI Jakarta\\\",\\\"city\\\":\\\"Jakarta\\\",\\\"address\\\":\\\"Jl. ...\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-08-30 18:59:46\",\"created_at\":\"2025-08-30 18:59:46\",\"id\":67}', '202.59.201.172', '2025-08-30 18:59:46', '2025-08-30 18:59:46'),
(118, 'audit:created', 68, 'App\\Models\\Transaksi#68', 43, '{\"invoice\":\"TRX-2392Z600LQ\",\"events\":\"[1]\",\"peserta_id\":43,\"amount\":75000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Jawa Barat\",\"city\":\"Bandung\",\"address\":\"Jl. ... No. ...\",\"no_hp\":\"0811...\",\"nik\":\"317...\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":1,\\\"name\\\":\\\"Teman A\\\",\\\"email\\\":\\\"a@example.com\\\",\\\"phone\\\":\\\"0811...\\\",\\\"nik\\\":\\\"317...\\\",\\\"province\\\":\\\"Jawa Barat\\\",\\\"city\\\":\\\"Bandung\\\",\\\"address\\\":\\\"Jl. ... No. ...\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-09-01 12:44:18\",\"created_at\":\"2025-09-01 12:44:18\",\"id\":68}', '202.59.201.172', '2025-09-01 12:44:18', '2025-09-01 12:44:18'),
(119, 'audit:created', 69, 'App\\Models\\Transaksi#69', 43, '{\"invoice\":\"TRX-Z1F59E7MA0\",\"events\":\"[\\\"1\\\",\\\"2\\\"]\",\"peserta_id\":43,\"amount\":275000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Aceh\",\"city\":\"Kabupaten Aceh Barat\",\"no_hp\":\"0878372823782738\",\"nik\":\"989089089089089080\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":\\\"1\\\",\\\"nik\\\":\\\"989089089089089080\\\",\\\"name\\\":\\\"Fail1\\\",\\\"email\\\":\\\"fail@fail.com\\\",\\\"phone\\\":\\\"0878372823782738\\\",\\\"province\\\":\\\"Aceh\\\",\\\"city\\\":\\\"Kabupaten Aceh Barat\\\"},{\\\"ticketId\\\":\\\"2\\\",\\\"nik\\\":\\\"9089089089089009\\\",\\\"name\\\":\\\"fail2\\\",\\\"email\\\":\\\"fail@fail.com\\\",\\\"phone\\\":\\\"9809890809890809\\\",\\\"province\\\":\\\"Bali\\\",\\\"city\\\":\\\"Kabupaten Badung\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-09-01 15:50:32\",\"created_at\":\"2025-09-01 15:50:32\",\"id\":69}', '202.59.201.172', '2025-09-01 15:50:32', '2025-09-01 15:50:32'),
(120, 'audit:updated', 69, 'App\\Models\\Transaksi#69', NULL, '{\"status\":\"expired\",\"updated_at\":\"2025-09-01 16:17:28\",\"id\":69}', '8.215.23.167', '2025-09-01 16:17:28', '2025-09-01 16:17:28');
INSERT INTO `audit_logs` (`id`, `description`, `subject_id`, `subject_type`, `user_id`, `properties`, `host`, `created_at`, `updated_at`) VALUES
(121, 'audit:created', 70, 'App\\Models\\Transaksi#70', 43, '{\"invoice\":\"TRX-6SQM7N662P\",\"events\":\"[\\\"1\\\"]\",\"peserta_id\":43,\"amount\":75000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Bali\",\"city\":\"Kabupaten Badung\",\"no_hp\":\"098090909090\",\"nik\":\"009090809809809809\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":\\\"1\\\",\\\"nik\\\":\\\"009090809809809809\\\",\\\"name\\\":\\\"candra\\\",\\\"email\\\":\\\"can@can.com\\\",\\\"phone\\\":\\\"098090909090\\\",\\\"province\\\":\\\"Bali\\\",\\\"city\\\":\\\"Kabupaten Badung\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-09-01 16:35:36\",\"created_at\":\"2025-09-01 16:35:36\",\"id\":70}', '202.59.201.172', '2025-09-01 16:35:36', '2025-09-01 16:35:36'),
(122, 'audit:created', 71, 'App\\Models\\Transaksi#71', 43, '{\"invoice\":\"TRX-052FE21G6O\",\"events\":\"[\\\"1\\\"]\",\"peserta_id\":43,\"amount\":75000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Aceh\",\"city\":\"Kabupaten Aceh Barat\",\"no_hp\":\"2443422434324324\",\"nik\":\"098098098098098809\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":\\\"1\\\",\\\"nik\\\":\\\"098098098098098809\\\",\\\"name\\\":\\\"dwwdwd\\\",\\\"email\\\":\\\"dw@dm.com\\\",\\\"phone\\\":\\\"2443422434324324\\\",\\\"province\\\":\\\"Aceh\\\",\\\"city\\\":\\\"Kabupaten Aceh Barat\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-09-01 16:59:13\",\"created_at\":\"2025-09-01 16:59:13\",\"id\":71}', '202.59.201.172', '2025-09-01 16:59:13', '2025-09-01 16:59:13'),
(123, 'audit:created', 72, 'App\\Models\\Transaksi#72', 43, '{\"invoice\":\"TRX-Y1SVXLSW8J\",\"events\":\"[\\\"1\\\"]\",\"peserta_id\":43,\"amount\":75000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Aceh\",\"city\":\"Kabupaten Aceh Barat\",\"no_hp\":\"2443422434324324\",\"nik\":\"098098098098098809\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":\\\"1\\\",\\\"nik\\\":\\\"098098098098098809\\\",\\\"name\\\":\\\"dwwdwd\\\",\\\"email\\\":\\\"dw@dm.com\\\",\\\"phone\\\":\\\"2443422434324324\\\",\\\"province\\\":\\\"Aceh\\\",\\\"city\\\":\\\"Kabupaten Aceh Barat\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-09-01 17:03:03\",\"created_at\":\"2025-09-01 17:03:03\",\"id\":72}', '202.59.201.172', '2025-09-01 17:03:03', '2025-09-01 17:03:03'),
(124, 'audit:created', 73, 'App\\Models\\Transaksi#73', 43, '{\"invoice\":\"TRX-0ILJI8K4KV\",\"events\":\"[\\\"1\\\"]\",\"peserta_id\":43,\"amount\":75000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Aceh\",\"city\":\"Kabupaten Aceh Barat\",\"no_hp\":\"2443422434324324\",\"nik\":\"098098098098098809\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":\\\"1\\\",\\\"nik\\\":\\\"098098098098098809\\\",\\\"name\\\":\\\"dwwdwd\\\",\\\"email\\\":\\\"dw@dm.com\\\",\\\"phone\\\":\\\"2443422434324324\\\",\\\"province\\\":\\\"Aceh\\\",\\\"city\\\":\\\"Kabupaten Aceh Barat\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-09-01 17:12:49\",\"created_at\":\"2025-09-01 17:12:49\",\"id\":73}', '202.59.201.172', '2025-09-01 17:12:49', '2025-09-01 17:12:49'),
(125, 'audit:created', 74, 'App\\Models\\Transaksi#74', 43, '{\"invoice\":\"TRX-D52JIVZ3DU\",\"events\":\"[\\\"1\\\"]\",\"peserta_id\":43,\"amount\":75000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Aceh\",\"city\":\"Kabupaten Aceh Barat\",\"no_hp\":\"2443422434324324\",\"nik\":\"098098098098098809\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":\\\"1\\\",\\\"nik\\\":\\\"098098098098098809\\\",\\\"name\\\":\\\"dwwdwd\\\",\\\"email\\\":\\\"dw@dm.com\\\",\\\"phone\\\":\\\"2443422434324324\\\",\\\"province\\\":\\\"Aceh\\\",\\\"city\\\":\\\"Kabupaten Aceh Barat\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-09-01 17:14:15\",\"created_at\":\"2025-09-01 17:14:15\",\"id\":74}', '202.59.201.172', '2025-09-01 17:14:15', '2025-09-01 17:14:15'),
(126, 'audit:created', 75, 'App\\Models\\Transaksi#75', 43, '{\"invoice\":\"TRX-M7229ST123\",\"events\":\"[\\\"1\\\"]\",\"peserta_id\":43,\"amount\":75000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Aceh\",\"city\":\"Kabupaten Aceh Barat\",\"no_hp\":\"2443422434324324\",\"nik\":\"098098098098098809\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":\\\"1\\\",\\\"nik\\\":\\\"098098098098098809\\\",\\\"name\\\":\\\"dwwdwd\\\",\\\"email\\\":\\\"dw@dm.com\\\",\\\"phone\\\":\\\"2443422434324324\\\",\\\"province\\\":\\\"Aceh\\\",\\\"city\\\":\\\"Kabupaten Aceh Barat\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-09-01 17:17:00\",\"created_at\":\"2025-09-01 17:17:00\",\"id\":75}', '202.59.201.172', '2025-09-01 17:17:00', '2025-09-01 17:17:00'),
(127, 'audit:created', 76, 'App\\Models\\Transaksi#76', 43, '{\"invoice\":\"TRX-801V2U3XYH\",\"events\":\"[\\\"1\\\"]\",\"peserta_id\":43,\"amount\":75000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Aceh\",\"city\":\"Kabupaten Aceh Barat\",\"no_hp\":\"2443422434324324\",\"nik\":\"098098098098098809\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":\\\"1\\\",\\\"nik\\\":\\\"098098098098098809\\\",\\\"name\\\":\\\"dwwdwd\\\",\\\"email\\\":\\\"dw@dm.com\\\",\\\"phone\\\":\\\"2443422434324324\\\",\\\"province\\\":\\\"Aceh\\\",\\\"city\\\":\\\"Kabupaten Aceh Barat\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-09-01 17:19:32\",\"created_at\":\"2025-09-01 17:19:32\",\"id\":76}', '202.59.201.172', '2025-09-01 17:19:32', '2025-09-01 17:19:32'),
(128, 'audit:created', 77, 'App\\Models\\Transaksi#77', 43, '{\"invoice\":\"TRX-64M9ST73U8\",\"events\":\"[\\\"1\\\"]\",\"peserta_id\":43,\"amount\":75000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Aceh\",\"city\":\"Kabupaten Aceh Barat\",\"no_hp\":\"2443422434324324\",\"nik\":\"098098098098098809\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":\\\"1\\\",\\\"nik\\\":\\\"098098098098098809\\\",\\\"name\\\":\\\"dwwdwd\\\",\\\"email\\\":\\\"dw@dm.com\\\",\\\"phone\\\":\\\"2443422434324324\\\",\\\"province\\\":\\\"Aceh\\\",\\\"city\\\":\\\"Kabupaten Aceh Barat\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-09-01 17:19:38\",\"created_at\":\"2025-09-01 17:19:38\",\"id\":77}', '202.59.201.172', '2025-09-01 17:19:38', '2025-09-01 17:19:38'),
(129, 'audit:created', 78, 'App\\Models\\Transaksi#78', 43, '{\"invoice\":\"TRX-31I30L42Q4\",\"events\":\"[\\\"1\\\"]\",\"peserta_id\":43,\"amount\":75000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Aceh\",\"city\":\"Kabupaten Aceh Barat\",\"no_hp\":\"2443422434324324\",\"nik\":\"098098098098098809\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":\\\"1\\\",\\\"nik\\\":\\\"098098098098098809\\\",\\\"name\\\":\\\"dwwdwd\\\",\\\"email\\\":\\\"dw@dm.com\\\",\\\"phone\\\":\\\"2443422434324324\\\",\\\"province\\\":\\\"Aceh\\\",\\\"city\\\":\\\"Kabupaten Aceh Barat\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-09-01 17:21:41\",\"created_at\":\"2025-09-01 17:21:41\",\"id\":78}', '202.59.201.172', '2025-09-01 17:21:41', '2025-09-01 17:21:41'),
(130, 'audit:created', 79, 'App\\Models\\Transaksi#79', 43, '{\"invoice\":\"TRX-6FXYN1AH1Q\",\"events\":\"[\\\"1\\\"]\",\"peserta_id\":43,\"amount\":75000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Aceh\",\"city\":\"Kabupaten Aceh Barat\",\"no_hp\":\"2443422434324324\",\"nik\":\"098098098098098809\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":\\\"1\\\",\\\"nik\\\":\\\"098098098098098809\\\",\\\"name\\\":\\\"dwwdwd\\\",\\\"email\\\":\\\"dw@dm.com\\\",\\\"phone\\\":\\\"2443422434324324\\\",\\\"province\\\":\\\"Aceh\\\",\\\"city\\\":\\\"Kabupaten Aceh Barat\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-09-01 17:22:25\",\"created_at\":\"2025-09-01 17:22:25\",\"id\":79}', '202.59.201.172', '2025-09-01 17:22:25', '2025-09-01 17:22:25'),
(131, 'audit:created', 80, 'App\\Models\\Transaksi#80', 43, '{\"invoice\":\"TRX-BE2W24X732\",\"events\":\"[\\\"1\\\"]\",\"peserta_id\":43,\"amount\":75000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Aceh\",\"city\":\"Kabupaten Aceh Barat\",\"no_hp\":\"2443422434324324\",\"nik\":\"098098098098098809\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":\\\"1\\\",\\\"nik\\\":\\\"098098098098098809\\\",\\\"name\\\":\\\"dwwdwd\\\",\\\"email\\\":\\\"dw@dm.com\\\",\\\"phone\\\":\\\"2443422434324324\\\",\\\"province\\\":\\\"Aceh\\\",\\\"city\\\":\\\"Kabupaten Aceh Barat\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-09-01 17:26:52\",\"created_at\":\"2025-09-01 17:26:52\",\"id\":80}', '202.59.201.172', '2025-09-01 17:26:52', '2025-09-01 17:26:52'),
(132, 'audit:created', 81, 'App\\Models\\Transaksi#81', 43, '{\"invoice\":\"TRX-5559RR46B9\",\"events\":\"[\\\"1\\\"]\",\"peserta_id\":43,\"amount\":75000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Aceh\",\"city\":\"Kabupaten Aceh Barat\",\"no_hp\":\"2443422434324324\",\"nik\":\"098098098098098809\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":\\\"1\\\",\\\"nik\\\":\\\"098098098098098809\\\",\\\"name\\\":\\\"dwwdwd\\\",\\\"email\\\":\\\"dw@dm.com\\\",\\\"phone\\\":\\\"2443422434324324\\\",\\\"province\\\":\\\"Aceh\\\",\\\"city\\\":\\\"Kabupaten Aceh Barat\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-09-01 17:27:21\",\"created_at\":\"2025-09-01 17:27:21\",\"id\":81}', '202.59.201.172', '2025-09-01 17:27:21', '2025-09-01 17:27:21'),
(133, 'audit:created', 82, 'App\\Models\\Transaksi#82', 43, '{\"invoice\":\"TRX-65GB5B722T\",\"events\":\"[\\\"1\\\"]\",\"peserta_id\":43,\"amount\":75000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Aceh\",\"city\":\"Kabupaten Aceh Barat\",\"no_hp\":\"2443422434324324\",\"nik\":\"098098098098098809\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":\\\"1\\\",\\\"nik\\\":\\\"098098098098098809\\\",\\\"name\\\":\\\"dwwdwd\\\",\\\"email\\\":\\\"dw@dm.com\\\",\\\"phone\\\":\\\"2443422434324324\\\",\\\"province\\\":\\\"Aceh\\\",\\\"city\\\":\\\"Kabupaten Aceh Barat\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-09-01 17:28:43\",\"created_at\":\"2025-09-01 17:28:43\",\"id\":82}', '202.59.201.172', '2025-09-01 17:28:43', '2025-09-01 17:28:43'),
(134, 'audit:created', 83, 'App\\Models\\Transaksi#83', 43, '{\"invoice\":\"TRX-09L261DAMT\",\"events\":\"[\\\"1\\\"]\",\"peserta_id\":43,\"amount\":75000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Aceh\",\"city\":\"Kabupaten Aceh Barat\",\"no_hp\":\"2443422434324324\",\"nik\":\"098098098098098809\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":\\\"1\\\",\\\"nik\\\":\\\"098098098098098809\\\",\\\"name\\\":\\\"dwwdwd\\\",\\\"email\\\":\\\"dw@dm.com\\\",\\\"phone\\\":\\\"2443422434324324\\\",\\\"province\\\":\\\"Aceh\\\",\\\"city\\\":\\\"Kabupaten Aceh Barat\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-09-01 17:29:13\",\"created_at\":\"2025-09-01 17:29:13\",\"id\":83}', '202.59.201.172', '2025-09-01 17:29:13', '2025-09-01 17:29:13'),
(135, 'audit:created', 84, 'App\\Models\\Transaksi#84', 43, '{\"invoice\":\"TRX-73CS9DYAT8\",\"events\":\"[\\\"1\\\"]\",\"peserta_id\":43,\"amount\":75000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Aceh\",\"city\":\"Kabupaten Aceh Barat\",\"no_hp\":\"2443422434324324\",\"nik\":\"098098098098098809\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":\\\"1\\\",\\\"nik\\\":\\\"098098098098098809\\\",\\\"name\\\":\\\"dwwdwd\\\",\\\"email\\\":\\\"dw@dm.com\\\",\\\"phone\\\":\\\"2443422434324324\\\",\\\"province\\\":\\\"Aceh\\\",\\\"city\\\":\\\"Kabupaten Aceh Barat\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-09-01 17:31:15\",\"created_at\":\"2025-09-01 17:31:15\",\"id\":84}', '202.59.201.172', '2025-09-01 17:31:15', '2025-09-01 17:31:15'),
(136, 'audit:created', 85, 'App\\Models\\Transaksi#85', 43, '{\"invoice\":\"TRX-27KVL4XZ1M\",\"events\":\"[\\\"1\\\"]\",\"peserta_id\":43,\"amount\":75000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Aceh\",\"city\":\"Kabupaten Aceh Barat\",\"no_hp\":\"2443422434324324\",\"nik\":\"098098098098098809\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":\\\"1\\\",\\\"nik\\\":\\\"098098098098098809\\\",\\\"name\\\":\\\"dwwdwd\\\",\\\"email\\\":\\\"dw@dm.com\\\",\\\"phone\\\":\\\"2443422434324324\\\",\\\"province\\\":\\\"Aceh\\\",\\\"city\\\":\\\"Kabupaten Aceh Barat\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-09-01 17:31:46\",\"created_at\":\"2025-09-01 17:31:46\",\"id\":85}', '202.59.201.172', '2025-09-01 17:31:46', '2025-09-01 17:31:46'),
(137, 'audit:created', 86, 'App\\Models\\Transaksi#86', 43, '{\"invoice\":\"TRX-65B05TH653\",\"events\":\"[\\\"1\\\"]\",\"peserta_id\":43,\"amount\":75000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Bali\",\"city\":\"Kabupaten Badung\",\"no_hp\":\"0808989080989089\",\"nik\":\"890890809890890890\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":\\\"1\\\",\\\"nik\\\":\\\"890890809890890890\\\",\\\"name\\\":\\\"cac\\\",\\\"email\\\":\\\"ca@ca.com\\\",\\\"phone\\\":\\\"0808989080989089\\\",\\\"province\\\":\\\"Bali\\\",\\\"city\\\":\\\"Kabupaten Badung\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-09-01 17:43:59\",\"created_at\":\"2025-09-01 17:43:59\",\"id\":86}', '202.59.201.172', '2025-09-01 17:43:59', '2025-09-01 17:43:59'),
(138, 'audit:created', 87, 'App\\Models\\Transaksi#87', 43, '{\"invoice\":\"TRX-RHUY7E0UH4\",\"events\":\"[\\\"1\\\",\\\"2\\\"]\",\"peserta_id\":43,\"amount\":275000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Aceh\",\"city\":\"Kabupaten Aceh Barat\",\"no_hp\":\"908098098089\",\"nik\":\"080980980989080980\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":\\\"1\\\",\\\"nik\\\":\\\"080980980989080980\\\",\\\"name\\\":\\\"dwdwdw\\\",\\\"email\\\":\\\"ddwwd@dd.com\\\",\\\"phone\\\":\\\"908098098089\\\",\\\"province\\\":\\\"Aceh\\\",\\\"city\\\":\\\"Kabupaten Aceh Barat\\\"},{\\\"ticketId\\\":\\\"2\\\",\\\"nik\\\":\\\"0980989089080980\\\",\\\"name\\\":\\\"dwdwdwdw\\\",\\\"email\\\":\\\"dwwd@cc.ocm\\\",\\\"phone\\\":\\\"2098098089\\\",\\\"province\\\":\\\"Aceh\\\",\\\"city\\\":\\\"Kabupaten Aceh Barat\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-09-01 18:42:02\",\"created_at\":\"2025-09-01 18:42:02\",\"id\":87}', '202.59.201.172', '2025-09-01 18:42:02', '2025-09-01 18:42:02'),
(139, 'audit:created', 88, 'App\\Models\\Transaksi#88', 43, '{\"invoice\":\"TRX-SDQJ2RJC54\",\"events\":\"[\\\"1\\\"]\",\"peserta_id\":43,\"amount\":75000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Bali\",\"city\":\"Kabupaten Bangli\",\"no_hp\":\"098098908098998\",\"nik\":\"080809809809809809\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":\\\"1\\\",\\\"nik\\\":\\\"080809809809809809\\\",\\\"name\\\":\\\"cc\\\",\\\"email\\\":\\\"cc@cc.com\\\",\\\"phone\\\":\\\"098098908098998\\\",\\\"province\\\":\\\"Bali\\\",\\\"city\\\":\\\"Kabupaten Bangli\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-09-01 19:14:11\",\"created_at\":\"2025-09-01 19:14:11\",\"id\":88}', '202.59.201.172', '2025-09-01 19:14:11', '2025-09-01 19:14:11'),
(140, 'audit:created', 1, 'App\\Models\\Transaksi#1', 43, '{\"invoice\":\"TRX-U0EOUD9DW2\",\"events\":\"[\\\"1\\\"]\",\"peserta_id\":43,\"amount\":75000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Aceh\",\"city\":\"Kabupaten Aceh Barat\",\"no_hp\":\"080890809809\",\"nik\":\"990809890809809809\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":\\\"1\\\",\\\"nik\\\":\\\"990809890809809809\\\",\\\"name\\\":\\\"cjidjiow\\\",\\\"email\\\":\\\"dwwd@c.comc\\\",\\\"phone\\\":\\\"080890809809\\\",\\\"province\\\":\\\"Aceh\\\",\\\"city\\\":\\\"Kabupaten Aceh Barat\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-09-01 19:33:56\",\"created_at\":\"2025-09-01 19:33:56\",\"id\":1}', '202.59.201.172', '2025-09-01 19:33:56', '2025-09-01 19:33:56'),
(141, 'audit:created', 2, 'App\\Models\\Transaksi#2', 43, '{\"invoice\":\"TRX-KR1TWGLBJH\",\"events\":\"[\\\"1\\\"]\",\"peserta_id\":43,\"amount\":75000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Bali\",\"city\":\"Kabupaten Badung\",\"no_hp\":\"876796979979797\",\"nik\":\"908908908954664565\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":\\\"1\\\",\\\"nik\\\":\\\"908908908954664565\\\",\\\"name\\\":\\\"dwdwdw\\\",\\\"email\\\":\\\"wddw@ffmf.com\\\",\\\"phone\\\":\\\"876796979979797\\\",\\\"province\\\":\\\"Bali\\\",\\\"city\\\":\\\"Kabupaten Badung\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-09-01 19:44:17\",\"created_at\":\"2025-09-01 19:44:17\",\"id\":2}', '202.59.201.172', '2025-09-01 19:44:17', '2025-09-01 19:44:17'),
(142, 'audit:created', 3, 'App\\Models\\Transaksi#3', 43, '{\"invoice\":\"TRX-K49BWQ0MDB\",\"events\":\"[\\\"1\\\"]\",\"peserta_id\":43,\"amount\":75000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Bali\",\"city\":\"Kabupaten Bangli\",\"no_hp\":\"090909022\",\"nik\":\"009808098098090890\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":\\\"1\\\",\\\"nik\\\":\\\"009808098098090890\\\",\\\"name\\\":\\\"dwdw\\\",\\\"email\\\":\\\"wdwd@fm.com\\\",\\\"phone\\\":\\\"090909022\\\",\\\"province\\\":\\\"Bali\\\",\\\"city\\\":\\\"Kabupaten Bangli\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-09-01 20:01:33\",\"created_at\":\"2025-09-01 20:01:33\",\"id\":3}', '202.59.201.172', '2025-09-01 20:01:33', '2025-09-01 20:01:33'),
(143, 'audit:updated', 3, 'App\\Models\\Transaksi#3', NULL, '{\"status\":\"success\",\"updated_at\":\"2025-09-01 20:02:10\",\"id\":3}', '8.215.23.167', '2025-09-01 20:02:10', '2025-09-01 20:02:10'),
(144, 'audit:created', 4, 'App\\Models\\Transaksi#4', 43, '{\"invoice\":\"TRX-1541706496\",\"events\":\"[\\\"1\\\"]\",\"peserta_id\":43,\"amount\":75000,\"note\":\"Kezia1\",\"status\":\"pending\",\"province\":\"Aceh\",\"city\":\"Kabupaten Aceh Barat Daya\",\"no_hp\":\"08123747474\",\"nik\":\"845546466434364655\",\"email\":\"kezia1@gmail.com\",\"nama\":\"Kezia1\",\"participants\":\"[{\\\"ticketId\\\":\\\"1\\\",\\\"nik\\\":\\\"845546466434364655\\\",\\\"name\\\":\\\"kezia\\\",\\\"email\\\":\\\"kezia@gmail.com\\\",\\\"phone\\\":\\\"08123747474\\\",\\\"province\\\":\\\"Aceh\\\",\\\"city\\\":\\\"Kabupaten Aceh Barat Daya\\\"}]\",\"created_by_id\":43,\"updated_at\":\"2025-09-02 06:44:31\",\"created_at\":\"2025-09-02 06:44:31\",\"id\":4}', '182.2.166.141', '2025-09-02 06:44:31', '2025-09-02 06:44:31');

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` bigint UNSIGNED NOT NULL,
  `nama_event` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `harga` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_mulai` datetime DEFAULT NULL,
  `tanggal_selesai` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `nama_event`, `event_code`, `harga`, `tanggal_mulai`, `tanggal_selesai`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Tiket Untuk ASN', 'KORPRI_ASN', '75000', '2025-08-31 02:45:06', NULL, '2025-08-24 14:45:19', NULL, NULL),
(2, 'Tiket Untuk Umum', 'KORPRI_UMUM', '200000', '2025-08-31 02:45:06', NULL, '2025-08-24 14:45:19', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `faq_categories`
--

CREATE TABLE `faq_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faq_questions`
--

CREATE TABLE `faq_questions` (
  `id` bigint UNSIGNED NOT NULL,
  `question` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `answer` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `collection_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `conversions_disk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint UNSIGNED NOT NULL,
  `manipulations` json NOT NULL,
  `custom_properties` json NOT NULL,
  `generated_conversions` json NOT NULL,
  `responsive_images` json NOT NULL,
  `order_column` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`id`, `model_type`, `model_id`, `uuid`, `collection_name`, `name`, `file_name`, `mime_type`, `disk`, `conversions_disk`, `size`, `manipulations`, `custom_properties`, `generated_conversions`, `responsive_images`, `order_column`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\Transaksi', 34, '68bc8d22-c73f-4528-9427-659ae748aa0e', 'qr', 'TRX-7E3C8E4562', 'TRX-7E3C8E4562.png', 'image/png', 'public', 'public', 1635, '[]', '[]', '[]', '[]', 1, '2025-08-28 19:50:17', '2025-08-28 19:50:17'),
(2, 'App\\Models\\Transaksi', 35, '15da5465-d36e-46e1-a5e0-084895b13130', 'qr', 'TRX-KOTF2Z637X', 'TRX-KOTF2Z637X.png', 'image/png', 'public', 'public', 1612, '[]', '[]', '[]', '[]', 1, '2025-08-28 19:50:19', '2025-08-28 19:50:19'),
(3, 'App\\Models\\Transaksi', 36, 'dd3eef79-ef01-46ee-806d-da75e2d77f06', 'qr', 'TRX-94UH01N2G4', 'TRX-94UH01N2G4.png', 'image/png', 'public', 'public', 1634, '[]', '[]', '[]', '[]', 1, '2025-08-28 19:57:03', '2025-08-28 19:57:03'),
(4, 'App\\Models\\Transaksi', 37, '928b7011-f2a5-4e72-b4d0-d59a48a4a686', 'qr', 'TRX-HNNKP4985U', 'TRX-HNNKP4985U.png', 'image/png', 'public', 'public', 1703, '[]', '[]', '[]', '[]', 1, '2025-08-28 19:57:28', '2025-08-28 19:57:28'),
(5, 'App\\Models\\Transaksi', 38, '74e84ad3-ecaa-45a1-bb96-dbea441cff6b', 'qr', 'TRX-691464RI57', 'TRX-691464RI57.png', 'image/png', 'public', 'public', 1610, '[]', '[]', '[]', '[]', 1, '2025-08-28 19:59:49', '2025-08-28 19:59:49'),
(6, 'App\\Models\\Transaksi', 39, '49f3eaeb-65c2-4e82-9b92-c764171c547a', 'qr', 'TRX-3GUPJUCKD5', 'TRX-3GUPJUCKD5.png', 'image/png', 'public', 'public', 1604, '[]', '[]', '[]', '[]', 1, '2025-08-28 19:59:51', '2025-08-28 19:59:51'),
(7, 'App\\Models\\Transaksi', 40, 'ac95a871-6907-4c46-80a9-aaeea0e1470c', 'qr', 'TRX-6KC54I3IYQ', 'TRX-6KC54I3IYQ.png', 'image/png', 'public', 'public', 1655, '[]', '[]', '[]', '[]', 1, '2025-08-28 20:02:12', '2025-08-28 20:02:12'),
(8, 'App\\Models\\Transaksi', 41, '4e1d8e29-a5fa-40d9-8466-d2d23136a92e', 'qr', 'TRX-638RN613C5', 'TRX-638RN613C5.png', 'image/png', 'public', 'public', 1582, '[]', '[]', '[]', '[]', 1, '2025-08-28 20:02:13', '2025-08-28 20:02:13'),
(9, 'App\\Models\\Transaksi', 42, '3511257c-15d4-4c39-aba2-08b5274ff64c', 'qr', 'TRX-2RRSQB4QUY', 'TRX-2RRSQB4QUY.png', 'image/png', 'public', 'public', 1660, '[]', '[]', '[]', '[]', 1, '2025-08-28 20:03:43', '2025-08-28 20:03:43'),
(10, 'App\\Models\\Transaksi', 43, '1e55faec-e5a0-424d-876e-5bc587b360c1', 'qr', 'TRX-W5A87UR5TR', 'TRX-W5A87UR5TR.png', 'image/png', 'public', 'public', 1634, '[]', '[]', '[]', '[]', 1, '2025-08-28 20:04:17', '2025-08-28 20:04:17'),
(11, 'App\\Models\\Transaksi', 44, '3bc496f0-7853-4588-806f-1d347f7fa668', 'qr', 'TRX-4TF3BLU33J', 'TRX-4TF3BLU33J.png', 'image/png', 'public', 'public', 1619, '[]', '[]', '[]', '[]', 1, '2025-08-28 20:05:09', '2025-08-28 20:05:09'),
(12, 'App\\Models\\Transaksi', 45, 'abcc12a1-72df-4e6e-9015-c8b38f0b45d3', 'qr', 'TRX-OOJ6BWDO69', 'TRX-OOJ6BWDO69.png', 'image/png', 'public', 'public', 1651, '[]', '[]', '[]', '[]', 1, '2025-08-28 20:07:59', '2025-08-28 20:07:59'),
(13, 'App\\Models\\Transaksi', 46, 'b877d87f-3cc8-4eca-bd42-3da81f376f2e', 'qr', 'TRX-S553SE5NJE', 'TRX-S553SE5NJE.png', 'image/png', 'public', 'public', 1628, '[]', '[]', '[]', '[]', 1, '2025-08-28 20:11:17', '2025-08-28 20:11:17'),
(14, 'App\\Models\\Transaksi', 47, '50984ee2-07e8-4295-9129-0ebe4193bda2', 'qr', 'TRX-46LR1WMU8A', 'TRX-46LR1WMU8A.png', 'image/png', 'public', 'public', 1630, '[]', '[]', '[]', '[]', 1, '2025-08-28 23:28:09', '2025-08-28 23:28:09'),
(15, 'App\\Models\\Transaksi', 48, 'e2855578-b54f-4fce-9d1a-ce5aebed2122', 'qr', 'TRX-DR8NJ5T5GZ', 'TRX-DR8NJ5T5GZ.png', 'image/png', 'public', 'public', 1638, '[]', '[]', '[]', '[]', 1, '2025-08-28 23:36:17', '2025-08-28 23:36:17'),
(16, 'App\\Models\\Transaksi', 49, '77b44b9d-a214-4623-aace-06582efed62b', 'qr', 'TRX-8BCGP2J8AL', 'TRX-8BCGP2J8AL.png', 'image/png', 'public', 'public', 1609, '[]', '[]', '[]', '[]', 1, '2025-08-28 23:41:29', '2025-08-28 23:41:29'),
(17, 'App\\Models\\Transaksi', 50, '8f207ca4-f0a6-4c59-9fd4-76fd24341a76', 'qr', 'TRX-32Q6R240O8', 'TRX-32Q6R240O8.png', 'image/png', 'public', 'public', 1584, '[]', '[]', '[]', '[]', 1, '2025-08-28 23:44:25', '2025-08-28 23:44:25'),
(18, 'App\\Models\\Transaksi', 51, '471d604f-3c89-4cfa-9ff8-965738b7c0c6', 'qr', 'TRX-7083941Q6M', 'TRX-7083941Q6M.png', 'image/png', 'public', 'public', 1632, '[]', '[]', '[]', '[]', 1, '2025-08-28 23:55:37', '2025-08-28 23:55:37'),
(19, 'App\\Models\\Transaksi', 52, '4289cbe1-2a12-4d33-ae5a-af589294f209', 'qr', 'TRX-1R1SP1763E', 'TRX-1R1SP1763E.png', 'image/png', 'public', 'public', 1652, '[]', '[]', '[]', '[]', 1, '2025-08-29 00:28:11', '2025-08-29 00:28:11'),
(20, 'App\\Models\\Transaksi', 53, '5bb4d259-5bfc-4f3b-82da-d32477c66e8a', 'qr', 'TRX-D482W28KGD', 'TRX-D482W28KGD.png', 'image/png', 'public', 'public', 1627, '[]', '[]', '[]', '[]', 1, '2025-08-29 04:16:00', '2025-08-29 04:16:00'),
(21, 'App\\Models\\Transaksi', 54, '45d42315-70e0-4399-a64f-5f9479b31912', 'qr', 'TRX-V7TF31IYPS', 'TRX-V7TF31IYPS.png', 'image/png', 'public', 'public', 1674, '[]', '[]', '[]', '[]', 1, '2025-08-29 04:44:18', '2025-08-29 04:44:18'),
(22, 'App\\Models\\Transaksi', 55, '30523ac2-ed4e-473d-972c-252006c88d43', 'qr', 'TRX-YCO07JKU4T', 'TRX-YCO07JKU4T.png', 'image/png', 'public', 'public', 1703, '[]', '[]', '[]', '[]', 1, '2025-08-29 15:47:41', '2025-08-29 15:47:41'),
(23, 'App\\Models\\Transaksi', 56, 'd836dc55-dbe1-4ab9-bb34-56250d5d320a', 'qr', 'TRX-UG4RE3O3V8', 'TRX-UG4RE3O3V8.png', 'image/png', 'public', 'public', 1581, '[]', '[]', '[]', '[]', 1, '2025-08-29 15:50:28', '2025-08-29 15:50:28'),
(24, 'App\\Models\\Transaksi', 57, '43b6a449-d9a0-43b3-9783-513f8d87c92b', 'qr', 'TRX-H67142HWFZ', 'TRX-H67142HWFZ.png', 'image/png', 'public', 'public', 1624, '[]', '[]', '[]', '[]', 1, '2025-08-29 16:18:27', '2025-08-29 16:18:27'),
(25, 'App\\Models\\Transaksi', 58, 'a713d787-8895-4c3f-b62d-72e63375eabb', 'qr', 'TRX-W9DR568V12', 'TRX-W9DR568V12.png', 'image/png', 'public', 'public', 1630, '[]', '[]', '[]', '[]', 1, '2025-08-29 16:28:56', '2025-08-29 16:28:56'),
(26, 'App\\Models\\Transaksi', 59, '2a5b835c-6721-4b24-8a5c-b2d28347fc3b', 'qr', 'TRX-20V1P44V55', 'TRX-20V1P44V55.png', 'image/png', 'public', 'public', 1692, '[]', '[]', '[]', '[]', 1, '2025-08-29 16:29:44', '2025-08-29 16:29:44'),
(27, 'App\\Models\\Transaksi', 60, '6bb1df88-6c5a-47cf-ac9a-f007a81d1923', 'qr', 'TRX-0LOL2O2AA0', 'TRX-0LOL2O2AA0.png', 'image/png', 'public', 'public', 1559, '[]', '[]', '[]', '[]', 1, '2025-08-29 16:30:40', '2025-08-29 16:30:40'),
(28, 'App\\Models\\Transaksi', 61, '084ffc27-523d-47c9-bf74-4308d87d1ed9', 'qr', 'TRX-848T12K1C3', 'TRX-848T12K1C3.png', 'image/png', 'public', 'public', 1615, '[]', '[]', '[]', '[]', 1, '2025-08-29 20:52:37', '2025-08-29 20:52:37'),
(29, 'App\\Models\\Transaksi', 62, 'eb79667f-dc4f-4d43-902a-f8b7612209b8', 'qr', 'TRX-N4720UI287', 'TRX-N4720UI287.png', 'image/png', 'public', 'public', 1684, '[]', '[]', '[]', '[]', 1, '2025-08-29 21:17:32', '2025-08-29 21:17:32'),
(30, 'App\\Models\\Transaksi', 63, '4652d0ec-b669-43c6-aead-568241280896', 'qr', 'TRX-MOXV0823U1', 'TRX-MOXV0823U1.png', 'image/png', 'public', 'public', 1636, '[]', '[]', '[]', '[]', 1, '2025-08-30 06:19:34', '2025-08-30 06:19:34'),
(31, 'App\\Models\\Transaksi', 64, 'c4c87411-da3c-45ae-bf2f-a54ab20497d9', 'qr', 'TRX-Q8T5CC8X14', 'TRX-Q8T5CC8X14.png', 'image/png', 'public', 'public', 1637, '[]', '[]', '[]', '[]', 1, '2025-08-30 07:31:35', '2025-08-30 07:31:35');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_100000_create_password_resets_table', 1),
(2, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(3, '2022_07_13_000001_create_audit_logs_table', 1),
(4, '2022_07_13_000002_create_media_table', 1),
(5, '2022_07_13_000003_create_permissions_table', 1),
(6, '2022_07_13_000004_create_roles_table', 1),
(7, '2022_07_13_000005_create_users_table', 1),
(8, '2022_07_13_000006_create_pendaftars_table', 1),
(9, '2022_07_13_000007_create_events_table', 1),
(10, '2022_07_13_000008_create_permission_role_pivot_table', 1),
(11, '2022_07_13_000009_create_role_user_pivot_table', 1),
(12, '2022_07_13_000010_add_relationship_fields_to_pendaftars_table', 1),
(13, '2022_07_21_214250_transaksi', 1),
(14, '2022_08_07_000006_create_tikets_table', 1),
(15, '2022_08_07_000008_create_banners_table', 1),
(16, '2022_08_07_000009_create_user_alerts_table', 1),
(17, '2022_08_07_000010_create_faq_categories_table', 1),
(18, '2022_08_07_000011_create_faq_questions_table', 1),
(19, '2022_08_07_000012_create_transaksis_table', 1),
(20, '2022_08_07_000013_create_sponsors_table', 1),
(21, '2022_08_07_000014_create_settings_table', 1),
(22, '2022_08_07_000017_create_user_user_alert_pivot_table', 1),
(23, '2022_08_07_000018_add_relationship_fields_to_tikets_table', 1),
(24, '2022_08_07_000019_add_relationship_fields_to_faq_questions_table', 1),
(25, '2022_08_07_000020_add_relationship_fields_to_transaksis_table', 1),
(26, '2022_08_07_000021_add_approval_fields', 1),
(27, '2025_08_25_175500_create_refresh_tokens_table', 2),
(28, '2025_08_26_000001_add_fields_to_pendaftars_table', 3),
(29, '2025_08_27_000000_add_paired_at_to_pendaftars_table', 4),
(30, '2025_08_28_050000_add_registration_fields_to_users_table', 4),
(31, '2025_08_28_050100_migrate_pendaftars_to_users', 4),
(32, '2025_08_30_000000_add_paired_at_to_transactions_table', 4),
(33, '2025_08_30_000100_add_nomor_punggung_to_transactions_table', 4),
(34, '2025_08_30_000200_add_participants_to_transactions_table', 4);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pendaftars`
--

CREATE TABLE `pendaftars` (
  `id` bigint UNSIGNED NOT NULL,
  `no_tiket` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomor_punggung` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paired_at` timestamp NULL DEFAULT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nik` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_hp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checkin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_at` datetime DEFAULT NULL,
  `finish_at` datetime DEFAULT NULL,
  `notes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status_payment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_bayar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `event_id` bigint UNSIGNED DEFAULT NULL,
  `paired_att` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pendaftars`
--

INSERT INTO `pendaftars` (`id`, `no_tiket`, `nomor_punggung`, `paired_at`, `nama`, `nik`, `email`, `no_hp`, `province`, `city`, `address`, `checkin`, `start_at`, `finish_at`, `notes`, `status_payment`, `payment_type`, `total_bayar`, `created_at`, `updated_at`, `deleted_at`, `event_id`, `paired_att`) VALUES
(1, '0001', '00001', NULL, 'Runner-0001', '1111', 'Runner-0001@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 07:53:06', '2025-08-27 18:40:15', NULL, NULL, '2025-08-27 18:40:15'),
(2, '02', NULL, NULL, 'Runner-02', '1111', 'Runner-02@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 07:54:38', '2025-08-24 07:54:38', NULL, NULL, ''),
(3, '03', NULL, NULL, 'Runner-03', '1111', 'Runner-03@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 07:55:54', '2025-08-24 07:55:54', NULL, NULL, ''),
(4, '04', NULL, NULL, 'Runner-04', '1111', 'Runner-04@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 07:57:48', '2025-08-24 07:57:48', NULL, NULL, ''),
(5, '05', NULL, NULL, 'Runner-05', '1111', 'Runner-05@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 07:58:20', '2025-08-24 07:58:20', NULL, NULL, ''),
(6, '06', NULL, NULL, 'Runner-06', '1111', 'Runner-06@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 07:58:56', '2025-08-24 07:58:56', NULL, NULL, ''),
(7, '07', NULL, NULL, 'failamir abdullah', '1234567890', 'ifailamir@gmail.com', '0812312312312', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, '200000', '2025-08-24 07:59:11', '2025-08-24 07:59:11', NULL, NULL, ''),
(8, '08', NULL, NULL, 'Runner-08', '1111', 'Runner-08@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 08:04:03', '2025-08-24 08:04:03', NULL, NULL, ''),
(9, '09', NULL, NULL, 'Runner-09', '1111', 'Runner-09@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 09:08:25', '2025-08-24 09:08:25', NULL, NULL, ''),
(10, '010', NULL, NULL, 'Runner-010', '1111', 'Runner-010@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 10:37:49', '2025-08-24 10:37:49', NULL, NULL, ''),
(11, '010', NULL, NULL, 'Runner-010', '1111', 'Runner-010@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 10:45:38', '2025-08-24 10:45:38', NULL, NULL, ''),
(12, '010', NULL, NULL, 'Runner-010', '1111', 'Runner-010@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 10:51:40', '2025-08-24 10:51:40', NULL, NULL, ''),
(13, '10', NULL, NULL, 'Runner-10', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:21:52', '2025-08-25 13:21:52', NULL, NULL, ''),
(14, '11', NULL, NULL, 'Runner-11', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:27:30', '2025-08-25 13:27:30', NULL, NULL, ''),
(15, '12', NULL, NULL, 'Runner-12', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:28:07', '2025-08-25 13:28:07', NULL, NULL, ''),
(16, '13', NULL, NULL, 'Runner-13', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:32:37', '2025-08-25 13:32:37', NULL, NULL, ''),
(17, '14', NULL, NULL, 'Runner-14', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:34:49', '2025-08-25 13:34:49', NULL, NULL, ''),
(18, '00015', NULL, NULL, 'Runner-00015', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:41:37', '2025-08-25 13:41:37', NULL, NULL, ''),
(19, '00016', NULL, NULL, 'Runner-00016', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:46:24', '2025-08-25 13:46:24', NULL, NULL, ''),
(20, '00017', NULL, NULL, 'Runner-00017', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:46:43', '2025-08-25 13:46:43', NULL, NULL, ''),
(21, '00018', NULL, NULL, 'Runner-00018', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:47:02', '2025-08-25 13:47:02', NULL, NULL, ''),
(22, '00019', NULL, NULL, 'Runner-00019', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:47:13', '2025-08-25 13:47:13', NULL, NULL, ''),
(23, '00020', NULL, NULL, 'Runner-00020', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:47:21', '2025-08-25 13:47:21', NULL, NULL, ''),
(24, '00021', NULL, NULL, 'Runner-00021', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:47:39', '2025-08-25 13:47:39', NULL, NULL, ''),
(25, '00022', NULL, NULL, 'Runner-00022', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 14:02:01', '2025-08-25 14:02:01', NULL, NULL, ''),
(26, '015', NULL, NULL, 'lombok', 'u67ty3tw34w535433q432c54353453', 'lombok2@cantik.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 17:37:46', '2025-08-25 17:37:46', NULL, 1, ''),
(27, '015', NULL, NULL, 'lombok', 'u67ty3tw34w535433q432c54353453', 'lombok2@cantik.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 17:41:57', '2025-08-25 17:41:57', NULL, 1, ''),
(28, '015', NULL, NULL, 'Admin', '1121212121', 'admin@admin.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 18:03:47', '2025-08-25 18:03:47', NULL, 1, ''),
(29, '015', NULL, NULL, 'Admin', '1121212121', 'admin@admin.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 18:04:40', '2025-08-25 18:04:40', NULL, 1, ''),
(30, '015', NULL, NULL, 'Admin', '1121212121', 'admin@admin.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 18:05:13', '2025-08-25 18:05:13', NULL, 1, ''),
(31, '015', NULL, NULL, 'Admin', '1121212121', 'admin@admin.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 18:05:54', '2025-08-25 18:05:54', NULL, 1, ''),
(32, '015', NULL, NULL, 'Admin', '1121212121', 'admin@admin.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 18:32:55', '2025-08-25 18:32:55', NULL, 1, ''),
(33, '015', NULL, NULL, 'lombok', '123871293871238912312', 'lombok2@cantik.com', '08213123123213', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, '200000', '2025-08-25 20:26:24', '2025-08-25 20:26:24', NULL, 1, ''),
(34, '15', NULL, NULL, 'Test Runner', '1234567890123456', 'testrunner@example.com', '08123456789', 'NTB', 'Lombok Tengah', 'Jl. Contoh 123', NULL, NULL, NULL, NULL, 'pending', NULL, '200000', '2025-08-26 14:07:34', '2025-08-26 14:07:34', NULL, 1, ''),
(35, '16', NULL, NULL, 'LT Runner', '9876543210987654', 'ltrunner@example.com', '081200000000', 'NTB', 'Mataram', 'Jl. Tunnel 1', NULL, NULL, NULL, NULL, 'pending', NULL, '200000', '2025-08-26 15:03:41', '2025-08-26 15:03:41', NULL, 1, ''),
(36, '017', NULL, NULL, 'lombok', '123871293871238912312', 'lombok2@cantik.com', '08213123123213', 'NTB', 'mataram', 'jln sade', NULL, NULL, NULL, NULL, 'pending', NULL, '200000', '2025-08-27 08:06:20', '2025-08-27 08:06:20', NULL, 1, ''),
(37, '017', NULL, NULL, 'lombok', '123871293871238912312', 'lombok2@cantik.com', '08213123123213', 'NTB', 'mataram', 'jln sade', NULL, NULL, NULL, NULL, 'pending', NULL, '200000', '2025-08-27 08:07:46', '2025-08-27 08:07:46', NULL, 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `title`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'user_management_access', NULL, NULL, NULL),
(2, 'permission_create', NULL, NULL, NULL),
(3, 'permission_edit', NULL, NULL, NULL),
(4, 'permission_show', NULL, NULL, NULL),
(5, 'permission_delete', NULL, NULL, NULL),
(6, 'permission_access', NULL, NULL, NULL),
(7, 'role_create', NULL, NULL, NULL),
(8, 'role_edit', NULL, NULL, NULL),
(9, 'role_show', NULL, NULL, NULL),
(10, 'role_delete', NULL, NULL, NULL),
(11, 'role_access', NULL, NULL, NULL),
(12, 'user_create', NULL, NULL, NULL),
(13, 'user_edit', NULL, NULL, NULL),
(14, 'user_show', NULL, NULL, NULL),
(15, 'user_delete', NULL, NULL, NULL),
(16, 'user_access', NULL, NULL, NULL),
(17, 'pendaftar_create', NULL, NULL, NULL),
(18, 'pendaftar_edit', NULL, NULL, NULL),
(19, 'pendaftar_show', NULL, NULL, NULL),
(20, 'pendaftar_delete', NULL, NULL, NULL),
(21, 'pendaftar_access', NULL, NULL, NULL),
(22, 'audit_log_show', NULL, NULL, NULL),
(23, 'audit_log_access', NULL, NULL, NULL),
(24, 'event_create', NULL, NULL, NULL),
(25, 'event_edit', NULL, NULL, NULL),
(26, 'event_show', NULL, NULL, NULL),
(27, 'event_delete', NULL, NULL, NULL),
(28, 'event_access', NULL, NULL, NULL),
(29, 'profile_password_edit', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `permission_role`
--

CREATE TABLE `permission_role` (
  `role_id` bigint UNSIGNED NOT NULL,
  `permission_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permission_role`
--

INSERT INTO `permission_role` (`role_id`, `permission_id`) VALUES
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
(2, 29);

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 4, 'u67ty3tw34w535433q432c54353453', '71f9c6b5727459c2d90c51eb2bcaa1810737380d9fa24a6fe9a25bffa41978ba', '[\"*\"]', NULL, '2025-08-25 16:16:59', '2025-08-25 16:16:59'),
(2, 'App\\Models\\User', 5, 'u67ty3tw34w535433q432c54353453', 'c36ba8d445f7382921b0ac63bdb5a03cd378b7c90a6c101aa9364e8980a4368c', '[\"*\"]', NULL, '2025-08-25 16:17:22', '2025-08-25 16:17:22'),
(7, 'App\\Models\\User', 9, 'api-token', '33db15d820d20b48f44021545493daa289c93d78dcf575b2f3bf91d9db248c80', '[\"*\"]', NULL, '2025-08-25 17:55:14', '2025-08-25 17:55:14'),
(9, 'App\\Models\\User', 10, 'api-token', 'afb745ebcb0dd7b0dbde636d4a6a2efddbb87d5f62714677119041b00436c8d4', '[\"*\"]', NULL, '2025-08-25 18:31:31', '2025-08-25 18:31:31'),
(10, 'App\\Models\\User', 11, 'api-token', 'a068be27acd93405fc95354c8e0a0601d5fa6e344d1c20dd2980d891dc91f260', '[\"*\"]', NULL, '2025-08-25 18:31:48', '2025-08-25 18:31:48'),
(11, 'App\\Models\\User', 12, 'api-token', '6d8e77a17d5f686c0b3f7a5df9ed88a878ebef2ae8885597233ae602c09a65a7', '[\"*\"]', NULL, '2025-08-25 18:31:51', '2025-08-25 18:31:51'),
(14, 'App\\Models\\User', 1, 'api-token', '7910cf490e064c395a7d1ec27aaf0bde23ee1c190d5f0dce4d6a9d2400b01d9c', '[\"*\"]', '2025-08-26 13:30:47', '2025-08-26 13:20:46', '2025-08-26 13:30:47'),
(15, 'App\\Models\\User', 13, 'api-token', '6eeb5f3ca73b94d85da6f0339d66325d25ecc17eefec15ac917cc8bafd26f5fa', '[\"*\"]', NULL, '2025-08-26 15:48:05', '2025-08-26 15:48:05'),
(16, 'App\\Models\\User', 14, 'api-token', 'ee8b3098e4e9d406bf73d99d54f53011ff9d3439de0f1c69fff005251cf2a4d0', '[\"*\"]', NULL, '2025-08-26 15:55:41', '2025-08-26 15:55:41'),
(17, 'App\\Models\\User', 15, 'api-token', 'cd86bb57d5f4fac6815ef078d1e08b7a713c6991e9f77d149edf722d83548a8c', '[\"*\"]', NULL, '2025-08-26 15:57:16', '2025-08-26 15:57:16'),
(18, 'App\\Models\\User', 16, 'api-token', '94161dc24082fbafce38cdc0b2cc1a7175d636257c18d6cc61deeaa4214c1bb6', '[\"*\"]', NULL, '2025-08-26 16:05:11', '2025-08-26 16:05:11'),
(19, 'App\\Models\\User', 17, 'api-token', '2fc7166a0e3690d07e0dceb1b5fde614adb51b1ab2d538420881fc5e258626f8', '[\"*\"]', NULL, '2025-08-26 16:05:45', '2025-08-26 16:05:45'),
(20, 'App\\Models\\User', 18, 'api-token', '03eed2f99141b89b73f2a896cc43235d15ca27aa40c152020d54e92820f1f98e', '[\"*\"]', NULL, '2025-08-26 16:07:41', '2025-08-26 16:07:41'),
(21, 'App\\Models\\User', 19, 'api-token', 'a8a1cfaf3e0caa1772f90b49eb4946f44b00bf168c3aaa44ac173e3d009b5916', '[\"*\"]', NULL, '2025-08-26 16:08:10', '2025-08-26 16:08:10'),
(22, 'App\\Models\\User', 20, 'api-token', '14531c108f9535d44e40d96d80266852d9c9dc1b20f865ccf5fdf4b8a08881a5', '[\"*\"]', NULL, '2025-08-26 16:13:19', '2025-08-26 16:13:19'),
(28, 'App\\Models\\User', 30, 'api-token', '8fc4ab9568cf706fb3cd9f1e7439c3235c6ef351df9d015c486d74e6c7429f60', '[\"*\"]', NULL, '2025-08-27 17:55:45', '2025-08-27 17:55:45'),
(39, 'App\\Models\\User', 21, 'api-token', '0d87d2e451c7f171b8fbe8a6a93d4f5543f46202e4cafdd63f8c2cb360deaf75', '[\"*\"]', '2025-08-28 17:11:58', '2025-08-28 17:11:58', '2025-08-28 17:11:58'),
(40, 'App\\Models\\User', 29, 'api-token', 'a58885129c2ada4f0d9e5f9ef6ddce31a6ac8e61cf0cc62985e10dd4d58ba989', '[\"*\"]', NULL, '2025-08-28 17:52:44', '2025-08-28 17:52:44');

-- --------------------------------------------------------

--
-- Table structure for table `refresh_tokens`
--

CREATE TABLE `refresh_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL DEFAULT '0',
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `refresh_tokens`
--

INSERT INTO `refresh_tokens` (`id`, `user_id`, `token`, `device_name`, `revoked`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 9, '4860e2ebcb5786377b0059bb8560797dfe955861a59360bad9c3bab76f2c6c0e', 'api-token', 0, '2025-09-24 17:55:14', '2025-08-25 17:55:14', '2025-08-25 17:55:14'),
(2, 1, '6782b30e5a0bb4aa8389f0d9a34cb04a5971c9ea54299416abd512c0a5d83b88', 'api-token', 0, '2025-09-24 17:58:31', '2025-08-25 17:58:31', '2025-08-25 17:58:31'),
(3, 1, '23387e9902c3bdb3235f0002de3dd4b882bb0debd27081943d5aed74f07437c1', 'api-token', 0, '2025-09-24 19:17:03', '2025-08-25 19:17:03', '2025-08-25 19:17:03'),
(4, 1, '5013ec67b405dd449a20251370792e8255d6d0992873f4902c847f8e70c92824', 'api-token', 0, '2025-09-24 19:41:46', '2025-08-25 19:41:46', '2025-08-25 19:41:46'),
(5, 1, '57481396c022201ff0a1bb0f5933b2a3a4c1fa9a2e9aaed0c8232321a0c981c4', 'api-token', 0, '2025-09-25 13:20:46', '2025-08-26 13:20:46', '2025-08-26 13:20:46'),
(6, 21, 'b0670617ca83d9d4d67b82be0541778923f9a328e96767d93d42763289b15d8e', 'api-token', 0, '2025-09-25 16:53:07', '2025-08-26 16:53:07', '2025-08-26 16:53:07'),
(7, 21, '49af7459052ed07b7908a7a0103de0ed4daf4a81b0d0daa5450e3a189d7313d5', 'api-token', 0, '2025-09-26 13:34:31', '2025-08-27 13:34:31', '2025-08-27 13:34:31'),
(8, 21, '77fc73dd32848273e19628a52b9775b6a288b08a3d22fc097a11de93d9cdb9cd', 'api-token', 0, '2025-09-26 15:43:07', '2025-08-27 15:43:07', '2025-08-27 15:43:07'),
(9, 21, '70350bbc3b42c6dc9265db0d158b22b1480e07451d85b9105111e3b862510d1f', 'api-token', 0, '2025-09-26 17:01:23', '2025-08-27 17:01:23', '2025-08-27 17:01:23'),
(10, 21, '5b7ee9ffcc426b47167a7ecefeba37809749d2e879f0028fdce51b1ae75abb83', 'api-token', 0, '2025-09-26 18:07:52', '2025-08-27 18:07:52', '2025-08-27 18:07:52'),
(11, 21, '5c427a0f21580aa34b40d770676b289a2ec9ae4b509da6c095073a9b652515d5', 'api-token', 0, '2025-09-26 18:08:26', '2025-08-27 18:08:26', '2025-08-27 18:08:26'),
(12, 29, 'a465870969fba8ed125ca2062eb4050fc0773ada8b8db54826ae27a3b05375cf', 'api-token', 0, '2025-09-26 18:12:19', '2025-08-27 18:12:19', '2025-08-27 18:12:19'),
(13, 29, '53471458e117dfc1e9736f27642f0d89faaa20afe4abb7722d5f7c41dffdcb42', 'api-token', 0, '2025-09-26 18:16:27', '2025-08-27 18:16:27', '2025-08-27 18:16:27'),
(14, 29, 'f4d0dba2bd1e96d028c1fdae5e2c1fde64dd330260151909463d1a9d97362ad3', 'api-token', 0, '2025-09-26 18:25:10', '2025-08-27 18:25:10', '2025-08-27 18:25:10'),
(15, 29, 'ed9946dca895ea318658a0419396038bc0142ecda2b443b44c85b384262236a1', 'api-token', 0, '2025-09-26 18:26:07', '2025-08-27 18:26:07', '2025-08-27 18:26:07'),
(16, 29, 'e6ecbc71314ce335af4e72cc06c5a98735912621751e52e9a7df04fd1ce9da17', 'api-token', 0, '2025-09-26 18:26:15', '2025-08-27 18:26:15', '2025-08-27 18:26:15'),
(17, 29, 'd623128faa86e3193cfea3cc0660571424fd09cb8c94d44704ded030bc345451', 'api-token', 0, '2025-09-26 18:26:18', '2025-08-27 18:26:18', '2025-08-27 18:26:18'),
(18, 29, '1be08c0d277cc282493dd2c48818139252f5e18c3f7be9a73d310ae5211f5c11', 'api-token', 0, '2025-09-26 18:27:20', '2025-08-27 18:27:20', '2025-08-27 18:27:20'),
(19, 21, '4b4b60e631987039acbbca3acd396eb8a18a994001409f7e4da9023a94f69b97', 'api-token', 0, '2025-09-27 13:11:27', '2025-08-28 13:11:27', '2025-08-28 13:11:27'),
(20, 21, '5bfe99ca651da2ef3df73b07c38578611e12122cf1132d8875d1141dd7aef6d8', 'api-token', 0, '2025-09-27 17:11:58', '2025-08-28 17:11:58', '2025-08-28 17:11:58'),
(21, 29, 'a747f9ea6435c4c2c147b0b782d57bf47297b5fcb102fc325016f1898164388b', 'api-token', 0, '2025-09-27 17:52:44', '2025-08-28 17:52:44', '2025-08-28 17:52:44');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `title`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Admin', NULL, NULL, NULL),
(2, 'User', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `role_user`
--

CREATE TABLE `role_user` (
  `user_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_user`
--

INSERT INTO `role_user` (`user_id`, `role_id`) VALUES
(1, 1),
(2, 2),
(3, 2),
(4, 2),
(5, 2),
(6, 2),
(7, 2),
(8, 2),
(9, 2),
(10, 2),
(11, 2),
(12, 2),
(13, 2),
(14, 2),
(15, 2),
(16, 2),
(17, 2),
(18, 2),
(19, 2),
(20, 2),
(21, 2),
(22, 2),
(23, 2),
(26, 2),
(27, 2),
(29, 2),
(30, 2),
(31, 2),
(33, 2),
(34, 2),
(35, 2),
(36, 2),
(37, 2),
(38, 2),
(39, 2),
(40, 2),
(41, 2),
(42, 2),
(43, 2),
(44, 2),
(45, 2),
(46, 2);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sponsors`
--

CREATE TABLE `sponsors` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` bigint UNSIGNED NOT NULL,
  `no_tiket` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checkin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status_payment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_bayar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `peserta_id` bigint UNSIGNED DEFAULT NULL,
  `event_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `no_tiket`, `checkin`, `notes`, `status_payment`, `payment_type`, `total_bayar`, `created_at`, `updated_at`, `deleted_at`, `peserta_id`, `event_id`) VALUES
(1, '015', NULL, NULL, NULL, NULL, NULL, '2025-08-25 18:04:40', '2025-08-25 18:04:40', NULL, 1, 1),
(2, '015', NULL, NULL, NULL, NULL, NULL, '2025-08-25 18:05:13', '2025-08-25 18:05:13', NULL, 1, 1),
(3, '015', NULL, NULL, NULL, NULL, NULL, '2025-08-25 18:05:54', '2025-08-25 18:05:54', NULL, 1, 1),
(4, '015', NULL, NULL, NULL, NULL, NULL, '2025-08-25 18:32:55', '2025-08-25 18:32:55', NULL, 1, 1),
(5, '015', NULL, NULL, NULL, NULL, NULL, '2025-08-25 20:26:24', '2025-08-25 20:26:24', NULL, 6, 1),
(6, '017', NULL, NULL, NULL, NULL, NULL, '2025-08-27 08:06:20', '2025-08-27 08:06:20', NULL, 6, 1),
(7, '017', NULL, NULL, NULL, NULL, NULL, '2025-08-27 08:07:46', '2025-08-27 08:07:46', NULL, 6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tikets`
--

CREATE TABLE `tikets` (
  `id` bigint UNSIGNED NOT NULL,
  `no_tiket` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checkin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status_payment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_bayar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `peserta_id` bigint UNSIGNED DEFAULT NULL,
  `event_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tikets`
--

INSERT INTO `tikets` (`id`, `no_tiket`, `checkin`, `notes`, `status_payment`, `payment_type`, `total_bayar`, `created_at`, `updated_at`, `deleted_at`, `peserta_id`, `event_id`) VALUES
(1, '015', NULL, NULL, NULL, NULL, NULL, '2025-08-25 18:04:40', '2025-08-25 18:04:40', NULL, 1, 1),
(2, '015', NULL, NULL, NULL, NULL, NULL, '2025-08-25 18:05:13', '2025-08-25 18:05:13', NULL, 1, 1),
(3, '015', NULL, NULL, NULL, NULL, NULL, '2025-08-25 18:05:54', '2025-08-25 18:05:54', NULL, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tiket_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `snap_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `peserta_id` bigint UNSIGNED DEFAULT NULL,
  `created_by_id` bigint UNSIGNED DEFAULT NULL,
  `events` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `participants` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uid` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paired_at` timestamp NULL DEFAULT NULL,
  `nomor_punggung` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `invoice`, `event_id`, `tiket_id`, `amount`, `note`, `snap_token`, `status`, `created_at`, `updated_at`, `deleted_at`, `peserta_id`, `created_by_id`, `events`, `participants`, `payment_url`, `qr`, `email`, `uid`, `province`, `city`, `address`, `no_hp`, `nik`, `nama`, `paired_at`, `nomor_punggung`) VALUES
(1, 'TRX-U0EOUD9DW2', NULL, NULL, '75000', 'Kezia1', NULL, 'pending', '2025-09-01 19:33:56', '2025-09-01 19:33:56', NULL, 43, 43, '[\"1\"]', '[{\"ticketId\":\"1\",\"nik\":\"990809890809809809\",\"name\":\"cjidjiow\",\"email\":\"dwwd@c.comc\",\"phone\":\"080890809809\",\"province\":\"Aceh\",\"city\":\"Kabupaten Aceh Barat\"}]', 'https://app.sandbox.midtrans.com/snap/v4/redirection/ffe5f7a0-d243-405d-8c6a-aacee768ac2b', NULL, 'kezia1@gmail.com', NULL, 'Aceh', 'Kabupaten Aceh Barat', NULL, '080890809809', '990809890809809809', 'Kezia1', NULL, NULL),
(2, 'TRX-KR1TWGLBJH', NULL, NULL, '75000', 'Kezia1', NULL, 'pending', '2025-09-01 19:44:17', '2025-09-01 19:44:18', NULL, 43, 43, '[\"1\"]', '[{\"ticketId\":\"1\",\"nik\":\"908908908954664565\",\"name\":\"dwdwdw\",\"email\":\"wddw@ffmf.com\",\"phone\":\"876796979979797\",\"province\":\"Bali\",\"city\":\"Kabupaten Badung\"}]', 'https://app.sandbox.midtrans.com/snap/v4/redirection/581da890-2547-4089-9f00-bbdcd95148c7', NULL, 'kezia1@gmail.com', NULL, 'Bali', 'Kabupaten Badung', NULL, '876796979979797', '908908908954664565', 'Kezia1', NULL, NULL),
(3, 'TRX-K49BWQ0MDB', NULL, NULL, '75000', 'Kezia1', NULL, 'success', '2025-09-01 20:01:33', '2025-09-01 20:02:10', NULL, 43, 43, '[\"1\"]', '[{\"ticketId\":\"1\",\"nik\":\"009808098098090890\",\"name\":\"dwdw\",\"email\":\"wdwd@fm.com\",\"phone\":\"090909022\",\"province\":\"Bali\",\"city\":\"Kabupaten Bangli\"}]', 'https://app.sandbox.midtrans.com/snap/v4/redirection/25918c13-f8b5-4b16-80c4-414581a21c86', NULL, 'kezia1@gmail.com', NULL, 'Bali', 'Kabupaten Bangli', NULL, '090909022', '009808098098090890', 'Kezia1', NULL, NULL),
(4, 'TRX-1541706496', NULL, NULL, '75000', 'Kezia1', NULL, 'pending', '2025-09-02 06:44:31', '2025-09-02 06:44:31', NULL, 43, 43, '[\"1\"]', '[{\"ticketId\":\"1\",\"nik\":\"845546466434364655\",\"name\":\"kezia\",\"email\":\"kezia@gmail.com\",\"phone\":\"08123747474\",\"province\":\"Aceh\",\"city\":\"Kabupaten Aceh Barat Daya\"}]', 'https://app.sandbox.midtrans.com/snap/v4/redirection/7491ab6f-c2e6-48a7-baa3-b55c0acfc389', NULL, 'kezia1@gmail.com', NULL, 'Aceh', 'Kabupaten Aceh Barat Daya', NULL, '08123747474', '845546466434364655', 'Kezia1', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `peserta_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` bigint NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `snap_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','success','Expired','failed','Refund') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaksis`
--

CREATE TABLE `transaksis` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tiket_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `snap_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `peserta_id` bigint UNSIGNED DEFAULT NULL,
  `created_by_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaksis`
--

INSERT INTO `transaksis` (`id`, `invoice`, `event_id`, `tiket_id`, `amount`, `note`, `snap_token`, `status`, `created_at`, `updated_at`, `deleted_at`, `peserta_id`, `created_by_id`) VALUES
(3, 'TRX-1N5Q0QS038', NULL, NULL, '200000', 'failamir abdullah', '4b0a1920-6fe7-4a4e-91a4-805d86f008b3', 'pending', '2025-08-24 09:08:35', '2025-08-24 09:08:36', NULL, NULL, NULL),
(4, 'TRX-78Y3Q8Z2ID', NULL, NULL, '200000', 'failamir abdullah', 'd1143135-c756-43bd-8fed-bbb320e362b8', 'pending', '2025-08-24 10:38:22', '2025-08-24 10:38:24', NULL, NULL, NULL),
(5, 'TRX-H540UR6444', NULL, NULL, '200000', 'failamir abdullah', 'ea937d4b-1dcf-4fcf-863c-4dd9c8b1f18f', 'pending', '2025-08-24 10:54:58', '2025-08-24 10:54:59', NULL, NULL, NULL),
(6, 'TRX-25DBPA44L7', NULL, NULL, '200000', 'failamir abdullah', '4950ed2d-a724-4709-9d73-57484f9f94fc', 'pending', '2025-08-25 14:05:28', '2025-08-25 14:05:29', NULL, NULL, 2);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `uid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `region` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `village` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checkin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `nomor_punggung` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_payment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_bayar` decimal(15,2) DEFAULT NULL,
  `event_id` bigint UNSIGNED DEFAULT NULL,
  `start_at` datetime DEFAULT NULL,
  `finish_at` datetime DEFAULT NULL,
  `role` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_tiket` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_name` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`, `uid`, `nik`, `city`, `region`, `village`, `checkin`, `notes`, `nomor_punggung`, `status_payment`, `payment_type`, `total_bayar`, `event_id`, `start_at`, `finish_at`, `role`, `no_hp`, `approved`, `no_tiket`, `device_name`) VALUES
(1, 'Admin', 'admin@admin.com', NULL, '$2y$10$sqFsaRZbpwhmgza39HMj3urb0lf6sDAkMXoPC1mQj4e23rDOTki2u', NULL, NULL, '2025-08-30 14:46:33', NULL, '1121212121', '1121212121', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 200000.00, 1, NULL, NULL, NULL, NULL, NULL, '015', NULL),
(2, 'Runner-00022', 'ifailamir@gmail.com', NULL, '$2y$10$YHjjF6UwMgtXqkwJieGQ5OXwgdEfPmBuLHTRQ3JoCc0ZzmGevZBn.', 'pEYRM1rFsSFSxni4qUKvwl4gfzoRsFT8dL2x1D5YyXJw5lsQKQ4ZzyPxistD', '2025-08-24 13:02:00', '2025-08-30 14:46:33', NULL, '2313122144', '1111', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, 200000.00, NULL, NULL, NULL, NULL, '0812312312312', NULL, '00022', NULL),
(3, 'fatimahgelora', 'fatimahgelora@gmail.com', NULL, '$2y$10$M7icIK9WriK8q2gay01pmeDJsHlF3YfT1cfVQxLPSHl12qwOyMVAC', '4mOOaJtITBvyPB24TQc5EVRsyiOGgj4rIekOHqPiYjYkMprjfv2UAKoazi13', '2025-08-24 13:31:49', '2025-08-24 13:31:49', NULL, '6575675675', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'lombok', 'lombok@cantik.com', NULL, '$2y$10$c78BAYDrRvTs5mu00G8YluhgdFnp/qjyVPteqmQE0MJKfFMx4B4cG', NULL, '2025-08-25 16:16:57', '2025-08-25 16:16:57', NULL, '9789879789', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'lombok', 'lombok1@cantik.com', NULL, '$2y$10$DMZMoAeXFA5IeX8HB33v8O721Sv5jbE63Udc2zjdyghkx7SEqTI7S', NULL, '2025-08-25 16:17:21', '2025-08-25 16:17:21', NULL, '6454433444', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'lombok', 'lombok2@cantik.com', NULL, '$2y$10$bhnQNy05FNkT8ll6pMj9T.16Jvy.kJb5IFSNtn22OOs/v5uA4isOa', NULL, '2025-08-25 16:35:30', '2025-08-30 14:46:33', NULL, 'u67ty3tw34w535433q432c54353453', '123871293871238912312', 'mataram', 'NTB', 'jln sade', NULL, NULL, NULL, 'pending', NULL, 200000.00, 1, NULL, NULL, NULL, '08213123123213', NULL, '017', NULL),
(7, 'lombok', 'lombok3@cantik.com', NULL, '$2y$10$ryMCOgYhVYPxzwnhyKdwke8uEJcc3ff52RCgWpZ2BDdQOBb7tqEWm', NULL, '2025-08-25 16:35:50', '2025-08-25 16:35:50', NULL, 'u67ty3tw34w535433q432c54353453', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'lombok', 'lombokk@cantik.com', NULL, '$2y$10$EmERax1DHs/JF2NT7E/tre6Nb6uXXwZ4cgcT2cMbJ47BXY7aNCxeC', NULL, '2025-08-25 17:27:11', '2025-08-25 17:27:11', NULL, 'u67ty3tw34w535433q432c54353453', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'lombok', 'lombok4@cantik.com', NULL, '$2y$10$JXuK7pYpkdx8n7yqi5faee/.s6qGPS3G5LHM7NlgTXVFgnlMKsCCS', NULL, '2025-08-25 17:29:17', '2025-08-25 17:29:17', NULL, 'u67ty3tw34w535433q432c54353453', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'lombok', 'lombok5@cantik.com', NULL, '$2y$10$QFene5QWuRJPlOJvHsbeOuiXpbj6V3Q3K78DPDEHH/TlxL7eLA3Ym', NULL, '2025-08-25 18:31:31', '2025-08-25 18:31:31', NULL, 'u67ty3tw34w535433q432c54353453', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'lombok', 'lombok6@cantik.com', NULL, '$2y$10$ySA460x15Ul2l13yXMbl6u5lQEx7JjSLg1uJUOjQ50SQXhQvTm58G', NULL, '2025-08-25 18:31:47', '2025-08-25 18:31:47', NULL, 'u67ty3tw34w535433q432c54353453', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'lombok', 'lombok7@cantik.com', NULL, '$2y$10$t.IffRhK8Wt7GRXBcGXABOpH9hcAU.d7Wj9EWP820PXtMyuIsjcwS', NULL, '2025-08-25 18:31:51', '2025-08-25 18:31:51', NULL, 'u67ty3tw34w535433q432c54353453', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 'Failku', 'fail@gmail.com', NULL, '$2y$10$vNBgGUhlyD6lqH9zt8/ybeviCEviQnPjxeWzPjHGZKBjlSCTDwBzq', NULL, '2025-08-26 15:48:05', '2025-08-26 15:48:05', NULL, 'kvf3VyhFpxSLrYKlkiYFJLA1P3s2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 'Kalisha', 'kalisa@gmail.com', NULL, '$2y$10$NyC/11lJhA0q2TqkgnU7qO4qLVJVeWcKP/xE3t4Y9F92S.fMhV.7q', NULL, '2025-08-26 15:55:41', '2025-08-26 15:55:41', NULL, 'fxSIEH6MQdhScIcw1KMJavUQfSv1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 'lombok', 'lombok8@cantik.com', NULL, '$2y$10$cpeWpcpjeK325WmcDMLhuuQEZvcCpWu1s1FaJy0Stku0dUfhkoDcu', NULL, '2025-08-26 15:57:16', '2025-08-26 15:57:16', NULL, 'u67ty3tw34w535433q432c54353453', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'Yahu', 'yahu@yahu.com', NULL, '$2y$10$zISYFzlf6p72hAW9ttfH9OwkI5dL3VFm2irueSdUX7D5LwkgqnRoS', NULL, '2025-08-26 16:05:11', '2025-08-26 16:05:11', NULL, 'krwMVtw6yeaOpNXuouBrjan7RA82', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 'lombok', 'lombok4@cantiks.com', NULL, '$2y$10$orNhrp4tGpsVOnaPyEbozOhpn8Lugae2lyEBckrpU9GISnCHajmOm', NULL, '2025-08-26 16:05:45', '2025-08-26 16:05:45', NULL, 'u67ty3tw34w535433q432c54353453', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 'Yahu', 'yahu@yahus.com', NULL, '$2y$10$brDuMwt2/cXHrT2cE/6gkOZcgKY/4nn.9NiG66TJIgguyy9I/N.b2', NULL, '2025-08-26 16:07:41', '2025-08-26 16:07:41', NULL, 'BaiVHIn9ClPOFNRVPWPkWtFTzRC2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 'Yahu', 'yahu@yashus.com', NULL, '$2y$10$Iz205.CYRSi25YJP4q5Z1e1qddM6HEB/2I4AHK9wisyA5Ez.vgUeW', NULL, '2025-08-26 16:08:10', '2025-08-26 16:08:10', NULL, 'eKEz8xfy43fg50xocm4Kpl6CANs2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 'pail', 'pail@pail.com', NULL, '$2y$10$oOk25xK2xorR3dOthf.zFukVGoUvIkaww59nsK.7aSeB0uKLyRefa', NULL, '2025-08-26 16:13:19', '2025-08-26 16:13:19', NULL, 'ZYpsIKNJCKftpmShcQayfz7yFLl2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 'demo1234', 'demo@keenthemes.com', NULL, '$2y$10$PblAQTy3C2ybn3Fu0VwIle2EIV3wga3wzMGBYf0Tkmr7ksNgIgyNS', NULL, '2025-08-26 16:53:02', '2025-08-26 16:53:02', NULL, 'u67ty3tw34w535433q432c54353453', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 'fafafa', 'fafaf@fafsa.faf', NULL, NULL, NULL, '2025-08-27 09:11:50', '2025-08-27 09:11:50', NULL, 'guest_17562859103430', '21312312312312', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '08312312312312', NULL, NULL, NULL),
(23, 'fafa', 'fafa@fafa.faf', NULL, NULL, NULL, '2025-08-27 09:13:40', '2025-08-27 09:13:40', NULL, 'guest_17562860205990', '132123123123', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '08456546456456', NULL, NULL, NULL),
(26, 'afaga', 'gaga@fafa.fgag', NULL, NULL, NULL, '2025-08-27 09:18:25', '2025-08-27 09:18:25', NULL, 'guest_17562863051690', '9213123123123123', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '08213123123123', NULL, NULL, NULL),
(27, 'Fail Amirku', 'fail@fail.com', NULL, NULL, NULL, '2025-08-27 17:16:02', '2025-08-27 17:16:02', NULL, 'guest_17563149625100', '4984908098098098', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0890809809809', NULL, NULL, NULL),
(29, 'Test1', 'test1@gmail.com', NULL, '$2y$10$sqFsaRZbpwhmgza39HMj3urb0lf6sDAkMXoPC1mQj4e23rDOTki2u', NULL, '2025-08-27 17:29:16', '2025-08-27 17:29:16', NULL, '66554544d4xTLSMJW4a6wHLhV4YeSz4An46Nui1', '9890890090890890', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3553535353', NULL, NULL, NULL),
(30, 'Candra', 'candra@gmail.com', NULL, '$2y$10$IYO/aoBMTIUf4WzkyqvBseYGCEWfrSf21tBW9xXGM8mKvUVHbAJjm', NULL, '2025-08-27 17:55:45', '2025-08-27 17:55:45', NULL, '4dxTLSMJW4a6wHLhVYeSzAn6Nui1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(31, 'huhhg', 'hyghv', NULL, '$2y$10$1G9j5opP8qdWGFGPfAgDi.gbeGOic1ZgElMYVjE33vhBhXLbG2svK', NULL, '2025-08-27 18:57:31', '2025-08-27 18:57:31', NULL, 'guest_17563210511830', '5282825', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '6686', NULL, NULL, NULL),
(33, 'kalisya', 'kalisya@cantik.comm', NULL, '$2y$10$cia.xX4hKwRJaj0F9TSXmeLJE6Qp3AJsUzVlelkqQR.2mXcO9XdpW', NULL, '2025-08-28 18:01:16', '2025-08-28 18:01:16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(34, 'kalisya', 'kalisyaa@cantik.com', NULL, '$2y$10$on2q9gmjcJpvCyTQTQFIjO99QaWeA4g/pfVIafBSiO8DD8rdTfgxy', NULL, '2025-08-28 18:01:23', '2025-08-28 18:01:23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(35, 'kalisya', 'kalisyaaa@cantik.com', NULL, '$2y$10$KAgzXMIX8LxnUxPvS7MBtusNwtqpVnr00UKBTTV/c/sdo2J1F/Tt6', NULL, '2025-08-28 18:01:56', '2025-08-28 18:01:56', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(36, 'kalisya', 'kalisyaaaa@cantik.com', NULL, '$2y$10$1mZHNjcYyRQu1q1Infvoj.Szoa6Hkzm8cU4Sf1Nnyd8ReY31qzaWu', NULL, '2025-08-28 18:10:21', '2025-08-28 18:10:21', NULL, 'q1YeBMAewaaxHaEhg4kf0eh4FWs2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(37, 'kalisya', 'kalisyaaaaa@cantik.com', NULL, '$2y$10$0taFRVImLx1YTelWskogPe3iasfYmf/npbqixr4VU0Z82FS303S/m', NULL, '2025-08-28 18:11:48', '2025-08-28 18:11:48', NULL, '5IiKg4GHJpSPZZv7Z35aUc6DVQC3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(38, 'Failku', 'fail@amir.com', NULL, '$2y$10$6pg2CaRkd/sxnZ9t2Oi5COvfkUjDS7JTTZv9SJHIofThXKRRJc5IG', NULL, '2025-08-28 18:14:36', '2025-08-28 18:14:36', NULL, 'qyuEm3TCj1b3pPIzwLgspNYsklL2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(39, 'kalisya', 'kalisya@cantik.com', NULL, '$2y$10$tqxkbgkIOiegn2iNUe12AOrj8NmdLDkviZVCgoUoWY.jN0.1bveNy', NULL, '2025-08-28 18:28:45', '2025-08-28 18:28:45', NULL, 'SCjXHj7x3gTkWhe8LfAMITt1Q4Y2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(40, 'kalisya', 'kalisya1@cantik.com', NULL, '$2y$10$NnwmhX1Dwnc0D8VXGIa1ouhTYFujcKCHb5DrO03.aSRlDNLiZin8y', NULL, '2025-08-28 18:41:06', '2025-08-28 18:41:06', NULL, 'OYaXS4JUBLedHqjmDe8VY4gQVIJ2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(41, 'kalisya', 'kalisya@noktula.diandra', NULL, '$2y$10$uiYw2iTwOVFd1wsuxXyEueLghBI36LYciVrX/MsFCHmmd6FfzMQl6', NULL, '2025-08-28 19:40:10', '2025-08-28 19:40:10', NULL, 'PaXrvgLrWfg3FSBe5AJaBz0S5sA3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(42, 'Kezia', 'kezia@gmail.com', NULL, '$2y$10$5MS0Lxl0olG/FVz2hNvHe.tNSM7EBAkuBwSuPcuHGTl48SKjK6w6O', NULL, '2025-08-29 04:11:58', '2025-08-29 04:11:58', NULL, 'OyQFQ0Ybx8dTNyGxq8pUPrg5eE43', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(43, 'Kezia1', 'kezia1@gmail.com', NULL, '$2y$10$MgoscCOnmZe5.ml6Ozf.fO2LIwuI56hfD/8Jo9B3gDnOP92Bq6i3q', NULL, '2025-08-29 04:43:26', '2025-08-29 04:43:26', NULL, '6KhEZhthcjO1IGcrUzntARuwLkh1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(44, '9iouio', 'fai@l.com', NULL, '$2y$10$6VUm3dpg5uX8R7cRZ/9BfuLrOBMHXRA5zRnxSUj26plFP1bf.Mgya', NULL, '2025-08-29 20:48:27', '2025-08-29 20:48:27', NULL, 'PO4xXUvuNZeRmetEKjY56ZuIShB2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(45, 'Candra', 'candra@candra.com', NULL, '$2y$10$fxh1YpeZ1gC1XweRC.EBIOQSsZ7V4jM8FNrChJXHxXPiaUTcaMRQW', NULL, '2025-08-30 06:18:59', '2025-08-30 06:18:59', NULL, 'qF1b2tKydlSMP6OBpa6XA43G3kE2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(46, 'lalu rio', 'lvlysunday@gmail.com', NULL, '$2y$10$Dbxwn7vrHsaCLoJistldn.H2jq1vHNN00Z2C2neYpOz7ybzo2sjPS', NULL, '2025-08-30 08:03:30', '2025-08-30 08:03:30', NULL, 'sja8SFp8IKerbFmzV8dJbn92Ecs1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(47, 'Runner-010', 'Runner-010@gmail.com', NULL, '$2y$10$GfWWiZxVgAvqd3rQUNc7h.yiJI48cBYn4nffy9xspUPJyBYzUVjiu', NULL, '2025-08-30 14:46:32', '2025-08-30 14:46:33', NULL, NULL, '1111', NULL, NULL, NULL, NULL, NULL, '00001', NULL, NULL, 200000.00, NULL, NULL, NULL, NULL, NULL, NULL, '010', NULL),
(48, 'Test Runner', 'testrunner@example.com', NULL, '$2y$10$b5UZ2qXsOLmhUfH6qG65Ke6S0K9wVUc3EhBb3BAqYlGzm/ZDxPVJy', NULL, '2025-08-30 14:46:33', '2025-08-30 14:46:33', NULL, NULL, '1234567890123456', 'Lombok Tengah', 'NTB', 'Jl. Contoh 123', NULL, NULL, NULL, 'pending', NULL, 200000.00, 1, NULL, NULL, NULL, '08123456789', NULL, '15', NULL),
(49, 'LT Runner', 'ltrunner@example.com', NULL, '$2y$10$TyEEZnbgVgnqDveoJawhjuMYNUY6uEsNH23ZYiUbldXgD.GVP2qN6', NULL, '2025-08-30 14:46:33', '2025-08-30 14:46:33', NULL, NULL, '9876543210987654', 'Mataram', 'NTB', 'Jl. Tunnel 1', NULL, NULL, NULL, 'pending', NULL, 200000.00, 1, NULL, NULL, NULL, '081200000000', NULL, '16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_alerts`
--

CREATE TABLE `user_alerts` (
  `id` bigint UNSIGNED NOT NULL,
  `alert_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alert_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_user_alert`
--

CREATE TABLE `user_user_alert` (
  `user_alert_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `read` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faq_categories`
--
ALTER TABLE `faq_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faq_questions`
--
ALTER TABLE `faq_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_fk_7114374` (`category_id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `media_uuid_unique` (`uuid`),
  ADD KEY `media_model_type_model_id_index` (`model_type`,`model_id`),
  ADD KEY `media_order_column_index` (`order_column`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `pendaftars`
--
ALTER TABLE `pendaftars`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_fk_6953391` (`event_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD KEY `role_id_fk_6952520` (`role_id`),
  ADD KEY `permission_id_fk_6952520` (`permission_id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `refresh_tokens_token_unique` (`token`),
  ADD KEY `refresh_tokens_user_id_foreign` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_user`
--
ALTER TABLE `role_user`
  ADD KEY `user_id_fk_6952529` (`user_id`),
  ADD KEY `role_id_fk_6952529` (`role_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sponsors`
--
ALTER TABLE `sponsors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `peserta_fk_7114415` (`peserta_id`);

--
-- Indexes for table `tikets`
--
ALTER TABLE `tikets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `peserta_fk_7114415` (`peserta_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `peserta_fk_7114422` (`peserta_id`),
  ADD KEY `created_by_fk_7114430` (`created_by_id`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transaksis`
--
ALTER TABLE `transaksis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `peserta_fk_7114422` (`peserta_id`),
  ADD KEY `created_by_fk_7114430` (`created_by_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_alerts`
--
ALTER TABLE `user_alerts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_user_alert`
--
ALTER TABLE `user_user_alert`
  ADD KEY `user_alert_id_fk_7114365` (`user_alert_id`),
  ADD KEY `user_id_fk_7114365` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `faq_categories`
--
ALTER TABLE `faq_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faq_questions`
--
ALTER TABLE `faq_questions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `pendaftars`
--
ALTER TABLE `pendaftars`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sponsors`
--
ALTER TABLE `sponsors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tikets`
--
ALTER TABLE `tikets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaksis`
--
ALTER TABLE `transaksis`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `user_alerts`
--
ALTER TABLE `user_alerts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `faq_questions`
--
ALTER TABLE `faq_questions`
  ADD CONSTRAINT `category_fk_7114374` FOREIGN KEY (`category_id`) REFERENCES `faq_categories` (`id`);

--
-- Constraints for table `pendaftars`
--
ALTER TABLE `pendaftars`
  ADD CONSTRAINT `event_fk_6953391` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);

--
-- Constraints for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD CONSTRAINT `permission_id_fk_6952520` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_id_fk_6952520` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  ADD CONSTRAINT `refresh_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_user`
--
ALTER TABLE `role_user`
  ADD CONSTRAINT `role_id_fk_6952529` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_id_fk_6952529` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tikets`
--
ALTER TABLE `tikets`
  ADD CONSTRAINT `peserta_fk_7114415` FOREIGN KEY (`peserta_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `transaksis`
--
ALTER TABLE `transaksis`
  ADD CONSTRAINT `created_by_fk_7114430` FOREIGN KEY (`created_by_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `peserta_fk_7114422` FOREIGN KEY (`peserta_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_user_alert`
--
ALTER TABLE `user_user_alert`
  ADD CONSTRAINT `user_alert_id_fk_7114365` FOREIGN KEY (`user_alert_id`) REFERENCES `user_alerts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_id_fk_7114365` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
