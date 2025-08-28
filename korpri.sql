-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 27, 2025 at 07:30 PM
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
(62, 'audit:created', 23, 'App\\Models\\Transaksi#23', NULL, '{\"invoice\":\"TRX-EA40T3ICWD\",\"events\":\"1\",\"peserta_id\":29,\"amount\":\"200000\",\"note\":\"Test1\",\"status\":\"pending\",\"updated_at\":\"2025-08-27 17:29:16\",\"created_at\":\"2025-08-27 17:29:16\",\"id\":23}', '103.136.59.14', '2025-08-27 17:29:16', '2025-08-27 17:29:16');

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
(1, 'Tiket Untuk ASN', 'KORPRI_ASN', '200000', '2025-08-31 02:45:06', NULL, '2025-08-24 14:45:19', NULL, NULL),
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
(28, '2025_08_26_000001_add_fields_to_pendaftars_table', 3);

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
  `paired_at` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pendaftars`
--

INSERT INTO `pendaftars` (`id`, `no_tiket`, `nomor_punggung`, `nama`, `nik`, `email`, `no_hp`, `province`, `city`, `address`, `checkin`, `start_at`, `finish_at`, `notes`, `status_payment`, `payment_type`, `total_bayar`, `created_at`, `updated_at`, `deleted_at`, `event_id`, `paired_at`) VALUES
(1, '0001', NULL, 'Runner-0001', '1111', 'Runner-0001@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 07:53:06', '2025-08-24 07:53:06', NULL, NULL, ''),
(2, '02', NULL, 'Runner-02', '1111', 'Runner-02@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 07:54:38', '2025-08-24 07:54:38', NULL, NULL, ''),
(3, '03', NULL, 'Runner-03', '1111', 'Runner-03@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 07:55:54', '2025-08-24 07:55:54', NULL, NULL, ''),
(4, '04', NULL, 'Runner-04', '1111', 'Runner-04@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 07:57:48', '2025-08-24 07:57:48', NULL, NULL, ''),
(5, '05', NULL, 'Runner-05', '1111', 'Runner-05@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 07:58:20', '2025-08-24 07:58:20', NULL, NULL, ''),
(6, '06', NULL, 'Runner-06', '1111', 'Runner-06@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 07:58:56', '2025-08-24 07:58:56', NULL, NULL, ''),
(7, '07', NULL, 'failamir abdullah', '1234567890', 'ifailamir@gmail.com', '0812312312312', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, '200000', '2025-08-24 07:59:11', '2025-08-24 07:59:11', NULL, NULL, ''),
(8, '08', NULL, 'Runner-08', '1111', 'Runner-08@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 08:04:03', '2025-08-24 08:04:03', NULL, NULL, ''),
(9, '09', NULL, 'Runner-09', '1111', 'Runner-09@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 09:08:25', '2025-08-24 09:08:25', NULL, NULL, ''),
(10, '010', NULL, 'Runner-010', '1111', 'Runner-010@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 10:37:49', '2025-08-24 10:37:49', NULL, NULL, ''),
(11, '010', NULL, 'Runner-010', '1111', 'Runner-010@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 10:45:38', '2025-08-24 10:45:38', NULL, NULL, ''),
(12, '010', NULL, 'Runner-010', '1111', 'Runner-010@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 10:51:40', '2025-08-24 10:51:40', NULL, NULL, ''),
(13, '10', NULL, 'Runner-10', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:21:52', '2025-08-25 13:21:52', NULL, NULL, ''),
(14, '11', NULL, 'Runner-11', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:27:30', '2025-08-25 13:27:30', NULL, NULL, ''),
(15, '12', NULL, 'Runner-12', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:28:07', '2025-08-25 13:28:07', NULL, NULL, ''),
(16, '13', NULL, 'Runner-13', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:32:37', '2025-08-25 13:32:37', NULL, NULL, ''),
(17, '14', NULL, 'Runner-14', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:34:49', '2025-08-25 13:34:49', NULL, NULL, ''),
(18, '00015', NULL, 'Runner-00015', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:41:37', '2025-08-25 13:41:37', NULL, NULL, ''),
(19, '00016', NULL, 'Runner-00016', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:46:24', '2025-08-25 13:46:24', NULL, NULL, ''),
(20, '00017', NULL, 'Runner-00017', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:46:43', '2025-08-25 13:46:43', NULL, NULL, ''),
(21, '00018', NULL, 'Runner-00018', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:47:02', '2025-08-25 13:47:02', NULL, NULL, ''),
(22, '00019', NULL, 'Runner-00019', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:47:13', '2025-08-25 13:47:13', NULL, NULL, ''),
(23, '00020', NULL, 'Runner-00020', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:47:21', '2025-08-25 13:47:21', NULL, NULL, ''),
(24, '00021', NULL, 'Runner-00021', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:47:39', '2025-08-25 13:47:39', NULL, NULL, ''),
(25, '00022', NULL, 'Runner-00022', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 14:02:01', '2025-08-25 14:02:01', NULL, NULL, ''),
(26, '015', NULL, 'lombok', 'u67ty3tw34w535433q432c54353453', 'lombok2@cantik.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 17:37:46', '2025-08-25 17:37:46', NULL, 1, ''),
(27, '015', NULL, 'lombok', 'u67ty3tw34w535433q432c54353453', 'lombok2@cantik.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 17:41:57', '2025-08-25 17:41:57', NULL, 1, ''),
(28, '015', NULL, 'Admin', '1121212121', 'admin@admin.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 18:03:47', '2025-08-25 18:03:47', NULL, 1, ''),
(29, '015', NULL, 'Admin', '1121212121', 'admin@admin.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 18:04:40', '2025-08-25 18:04:40', NULL, 1, ''),
(30, '015', NULL, 'Admin', '1121212121', 'admin@admin.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 18:05:13', '2025-08-25 18:05:13', NULL, 1, ''),
(31, '015', NULL, 'Admin', '1121212121', 'admin@admin.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 18:05:54', '2025-08-25 18:05:54', NULL, 1, ''),
(32, '015', NULL, 'Admin', '1121212121', 'admin@admin.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 18:32:55', '2025-08-25 18:32:55', NULL, 1, ''),
(33, '015', NULL, 'lombok', '123871293871238912312', 'lombok2@cantik.com', '08213123123213', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, '200000', '2025-08-25 20:26:24', '2025-08-25 20:26:24', NULL, 1, ''),
(34, '15', NULL, 'Test Runner', '1234567890123456', 'testrunner@example.com', '08123456789', 'NTB', 'Lombok Tengah', 'Jl. Contoh 123', NULL, NULL, NULL, NULL, 'pending', NULL, '200000', '2025-08-26 14:07:34', '2025-08-26 14:07:34', NULL, 1, ''),
(35, '16', NULL, 'LT Runner', '9876543210987654', 'ltrunner@example.com', '081200000000', 'NTB', 'Mataram', 'Jl. Tunnel 1', NULL, NULL, NULL, NULL, 'pending', NULL, '200000', '2025-08-26 15:03:41', '2025-08-26 15:03:41', NULL, 1, ''),
(36, '017', NULL, 'lombok', '123871293871238912312', 'lombok2@cantik.com', '08213123123213', 'NTB', 'mataram', 'jln sade', NULL, NULL, NULL, NULL, 'pending', NULL, '200000', '2025-08-27 08:06:20', '2025-08-27 08:06:20', NULL, 1, ''),
(37, '017', NULL, 'lombok', '123871293871238912312', 'lombok2@cantik.com', '08213123123213', 'NTB', 'mataram', 'jln sade', NULL, NULL, NULL, NULL, 'pending', NULL, '200000', '2025-08-27 08:07:46', '2025-08-27 08:07:46', NULL, 1, '');

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
(27, 'App\\Models\\User', 21, 'api-token', 'b8c0b375d79eb6656f4178fa7564539688c7b872cde2c739a163da102cef8532', '[\"*\"]', '2025-08-27 17:30:21', '2025-08-27 17:01:23', '2025-08-27 17:30:21');

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
(9, 21, '70350bbc3b42c6dc9265db0d158b22b1480e07451d85b9105111e3b862510d1f', 'api-token', 0, '2025-09-26 17:01:23', '2025-08-27 17:01:23', '2025-08-27 17:01:23');

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
(29, 2);

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
  `payment_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `invoice`, `event_id`, `tiket_id`, `amount`, `note`, `snap_token`, `status`, `created_at`, `updated_at`, `deleted_at`, `peserta_id`, `created_by_id`, `events`, `payment_url`) VALUES
(3, 'TRX-1N5Q0QS038', NULL, NULL, '200000', 'failamir abdullah', '4b0a1920-6fe7-4a4e-91a4-805d86f008b3', 'pending', '2025-08-24 09:08:35', '2025-08-24 09:08:36', NULL, NULL, NULL, NULL, NULL),
(4, 'TRX-78Y3Q8Z2ID', NULL, NULL, '200000', 'failamir abdullah', 'd1143135-c756-43bd-8fed-bbb320e362b8', 'pending', '2025-08-24 10:38:22', '2025-08-24 10:38:24', NULL, NULL, NULL, NULL, NULL),
(5, 'TRX-H540UR6444', NULL, NULL, '200000', 'failamir abdullah', 'ea937d4b-1dcf-4fcf-863c-4dd9c8b1f18f', 'pending', '2025-08-24 10:54:58', '2025-08-24 10:54:59', NULL, NULL, NULL, NULL, NULL),
(6, 'TRX-25DBPA44L7', NULL, NULL, '200000', 'failamir abdullah', '4950ed2d-a724-4709-9d73-57484f9f94fc', 'pending', '2025-08-25 14:05:28', '2025-08-25 14:05:29', NULL, NULL, 2, NULL, NULL),
(7, 'TRX-O971CG3M49', NULL, NULL, '200000', 'Admin', NULL, 'pending', '2025-08-25 18:05:54', '2025-08-25 18:05:54', NULL, 1, NULL, 's:3:\"015\";', NULL),
(8, 'TRX-H2B29F86YW', NULL, NULL, '200000', 'Admin', NULL, 'pending', '2025-08-25 18:32:55', '2025-08-25 18:32:55', NULL, 1, NULL, 's:3:\"015\";', NULL),
(9, 'TRX-YMRIYUJ61K', NULL, NULL, '200000', 'lombok', NULL, 'pending', '2025-08-25 20:26:24', '2025-08-25 20:26:24', NULL, 6, NULL, 's:3:\"015\";', NULL),
(10, 'TRX-PGYXDZTHN5', '1', NULL, '200000', 'Test Runner', NULL, 'pending', '2025-08-26 14:07:34', '2025-08-26 14:07:34', NULL, NULL, NULL, 's:2:\"15\";', NULL),
(11, 'TRX-P09YOWTAAT', '1', NULL, '200000', 'LT Runner', NULL, 'pending', '2025-08-26 15:03:41', '2025-08-26 15:03:41', NULL, NULL, NULL, 's:2:\"16\";', NULL),
(12, 'TRX-766KJ7TD5U', NULL, NULL, '200000', 'lombok', NULL, 'pending', '2025-08-27 08:06:20', '2025-08-27 08:06:20', NULL, 6, NULL, 's:3:\"017\";', NULL),
(13, 'TRX-5FX5Q5XGM0', NULL, NULL, '200000', 'lombok', NULL, 'pending', '2025-08-27 08:07:46', '2025-08-27 08:07:46', NULL, 6, NULL, 's:3:\"017\";', NULL),
(14, 'TRX-4754EEQ9R7', NULL, NULL, '200000', 'lombok', NULL, 'pending', '2025-08-27 08:13:32', '2025-08-27 08:13:32', NULL, 6, NULL, '1', NULL),
(15, 'TRX-SKY79T98TO', NULL, NULL, '200000', 'lombok', NULL, 'pending', '2025-08-27 08:14:40', '2025-08-27 08:14:40', NULL, 6, NULL, '1', 'https://app.sandbox.midtrans.com/snap/v4/redirection/e3ccee42-0917-4163-8a12-fc8560b88615'),
(16, 'TRX-W5ZR9Z5I0G', NULL, NULL, '200000', 'lombok', NULL, 'pending', '2025-08-27 08:18:15', '2025-08-27 08:18:15', NULL, 6, NULL, '1', 'https://app.sandbox.midtrans.com/snap/v4/redirection/0a6fd361-c551-4380-b950-7aef10bf4950'),
(17, 'TRX-2Q126ALC9R', NULL, NULL, '200000', 'lombok', NULL, 'pending', '2025-08-27 08:18:16', '2025-08-27 08:18:17', NULL, 6, NULL, '1', 'https://app.sandbox.midtrans.com/snap/v4/redirection/d2c0d5c0-d7b6-49ef-9422-bf8270cbef0c'),
(18, 'TRX-Q298PF7P24', NULL, NULL, '200000', 'lombok', NULL, 'pending', '2025-08-27 08:56:41', '2025-08-27 08:56:42', NULL, 6, NULL, '1', 'https://app.sandbox.midtrans.com/snap/v4/redirection/fd46e102-581e-4ced-bff5-2cffd90ca00b'),
(19, 'TRX-L25262KH93', NULL, NULL, NULL, 'fafafa', NULL, 'pending', '2025-08-27 09:11:50', '2025-08-27 09:11:50', NULL, 22, NULL, '1', NULL),
(20, 'TRX-9USLXP3YIU', NULL, NULL, NULL, 'fafa', NULL, 'pending', '2025-08-27 09:13:40', '2025-08-27 09:13:40', NULL, 23, NULL, '1', NULL),
(21, 'TRX-OJ2Z4I951E', NULL, NULL, '200000', 'afaga', NULL, 'pending', '2025-08-27 09:18:25', '2025-08-27 09:18:25', NULL, 26, NULL, '1', 'https://app.sandbox.midtrans.com/snap/v4/redirection/046f2e1d-48bc-4917-92fe-f8352cf331b9'),
(22, 'TRX-5UUKMU4Q34', NULL, NULL, '200000', 'Fail Amirku', NULL, 'pending', '2025-08-27 17:16:02', '2025-08-27 17:16:02', NULL, 27, NULL, '1', 'https://app.sandbox.midtrans.com/snap/v4/redirection/d5d5feb9-3287-47b2-a483-e74ed6a77064'),
(23, 'TRX-EA40T3ICWD', NULL, NULL, '200000', 'Test1', NULL, 'pending', '2025-08-27 17:29:16', '2025-08-27 17:29:17', NULL, 29, NULL, '1', 'https://app.sandbox.midtrans.com/snap/v4/redirection/a199ca6c-9039-40ab-a86f-2ee10106ceba');

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
  `role` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`, `uid`, `nik`, `city`, `region`, `village`, `role`, `no_hp`, `approved`) VALUES
(1, 'Admin', 'admin@admin.com', NULL, '$2y$10$sqFsaRZbpwhmgza39HMj3urb0lf6sDAkMXoPC1mQj4e23rDOTki2u', NULL, NULL, NULL, NULL, '1121212121', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'Fail Amir', 'ifailamir@gmail.com', NULL, '$2y$10$YHjjF6UwMgtXqkwJieGQ5OXwgdEfPmBuLHTRQ3JoCc0ZzmGevZBn.', 'pEYRM1rFsSFSxni4qUKvwl4gfzoRsFT8dL2x1D5YyXJw5lsQKQ4ZzyPxistD', '2025-08-24 13:02:00', '2025-08-24 13:02:00', NULL, '2313122144', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'fatimahgelora', 'fatimahgelora@gmail.com', NULL, '$2y$10$M7icIK9WriK8q2gay01pmeDJsHlF3YfT1cfVQxLPSHl12qwOyMVAC', '4mOOaJtITBvyPB24TQc5EVRsyiOGgj4rIekOHqPiYjYkMprjfv2UAKoazi13', '2025-08-24 13:31:49', '2025-08-24 13:31:49', NULL, '6575675675', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'lombok', 'lombok@cantik.com', NULL, '$2y$10$c78BAYDrRvTs5mu00G8YluhgdFnp/qjyVPteqmQE0MJKfFMx4B4cG', NULL, '2025-08-25 16:16:57', '2025-08-25 16:16:57', NULL, '9789879789', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'lombok', 'lombok1@cantik.com', NULL, '$2y$10$DMZMoAeXFA5IeX8HB33v8O721Sv5jbE63Udc2zjdyghkx7SEqTI7S', NULL, '2025-08-25 16:17:21', '2025-08-25 16:17:21', NULL, '6454433444', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'lombok', 'lombok2@cantik.com', NULL, '$2y$10$bhnQNy05FNkT8ll6pMj9T.16Jvy.kJb5IFSNtn22OOs/v5uA4isOa', NULL, '2025-08-25 16:35:30', '2025-08-25 16:35:30', NULL, 'u67ty3tw34w535433q432c54353453', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'lombok', 'lombok3@cantik.com', NULL, '$2y$10$ryMCOgYhVYPxzwnhyKdwke8uEJcc3ff52RCgWpZ2BDdQOBb7tqEWm', NULL, '2025-08-25 16:35:50', '2025-08-25 16:35:50', NULL, 'u67ty3tw34w535433q432c54353453', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'lombok', 'lombokk@cantik.com', NULL, '$2y$10$EmERax1DHs/JF2NT7E/tre6Nb6uXXwZ4cgcT2cMbJ47BXY7aNCxeC', NULL, '2025-08-25 17:27:11', '2025-08-25 17:27:11', NULL, 'u67ty3tw34w535433q432c54353453', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'lombok', 'lombok4@cantik.com', NULL, '$2y$10$JXuK7pYpkdx8n7yqi5faee/.s6qGPS3G5LHM7NlgTXVFgnlMKsCCS', NULL, '2025-08-25 17:29:17', '2025-08-25 17:29:17', NULL, 'u67ty3tw34w535433q432c54353453', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'lombok', 'lombok5@cantik.com', NULL, '$2y$10$QFene5QWuRJPlOJvHsbeOuiXpbj6V3Q3K78DPDEHH/TlxL7eLA3Ym', NULL, '2025-08-25 18:31:31', '2025-08-25 18:31:31', NULL, 'u67ty3tw34w535433q432c54353453', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'lombok', 'lombok6@cantik.com', NULL, '$2y$10$ySA460x15Ul2l13yXMbl6u5lQEx7JjSLg1uJUOjQ50SQXhQvTm58G', NULL, '2025-08-25 18:31:47', '2025-08-25 18:31:47', NULL, 'u67ty3tw34w535433q432c54353453', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'lombok', 'lombok7@cantik.com', NULL, '$2y$10$t.IffRhK8Wt7GRXBcGXABOpH9hcAU.d7Wj9EWP820PXtMyuIsjcwS', NULL, '2025-08-25 18:31:51', '2025-08-25 18:31:51', NULL, 'u67ty3tw34w535433q432c54353453', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 'Failku', 'fail@gmail.com', NULL, '$2y$10$vNBgGUhlyD6lqH9zt8/ybeviCEviQnPjxeWzPjHGZKBjlSCTDwBzq', NULL, '2025-08-26 15:48:05', '2025-08-26 15:48:05', NULL, 'kvf3VyhFpxSLrYKlkiYFJLA1P3s2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 'Kalisha', 'kalisa@gmail.com', NULL, '$2y$10$NyC/11lJhA0q2TqkgnU7qO4qLVJVeWcKP/xE3t4Y9F92S.fMhV.7q', NULL, '2025-08-26 15:55:41', '2025-08-26 15:55:41', NULL, 'fxSIEH6MQdhScIcw1KMJavUQfSv1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 'lombok', 'lombok8@cantik.com', NULL, '$2y$10$cpeWpcpjeK325WmcDMLhuuQEZvcCpWu1s1FaJy0Stku0dUfhkoDcu', NULL, '2025-08-26 15:57:16', '2025-08-26 15:57:16', NULL, 'u67ty3tw34w535433q432c54353453', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'Yahu', 'yahu@yahu.com', NULL, '$2y$10$zISYFzlf6p72hAW9ttfH9OwkI5dL3VFm2irueSdUX7D5LwkgqnRoS', NULL, '2025-08-26 16:05:11', '2025-08-26 16:05:11', NULL, 'krwMVtw6yeaOpNXuouBrjan7RA82', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 'lombok', 'lombok4@cantiks.com', NULL, '$2y$10$orNhrp4tGpsVOnaPyEbozOhpn8Lugae2lyEBckrpU9GISnCHajmOm', NULL, '2025-08-26 16:05:45', '2025-08-26 16:05:45', NULL, 'u67ty3tw34w535433q432c54353453', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 'Yahu', 'yahu@yahus.com', NULL, '$2y$10$brDuMwt2/cXHrT2cE/6gkOZcgKY/4nn.9NiG66TJIgguyy9I/N.b2', NULL, '2025-08-26 16:07:41', '2025-08-26 16:07:41', NULL, 'BaiVHIn9ClPOFNRVPWPkWtFTzRC2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 'Yahu', 'yahu@yashus.com', NULL, '$2y$10$Iz205.CYRSi25YJP4q5Z1e1qddM6HEB/2I4AHK9wisyA5Ez.vgUeW', NULL, '2025-08-26 16:08:10', '2025-08-26 16:08:10', NULL, 'eKEz8xfy43fg50xocm4Kpl6CANs2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 'pail', 'pail@pail.com', NULL, '$2y$10$oOk25xK2xorR3dOthf.zFukVGoUvIkaww59nsK.7aSeB0uKLyRefa', NULL, '2025-08-26 16:13:19', '2025-08-26 16:13:19', NULL, 'ZYpsIKNJCKftpmShcQayfz7yFLl2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 'demo1234', 'demo@keenthemes.com', NULL, '$2y$10$PblAQTy3C2ybn3Fu0VwIle2EIV3wga3wzMGBYf0Tkmr7ksNgIgyNS', NULL, '2025-08-26 16:53:02', '2025-08-26 16:53:02', NULL, 'u67ty3tw34w535433q432c54353453', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 'fafafa', 'fafaf@fafsa.faf', NULL, NULL, NULL, '2025-08-27 09:11:50', '2025-08-27 09:11:50', NULL, 'guest_17562859103430', '21312312312312', NULL, NULL, NULL, NULL, '08312312312312', NULL),
(23, 'fafa', 'fafa@fafa.faf', NULL, NULL, NULL, '2025-08-27 09:13:40', '2025-08-27 09:13:40', NULL, 'guest_17562860205990', '132123123123', NULL, NULL, NULL, NULL, '08456546456456', NULL),
(26, 'afaga', 'gaga@fafa.fgag', NULL, NULL, NULL, '2025-08-27 09:18:25', '2025-08-27 09:18:25', NULL, 'guest_17562863051690', '9213123123123123', NULL, NULL, NULL, NULL, '08213123123123', NULL),
(27, 'Fail Amirku', 'fail@fail.com', NULL, NULL, NULL, '2025-08-27 17:16:02', '2025-08-27 17:16:02', NULL, 'guest_17563149625100', '4984908098098098', NULL, NULL, NULL, NULL, '0890809809809', NULL),
(29, 'Test1', 'test1@gmail.com', NULL, NULL, NULL, '2025-08-27 17:29:16', '2025-08-27 17:29:16', NULL, 'guest_17563157566340', '9890890090890890', NULL, NULL, NULL, NULL, '3553535353', NULL);

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

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
