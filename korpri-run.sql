-- -------------------------------------------------------------
-- TablePlus 5.5.2(512)
--
-- https://tableplus.com/
--
-- Database: korpri-run
-- Generation Time: 2025-08-26 22:45:34.0430
-- -------------------------------------------------------------


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `properties` text COLLATE utf8mb4_unicode_ci,
  `host` varchar(46) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

CREATE TABLE `banners` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

CREATE TABLE `events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_event` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `harga` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_mulai` datetime DEFAULT NULL,
  `tanggal_selesai` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

CREATE TABLE `faq_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

CREATE TABLE `faq_questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question` longtext COLLATE utf8mb4_unicode_ci,
  `answer` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_fk_7114374` (`category_id`),
  CONSTRAINT `category_fk_7114374` FOREIGN KEY (`category_id`) REFERENCES `faq_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

CREATE TABLE `media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `collection_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `conversions_disk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint(20) unsigned NOT NULL,
  `manipulations` json NOT NULL,
  `custom_properties` json NOT NULL,
  `generated_conversions` json NOT NULL,
  `responsive_images` json NOT NULL,
  `order_column` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_uuid_unique` (`uuid`),
  KEY `media_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `media_order_column_index` (`order_column`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

CREATE TABLE `pendaftars` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_tiket` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomor_punggung` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nik` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_hp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checkin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_at` datetime DEFAULT NULL,
  `finish_at` datetime DEFAULT NULL,
  `notes` longtext COLLATE utf8mb4_unicode_ci,
  `status_payment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_bayar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `event_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `event_fk_6953391` (`event_id`),
  CONSTRAINT `event_fk_6953391` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

CREATE TABLE `permission_role` (
  `role_id` bigint(20) unsigned NOT NULL,
  `permission_id` bigint(20) unsigned NOT NULL,
  KEY `role_id_fk_6952520` (`role_id`),
  KEY `permission_id_fk_6952520` (`permission_id`),
  CONSTRAINT `permission_id_fk_6952520` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_id_fk_6952520` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

CREATE TABLE `refresh_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL DEFAULT '0',
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `refresh_tokens_token_unique` (`token`),
  KEY `refresh_tokens_user_id_foreign` (`user_id`),
  CONSTRAINT `refresh_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

CREATE TABLE `role_user` (
  `user_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  KEY `user_id_fk_6952529` (`user_id`),
  KEY `role_id_fk_6952529` (`role_id`),
  CONSTRAINT `role_id_fk_6952529` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_id_fk_6952529` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

CREATE TABLE `sponsors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

CREATE TABLE `tickets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_tiket` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checkin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` longtext COLLATE utf8mb4_unicode_ci,
  `status_payment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_bayar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `peserta_id` bigint(20) unsigned DEFAULT NULL,
  `event_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `peserta_fk_7114415` (`peserta_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

CREATE TABLE `tikets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_tiket` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checkin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` longtext COLLATE utf8mb4_unicode_ci,
  `status_payment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_bayar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `peserta_id` bigint(20) unsigned DEFAULT NULL,
  `event_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `peserta_fk_7114415` (`peserta_id`),
  CONSTRAINT `peserta_fk_7114415` FOREIGN KEY (`peserta_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

CREATE TABLE `transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tiket_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` longtext COLLATE utf8mb4_unicode_ci,
  `snap_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `peserta_id` bigint(20) unsigned DEFAULT NULL,
  `created_by_id` bigint(20) unsigned DEFAULT NULL,
  `events` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `peserta_fk_7114422` (`peserta_id`),
  KEY `created_by_fk_7114430` (`created_by_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

CREATE TABLE `transaksi` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `peserta_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` bigint(20) NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `snap_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','success','Expired','failed','Refund') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

CREATE TABLE `transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tiket_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` longtext COLLATE utf8mb4_unicode_ci,
  `snap_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `peserta_id` bigint(20) unsigned DEFAULT NULL,
  `created_by_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `peserta_fk_7114422` (`peserta_id`),
  KEY `created_by_fk_7114430` (`created_by_id`),
  CONSTRAINT `created_by_fk_7114430` FOREIGN KEY (`created_by_id`) REFERENCES `users` (`id`),
  CONSTRAINT `peserta_fk_7114422` FOREIGN KEY (`peserta_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

CREATE TABLE `user_alerts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `alert_text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alert_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

CREATE TABLE `user_user_alert` (
  `user_alert_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `read` tinyint(1) NOT NULL DEFAULT '0',
  KEY `user_alert_id_fk_7114365` (`user_alert_id`),
  KEY `user_id_fk_7114365` (`user_id`),
  CONSTRAINT `user_alert_id_fk_7114365` FOREIGN KEY (`user_alert_id`) REFERENCES `user_alerts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_id_fk_7114365` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `uid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `region` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `village` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

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
(48, 'audit:created', 11, 'App\\Models\\Transaksi#11', NULL, '{\"invoice\":\"TRX-P09YOWTAAT\",\"events\":\"s:2:\\\"16\\\";\",\"event_id\":1,\"amount\":\"200000\",\"note\":\"LT Runner\",\"status\":\"pending\",\"updated_at\":\"2025-08-26 15:03:41\",\"created_at\":\"2025-08-26 15:03:41\",\"id\":11}', '127.0.0.1', '2025-08-26 15:03:41', '2025-08-26 15:03:41');

INSERT INTO `events` (`id`, `nama_event`, `event_code`, `harga`, `tanggal_mulai`, `tanggal_selesai`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Tiket Untuk ASN', 'KORPRI_ASN', '200000', '2025-08-31 02:45:06', NULL, '2025-08-24 14:45:19', NULL, NULL),
(2, 'Tiket Untuk Umum', 'KORPRI_UMUM', '200000', '2025-08-31 02:45:06', NULL, '2025-08-24 14:45:19', NULL, NULL);

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
(19, '2022_08_07_000012_create_transactions_table', 1),
(20, '2022_08_07_000013_create_sponsors_table', 1),
(21, '2022_08_07_000014_create_settings_table', 1),
(22, '2022_08_07_000017_create_user_user_alert_pivot_table', 1),
(23, '2022_08_07_000018_add_relationship_fields_to_tikets_table', 1),
(24, '2022_08_07_000019_add_relationship_fields_to_faq_questions_table', 1),
(25, '2022_08_07_000020_add_relationship_fields_to_transactions_table', 1),
(26, '2022_08_07_000021_add_approval_fields', 1),
(27, '2025_08_25_175500_create_refresh_tokens_table', 2),
(28, '2025_08_26_000001_add_fields_to_pendaftars_table', 3);

INSERT INTO `pendaftars` (`id`, `no_tiket`, `nomor_punggung`, `nama`, `nik`, `email`, `no_hp`, `province`, `city`, `address`, `checkin`, `start_at`, `finish_at`, `notes`, `status_payment`, `payment_type`, `total_bayar`, `created_at`, `updated_at`, `deleted_at`, `event_id`) VALUES
(1, '0001', NULL, 'Runner-0001', '1111', 'Runner-0001@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 07:53:06', '2025-08-24 07:53:06', NULL, NULL),
(2, '02', NULL, 'Runner-02', '1111', 'Runner-02@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 07:54:38', '2025-08-24 07:54:38', NULL, NULL),
(3, '03', NULL, 'Runner-03', '1111', 'Runner-03@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 07:55:54', '2025-08-24 07:55:54', NULL, NULL),
(4, '04', NULL, 'Runner-04', '1111', 'Runner-04@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 07:57:48', '2025-08-24 07:57:48', NULL, NULL),
(5, '05', NULL, 'Runner-05', '1111', 'Runner-05@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 07:58:20', '2025-08-24 07:58:20', NULL, NULL),
(6, '06', NULL, 'Runner-06', '1111', 'Runner-06@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 07:58:56', '2025-08-24 07:58:56', NULL, NULL),
(7, '07', NULL, 'failamir abdullah', '1234567890', 'ifailamir@gmail.com', '0812312312312', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, '200000', '2025-08-24 07:59:11', '2025-08-24 07:59:11', NULL, NULL),
(8, '08', NULL, 'Runner-08', '1111', 'Runner-08@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 08:04:03', '2025-08-24 08:04:03', NULL, NULL),
(9, '09', NULL, 'Runner-09', '1111', 'Runner-09@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 09:08:25', '2025-08-24 09:08:25', NULL, NULL),
(10, '010', NULL, 'Runner-010', '1111', 'Runner-010@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 10:37:49', '2025-08-24 10:37:49', NULL, NULL),
(11, '010', NULL, 'Runner-010', '1111', 'Runner-010@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 10:45:38', '2025-08-24 10:45:38', NULL, NULL),
(12, '010', NULL, 'Runner-010', '1111', 'Runner-010@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-24 10:51:40', '2025-08-24 10:51:40', NULL, NULL),
(13, '10', NULL, 'Runner-10', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:21:52', '2025-08-25 13:21:52', NULL, NULL),
(14, '11', NULL, 'Runner-11', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:27:30', '2025-08-25 13:27:30', NULL, NULL),
(15, '12', NULL, 'Runner-12', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:28:07', '2025-08-25 13:28:07', NULL, NULL),
(16, '13', NULL, 'Runner-13', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:32:37', '2025-08-25 13:32:37', NULL, NULL),
(17, '14', NULL, 'Runner-14', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:34:49', '2025-08-25 13:34:49', NULL, NULL),
(18, '00015', NULL, 'Runner-00015', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:41:37', '2025-08-25 13:41:37', NULL, NULL),
(19, '00016', NULL, 'Runner-00016', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:46:24', '2025-08-25 13:46:24', NULL, NULL),
(20, '00017', NULL, 'Runner-00017', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:46:43', '2025-08-25 13:46:43', NULL, NULL),
(21, '00018', NULL, 'Runner-00018', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:47:02', '2025-08-25 13:47:02', NULL, NULL),
(22, '00019', NULL, 'Runner-00019', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:47:13', '2025-08-25 13:47:13', NULL, NULL),
(23, '00020', NULL, 'Runner-00020', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:47:21', '2025-08-25 13:47:21', NULL, NULL),
(24, '00021', NULL, 'Runner-00021', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 13:47:39', '2025-08-25 13:47:39', NULL, NULL),
(25, '00022', NULL, 'Runner-00022', '1111', 'ifailamir@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 14:02:01', '2025-08-25 14:02:01', NULL, NULL),
(26, '015', NULL, 'lombok', 'u67ty3tw34w535433q432c54353453', 'lombok2@cantik.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 17:37:46', '2025-08-25 17:37:46', NULL, 1),
(27, '015', NULL, 'lombok', 'u67ty3tw34w535433q432c54353453', 'lombok2@cantik.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 17:41:57', '2025-08-25 17:41:57', NULL, 1),
(28, '015', NULL, 'Admin', '1121212121', 'admin@admin.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 18:03:47', '2025-08-25 18:03:47', NULL, 1),
(29, '015', NULL, 'Admin', '1121212121', 'admin@admin.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 18:04:40', '2025-08-25 18:04:40', NULL, 1),
(30, '015', NULL, 'Admin', '1121212121', 'admin@admin.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 18:05:13', '2025-08-25 18:05:13', NULL, 1),
(31, '015', NULL, 'Admin', '1121212121', 'admin@admin.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 18:05:54', '2025-08-25 18:05:54', NULL, 1),
(32, '015', NULL, 'Admin', '1121212121', 'admin@admin.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '2025-08-25 18:32:55', '2025-08-25 18:32:55', NULL, 1),
(33, '015', NULL, 'lombok', '123871293871238912312', 'lombok2@cantik.com', '08213123123213', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, '200000', '2025-08-25 20:26:24', '2025-08-25 20:26:24', NULL, 1),
(34, '15', NULL, 'Test Runner', '1234567890123456', 'testrunner@example.com', '08123456789', 'NTB', 'Lombok Tengah', 'Jl. Contoh 123', NULL, NULL, NULL, NULL, 'pending', NULL, '200000', '2025-08-26 14:07:34', '2025-08-26 14:07:34', NULL, 1),
(35, '16', NULL, 'LT Runner', '9876543210987654', 'ltrunner@example.com', '081200000000', 'NTB', 'Mataram', 'Jl. Tunnel 1', NULL, NULL, NULL, NULL, 'pending', NULL, '200000', '2025-08-26 15:03:41', '2025-08-26 15:03:41', NULL, 1);

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

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 4, 'u67ty3tw34w535433q432c54353453', '71f9c6b5727459c2d90c51eb2bcaa1810737380d9fa24a6fe9a25bffa41978ba', '[\"*\"]', NULL, '2025-08-25 16:16:59', '2025-08-25 16:16:59'),
(2, 'App\\Models\\User', 5, 'u67ty3tw34w535433q432c54353453', 'c36ba8d445f7382921b0ac63bdb5a03cd378b7c90a6c101aa9364e8980a4368c', '[\"*\"]', NULL, '2025-08-25 16:17:22', '2025-08-25 16:17:22'),
(7, 'App\\Models\\User', 9, 'api-token', '33db15d820d20b48f44021545493daa289c93d78dcf575b2f3bf91d9db248c80', '[\"*\"]', NULL, '2025-08-25 17:55:14', '2025-08-25 17:55:14'),
(9, 'App\\Models\\User', 10, 'api-token', 'afb745ebcb0dd7b0dbde636d4a6a2efddbb87d5f62714677119041b00436c8d4', '[\"*\"]', NULL, '2025-08-25 18:31:31', '2025-08-25 18:31:31'),
(10, 'App\\Models\\User', 11, 'api-token', 'a068be27acd93405fc95354c8e0a0601d5fa6e344d1c20dd2980d891dc91f260', '[\"*\"]', NULL, '2025-08-25 18:31:48', '2025-08-25 18:31:48'),
(11, 'App\\Models\\User', 12, 'api-token', '6d8e77a17d5f686c0b3f7a5df9ed88a878ebef2ae8885597233ae602c09a65a7', '[\"*\"]', NULL, '2025-08-25 18:31:51', '2025-08-25 18:31:51'),
(14, 'App\\Models\\User', 1, 'api-token', '7910cf490e064c395a7d1ec27aaf0bde23ee1c190d5f0dce4d6a9d2400b01d9c', '[\"*\"]', '2025-08-26 13:30:47', '2025-08-26 13:20:46', '2025-08-26 13:30:47');

INSERT INTO `refresh_tokens` (`id`, `user_id`, `token`, `device_name`, `revoked`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 9, '4860e2ebcb5786377b0059bb8560797dfe955861a59360bad9c3bab76f2c6c0e', 'api-token', 0, '2025-09-24 17:55:14', '2025-08-25 17:55:14', '2025-08-25 17:55:14'),
(2, 1, '6782b30e5a0bb4aa8389f0d9a34cb04a5971c9ea54299416abd512c0a5d83b88', 'api-token', 0, '2025-09-24 17:58:31', '2025-08-25 17:58:31', '2025-08-25 17:58:31'),
(3, 1, '23387e9902c3bdb3235f0002de3dd4b882bb0debd27081943d5aed74f07437c1', 'api-token', 0, '2025-09-24 19:17:03', '2025-08-25 19:17:03', '2025-08-25 19:17:03'),
(4, 1, '5013ec67b405dd449a20251370792e8255d6d0992873f4902c847f8e70c92824', 'api-token', 0, '2025-09-24 19:41:46', '2025-08-25 19:41:46', '2025-08-25 19:41:46'),
(5, 1, '57481396c022201ff0a1bb0f5933b2a3a4c1fa9a2e9aaed0c8232321a0c981c4', 'api-token', 0, '2025-09-25 13:20:46', '2025-08-26 13:20:46', '2025-08-26 13:20:46');

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
(12, 2);

INSERT INTO `roles` (`id`, `title`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Admin', NULL, NULL, NULL),
(2, 'User', NULL, NULL, NULL);

INSERT INTO `tickets` (`id`, `no_tiket`, `checkin`, `notes`, `status_payment`, `payment_type`, `total_bayar`, `created_at`, `updated_at`, `deleted_at`, `peserta_id`, `event_id`) VALUES
(1, '015', NULL, NULL, NULL, NULL, NULL, '2025-08-25 18:04:40', '2025-08-25 18:04:40', NULL, 1, 1),
(2, '015', NULL, NULL, NULL, NULL, NULL, '2025-08-25 18:05:13', '2025-08-25 18:05:13', NULL, 1, 1),
(3, '015', NULL, NULL, NULL, NULL, NULL, '2025-08-25 18:05:54', '2025-08-25 18:05:54', NULL, 1, 1),
(4, '015', NULL, NULL, NULL, NULL, NULL, '2025-08-25 18:32:55', '2025-08-25 18:32:55', NULL, 1, 1),
(5, '015', NULL, NULL, NULL, NULL, NULL, '2025-08-25 20:26:24', '2025-08-25 20:26:24', NULL, 6, 1);

INSERT INTO `tikets` (`id`, `no_tiket`, `checkin`, `notes`, `status_payment`, `payment_type`, `total_bayar`, `created_at`, `updated_at`, `deleted_at`, `peserta_id`, `event_id`) VALUES
(1, '015', NULL, NULL, NULL, NULL, NULL, '2025-08-25 18:04:40', '2025-08-25 18:04:40', NULL, 1, 1),
(2, '015', NULL, NULL, NULL, NULL, NULL, '2025-08-25 18:05:13', '2025-08-25 18:05:13', NULL, 1, 1),
(3, '015', NULL, NULL, NULL, NULL, NULL, '2025-08-25 18:05:54', '2025-08-25 18:05:54', NULL, 1, 1);

INSERT INTO `transactions` (`id`, `invoice`, `event_id`, `tiket_id`, `amount`, `note`, `snap_token`, `status`, `created_at`, `updated_at`, `deleted_at`, `peserta_id`, `created_by_id`, `events`) VALUES
(3, 'TRX-1N5Q0QS038', NULL, NULL, '200000', 'failamir abdullah', '4b0a1920-6fe7-4a4e-91a4-805d86f008b3', 'pending', '2025-08-24 09:08:35', '2025-08-24 09:08:36', NULL, NULL, NULL, NULL),
(4, 'TRX-78Y3Q8Z2ID', NULL, NULL, '200000', 'failamir abdullah', 'd1143135-c756-43bd-8fed-bbb320e362b8', 'pending', '2025-08-24 10:38:22', '2025-08-24 10:38:24', NULL, NULL, NULL, NULL),
(5, 'TRX-H540UR6444', NULL, NULL, '200000', 'failamir abdullah', 'ea937d4b-1dcf-4fcf-863c-4dd9c8b1f18f', 'pending', '2025-08-24 10:54:58', '2025-08-24 10:54:59', NULL, NULL, NULL, NULL),
(6, 'TRX-25DBPA44L7', NULL, NULL, '200000', 'failamir abdullah', '4950ed2d-a724-4709-9d73-57484f9f94fc', 'pending', '2025-08-25 14:05:28', '2025-08-25 14:05:29', NULL, NULL, 2, NULL),
(7, 'TRX-O971CG3M49', NULL, NULL, '200000', 'Admin', NULL, 'pending', '2025-08-25 18:05:54', '2025-08-25 18:05:54', NULL, 1, NULL, 's:3:\"015\";'),
(8, 'TRX-H2B29F86YW', NULL, NULL, '200000', 'Admin', NULL, 'pending', '2025-08-25 18:32:55', '2025-08-25 18:32:55', NULL, 1, NULL, 's:3:\"015\";'),
(9, 'TRX-YMRIYUJ61K', NULL, NULL, '200000', 'lombok', NULL, 'pending', '2025-08-25 20:26:24', '2025-08-25 20:26:24', NULL, 6, NULL, 's:3:\"015\";'),
(10, 'TRX-PGYXDZTHN5', '1', NULL, '200000', 'Test Runner', NULL, 'pending', '2025-08-26 14:07:34', '2025-08-26 14:07:34', NULL, NULL, NULL, 's:2:\"15\";'),
(11, 'TRX-P09YOWTAAT', '1', NULL, '200000', 'LT Runner', NULL, 'pending', '2025-08-26 15:03:41', '2025-08-26 15:03:41', NULL, NULL, NULL, 's:2:\"16\";');

INSERT INTO `transactions` (`id`, `invoice`, `event_id`, `tiket_id`, `amount`, `note`, `snap_token`, `status`, `created_at`, `updated_at`, `deleted_at`, `peserta_id`, `created_by_id`) VALUES
(3, 'TRX-1N5Q0QS038', NULL, NULL, '200000', 'failamir abdullah', '4b0a1920-6fe7-4a4e-91a4-805d86f008b3', 'pending', '2025-08-24 09:08:35', '2025-08-24 09:08:36', NULL, NULL, NULL),
(4, 'TRX-78Y3Q8Z2ID', NULL, NULL, '200000', 'failamir abdullah', 'd1143135-c756-43bd-8fed-bbb320e362b8', 'pending', '2025-08-24 10:38:22', '2025-08-24 10:38:24', NULL, NULL, NULL),
(5, 'TRX-H540UR6444', NULL, NULL, '200000', 'failamir abdullah', 'ea937d4b-1dcf-4fcf-863c-4dd9c8b1f18f', 'pending', '2025-08-24 10:54:58', '2025-08-24 10:54:59', NULL, NULL, NULL),
(6, 'TRX-25DBPA44L7', NULL, NULL, '200000', 'failamir abdullah', '4950ed2d-a724-4709-9d73-57484f9f94fc', 'pending', '2025-08-25 14:05:28', '2025-08-25 14:05:29', NULL, NULL, 2);

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
(12, 'lombok', 'lombok7@cantik.com', NULL, '$2y$10$t.IffRhK8Wt7GRXBcGXABOpH9hcAU.d7Wj9EWP820PXtMyuIsjcwS', NULL, '2025-08-25 18:31:51', '2025-08-25 18:31:51', NULL, 'u67ty3tw34w535433q432c54353453', NULL, NULL, NULL, NULL, NULL, NULL, NULL);



/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;