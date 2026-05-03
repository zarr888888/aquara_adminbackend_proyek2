-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping structure for table aquara_proyek2.activity_log
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `causer_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint unsigned DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `batch_uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.activity_log: ~66 rows (approximately)
INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES
	(1, 'default', 'updated', 'App\\Models\\User', 'updated', 1, 'App\\Models\\User', 8, '{"old": {"name": "Diks", "updated_at": "2026-04-05T07:14:12.000000Z"}, "attributes": {"name": "Kevin Diks", "updated_at": "2026-04-09T17:35:49.000000Z"}}', NULL, '2026-04-09 10:35:49', '2026-04-09 10:35:49'),
	(2, 'default', 'updated', 'App\\Models\\User', 'updated', 8, 'App\\Models\\User', 8, '{"old": {"remember_token": "8UJta7YfJEuA3l4Q6kDpJyOWPAOpVoe8BN0lC3mypJdZJ0SwGKs33ANjj68V"}, "attributes": {"remember_token": "86H17xgxfst6nQHpWBRMJoiTqFcJCLHu8eVs1DjGi1g0dPidawRnv8CxsrDW"}}', NULL, '2026-04-09 11:08:08', '2026-04-09 11:08:08'),
	(3, 'default', 'updated', 'App\\Models\\User', 'updated', 7, 'App\\Models\\User', 7, '{"old": {"remember_token": "ujreHMZRMIRvwjVuScmzby6Yb0sNLHGV2UiiZTBDpTWL68JdYU8zmELtksRI"}, "attributes": {"remember_token": "d8aWcdfTWUmf6clkxeDlRwpBRgbovOAaCmgXs6MMjspdmjbd0v7CV8l8mkN4"}}', NULL, '2026-04-09 11:38:18', '2026-04-09 11:38:18'),
	(4, 'default', 'updated', 'App\\Models\\User', 'updated', 8, 'App\\Models\\User', 8, '{"old": {"remember_token": "86H17xgxfst6nQHpWBRMJoiTqFcJCLHu8eVs1DjGi1g0dPidawRnv8CxsrDW"}, "attributes": {"remember_token": "CHO381Q6jaIalwxg0h3MS300YtVYqNVuisF3nY5Z9EN7Rwhn7lVQvbunkrkk"}}', NULL, '2026-04-09 19:20:31', '2026-04-09 19:20:31'),
	(5, 'default', 'updated', 'App\\Models\\User', 'updated', 7, 'App\\Models\\User', 7, '{"old": {"remember_token": "d8aWcdfTWUmf6clkxeDlRwpBRgbovOAaCmgXs6MMjspdmjbd0v7CV8l8mkN4"}, "attributes": {"remember_token": "ih0BVecKqdliCkuF6XDYFt9dS6Hrl0IkeWgnMkdjLMoarZ998l5f4avea1n6"}}', NULL, '2026-04-09 20:17:58', '2026-04-09 20:17:58'),
	(6, 'default', 'updated', 'App\\Models\\User', 'updated', 8, 'App\\Models\\User', 8, '{"old": {"remember_token": "CHO381Q6jaIalwxg0h3MS300YtVYqNVuisF3nY5Z9EN7Rwhn7lVQvbunkrkk"}, "attributes": {"remember_token": "fHJcESEzi3mpVbREOGgQFofqX8CnAGQhkKBlqTQPDXWXnalx1nxHPrOpmPaJ"}}', NULL, '2026-04-09 20:48:43', '2026-04-09 20:48:43'),
	(7, 'default', 'created', 'App\\Models\\User', 'created', 9, NULL, NULL, '{"attributes": {"id": 9, "otp": null, "name": "Arda Guller", "email": "alfajarislamia@gmail.com", "phone": "085161709807", "password": "$2y$12$OnANbKX2rXe9Us2R90Rza.j0fOCHs8EG8bL51WiBpqPLFvdG4sFl.", "google_id": null, "created_at": "2026-04-18T06:14:07.000000Z", "updated_at": "2026-04-18T06:14:07.000000Z", "foto_profil": null, "fokus_budidaya": null, "otp_expired_at": null, "remember_token": null, "email_verified_at": null}}', NULL, '2026-04-17 23:14:08', '2026-04-17 23:14:08'),
	(8, 'default', 'updated', 'App\\Models\\User', 'updated', 9, NULL, NULL, '{"old": {"otp": null, "updated_at": "2026-04-18T06:14:07.000000Z", "otp_expired_at": null}, "attributes": {"otp": "607964", "updated_at": "2026-04-18T06:14:23.000000Z", "otp_expired_at": "2026-04-18 06:19:23"}}', NULL, '2026-04-17 23:14:23', '2026-04-17 23:14:23'),
	(9, 'default', 'updated', 'App\\Models\\User', 'updated', 9, NULL, NULL, '{"old": {"otp": "607964", "updated_at": "2026-04-18T06:14:23.000000Z", "otp_expired_at": "2026-04-18 06:19:23", "email_verified_at": null}, "attributes": {"otp": null, "updated_at": "2026-04-18T06:15:36.000000Z", "otp_expired_at": null, "email_verified_at": "2026-04-18T06:15:36.000000Z"}}', NULL, '2026-04-17 23:15:36', '2026-04-17 23:15:36'),
	(10, 'default', 'created', 'App\\Models\\User', 'created', 10, NULL, NULL, '{"attributes": {"id": 10, "otp": null, "name": "Mesut Ozil", "email": null, "phone": "08984983983", "password": "$2y$12$ucdUM5KAvehPACt9/M.XjeO2SnwDrAMZlWUcBPdSuUOUYab3gMVXa", "google_id": null, "created_at": "2026-04-18T06:16:57.000000Z", "updated_at": "2026-04-18T06:16:57.000000Z", "foto_profil": null, "fokus_budidaya": null, "otp_expired_at": null, "remember_token": null, "email_verified_at": null}}', NULL, '2026-04-17 23:16:57', '2026-04-17 23:16:57'),
	(11, 'default', 'updated', 'App\\Models\\User', 'updated', 10, NULL, NULL, '{"old": {"otp": null, "updated_at": "2026-04-18T06:16:57.000000Z", "otp_expired_at": null}, "attributes": {"otp": "404383", "updated_at": "2026-04-18T06:17:00.000000Z", "otp_expired_at": "2026-04-18 06:22:00"}}', NULL, '2026-04-17 23:17:00', '2026-04-17 23:17:00'),
	(12, 'default', 'updated', 'App\\Models\\User', 'updated', 10, NULL, NULL, '{"old": {"otp": "404383", "updated_at": "2026-04-18T06:17:00.000000Z", "otp_expired_at": "2026-04-18 06:22:00", "email_verified_at": null}, "attributes": {"otp": null, "updated_at": "2026-04-18T06:17:39.000000Z", "otp_expired_at": null, "email_verified_at": "2026-04-18T06:17:39.000000Z"}}', NULL, '2026-04-17 23:17:39', '2026-04-17 23:17:39'),
	(13, 'default', 'updated', 'App\\Models\\User', 'updated', 1, NULL, NULL, '{"old": {"otp": null, "updated_at": "2026-04-09T17:35:49.000000Z", "otp_expired_at": null}, "attributes": {"otp": "466140", "updated_at": "2026-04-18T06:21:08.000000Z", "otp_expired_at": "2026-04-18 06:26:08"}}', NULL, '2026-04-17 23:21:08', '2026-04-17 23:21:08'),
	(14, 'default', 'updated', 'App\\Models\\User', 'updated', 1, NULL, NULL, '{"old": {"otp": "466140", "updated_at": "2026-04-18T06:21:08.000000Z", "otp_expired_at": "2026-04-18 06:26:08"}, "attributes": {"otp": null, "updated_at": "2026-04-18T06:21:32.000000Z", "otp_expired_at": null}}', NULL, '2026-04-17 23:21:32', '2026-04-17 23:21:32'),
	(15, 'default', 'updated', 'App\\Models\\User', 'updated', 9, NULL, NULL, '{"old": {"google_id": null, "updated_at": "2026-04-18T06:15:36.000000Z"}, "attributes": {"google_id": "115330693542069218270", "updated_at": "2026-04-18T06:23:28.000000Z"}}', NULL, '2026-04-17 23:23:28', '2026-04-17 23:23:28'),
	(16, 'default', 'updated', 'App\\Models\\User', 'updated', 9, NULL, NULL, '{"old": {"otp": null, "updated_at": "2026-04-18T06:23:28.000000Z", "otp_expired_at": null}, "attributes": {"otp": "902230", "updated_at": "2026-04-18T06:58:23.000000Z", "otp_expired_at": "2026-04-18 07:03:23"}}', NULL, '2026-04-17 23:58:23', '2026-04-17 23:58:23'),
	(17, 'default', 'updated', 'App\\Models\\User', 'updated', 9, NULL, NULL, '{"old": {"otp": "902230", "password": "$2y$12$OnANbKX2rXe9Us2R90Rza.j0fOCHs8EG8bL51WiBpqPLFvdG4sFl.", "updated_at": "2026-04-18T06:58:23.000000Z", "otp_expired_at": "2026-04-18 07:03:23"}, "attributes": {"otp": null, "password": "$2y$12$Rv6OSwfs.0.C3f/xgdTwvedC.j5PtZI/dp.vOBTWFkA8BW3/4IE3q", "updated_at": "2026-04-18T06:59:09.000000Z", "otp_expired_at": null}}', NULL, '2026-04-17 23:59:10', '2026-04-17 23:59:10'),
	(18, 'default', 'updated', 'App\\Models\\User', 'updated', 9, NULL, NULL, '{"old": {"otp": null, "updated_at": "2026-04-18T06:59:09.000000Z", "otp_expired_at": null}, "attributes": {"otp": "689198", "updated_at": "2026-04-18T07:07:03.000000Z", "otp_expired_at": "2026-04-18 07:12:03"}}', NULL, '2026-04-18 00:07:03', '2026-04-18 00:07:03'),
	(19, 'default', 'updated', 'App\\Models\\User', 'updated', 9, NULL, NULL, '{"old": {"otp": "689198", "password": "$2y$12$Rv6OSwfs.0.C3f/xgdTwvedC.j5PtZI/dp.vOBTWFkA8BW3/4IE3q", "updated_at": "2026-04-18T07:07:03.000000Z", "otp_expired_at": "2026-04-18 07:12:03"}, "attributes": {"otp": null, "password": "$2y$12$.ujKaymeeL7FMMF7St8hJOg4DAlUCJi6JA6G0BeiljC.KDxpkiRFK", "updated_at": "2026-04-18T07:07:48.000000Z", "otp_expired_at": null}}', NULL, '2026-04-18 00:07:48', '2026-04-18 00:07:48'),
	(20, 'default', 'updated', 'App\\Models\\User', 'updated', 9, NULL, NULL, '{"old": {"otp": null, "updated_at": "2026-04-18T07:07:48.000000Z", "otp_expired_at": null}, "attributes": {"otp": "355293", "updated_at": "2026-04-18T07:09:56.000000Z", "otp_expired_at": "2026-04-18 07:14:56"}}', NULL, '2026-04-18 00:09:56', '2026-04-18 00:09:56'),
	(21, 'default', 'updated', 'App\\Models\\User', 'updated', 9, NULL, NULL, '{"old": {"otp": "355293", "password": "$2y$12$.ujKaymeeL7FMMF7St8hJOg4DAlUCJi6JA6G0BeiljC.KDxpkiRFK", "updated_at": "2026-04-18T07:09:56.000000Z", "otp_expired_at": "2026-04-18 07:14:56"}, "attributes": {"otp": null, "password": "$2y$12$jRxaAiUUGJ/BYLjCrWyNaODKYJsRHGGl2CBfVxBhyYOwxIgAgVH9q", "updated_at": "2026-04-18T07:11:08.000000Z", "otp_expired_at": null}}', NULL, '2026-04-18 00:11:08', '2026-04-18 00:11:08'),
	(22, 'default', 'updated', 'App\\Models\\User', 'updated', 9, NULL, NULL, '{"old": {"otp": null, "updated_at": "2026-04-18T07:11:08.000000Z", "otp_expired_at": null}, "attributes": {"otp": "981538", "updated_at": "2026-04-18T07:23:32.000000Z", "otp_expired_at": "2026-04-18 07:28:32"}}', NULL, '2026-04-18 00:23:32', '2026-04-18 00:23:32'),
	(23, 'default', 'updated', 'App\\Models\\User', 'updated', 9, NULL, NULL, '{"old": {"otp": "981538", "password": "$2y$12$jRxaAiUUGJ/BYLjCrWyNaODKYJsRHGGl2CBfVxBhyYOwxIgAgVH9q", "updated_at": "2026-04-18T07:23:32.000000Z", "otp_expired_at": "2026-04-18 07:28:32"}, "attributes": {"otp": null, "password": "$2y$12$MRvX.oBM8xAYhatNwW5EPe660AED.HbbGcsqdFZkgKT3Q79iPbR8q", "updated_at": "2026-04-18T07:24:00.000000Z", "otp_expired_at": null}}', NULL, '2026-04-18 00:24:00', '2026-04-18 00:24:00'),
	(24, 'default', 'updated', 'App\\Models\\User', 'updated', 9, NULL, NULL, '{"old": {"otp": null, "updated_at": "2026-04-18T07:24:00.000000Z", "otp_expired_at": null}, "attributes": {"otp": "447960", "updated_at": "2026-04-18T07:26:41.000000Z", "otp_expired_at": "2026-04-18 07:31:41"}}', NULL, '2026-04-18 00:26:41', '2026-04-18 00:26:41'),
	(25, 'default', 'updated', 'App\\Models\\User', 'updated', 9, NULL, NULL, '{"old": {"otp": "447960", "updated_at": "2026-04-18T07:26:41.000000Z", "otp_expired_at": "2026-04-18 07:31:41"}, "attributes": {"otp": "378961", "updated_at": "2026-04-18T07:28:14.000000Z", "otp_expired_at": "2026-04-18 07:33:14"}}', NULL, '2026-04-18 00:28:14', '2026-04-18 00:28:14'),
	(26, 'default', 'updated', 'App\\Models\\User', 'updated', 9, NULL, NULL, '{"old": {"otp": "378961", "password": "$2y$12$MRvX.oBM8xAYhatNwW5EPe660AED.HbbGcsqdFZkgKT3Q79iPbR8q", "updated_at": "2026-04-18T07:28:14.000000Z", "otp_expired_at": "2026-04-18 07:33:14"}, "attributes": {"otp": null, "password": "$2y$12$3d6YIyDsgcQK5Cot7jI2DOfkmPA7qYtjnvXn18VZ.OYCnxCIhSt1e", "updated_at": "2026-04-18T07:28:42.000000Z", "otp_expired_at": null}}', NULL, '2026-04-18 00:28:42', '2026-04-18 00:28:42'),
	(27, 'default', 'updated', 'App\\Models\\User', 'updated', 9, NULL, NULL, '{"old": {"otp": null, "updated_at": "2026-04-18T07:28:42.000000Z", "otp_expired_at": null}, "attributes": {"otp": "136169", "updated_at": "2026-04-18T07:31:21.000000Z", "otp_expired_at": "2026-04-18 07:36:21"}}', NULL, '2026-04-18 00:31:22', '2026-04-18 00:31:22'),
	(28, 'default', 'updated', 'App\\Models\\User', 'updated', 9, NULL, NULL, '{"old": {"otp": "136169", "password": "$2y$12$3d6YIyDsgcQK5Cot7jI2DOfkmPA7qYtjnvXn18VZ.OYCnxCIhSt1e", "updated_at": "2026-04-18T07:31:21.000000Z", "otp_expired_at": "2026-04-18 07:36:21"}, "attributes": {"otp": null, "password": "$2y$12$UzIZ.U2Fkd/KZ/dpGg0rtuRTDr6TzpoAJ92FE1vMotnK.BqF6Jww6", "updated_at": "2026-04-18T07:31:43.000000Z", "otp_expired_at": null}}', NULL, '2026-04-18 00:31:43', '2026-04-18 00:31:43'),
	(29, 'default', 'updated', 'App\\Models\\User', 'updated', 9, NULL, NULL, '{"old": {"otp": null, "updated_at": "2026-04-18T07:31:43.000000Z", "otp_expired_at": null}, "attributes": {"otp": "464754", "updated_at": "2026-04-18T07:32:06.000000Z", "otp_expired_at": "2026-04-18 07:37:06"}}', NULL, '2026-04-18 00:32:06', '2026-04-18 00:32:06'),
	(30, 'default', 'updated', 'App\\Models\\User', 'updated', 7, 'App\\Models\\User', 7, '{"old": {"remember_token": "ih0BVecKqdliCkuF6XDYFt9dS6Hrl0IkeWgnMkdjLMoarZ998l5f4avea1n6"}, "attributes": {"remember_token": "a1sF2StnubuAXHLMK0hy4dE2RxY1CWSkZyJDKzFLs79kKQZt68NJqItUDuEO"}}', NULL, '2026-04-18 19:26:09', '2026-04-18 19:26:09'),
	(31, 'default', 'deleted', 'App\\Models\\User', 'deleted', 1, 'App\\Models\\User', 8, '{"old": {"id": 1, "otp": null, "name": "Kevin Diks", "email": null, "phone": "085321820026", "password": "$2y$12$J/Ap3PCGPC//Kv0ZQZ9Va.W6IG8o7hOlIwntCkoRVKZkK2USfb0bm", "google_id": null, "created_at": "2026-04-04T11:04:45.000000Z", "updated_at": "2026-04-18T06:21:32.000000Z", "foto_profil": null, "fokus_budidaya": null, "otp_expired_at": null, "remember_token": null, "email_verified_at": null}}', NULL, '2026-04-19 08:43:25', '2026-04-19 08:43:25'),
	(32, 'default', 'deleted', 'App\\Models\\User', 'deleted', 2, 'App\\Models\\User', 8, '{"old": {"id": 2, "otp": null, "name": "Jay", "email": null, "phone": "123", "password": "$2y$12$jQfP90lbwxldgVRhFpQ.bO6SA9bEjPsxd1Nz/m3PRtDqV2Caqjpra", "google_id": null, "created_at": "2026-04-04T11:05:27.000000Z", "updated_at": "2026-04-04T11:05:27.000000Z", "foto_profil": null, "fokus_budidaya": null, "otp_expired_at": null, "remember_token": null, "email_verified_at": null}}', NULL, '2026-04-19 08:45:07', '2026-04-19 08:45:07'),
	(33, 'default', 'deleted', 'App\\Models\\User', 'deleted', 3, 'App\\Models\\User', 8, '{"old": {"id": 3, "otp": null, "name": "Yamal", "email": null, "phone": "087123456789", "password": "$2y$12$fzrm4Hm.WseYWHjRkHzEJeEdfKmb8DjJRtQtza30XlHIxpQWyrlRK", "google_id": null, "created_at": "2026-04-04T11:14:49.000000Z", "updated_at": "2026-04-04T11:14:49.000000Z", "foto_profil": null, "fokus_budidaya": null, "otp_expired_at": null, "remember_token": null, "email_verified_at": null}}', NULL, '2026-04-19 08:45:07', '2026-04-19 08:45:07'),
	(34, 'default', 'deleted', 'App\\Models\\User', 'deleted', 4, 'App\\Models\\User', 8, '{"old": {"id": 4, "otp": null, "name": "Alfajar", "email": "alfajarislamiakbar@gmail.com", "phone": "GGL-177536432254", "password": "$2y$12$wcavxRVu7APfwUN7EXD4YurARHyf/cbTzAz48emoy3fuOAzrtjhiG", "google_id": "110855269850310652319", "created_at": "2026-04-05T04:45:22.000000Z", "updated_at": "2026-04-05T05:11:18.000000Z", "foto_profil": "profil-fotos/wV7YsB03E8e2KyvFvLWO7ilKLDCqoHVYwCJWGzvG.jpg", "fokus_budidaya": null, "otp_expired_at": null, "remember_token": null, "email_verified_at": null}}', NULL, '2026-04-19 08:45:07', '2026-04-19 08:45:07'),
	(35, 'default', 'deleted', 'App\\Models\\User', 'deleted', 5, 'App\\Models\\User', 8, '{"old": {"id": 5, "otp": null, "name": "Vadesta", "email": null, "phone": "089789456321", "password": "$2y$12$tbRZUrsSrKkh2ugPaGP1ieLicY3bv1ff5V.0QeAHYFIItHVyw1bS6", "google_id": null, "created_at": "2026-04-05T04:51:00.000000Z", "updated_at": "2026-04-05T04:51:00.000000Z", "foto_profil": null, "fokus_budidaya": null, "otp_expired_at": null, "remember_token": null, "email_verified_at": null}}', NULL, '2026-04-19 08:45:07', '2026-04-19 08:45:07'),
	(36, 'default', 'deleted', 'App\\Models\\User', 'deleted', 6, 'App\\Models\\User', 8, '{"old": {"id": 6, "otp": null, "name": "Zaki", "email": "wa_1775371066@aquara.com", "phone": "083142373332", "password": "$2y$12$Z1rtcL9a1MkIfgXMYJuxOup829a7q37UUDGNewMV/76MCptQP99ke", "google_id": null, "created_at": "2026-04-05T06:37:47.000000Z", "updated_at": "2026-04-05T07:18:37.000000Z", "foto_profil": "profil-fotos/8DXS18vFZSeKHvnCBgnd0Xb0J5e0O078UZFCgRq1.jpg", "fokus_budidaya": null, "otp_expired_at": null, "remember_token": null, "email_verified_at": null}}', NULL, '2026-04-19 08:45:07', '2026-04-19 08:45:07'),
	(37, 'default', 'deleted', 'App\\Models\\User', 'deleted', 9, 'App\\Models\\User', 8, '{"old": {"id": 9, "otp": "464754", "name": "Arda Guller", "email": "alfajarislamia@gmail.com", "phone": "085161709807", "password": "$2y$12$UzIZ.U2Fkd/KZ/dpGg0rtuRTDr6TzpoAJ92FE1vMotnK.BqF6Jww6", "google_id": "115330693542069218270", "created_at": "2026-04-18T06:14:07.000000Z", "updated_at": "2026-04-18T07:32:06.000000Z", "foto_profil": null, "fokus_budidaya": null, "otp_expired_at": "2026-04-18 07:37:06", "remember_token": null, "email_verified_at": "2026-04-18T06:15:36.000000Z"}}', NULL, '2026-04-19 08:45:07', '2026-04-19 08:45:07'),
	(38, 'default', 'deleted', 'App\\Models\\User', 'deleted', 10, 'App\\Models\\User', 8, '{"old": {"id": 10, "otp": null, "name": "Mesut Ozil", "email": null, "phone": "08984983983", "password": "$2y$12$ucdUM5KAvehPACt9/M.XjeO2SnwDrAMZlWUcBPdSuUOUYab3gMVXa", "google_id": null, "created_at": "2026-04-18T06:16:57.000000Z", "updated_at": "2026-04-18T06:17:39.000000Z", "foto_profil": null, "fokus_budidaya": null, "otp_expired_at": null, "remember_token": null, "email_verified_at": "2026-04-18T06:17:39.000000Z"}}', NULL, '2026-04-19 08:45:08', '2026-04-19 08:45:08'),
	(39, 'default', 'created', 'App\\Models\\User', 'created', 11, NULL, NULL, '{"attributes": {"id": 11, "otp": null, "name": "Alfajar", "email": null, "phone": "085321820026", "password": "$2y$12$r34HR0HiVOy6BnahGKnrfOS4mmUq23efBkfpU4NBQfvsJXzQs6Ohy", "google_id": null, "created_at": "2026-04-19T15:46:15.000000Z", "updated_at": "2026-04-19T15:46:15.000000Z", "foto_profil": null, "fokus_budidaya": null, "otp_expired_at": null, "remember_token": null, "email_verified_at": null}}', NULL, '2026-04-19 08:46:16', '2026-04-19 08:46:16'),
	(40, 'default', 'updated', 'App\\Models\\User', 'updated', 11, NULL, NULL, '{"old": {"otp": null, "updated_at": "2026-04-19T15:46:15.000000Z", "otp_expired_at": null}, "attributes": {"otp": "948874", "updated_at": "2026-04-19T15:51:39.000000Z", "otp_expired_at": "2026-04-19 15:56:39"}}', NULL, '2026-04-19 08:51:39', '2026-04-19 08:51:39'),
	(41, 'default', 'updated', 'App\\Models\\User', 'updated', 11, NULL, NULL, '{"old": {"otp": "948874", "updated_at": "2026-04-19T15:51:39.000000Z", "otp_expired_at": "2026-04-19 15:56:39"}, "attributes": {"otp": null, "updated_at": "2026-04-19T15:52:02.000000Z", "otp_expired_at": null}}', NULL, '2026-04-19 08:52:02', '2026-04-19 08:52:02'),
	(42, 'default', 'updated', 'App\\Models\\User', 'updated', 11, 'App\\Models\\User', 11, '{"old": {"otp": null, "updated_at": "2026-04-19T15:52:02.000000Z", "otp_expired_at": null}, "attributes": {"otp": "493114", "updated_at": "2026-04-19T16:49:39.000000Z", "otp_expired_at": "2026-04-19 16:54:39"}}', NULL, '2026-04-19 09:49:39', '2026-04-19 09:49:39'),
	(43, 'default', 'updated', 'App\\Models\\User', 'updated', 11, 'App\\Models\\User', 11, '{"old": {"otp": "493114", "email": null, "phone": "085321820026", "updated_at": "2026-04-19T16:49:39.000000Z", "otp_expired_at": "2026-04-19 16:54:39"}, "attributes": {"otp": null, "email": "alfajarislamia@gmail.com", "phone": "085161709807", "updated_at": "2026-04-19T16:50:27.000000Z", "otp_expired_at": null}}', NULL, '2026-04-19 09:50:27', '2026-04-19 09:50:27'),
	(44, 'default', 'updated', 'App\\Models\\User', 'updated', 11, NULL, NULL, '{"old": {"otp": null, "updated_at": "2026-04-19T16:50:27.000000Z", "otp_expired_at": null}, "attributes": {"otp": "692652", "updated_at": "2026-04-19T16:53:03.000000Z", "otp_expired_at": "2026-04-19 16:58:03"}}', NULL, '2026-04-19 09:53:03', '2026-04-19 09:53:03'),
	(45, 'default', 'updated', 'App\\Models\\User', 'updated', 11, NULL, NULL, '{"old": {"otp": "692652", "updated_at": "2026-04-19T16:53:03.000000Z", "otp_expired_at": "2026-04-19 16:58:03"}, "attributes": {"otp": null, "updated_at": "2026-04-19T16:53:31.000000Z", "otp_expired_at": null}}', NULL, '2026-04-19 09:53:31', '2026-04-19 09:53:31'),
	(46, 'default', 'updated', 'App\\Models\\User', 'updated', 11, 'App\\Models\\User', 11, '{"old": {"otp": null, "updated_at": "2026-04-19T16:53:31.000000Z", "otp_expired_at": null}, "attributes": {"otp": "800684", "updated_at": "2026-04-19T16:55:30.000000Z", "otp_expired_at": "2026-04-19 17:00:30"}}', NULL, '2026-04-19 09:55:30', '2026-04-19 09:55:30'),
	(47, 'default', 'updated', 'App\\Models\\User', 'updated', 11, 'App\\Models\\User', 11, '{"old": {"otp": "800684", "email": "alfajarislamia@gmail.com", "updated_at": "2026-04-19T16:55:30.000000Z", "otp_expired_at": "2026-04-19 17:00:30"}, "attributes": {"otp": null, "email": "sansaelahh1@gmail.com", "updated_at": "2026-04-19T16:56:14.000000Z", "otp_expired_at": null}}', NULL, '2026-04-19 09:56:14', '2026-04-19 09:56:14'),
	(48, 'default', 'updated', 'App\\Models\\User', 'updated', 11, NULL, NULL, '{"old": {"google_id": null, "updated_at": "2026-04-19T16:56:14.000000Z"}, "attributes": {"google_id": "104268554757363368456", "updated_at": "2026-04-19T16:57:49.000000Z"}}', NULL, '2026-04-19 09:57:49', '2026-04-19 09:57:49'),
	(49, 'default', 'updated', 'App\\Models\\User', 'updated', 11, NULL, NULL, '{"old": {"otp": null, "updated_at": "2026-04-19T16:57:49.000000Z", "otp_expired_at": null}, "attributes": {"otp": "224106", "updated_at": "2026-04-19T16:59:25.000000Z", "otp_expired_at": "2026-04-19 17:04:25"}}', NULL, '2026-04-19 09:59:25', '2026-04-19 09:59:25'),
	(50, 'default', 'updated', 'App\\Models\\User', 'updated', 11, NULL, NULL, '{"old": {"otp": "224106", "updated_at": "2026-04-19T16:59:25.000000Z", "otp_expired_at": "2026-04-19 17:04:25"}, "attributes": {"otp": null, "updated_at": "2026-04-19T16:59:52.000000Z", "otp_expired_at": null}}', NULL, '2026-04-19 09:59:53', '2026-04-19 09:59:53'),
	(51, 'default', 'updated', 'App\\Models\\User', 'updated', 11, 'App\\Models\\User', 11, '{"old": {"otp": null, "updated_at": "2026-04-19T16:59:52.000000Z", "otp_expired_at": null}, "attributes": {"otp": "590600", "updated_at": "2026-04-19T17:01:22.000000Z", "otp_expired_at": "2026-04-19 17:06:22"}}', NULL, '2026-04-19 10:01:22', '2026-04-19 10:01:22'),
	(52, 'default', 'deleted', 'App\\Models\\User', 'deleted', 11, 'App\\Models\\User', 11, '{"old": {"id": 11, "otp": "590600", "name": "Alfajar", "email": "sansaelahh1@gmail.com", "phone": "085161709807", "password": "$2y$12$r34HR0HiVOy6BnahGKnrfOS4mmUq23efBkfpU4NBQfvsJXzQs6Ohy", "google_id": "104268554757363368456", "created_at": "2026-04-19T15:46:15.000000Z", "updated_at": "2026-04-19T17:01:22.000000Z", "foto_profil": null, "fokus_budidaya": null, "otp_expired_at": "2026-04-19 17:06:22", "remember_token": null, "email_verified_at": null}}', NULL, '2026-04-19 10:02:26', '2026-04-19 10:02:26'),
	(53, 'default', 'created', 'App\\Models\\User', 'created', 12, NULL, NULL, '{"attributes": {"id": 12, "otp": null, "name": "Hasan Toys", "email": null, "phone": "085161709807", "password": "$2y$12$93jEWI6CzwQ6dokLklSrMu/2iJ3ki8g6aWg4NgP7DMKstNDCkUwuS", "google_id": null, "created_at": "2026-04-19T17:03:32.000000Z", "updated_at": "2026-04-19T17:03:32.000000Z", "foto_profil": null, "fokus_budidaya": null, "otp_expired_at": null, "remember_token": null, "email_verified_at": null}}', NULL, '2026-04-19 10:03:32', '2026-04-19 10:03:32'),
	(54, 'default', 'updated', 'App\\Models\\User', 'updated', 12, NULL, NULL, '{"old": {"otp": null, "updated_at": "2026-04-19T17:03:32.000000Z", "otp_expired_at": null}, "attributes": {"otp": "509414", "updated_at": "2026-04-19T17:03:45.000000Z", "otp_expired_at": "2026-04-19 17:08:45"}}', NULL, '2026-04-19 10:03:45', '2026-04-19 10:03:45'),
	(55, 'default', 'updated', 'App\\Models\\User', 'updated', 12, NULL, NULL, '{"old": {"otp": "509414", "updated_at": "2026-04-19T17:03:45.000000Z", "otp_expired_at": "2026-04-19 17:08:45", "email_verified_at": null}, "attributes": {"otp": null, "updated_at": "2026-04-19T17:04:19.000000Z", "otp_expired_at": null, "email_verified_at": "2026-04-19T17:04:19.000000Z"}}', NULL, '2026-04-19 10:04:19', '2026-04-19 10:04:19'),
	(56, 'default', 'updated', 'App\\Models\\User', 'updated', 12, 'App\\Models\\User', 12, '{"old": {"otp": null, "updated_at": "2026-04-19T17:04:19.000000Z", "otp_expired_at": null}, "attributes": {"otp": "644771", "updated_at": "2026-04-19T17:04:57.000000Z", "otp_expired_at": "2026-04-19 17:09:57"}}', NULL, '2026-04-19 10:04:57', '2026-04-19 10:04:57'),
	(57, 'default', 'deleted', 'App\\Models\\User', 'deleted', 12, 'App\\Models\\User', 12, '{"old": {"id": 12, "otp": "644771", "name": "Hasan Toys", "email": null, "phone": "085161709807", "password": "$2y$12$93jEWI6CzwQ6dokLklSrMu/2iJ3ki8g6aWg4NgP7DMKstNDCkUwuS", "google_id": null, "created_at": "2026-04-19T17:03:32.000000Z", "updated_at": "2026-04-19T17:04:57.000000Z", "foto_profil": null, "fokus_budidaya": null, "otp_expired_at": "2026-04-19 17:09:57", "remember_token": null, "email_verified_at": "2026-04-19T17:04:19.000000Z"}}', NULL, '2026-04-19 10:05:35', '2026-04-19 10:05:35'),
	(58, 'default', 'created', 'App\\Models\\User', 'created', 13, NULL, NULL, '{"attributes": {"id": 13, "otp": null, "name": "Asnawi", "email": "alfajarislamia@gmail.com", "phone": "085321820026", "password": "$2y$12$cEaxOP220NaL7NCvn8vHeuynj9cgdp3bAqpU.j.KUag7jBVnSe9EK", "google_id": null, "created_at": "2026-04-19T17:06:45.000000Z", "updated_at": "2026-04-19T17:06:45.000000Z", "foto_profil": null, "fokus_budidaya": null, "otp_expired_at": null, "remember_token": null, "email_verified_at": null}}', NULL, '2026-04-19 10:06:45', '2026-04-19 10:06:45'),
	(59, 'default', 'updated', 'App\\Models\\User', 'updated', 13, NULL, NULL, '{"old": {"otp": null, "updated_at": "2026-04-19T17:06:45.000000Z", "otp_expired_at": null}, "attributes": {"otp": "126256", "updated_at": "2026-04-19T17:06:56.000000Z", "otp_expired_at": "2026-04-19 17:11:56"}}', NULL, '2026-04-19 10:06:56', '2026-04-19 10:06:56'),
	(60, 'default', 'updated', 'App\\Models\\User', 'updated', 13, NULL, NULL, '{"old": {"otp": "126256", "updated_at": "2026-04-19T17:06:56.000000Z", "otp_expired_at": "2026-04-19 17:11:56", "email_verified_at": null}, "attributes": {"otp": null, "updated_at": "2026-04-19T17:07:35.000000Z", "otp_expired_at": null, "email_verified_at": "2026-04-19T17:07:35.000000Z"}}', NULL, '2026-04-19 10:07:35', '2026-04-19 10:07:35'),
	(61, 'default', 'updated', 'App\\Models\\User', 'updated', 13, 'App\\Models\\User', 13, '{"old": {"otp": null, "updated_at": "2026-04-19T17:07:35.000000Z", "otp_expired_at": null}, "attributes": {"otp": "324818", "updated_at": "2026-04-19T17:08:56.000000Z", "otp_expired_at": "2026-04-19 17:13:56"}}', NULL, '2026-04-19 10:08:56', '2026-04-19 10:08:56'),
	(62, 'default', 'deleted', 'App\\Models\\User', 'deleted', 13, 'App\\Models\\User', 13, '{"old": {"id": 13, "otp": "324818", "name": "Asnawi", "email": "alfajarislamia@gmail.com", "phone": "085321820026", "password": "$2y$12$cEaxOP220NaL7NCvn8vHeuynj9cgdp3bAqpU.j.KUag7jBVnSe9EK", "google_id": null, "created_at": "2026-04-19T17:06:45.000000Z", "updated_at": "2026-04-19T17:08:56.000000Z", "foto_profil": null, "fokus_budidaya": null, "otp_expired_at": "2026-04-19 17:13:56", "remember_token": null, "email_verified_at": "2026-04-19T17:07:35.000000Z"}}', NULL, '2026-04-19 10:09:23', '2026-04-19 10:09:23'),
	(63, 'default', 'created', 'App\\Models\\User', 'created', 14, NULL, NULL, '{"attributes": {"id": 14, "otp": null, "name": "Arhan", "email": null, "phone": "085161709807", "password": "$2y$12$NNkZIE3XYtQadrCEMlfay.4pGjJ6fHsncff/zlwdVDQQXv/Zj2jhC", "google_id": null, "created_at": "2026-04-19T17:09:48.000000Z", "updated_at": "2026-04-19T17:09:48.000000Z", "foto_profil": null, "fokus_budidaya": null, "otp_expired_at": null, "remember_token": null, "email_verified_at": null}}', NULL, '2026-04-19 10:09:49', '2026-04-19 10:09:49'),
	(64, 'default', 'updated', 'App\\Models\\User', 'updated', 14, NULL, NULL, '{"old": {"otp": null, "updated_at": "2026-04-19T17:09:48.000000Z", "otp_expired_at": null}, "attributes": {"otp": "372382", "updated_at": "2026-04-19T17:09:57.000000Z", "otp_expired_at": "2026-04-19 17:14:57"}}', NULL, '2026-04-19 10:09:57', '2026-04-19 10:09:57'),
	(65, 'default', 'updated', 'App\\Models\\User', 'updated', 14, NULL, NULL, '{"old": {"otp": "372382", "updated_at": "2026-04-19T17:09:57.000000Z", "otp_expired_at": "2026-04-19 17:14:57", "email_verified_at": null}, "attributes": {"otp": null, "updated_at": "2026-04-19T17:10:29.000000Z", "otp_expired_at": null, "email_verified_at": "2026-04-19T17:10:29.000000Z"}}', NULL, '2026-04-19 10:10:29', '2026-04-19 10:10:29'),
	(66, 'default', 'updated', 'App\\Models\\User', 'updated', 14, 'App\\Models\\User', 14, '{"old": {"otp": null, "updated_at": "2026-04-19T17:10:29.000000Z", "otp_expired_at": null}, "attributes": {"otp": "700461", "updated_at": "2026-04-19T17:10:54.000000Z", "otp_expired_at": "2026-04-19 17:15:54"}}', NULL, '2026-04-19 10:10:54', '2026-04-19 10:10:54'),
	(67, 'default', 'updated', 'App\\Models\\User', 'updated', 14, 'App\\Models\\User', 14, '{"old": {"otp": "700461", "updated_at": "2026-04-19T17:10:54.000000Z", "otp_expired_at": "2026-04-19 17:15:54"}, "attributes": {"otp": "815449", "updated_at": "2026-04-19T17:11:04.000000Z", "otp_expired_at": "2026-04-19 17:16:04"}}', NULL, '2026-04-19 10:11:04', '2026-04-19 10:11:04'),
	(68, 'default', 'deleted', 'App\\Models\\User', 'deleted', 14, 'App\\Models\\User', 14, '{"old": {"id": 14, "otp": "815449", "name": "Arhan", "email": null, "phone": "085161709807", "password": "$2y$12$NNkZIE3XYtQadrCEMlfay.4pGjJ6fHsncff/zlwdVDQQXv/Zj2jhC", "google_id": null, "created_at": "2026-04-19T17:09:48.000000Z", "updated_at": "2026-04-19T17:11:04.000000Z", "foto_profil": null, "fokus_budidaya": null, "otp_expired_at": "2026-04-19 17:16:04", "remember_token": null, "email_verified_at": "2026-04-19T17:10:29.000000Z"}}', NULL, '2026-04-19 10:11:30', '2026-04-19 10:11:30'),
	(69, 'default', 'created', 'App\\Models\\User', 'created', 15, NULL, NULL, '{"attributes": {"id": 15, "otp": null, "name": "Denny Caknan", "email": "alfajarislamia@gmail.com", "phone": "085161709807", "password": "$2y$12$5a98.8ksUncXfCVYPIzUqOY7ni0e4skTDdrf8nMOn2e.cwHIOVQue", "google_id": null, "created_at": "2026-04-19T17:13:22.000000Z", "updated_at": "2026-04-19T17:13:22.000000Z", "foto_profil": null, "fokus_budidaya": null, "otp_expired_at": null, "remember_token": null, "email_verified_at": null}}', NULL, '2026-04-19 10:13:22', '2026-04-19 10:13:22'),
	(70, 'default', 'updated', 'App\\Models\\User', 'updated', 15, NULL, NULL, '{"old": {"otp": null, "updated_at": "2026-04-19T17:13:22.000000Z", "otp_expired_at": null}, "attributes": {"otp": "648829", "updated_at": "2026-04-19T17:13:30.000000Z", "otp_expired_at": "2026-04-19 17:18:30"}}', NULL, '2026-04-19 10:13:30', '2026-04-19 10:13:30'),
	(71, 'default', 'updated', 'App\\Models\\User', 'updated', 15, NULL, NULL, '{"old": {"otp": "648829", "updated_at": "2026-04-19T17:13:30.000000Z", "otp_expired_at": "2026-04-19 17:18:30", "email_verified_at": null}, "attributes": {"otp": null, "updated_at": "2026-04-19T17:14:07.000000Z", "otp_expired_at": null, "email_verified_at": "2026-04-19T17:14:07.000000Z"}}', NULL, '2026-04-19 10:14:07', '2026-04-19 10:14:07'),
	(72, 'default', 'updated', 'App\\Models\\User', 'updated', 15, 'App\\Models\\User', 15, '{"old": {"updated_at": "2026-04-19T17:14:07.000000Z", "fokus_budidaya": null}, "attributes": {"updated_at": "2026-04-20T02:51:24.000000Z", "fokus_budidaya": "Gurame, Lele Sangkuriang, Patin"}}', NULL, '2026-04-19 19:51:24', '2026-04-19 19:51:24'),
	(73, 'default', 'updated', 'App\\Models\\User', 'updated', 15, 'App\\Models\\User', 15, '{"old": {"updated_at": "2026-04-20T02:51:24.000000Z", "foto_profil": null}, "attributes": {"updated_at": "2026-04-20T02:51:57.000000Z", "foto_profil": "profil-fotos/pBTMfcnDH1669qzolb11kHyU1Ko5qeOjMZEHKyqJ.jpg"}}', NULL, '2026-04-19 19:51:57', '2026-04-19 19:51:57'),
	(74, 'default', 'created', 'App\\Models\\User', 'created', 16, NULL, NULL, '{"attributes": {"id": 16, "otp": null, "name": "Seva", "email": null, "phone": "087777426068", "password": "$2y$12$aQQVCRZPrU2IyXi96XGvbeLHkWpY8LcaPNz1hJb7bjt4N0fxRa52m", "google_id": null, "created_at": "2026-04-20T05:15:47.000000Z", "updated_at": "2026-04-20T05:15:47.000000Z", "foto_profil": null, "fokus_budidaya": null, "otp_expired_at": null, "remember_token": null, "email_verified_at": null}}', NULL, '2026-04-19 22:15:49', '2026-04-19 22:15:49'),
	(75, 'default', 'updated', 'App\\Models\\User', 'updated', 16, NULL, NULL, '{"old": {"otp": null, "updated_at": "2026-04-20T05:15:47.000000Z", "otp_expired_at": null}, "attributes": {"otp": "917828", "updated_at": "2026-04-20T05:15:57.000000Z", "otp_expired_at": "2026-04-20 05:20:57"}}', NULL, '2026-04-19 22:15:57', '2026-04-19 22:15:57'),
	(76, 'default', 'updated', 'App\\Models\\User', 'updated', 16, NULL, NULL, '{"old": {"otp": "917828", "updated_at": "2026-04-20T05:15:57.000000Z", "otp_expired_at": "2026-04-20 05:20:57", "email_verified_at": null}, "attributes": {"otp": null, "updated_at": "2026-04-20T05:16:39.000000Z", "otp_expired_at": null, "email_verified_at": "2026-04-20T05:16:39.000000Z"}}', NULL, '2026-04-19 22:16:39', '2026-04-19 22:16:39');

-- Dumping structure for table aquara_proyek2.ai_consultations
CREATE TABLE IF NOT EXISTS `ai_consultations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `disease_detected` text COLLATE utf8mb4_unicode_ci,
  `water_quality_status` text COLLATE utf8mb4_unicode_ci,
  `solution` text COLLATE utf8mb4_unicode_ci,
  `raw_ai_response` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ai_consultations_user_id_foreign` (`user_id`),
  CONSTRAINT `ai_consultations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.ai_consultations: ~6 rows (approximately)
INSERT INTO `ai_consultations` (`id`, `user_id`, `image_path`, `disease_detected`, `water_quality_status`, `solution`, `raw_ai_response`, `created_at`, `updated_at`) VALUES
	(1, NULL, 'consultations/xl0Y2HeJV4lmChblPbChAOTWIQU1rpIEMcyeEC9p.jpg', 'Dugaan kuat Epizootic Ulcerative Syndrome (EUS) atau infeksi bakteri sekunder (misalnya Aeromonas sp.) yang menyebabkan luka ulseratif parah.', 'Tidak dapat dideteksi langsung dari gambar karena tidak ada air kolam yang terlihat. Namun, penyakit seperti EUS seringkali dipicu atau diperparah oleh kondisi kualitas air yang buruk, seperti fluktuasi suhu, pH tidak stabil, kadar oksigen terlarut rendah, atau akumulasi bahan organik berlebih.', '1. Isolasi dan musnahkan ikan yang sakit parah untuk mencegah penyebaran. 2. Untuk ikan yang masih bisa diselamatkan, berikan perlakuan perendaman dengan larutan garam 1-2% (10-20 gram garam per liter air) selama 30-60 menit setiap hari selama 3-5 hari. 3. Oleskan larutan Povidone Iodine encer (1:10 dengan air) pada luka yang terbuka jika memungkinkan. 4. Lakukan penggantian air kolam secara bertahap (20-30% setiap hari) dengan air bersih yang telah diendapkan dan aerasi. 5. Tingkatkan aerasi di kolam untuk memastikan kadar oksigen terlarut optimal (>4 ppm). 6. Kurangi atau hentikan pemberian pakan untuk sementara waktu hingga kondisi ikan membaik dan kualitas air stabil. 7. Lakukan pengukuran rutin terhadap parameter kualitas air (pH, DO, amonia, nitrit) dan sesuaikan jika ada yang di luar batas optimal. 8. Lakukan desinfeksi kolam secara menyeluruh setelah panen atau jika terjadi kematian massal sebelum siklus budidaya berikutnya.', '{\n    "disease_detected": "Dugaan kuat Epizootic Ulcerative Syndrome (EUS) atau infeksi bakteri sekunder (misalnya Aeromonas sp.) yang menyebabkan luka ulseratif parah.",\n    "water_quality_status": "Tidak dapat dideteksi langsung dari gambar karena tidak ada air kolam yang terlihat. Namun, penyakit seperti EUS seringkali dipicu atau diperparah oleh kondisi kualitas air yang buruk, seperti fluktuasi suhu, pH tidak stabil, kadar oksigen terlarut rendah, atau akumulasi bahan organik berlebih.",\n    "solution": "1. Isolasi dan musnahkan ikan yang sakit parah untuk mencegah penyebaran. 2. Untuk ikan yang masih bisa diselamatkan, berikan perlakuan perendaman dengan larutan garam 1-2% (10-20 gram garam per liter air) selama 30-60 menit setiap hari selama 3-5 hari. 3. Oleskan larutan Povidone Iodine encer (1:10 dengan air) pada luka yang terbuka jika memungkinkan. 4. Lakukan penggantian air kolam secara bertahap (20-30% setiap hari) dengan air bersih yang telah diendapkan dan aerasi. 5. Tingkatkan aerasi di kolam untuk memastikan kadar oksigen terlarut optimal (>4 ppm). 6. Kurangi atau hentikan pemberian pakan untuk sementara waktu hingga kondisi ikan membaik dan kualitas air stabil. 7. Lakukan pengukuran rutin terhadap parameter kualitas air (pH, DO, amonia, nitrit) dan sesuaikan jika ada yang di luar batas optimal. 8. Lakukan desinfeksi kolam secara menyeluruh setelah panen atau jika terjadi kematian massal sebelum siklus budidaya berikutnya."\n}', '2026-04-19 12:32:41', '2026-04-19 12:32:41'),
	(2, NULL, 'consultations/5C40S4ua67slMQdENaVrJjFhK4nwhOZSCfHUeMG5.jpg', 'Dugaan Epizootic Ulcerative Syndrome (EUS) atau Penyakit Bintik Merah, ditandai dengan lesi ulseratif berwarna merah pada tubuh ikan.', 'Tidak dapat ditentukan dari gambar, namun kondisi penyakit ini sering diperparah oleh kualitas air yang buruk (misalnya pH rendah, suhu ekstrem, atau tingginya bahan organik).', '1. Pisahkan dan isolasi ikan yang menunjukkan gejala sakit untuk mencegah penyebaran lebih lanjut. 2. Lakukan penggantian air kolam secara parsial (20-30% setiap hari) untuk memperbaiki kualitas air. 3. Pastikan parameter kualitas air seperti pH (optimal 6.5-8.5), oksigen terlarut, dan amonia berada pada kondisi optimal. 4. Kurangi kepadatan tebar ikan. 5. Berikan pakan dengan suplemen vitamin C atau imunostimulan untuk meningkatkan daya tahan tubuh ikan. 6. Untuk ikan yang terinfeksi, dapat dilakukan perendaman dengan larutan garam dapur (NaCl) 1-2 ppt selama beberapa hari atau larutan kalium permanganat dengan dosis yang tepat (sesuai rekomendasi ahli) untuk membantu mengendalikan infeksi sekunder bakteri.', '{\n    "disease_detected": "Dugaan Epizootic Ulcerative Syndrome (EUS) atau Penyakit Bintik Merah, ditandai dengan lesi ulseratif berwarna merah pada tubuh ikan.",\n    "water_quality_status": "Tidak dapat ditentukan dari gambar, namun kondisi penyakit ini sering diperparah oleh kualitas air yang buruk (misalnya pH rendah, suhu ekstrem, atau tingginya bahan organik).",\n    "solution": "1. Pisahkan dan isolasi ikan yang menunjukkan gejala sakit untuk mencegah penyebaran lebih lanjut. 2. Lakukan penggantian air kolam secara parsial (20-30% setiap hari) untuk memperbaiki kualitas air. 3. Pastikan parameter kualitas air seperti pH (optimal 6.5-8.5), oksigen terlarut, dan amonia berada pada kondisi optimal. 4. Kurangi kepadatan tebar ikan. 5. Berikan pakan dengan suplemen vitamin C atau imunostimulan untuk meningkatkan daya tahan tubuh ikan. 6. Untuk ikan yang terinfeksi, dapat dilakukan perendaman dengan larutan garam dapur (NaCl) 1-2 ppt selama beberapa hari atau larutan kalium permanganat dengan dosis yang tepat (sesuai rekomendasi ahli) untuk membantu mengendalikan infeksi sekunder bakteri."\n}', '2026-04-19 19:38:12', '2026-04-19 19:38:12'),
	(3, NULL, 'consultations/lOzYflRoXB1AF8GPhMCsPX4hBf0JtmmrPhZxwnpd.jpg', 'Ulcerative Dermal Necrosis (UDN) atau infeksi bakteri (misalnya Aeromonas hydrophila) yang menyebabkan septicemia hemoragik.', 'Tidak terlihat langsung dari gambar, namun infeksi ini seringkali diperparah oleh kondisi kualitas air yang buruk, seperti kadar amonia tinggi, oksigen terlarut rendah, fluktuasi pH ekstrem, atau penumpukan bahan organik di kolam. Kondisi stres akibat kualitas air yang buruk dapat melemahkan imunitas ikan.', '1. **Isolasi dan Pemusnahan:** Segera pisahkan ikan yang menunjukkan gejala parah atau sudah mati dari kolam utama untuk mencegah penyebaran penyakit. Ikan yang mati sebaiknya dimusnahkan dengan cara yang higienis.2. **Perbaikan Kualitas Air:** Lakukan penggantian air sebagian (sekitar 30-50%) secara bertahap dan rutin selama beberapa hari. Periksa dan optimalkan parameter kualitas air seperti pH (idealnya 6.5-8.5), amonia (<0.01 ppm), nitrit (<0.1 ppm), dan oksigen terlarut (minimal 5 mg/L). Pastikan sistem aerasi berfungsi dengan baik untuk menjaga kadar oksigen.3. **Pengobatan Eksternal (Perendaman):** Jika memungkinkan, mandikan ikan yang sakit dalam larutan Kalium Permanganat (KMnO4) dengan konsentrasi 2-4 ppm selama 30-60 menit (pemandian jangka pendek) atau 0.5-1 ppm untuk perendaman lebih lama (8-12 jam), tergantung kondisi dan spesies ikan. Alternatif lain adalah larutan Formalin 25-30 ppm selama 30-60 menit, atau larutan garam dapur (NaCl) 5-10 ppt (5-10 kg/m3) untuk perendaman singkat (15-30 menit).4. **Pengobatan Internal (Melalui Pakan):** Untuk penanganan infeksi sistemik, berikan pakan yang dicampur antibiotik (misalnya Oxytetracycline dengan dosis 50-75 mg/kg biomassa ikan per hari selama 7-10 hari). Penggunaan antibiotik harus sesuai resep dan pengawasan dokter hewan akuatik untuk menghindari resistensi.5. **Peningkatan Imunitas:** Berikan pakan tambahan yang diperkaya vitamin (terutama Vitamin C) dan immunostimulan untuk meningkatkan daya tahan tubuh ikan.6. **Sanitasi Kolam:** Setelah wabah mereda, disarankan untuk mengeringkan kolam, melakukan pengapuran dasar kolam (menggunakan kapur pertanian atau kapur tohor) untuk desinfeksi, dan biarkan kolam kosong beberapa waktu sebelum diisi air kembali dan ditebar ikan baru.7. **Pencegahan:** Kelola kepadatan tebar yang optimal, berikan pakan berkualitas dan cukup nutrisi, serta lakukan monitoring kualitas air secara rutin. Hindari penanganan ikan yang kasar untuk mengurangi stres dan luka.', '{\n    "disease_detected": "Ulcerative Dermal Necrosis (UDN) atau infeksi bakteri (misalnya Aeromonas hydrophila) yang menyebabkan septicemia hemoragik.",\n    "water_quality_status": "Tidak terlihat langsung dari gambar, namun infeksi ini seringkali diperparah oleh kondisi kualitas air yang buruk, seperti kadar amonia tinggi, oksigen terlarut rendah, fluktuasi pH ekstrem, atau penumpukan bahan organik di kolam. Kondisi stres akibat kualitas air yang buruk dapat melemahkan imunitas ikan.",\n    "solution": "1. **Isolasi dan Pemusnahan:** Segera pisahkan ikan yang menunjukkan gejala parah atau sudah mati dari kolam utama untuk mencegah penyebaran penyakit. Ikan yang mati sebaiknya dimusnahkan dengan cara yang higienis.2. **Perbaikan Kualitas Air:** Lakukan penggantian air sebagian (sekitar 30-50%) secara bertahap dan rutin selama beberapa hari. Periksa dan optimalkan parameter kualitas air seperti pH (idealnya 6.5-8.5), amonia (<0.01 ppm), nitrit (<0.1 ppm), dan oksigen terlarut (minimal 5 mg/L). Pastikan sistem aerasi berfungsi dengan baik untuk menjaga kadar oksigen.3. **Pengobatan Eksternal (Perendaman):** Jika memungkinkan, mandikan ikan yang sakit dalam larutan Kalium Permanganat (KMnO4) dengan konsentrasi 2-4 ppm selama 30-60 menit (pemandian jangka pendek) atau 0.5-1 ppm untuk perendaman lebih lama (8-12 jam), tergantung kondisi dan spesies ikan. Alternatif lain adalah larutan Formalin 25-30 ppm selama 30-60 menit, atau larutan garam dapur (NaCl) 5-10 ppt (5-10 kg/m3) untuk perendaman singkat (15-30 menit).4. **Pengobatan Internal (Melalui Pakan):** Untuk penanganan infeksi sistemik, berikan pakan yang dicampur antibiotik (misalnya Oxytetracycline dengan dosis 50-75 mg/kg biomassa ikan per hari selama 7-10 hari). Penggunaan antibiotik harus sesuai resep dan pengawasan dokter hewan akuatik untuk menghindari resistensi.5. **Peningkatan Imunitas:** Berikan pakan tambahan yang diperkaya vitamin (terutama Vitamin C) dan immunostimulan untuk meningkatkan daya tahan tubuh ikan.6. **Sanitasi Kolam:** Setelah wabah mereda, disarankan untuk mengeringkan kolam, melakukan pengapuran dasar kolam (menggunakan kapur pertanian atau kapur tohor) untuk desinfeksi, dan biarkan kolam kosong beberapa waktu sebelum diisi air kembali dan ditebar ikan baru.7. **Pencegahan:** Kelola kepadatan tebar yang optimal, berikan pakan berkualitas dan cukup nutrisi, serta lakukan monitoring kualitas air secara rutin. Hindari penanganan ikan yang kasar untuk mengurangi stres dan luka."\n}', '2026-04-19 19:38:41', '2026-04-19 19:38:41'),
	(4, NULL, 'consultations/JAGUtaON0VujMxgDlXDn3fIug9cCz1FamoZIEqos.jpg', 'Tidak Terdeteksi', 'Tidak Terdeteksi', 'Maaf, gambar tidak valid. Harap unggah foto ikan atau air kolam yang jelas.', '{\n            "disease_detected": "Tidak Terdeteksi",\n            "water_quality_status": "Tidak Terdeteksi",\n            "solution": "Maaf, gambar tidak valid. Harap unggah foto ikan atau air kolam yang jelas."\n        }', '2026-04-19 19:39:05', '2026-04-19 19:39:05'),
	(5, NULL, 'consultations/BMeZnP0UxR8i76VouEfdzUEMFW1cXk9NCwOxmkah.jpg', 'Tidak Terdeteksi', 'Tidak Terdeteksi', 'Maaf, gambar tidak valid. Harap unggah foto ikan atau air kolam yang jelas.', '{\n            "disease_detected": "Tidak Terdeteksi",\n            "water_quality_status": "Tidak Terdeteksi",\n            "solution": "Maaf, gambar tidak valid. Harap unggah foto ikan atau air kolam yang jelas."\n        }', '2026-04-19 20:13:43', '2026-04-19 20:13:43'),
	(6, NULL, 'consultations/8onIjAiBBB2mo0zXs03lUt5Z0sNLHlR9NvYeXavD.jpg', 'Dugaan Infeksi Jamur (Saprolegniasis) dan Bakteri Sekunder', 'Tidak Terdeteksi dari foto, namun kualitas air yang buruk diduga menjadi faktor pemicu utama penyakit ini.', '1. **Isolasi Ikan Sakit:** Segera pisahkan ikan yang menunjukkan gejala untuk mencegah penularan ke ikan lain. 2. **Perbaiki Kualitas Air Kolam:** Lakukan pengecekan parameter kualitas air (pH, amonia, nitrit, oksigen terlarut, suhu). Lakukan penggantian air parsial secara bertahap (20-30%) untuk mengurangi beban organik dan toksin. Pastikan aerasi kolam optimal. 3. **Perendaman (Dipping/Bath Treatment):** Untuk penanganan awal, rendam ikan sakit dalam larutan garam dapur (non-yodium) konsentrasi 1-2 ppt (gram/liter) selama 30-60 menit per hari, selama 3-5 hari. Untuk infeksi jamur yang terlihat, bisa juga menggunakan larutan Methylene Blue dengan konsentrasi 1-2 ppm (mg/liter) sebagai perendaman jangka pendek (1 jam) di wadah terpisah. 4. **Pakan Berkualitas:** Berikan pakan yang mengandung vitamin dan mineral untuk meningkatkan imunitas ikan. 5. **Manajemen Kolam:** Hindari kepadatan tebar berlebihan dan bersihkan sisa pakan yang tidak termakan untuk menjaga kebersihan dasar kolam.', '{\n    "disease_detected": "Dugaan Infeksi Jamur (Saprolegniasis) dan Bakteri Sekunder",\n    "water_quality_status": "Tidak Terdeteksi dari foto, namun kualitas air yang buruk diduga menjadi faktor pemicu utama penyakit ini.",\n    "solution": "1. **Isolasi Ikan Sakit:** Segera pisahkan ikan yang menunjukkan gejala untuk mencegah penularan ke ikan lain. 2. **Perbaiki Kualitas Air Kolam:** Lakukan pengecekan parameter kualitas air (pH, amonia, nitrit, oksigen terlarut, suhu). Lakukan penggantian air parsial secara bertahap (20-30%) untuk mengurangi beban organik dan toksin. Pastikan aerasi kolam optimal. 3. **Perendaman (Dipping/Bath Treatment):** Untuk penanganan awal, rendam ikan sakit dalam larutan garam dapur (non-yodium) konsentrasi 1-2 ppt (gram/liter) selama 30-60 menit per hari, selama 3-5 hari. Untuk infeksi jamur yang terlihat, bisa juga menggunakan larutan Methylene Blue dengan konsentrasi 1-2 ppm (mg/liter) sebagai perendaman jangka pendek (1 jam) di wadah terpisah. 4. **Pakan Berkualitas:** Berikan pakan yang mengandung vitamin dan mineral untuk meningkatkan imunitas ikan. 5. **Manajemen Kolam:** Hindari kepadatan tebar berlebihan dan bersihkan sisa pakan yang tidak termakan untuk menjaga kebersihan dasar kolam."\n}', '2026-04-19 20:14:38', '2026-04-19 20:14:38');

-- Dumping structure for table aquara_proyek2.cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.cache: ~5 rows (approximately)
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
	('aquara-cache-backup-statuses', 'a:1:{i:0;a:8:{s:2:"id";i:0;s:4:"name";s:6:"AQUARA";s:4:"disk";s:5:"local";s:9:"reachable";b:1;s:7:"healthy";b:0;s:6:"amount";i:1;s:6:"newest";s:10:"1 week ago";s:11:"usedStorage";s:8:"21.26 MB";}}', 1776565875),
	('aquara-cache-backups-local', 'a:1:{i:0;a:4:{s:4:"disk";s:5:"local";s:4:"path";s:41:"AQUARA/only-files-2026-04-09-17-48-56.zip";s:4:"date";s:19:"2026-04-09 17:49:40";s:4:"size";s:8:"21.26 MB";}}', 1776565876),
	('aquara-cache-livewire-rate-limiter:a17961fa74e9275d529f489537f179c05d50c2f3', 'i:1;', 1776652724),
	('aquara-cache-livewire-rate-limiter:a17961fa74e9275d529f489537f179c05d50c2f3:timer', 'i:1776652724;', 1776652724),
	('aquara-cache-spatie.permission.cache', 'a:3:{s:5:"alias";a:4:{s:1:"a";s:2:"id";s:1:"b";s:4:"name";s:1:"c";s:10:"guard_name";s:1:"r";s:5:"roles";}s:11:"permissions";a:110:{i:0;a:4:{s:1:"a";i:1;s:1:"b";s:21:"view_ai::consultation";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:1;a:4:{s:1:"a";i:2;s:1:"b";s:25:"view_any_ai::consultation";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:2;a:4:{s:1:"a";i:3;s:1:"b";s:23:"create_ai::consultation";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:3;a:4:{s:1:"a";i:4;s:1:"b";s:23:"update_ai::consultation";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:4;a:4:{s:1:"a";i:5;s:1:"b";s:24:"restore_ai::consultation";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:5;a:4:{s:1:"a";i:6;s:1:"b";s:28:"restore_any_ai::consultation";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:6;a:4:{s:1:"a";i:7;s:1:"b";s:26:"replicate_ai::consultation";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:7;a:4:{s:1:"a";i:8;s:1:"b";s:24:"reorder_ai::consultation";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:8;a:4:{s:1:"a";i:9;s:1:"b";s:23:"delete_ai::consultation";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:9;a:4:{s:1:"a";i:10;s:1:"b";s:27:"delete_any_ai::consultation";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:10;a:4:{s:1:"a";i:11;s:1:"b";s:29:"force_delete_ai::consultation";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:11;a:4:{s:1:"a";i:12;s:1:"b";s:33:"force_delete_any_ai::consultation";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:12;a:4:{s:1:"a";i:13;s:1:"b";s:15:"view_fish::type";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:13;a:4:{s:1:"a";i:14;s:1:"b";s:19:"view_any_fish::type";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:14;a:4:{s:1:"a";i:15;s:1:"b";s:17:"create_fish::type";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:15;a:4:{s:1:"a";i:16;s:1:"b";s:17:"update_fish::type";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:16;a:4:{s:1:"a";i:17;s:1:"b";s:18:"restore_fish::type";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:17;a:4:{s:1:"a";i:18;s:1:"b";s:22:"restore_any_fish::type";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:18;a:4:{s:1:"a";i:19;s:1:"b";s:20:"replicate_fish::type";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:19;a:4:{s:1:"a";i:20;s:1:"b";s:18:"reorder_fish::type";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:20;a:4:{s:1:"a";i:21;s:1:"b";s:17:"delete_fish::type";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:21;a:4:{s:1:"a";i:22;s:1:"b";s:21:"delete_any_fish::type";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:22;a:4:{s:1:"a";i:23;s:1:"b";s:23:"force_delete_fish::type";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:23;a:4:{s:1:"a";i:24;s:1:"b";s:27:"force_delete_any_fish::type";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:24;a:4:{s:1:"a";i:25;s:1:"b";s:10:"view_pasar";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:25;a:4:{s:1:"a";i:26;s:1:"b";s:14:"view_any_pasar";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:26;a:4:{s:1:"a";i:27;s:1:"b";s:12:"create_pasar";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:27;a:4:{s:1:"a";i:28;s:1:"b";s:12:"update_pasar";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:28;a:4:{s:1:"a";i:29;s:1:"b";s:13:"restore_pasar";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:29;a:4:{s:1:"a";i:30;s:1:"b";s:17:"restore_any_pasar";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:30;a:4:{s:1:"a";i:31;s:1:"b";s:15:"replicate_pasar";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:31;a:4:{s:1:"a";i:32;s:1:"b";s:13:"reorder_pasar";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:32;a:4:{s:1:"a";i:33;s:1:"b";s:12:"delete_pasar";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:33;a:4:{s:1:"a";i:34;s:1:"b";s:16:"delete_any_pasar";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:34;a:4:{s:1:"a";i:35;s:1:"b";s:18:"force_delete_pasar";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:35;a:4:{s:1:"a";i:36;s:1:"b";s:22:"force_delete_any_pasar";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:36;a:4:{s:1:"a";i:37;s:1:"b";s:9:"view_post";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:37;a:4:{s:1:"a";i:38;s:1:"b";s:13:"view_any_post";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:38;a:4:{s:1:"a";i:39;s:1:"b";s:11:"create_post";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:39;a:4:{s:1:"a";i:40;s:1:"b";s:11:"update_post";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:40;a:4:{s:1:"a";i:41;s:1:"b";s:12:"restore_post";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:41;a:4:{s:1:"a";i:42;s:1:"b";s:16:"restore_any_post";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:42;a:4:{s:1:"a";i:43;s:1:"b";s:14:"replicate_post";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:43;a:4:{s:1:"a";i:44;s:1:"b";s:12:"reorder_post";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:44;a:4:{s:1:"a";i:45;s:1:"b";s:11:"delete_post";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:45;a:4:{s:1:"a";i:46;s:1:"b";s:15:"delete_any_post";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:46;a:4:{s:1:"a";i:47;s:1:"b";s:17:"force_delete_post";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:47;a:4:{s:1:"a";i:48;s:1:"b";s:21:"force_delete_any_post";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:48;a:4:{s:1:"a";i:49;s:1:"b";s:9:"view_role";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:49;a:4:{s:1:"a";i:50;s:1:"b";s:13:"view_any_role";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:50;a:4:{s:1:"a";i:51;s:1:"b";s:11:"create_role";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:51;a:4:{s:1:"a";i:52;s:1:"b";s:11:"update_role";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:52;a:4:{s:1:"a";i:53;s:1:"b";s:11:"delete_role";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:53;a:4:{s:1:"a";i:54;s:1:"b";s:15:"delete_any_role";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:54;a:4:{s:1:"a";i:55;s:1:"b";s:10:"view_stock";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:55;a:4:{s:1:"a";i:56;s:1:"b";s:14:"view_any_stock";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:56;a:4:{s:1:"a";i:57;s:1:"b";s:12:"create_stock";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:57;a:4:{s:1:"a";i:58;s:1:"b";s:12:"update_stock";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:58;a:4:{s:1:"a";i:59;s:1:"b";s:13:"restore_stock";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:59;a:4:{s:1:"a";i:60;s:1:"b";s:17:"restore_any_stock";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:60;a:4:{s:1:"a";i:61;s:1:"b";s:15:"replicate_stock";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:61;a:4:{s:1:"a";i:62;s:1:"b";s:13:"reorder_stock";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:62;a:4:{s:1:"a";i:63;s:1:"b";s:12:"delete_stock";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:63;a:4:{s:1:"a";i:64;s:1:"b";s:16:"delete_any_stock";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:64;a:4:{s:1:"a";i:65;s:1:"b";s:18:"force_delete_stock";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:65;a:4:{s:1:"a";i:66;s:1:"b";s:22:"force_delete_any_stock";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:66;a:4:{s:1:"a";i:67;s:1:"b";s:26:"widget_AiConsultationStats";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:67;a:4:{s:1:"a";i:68;s:1:"b";s:20:"widget_OverviewStats";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:68;a:4:{s:1:"a";i:69;s:1:"b";s:21:"widget_PendaftarChart";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:69;a:4:{s:1:"a";i:70;s:1:"b";s:26:"widget_AiConsultationChart";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:70;a:4:{s:1:"a";i:71;s:1:"b";s:19:"widget_LatestPasars";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:71;a:4:{s:1:"a";i:72;s:1:"b";s:28:"widget_LatestAiConsultations";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:72;a:4:{s:1:"a";i:73;s:1:"b";s:9:"view_user";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:73;a:4:{s:1:"a";i:74;s:1:"b";s:13:"view_any_user";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:74;a:4:{s:1:"a";i:75;s:1:"b";s:11:"create_user";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:75;a:4:{s:1:"a";i:76;s:1:"b";s:11:"update_user";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:76;a:4:{s:1:"a";i:77;s:1:"b";s:12:"restore_user";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:77;a:4:{s:1:"a";i:78;s:1:"b";s:16:"restore_any_user";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:78;a:4:{s:1:"a";i:79;s:1:"b";s:14:"replicate_user";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:79;a:4:{s:1:"a";i:80;s:1:"b";s:12:"reorder_user";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:80;a:4:{s:1:"a";i:81;s:1:"b";s:11:"delete_user";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:81;a:4:{s:1:"a";i:82;s:1:"b";s:15:"delete_any_user";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:82;a:4:{s:1:"a";i:83;s:1:"b";s:17:"force_delete_user";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:83;a:4:{s:1:"a";i:84;s:1:"b";s:21:"force_delete_any_user";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:84;a:4:{s:1:"a";i:85;s:1:"b";s:16:"view_activitylog";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:85;a:4:{s:1:"a";i:86;s:1:"b";s:20:"view_any_activitylog";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:86;a:4:{s:1:"a";i:87;s:1:"b";s:18:"create_activitylog";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:87;a:4:{s:1:"a";i:88;s:1:"b";s:18:"update_activitylog";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:88;a:4:{s:1:"a";i:89;s:1:"b";s:19:"restore_activitylog";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:89;a:4:{s:1:"a";i:90;s:1:"b";s:23:"restore_any_activitylog";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:90;a:4:{s:1:"a";i:91;s:1:"b";s:21:"replicate_activitylog";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:91;a:4:{s:1:"a";i:92;s:1:"b";s:19:"reorder_activitylog";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:92;a:4:{s:1:"a";i:93;s:1:"b";s:18:"delete_activitylog";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:93;a:4:{s:1:"a";i:94;s:1:"b";s:22:"delete_any_activitylog";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:94;a:4:{s:1:"a";i:95;s:1:"b";s:24:"force_delete_activitylog";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:95;a:4:{s:1:"a";i:96;s:1:"b";s:28:"force_delete_any_activitylog";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:96;a:4:{s:1:"a";i:97;s:1:"b";s:14:"view_exception";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:97;a:4:{s:1:"a";i:98;s:1:"b";s:18:"view_any_exception";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:98;a:4:{s:1:"a";i:99;s:1:"b";s:16:"create_exception";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:99;a:4:{s:1:"a";i:100;s:1:"b";s:16:"update_exception";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:100;a:4:{s:1:"a";i:101;s:1:"b";s:17:"restore_exception";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:101;a:4:{s:1:"a";i:102;s:1:"b";s:21:"restore_any_exception";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:102;a:4:{s:1:"a";i:103;s:1:"b";s:19:"replicate_exception";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:103;a:4:{s:1:"a";i:104;s:1:"b";s:17:"reorder_exception";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:104;a:4:{s:1:"a";i:105;s:1:"b";s:16:"delete_exception";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:105;a:4:{s:1:"a";i:106;s:1:"b";s:20:"delete_any_exception";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:106;a:4:{s:1:"a";i:107;s:1:"b";s:22:"force_delete_exception";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:107;a:4:{s:1:"a";i:108;s:1:"b";s:26:"force_delete_any_exception";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:108;a:4:{s:1:"a";i:109;s:1:"b";s:12:"page_Backups";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:109;a:4:{s:1:"a";i:110;s:1:"b";s:23:"widget_SystemInfoWidget";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}}s:5:"roles";a:2:{i:0;a:3:{s:1:"a";i:1;s:1:"b";s:11:"super_admin";s:1:"c";s:3:"web";}i:1;a:3:{s:1:"a";i:2;s:1:"b";s:11:"Admin Dinas";s:1:"c";s:3:"web";}}}', 1776699523);

-- Dumping structure for table aquara_proyek2.cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.cache_locks: ~0 rows (approximately)

-- Dumping structure for table aquara_proyek2.community_comments
CREATE TABLE IF NOT EXISTS `community_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `community_post_id` bigint unsigned NOT NULL,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `community_comments_community_post_id_foreign` (`community_post_id`),
  CONSTRAINT `community_comments_community_post_id_foreign` FOREIGN KEY (`community_post_id`) REFERENCES `community_posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.community_comments: ~2 rows (approximately)
INSERT INTO `community_comments` (`id`, `community_post_id`, `user_id`, `author_name`, `content`, `created_at`, `updated_at`) VALUES
	(1, 1, '15', 'Denny Caknan', 'gassin', '2026-04-19 12:31:16', '2026-04-19 12:31:16'),
	(2, 1, '15', 'Denny Caknan', '@Denny Caknan info bolo', '2026-04-19 19:40:27', '2026-04-19 19:40:27');

-- Dumping structure for table aquara_proyek2.community_posts
CREATE TABLE IF NOT EXISTS `community_posts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `likes_count` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.community_posts: ~2 rows (approximately)
INSERT INTO `community_posts` (`id`, `user_id`, `author_name`, `content`, `image`, `likes_count`, `created_at`, `updated_at`) VALUES
	(1, '15', 'Denny Caknan', 'Bismillah', 'forum_images/ybj3ISBr6srbyWGdj7MK980XvXUixPFYO25QHng9.jpg', 0, '2026-04-19 12:29:48', '2026-04-19 12:29:48'),
	(6, '15', 'Denny Caknan', 'info dong suhuu gimna', 'forum_images/XiC5U3T1SrsIFjr6P7QPmQJSgPojscvrseGps857.jpg', 0, '2026-04-19 20:16:43', '2026-04-19 20:16:43');

-- Dumping structure for table aquara_proyek2.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.failed_jobs: ~0 rows (approximately)

-- Dumping structure for table aquara_proyek2.filament_exceptions_table
CREATE TABLE IF NOT EXISTS `filament_exceptions_table` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `file` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `line` int NOT NULL,
  `trace` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `query` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `cookies` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.filament_exceptions_table: ~0 rows (approximately)

-- Dumping structure for table aquara_proyek2.fish_types
CREATE TABLE IF NOT EXISTS `fish_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fcr_ratio` decimal(8,2) NOT NULL DEFAULT '1.00',
  `harvest_duration_days` int NOT NULL,
  `standard_density_per_m2` int NOT NULL,
  `survival_rate` decimal(5,2) NOT NULL DEFAULT '90.00',
  `market_price_per_kg` decimal(12,2) NOT NULL,
  `feed_price_per_kg` decimal(12,2) NOT NULL,
  `cultivation_guide` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.fish_types: ~0 rows (approximately)

-- Dumping structure for table aquara_proyek2.jobs
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.jobs: ~0 rows (approximately)

-- Dumping structure for table aquara_proyek2.job_batches
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.job_batches: ~0 rows (approximately)

-- Dumping structure for table aquara_proyek2.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.migrations: ~20 rows (approximately)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(4, '2026_02_14_191935_create_fish_types_table', 1),
	(5, '2026_02_14_201420_create_personal_access_tokens_table', 1),
	(6, '2026_02_15_092452_create_stocks_table', 1),
	(7, '2026_02_16_024634_create_posts_table', 1),
	(8, '2026_02_16_041733_create_community_posts_table', 1),
	(9, '2026_02_16_041753_create_community_comments_table', 1),
	(10, '2026_02_16_064525_create_post_likes_table', 1),
	(11, '2026_02_22_055320_create_ai_consultations_table', 1),
	(12, '2026_02_28_074508_create_pasars_table', 1),
	(13, '2026_03_01_003146_add_profil_fields_to_users_table', 1),
	(14, '2026_04_04_103521_add_phone_to_users_table', 1),
	(15, '2026_04_05_031201_add_google_id_to_users_table', 2),
	(16, '2026_04_05_071002_add_otp_to_users_table', 3),
	(17, '2026_04_09_050518_create_permission_tables', 4),
	(18, '2026_04_09_170626_create_filament_exceptions_table', 5),
	(19, '2026_04_09_172644_create_activity_log_table', 6),
	(20, '2026_04_09_172645_add_event_column_to_activity_log_table', 6),
	(21, '2026_04_09_172646_add_batch_uuid_column_to_activity_log_table', 6),
	(22, '2026_04_19_193840_create_notifications_table', 7),
	(23, '2026_04_20_033820_add_reference_id_to_notifications_table', 8);

-- Dumping structure for table aquara_proyek2.model_has_permissions
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.model_has_permissions: ~0 rows (approximately)

-- Dumping structure for table aquara_proyek2.model_has_roles
CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.model_has_roles: ~2 rows (approximately)
INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
	(2, 'App\\Models\\User', 7),
	(1, 'App\\Models\\User', 8);

-- Dumping structure for table aquara_proyek2.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_foreign` (`user_id`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.notifications: ~6 rows (approximately)
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_id`, `is_read`, `created_at`, `updated_at`) VALUES
	(1, NULL, '💬 Diskusi Baru di Forum AQUARA', 'Denny Caknan bertanya: "Kartonyono"', 'forum', NULL, 0, '2026-04-19 20:02:44', '2026-04-19 20:02:44'),
	(2, NULL, '📢 Info Pasar AQUARA Terbaru!', 'Ada Mobile Legends nih! Harga Rp 40.000. Yuk cek!', 'pasar', NULL, 0, '2026-04-19 20:05:11', '2026-04-19 20:05:11'),
	(3, NULL, '🤖 Analisa AI AQUARA Selesai!', 'Pakar AI telah mendeteksi kondisi tambak/ikan Anda. Cek sekarang!', 'ai', NULL, 0, '2026-04-19 20:13:43', '2026-04-19 20:13:43'),
	(4, NULL, '🤖 Analisa AI AQUARA Selesai!', 'Pakar AI telah mendeteksi kondisi tambak/ikan Anda. Cek sekarang!', 'ai', NULL, 0, '2026-04-19 20:14:38', '2026-04-19 20:14:38'),
	(5, NULL, '💬 Diskusi Baru di Forum AQUARA', 'Denny Caknan bertanya: "info dong suhuu gimna"', 'forum', NULL, 0, '2026-04-19 20:16:43', '2026-04-19 20:16:43'),
	(6, NULL, '📢 Info Pasar AQUARA Terbaru!', 'Ada Barang nih! Harga Rp 200.000. Yuk cek!', 'pasar', NULL, 0, '2026-04-19 20:18:42', '2026-04-19 20:18:42');

-- Dumping structure for table aquara_proyek2.pasars
CREATE TABLE IF NOT EXISTS `pasars` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `nama_ikan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `harga` int NOT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomor_wa` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lokasi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pasars_user_id_foreign` (`user_id`),
  CONSTRAINT `pasars_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.pasars: ~4 rows (approximately)
INSERT INTO `pasars` (`id`, `user_id`, `nama_ikan`, `deskripsi`, `harga`, `foto`, `nomor_wa`, `lokasi`, `created_at`, `updated_at`) VALUES
	(1, 15, 'Lele Sangkuriang', 'Minimal beli per kg', 35000, 'pasar-fotos/4c9wpst8C29NSsAevKajhRJcO7wwVJYSdKEnNfTt.jpg', '085321820026', 'Desa Karangturi', '2026-04-19 19:45:57', '2026-04-19 19:45:57'),
	(3, 15, 'Gurame', 'minimal beli per ton', 50000, 'pasar-fotos/I9QrdHKvINAOTteXHGT04Z6IZFXFfYChdk0E02p6.jpg', '085161709807', 'Desa Pagirikan', '2026-04-19 19:48:13', '2026-04-19 19:50:36'),
	(4, 15, 'Mobile Legends', 'mabarrr', 40000, 'pasar-fotos/NiCUB7XPojl3BcL4gRZ3n4xmpZo7sJiLgrUTJ9wF.jpg', '085161709807', 'Pamayahan', '2026-04-19 20:05:11', '2026-04-19 20:05:11');

-- Dumping structure for table aquara_proyek2.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.password_reset_tokens: ~0 rows (approximately)

-- Dumping structure for table aquara_proyek2.permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.permissions: ~110 rows (approximately)
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
	(1, 'view_ai::consultation', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(2, 'view_any_ai::consultation', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(3, 'create_ai::consultation', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(4, 'update_ai::consultation', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(5, 'restore_ai::consultation', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(6, 'restore_any_ai::consultation', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(7, 'replicate_ai::consultation', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(8, 'reorder_ai::consultation', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(9, 'delete_ai::consultation', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(10, 'delete_any_ai::consultation', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(11, 'force_delete_ai::consultation', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(12, 'force_delete_any_ai::consultation', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(13, 'view_fish::type', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(14, 'view_any_fish::type', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(15, 'create_fish::type', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(16, 'update_fish::type', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(17, 'restore_fish::type', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(18, 'restore_any_fish::type', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(19, 'replicate_fish::type', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(20, 'reorder_fish::type', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(21, 'delete_fish::type', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(22, 'delete_any_fish::type', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(23, 'force_delete_fish::type', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(24, 'force_delete_any_fish::type', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(25, 'view_pasar', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(26, 'view_any_pasar', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(27, 'create_pasar', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(28, 'update_pasar', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(29, 'restore_pasar', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(30, 'restore_any_pasar', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(31, 'replicate_pasar', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(32, 'reorder_pasar', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(33, 'delete_pasar', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(34, 'delete_any_pasar', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(35, 'force_delete_pasar', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(36, 'force_delete_any_pasar', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(37, 'view_post', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(38, 'view_any_post', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(39, 'create_post', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(40, 'update_post', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(41, 'restore_post', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(42, 'restore_any_post', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(43, 'replicate_post', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(44, 'reorder_post', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(45, 'delete_post', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(46, 'delete_any_post', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(47, 'force_delete_post', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(48, 'force_delete_any_post', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(49, 'view_role', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(50, 'view_any_role', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(51, 'create_role', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(52, 'update_role', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(53, 'delete_role', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(54, 'delete_any_role', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(55, 'view_stock', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(56, 'view_any_stock', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(57, 'create_stock', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(58, 'update_stock', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(59, 'restore_stock', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(60, 'restore_any_stock', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(61, 'replicate_stock', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(62, 'reorder_stock', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(63, 'delete_stock', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(64, 'delete_any_stock', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(65, 'force_delete_stock', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(66, 'force_delete_any_stock', 'web', '2026-04-08 22:06:06', '2026-04-08 22:06:06'),
	(67, 'widget_AiConsultationStats', 'web', '2026-04-08 22:06:07', '2026-04-08 22:06:07'),
	(68, 'widget_OverviewStats', 'web', '2026-04-08 22:06:07', '2026-04-08 22:06:07'),
	(69, 'widget_PendaftarChart', 'web', '2026-04-08 22:06:07', '2026-04-08 22:06:07'),
	(70, 'widget_AiConsultationChart', 'web', '2026-04-08 22:06:07', '2026-04-08 22:06:07'),
	(71, 'widget_LatestPasars', 'web', '2026-04-08 22:06:08', '2026-04-08 22:06:08'),
	(72, 'widget_LatestAiConsultations', 'web', '2026-04-08 22:06:08', '2026-04-08 22:06:08'),
	(73, 'view_user', 'web', '2026-04-09 09:30:23', '2026-04-09 09:30:23'),
	(74, 'view_any_user', 'web', '2026-04-09 09:30:23', '2026-04-09 09:30:23'),
	(75, 'create_user', 'web', '2026-04-09 09:30:23', '2026-04-09 09:30:23'),
	(76, 'update_user', 'web', '2026-04-09 09:30:23', '2026-04-09 09:30:23'),
	(77, 'restore_user', 'web', '2026-04-09 09:30:23', '2026-04-09 09:30:23'),
	(78, 'restore_any_user', 'web', '2026-04-09 09:30:23', '2026-04-09 09:30:23'),
	(79, 'replicate_user', 'web', '2026-04-09 09:30:23', '2026-04-09 09:30:23'),
	(80, 'reorder_user', 'web', '2026-04-09 09:30:23', '2026-04-09 09:30:23'),
	(81, 'delete_user', 'web', '2026-04-09 09:30:23', '2026-04-09 09:30:23'),
	(82, 'delete_any_user', 'web', '2026-04-09 09:30:23', '2026-04-09 09:30:23'),
	(83, 'force_delete_user', 'web', '2026-04-09 09:30:23', '2026-04-09 09:30:23'),
	(84, 'force_delete_any_user', 'web', '2026-04-09 09:30:23', '2026-04-09 09:30:23'),
	(85, 'view_activitylog', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09'),
	(86, 'view_any_activitylog', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09'),
	(87, 'create_activitylog', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09'),
	(88, 'update_activitylog', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09'),
	(89, 'restore_activitylog', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09'),
	(90, 'restore_any_activitylog', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09'),
	(91, 'replicate_activitylog', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09'),
	(92, 'reorder_activitylog', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09'),
	(93, 'delete_activitylog', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09'),
	(94, 'delete_any_activitylog', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09'),
	(95, 'force_delete_activitylog', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09'),
	(96, 'force_delete_any_activitylog', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09'),
	(97, 'view_exception', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09'),
	(98, 'view_any_exception', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09'),
	(99, 'create_exception', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09'),
	(100, 'update_exception', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09'),
	(101, 'restore_exception', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09'),
	(102, 'restore_any_exception', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09'),
	(103, 'replicate_exception', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09'),
	(104, 'reorder_exception', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09'),
	(105, 'delete_exception', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09'),
	(106, 'delete_any_exception', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09'),
	(107, 'force_delete_exception', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09'),
	(108, 'force_delete_any_exception', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09'),
	(109, 'page_Backups', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09'),
	(110, 'widget_SystemInfoWidget', 'web', '2026-04-09 11:20:09', '2026-04-09 11:20:09');

-- Dumping structure for table aquara_proyek2.personal_access_tokens
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.personal_access_tokens: ~25 rows (approximately)
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
	(1, 'App\\Models\\User', 1, 'auth_token', 'deec84d61cd991a3bd5366de689919ee7dbebc3cef1f40cf4314a3ade7f5ec83', '["*"]', NULL, NULL, '2026-04-04 04:04:45', '2026-04-04 04:04:45'),
	(2, 'App\\Models\\User', 2, 'auth_token', '05bb6d03edf6798d60d63a1748226310af30e4b1ce9c463521c1754b869f533b', '["*"]', NULL, NULL, '2026-04-04 04:05:27', '2026-04-04 04:05:27'),
	(3, 'App\\Models\\User', 3, 'auth_token', '09238d24af88d9e105bd08cde7dc09369a7b56f45ede25ee33def4c2391a8a63', '["*"]', NULL, NULL, '2026-04-04 04:14:49', '2026-04-04 04:14:49'),
	(4, 'App\\Models\\User', 1, 'auth_token', '0e5c6c2ac97ccefc72d5a353b0d1cbbf643e8cbf2b676fd24a567d228b95aa93', '["*"]', NULL, NULL, '2026-04-04 04:23:26', '2026-04-04 04:23:26'),
	(5, 'App\\Models\\User', 3, 'auth_token', 'ef068af5a42c5df6b5ab09f013a4bcd725cfe6980887382d9077bf21bbaacdfa', '["*"]', NULL, NULL, '2026-04-04 04:24:10', '2026-04-04 04:24:10'),
	(6, 'App\\Models\\User', 4, 'auth_token', '795e7b3fbda7cd3bc3485cffe74d4c2df35ca937495da85dcfb503dcff3ee1ba', '["*"]', NULL, NULL, '2026-04-04 21:45:22', '2026-04-04 21:45:22'),
	(7, 'App\\Models\\User', 5, 'auth_token', '3f23e6ec7f6eb2e6b0bb3cbf405e1fa48399dd488e087e4d942e59e515ea8060', '["*"]', NULL, NULL, '2026-04-04 21:51:00', '2026-04-04 21:51:00'),
	(8, 'App\\Models\\User', 4, 'auth_token', '560a45be02155ace12a8e2f5c28b6c76d7d8f946faaf50cbb95a88bbe0619912', '["*"]', NULL, NULL, '2026-04-04 21:52:05', '2026-04-04 21:52:05'),
	(9, 'App\\Models\\User', 4, 'auth_token', '6378dbed06d48d4d737276651c452815333e616b0b71e1c01fb5c095174ce339', '["*"]', NULL, NULL, '2026-04-04 21:57:13', '2026-04-04 21:57:13'),
	(10, 'App\\Models\\User', 4, 'auth_token', '6db563a62fa147d6e8f6b06ab5ba8586d982f92ef826b282bbb2b395099c8bab', '["*"]', '2026-04-04 22:11:14', NULL, '2026-04-04 22:10:05', '2026-04-04 22:11:14'),
	(11, 'App\\Models\\User', 4, 'auth_token', '954264c492d0155648aceae22768f56dd1c399fb1084e37e21db9e3ffe081283', '["*"]', NULL, NULL, '2026-04-04 22:12:13', '2026-04-04 22:12:13'),
	(12, 'App\\Models\\User', 1, 'auth_token', '199d90e88db9d07b92f73b9802ffd0e8a8a7d8d2247cb16cadc2f58bc00d8902', '["*"]', NULL, NULL, '2026-04-05 00:14:13', '2026-04-05 00:14:13'),
	(13, 'App\\Models\\User', 6, 'auth_token', '216a848784bb5d705960ceb0719f15d12d402cfbd32c949fc397e87306720af8', '["*"]', '2026-04-05 00:16:51', NULL, '2026-04-05 00:15:48', '2026-04-05 00:16:51'),
	(14, 'App\\Models\\User', 6, 'auth_token', '690b92c5de1d390b9aee0f047a94481b45e36b5278ee0720dc3ffed056299b05', '["*"]', NULL, NULL, '2026-04-05 00:18:37', '2026-04-05 00:18:37'),
	(15, 'App\\Models\\User', 9, 'auth_token', '3953941e03b4390386c0ff6a18cc1acf274bb8560cc2810aa5b61c3c081d87a3', '["*"]', NULL, NULL, '2026-04-17 23:15:36', '2026-04-17 23:15:36'),
	(16, 'App\\Models\\User', 10, 'auth_token', '547998d71cad477b7ec794c15c09589b526cbeefe10a9fb1ecd26a042a7b46a4', '["*"]', NULL, NULL, '2026-04-17 23:17:39', '2026-04-17 23:17:39'),
	(17, 'App\\Models\\User', 1, 'auth_token', '072f9958941828542e24edb8f6b9c135395119a871655ee0307985a4cecee711', '["*"]', NULL, NULL, '2026-04-17 23:21:32', '2026-04-17 23:21:32'),
	(18, 'App\\Models\\User', 4, 'auth_token', '05f65fde8f4d89484804056cb5172fe3d183947ae034214be13f89d4061f3e68', '["*"]', NULL, NULL, '2026-04-17 23:22:18', '2026-04-17 23:22:18'),
	(19, 'App\\Models\\User', 9, 'auth_token', '7eaca6f38e89214f6672151a853a1430470dd5f6229f10932cae8b82bc439655', '["*"]', NULL, NULL, '2026-04-17 23:23:28', '2026-04-17 23:23:28'),
	(20, 'App\\Models\\User', 9, 'auth_token', '932ca3cdcb82c770ca8c6c004405d62dea8685735189e2659b24bee62e231da7', '["*"]', NULL, NULL, '2026-04-17 23:56:37', '2026-04-17 23:56:37'),
	(21, 'App\\Models\\User', 9, 'auth_token', '9fd561702b71f6fcb542820ba913705bc058335247bd3c6d8af4c3e7d2db0a29', '["*"]', NULL, NULL, '2026-04-18 00:03:01', '2026-04-18 00:03:01'),
	(22, 'App\\Models\\User', 9, 'auth_token', '3d0547cd819efb63561f4c2a7b15606504e9f6630b1c1f12d29e390685f747dc', '["*"]', NULL, NULL, '2026-04-18 00:05:55', '2026-04-18 00:05:55'),
	(23, 'App\\Models\\User', 9, 'auth_token', '986967b306c0b11c5b207605e44085567122a908033d550b1d637b6554aa0149', '["*"]', NULL, NULL, '2026-04-18 00:13:49', '2026-04-18 00:13:49'),
	(24, 'App\\Models\\User', 9, 'auth_token', 'd0702d3ade07c4818e648e02da4db48c4a83d9f68df018e9b6b68e5afa07c3a5', '["*"]', NULL, NULL, '2026-04-18 00:24:38', '2026-04-18 00:24:38'),
	(25, 'App\\Models\\User', 9, 'auth_token', 'c131b69b0e0479a43608baebf8f0a81c8f2df208063e77fabf474271ea14f6ff', '["*"]', NULL, NULL, '2026-04-18 00:33:37', '2026-04-18 00:33:37'),
	(34, 'App\\Models\\User', 15, 'auth_token', '629d00cd7a068c1cbfd8fafc1c07d5d3767aa9860db3c5114b58cbbdb9d3624d', '["*"]', NULL, NULL, '2026-04-19 10:14:07', '2026-04-19 10:14:07'),
	(35, 'App\\Models\\User', 15, 'auth_token', '06f3d2fdf32c3e7f6553b1a10b3be9412311321f74c07858cad2284ccd8c90eb', '["*"]', '2026-04-19 21:04:49', NULL, '2026-04-19 12:28:53', '2026-04-19 21:04:49'),
	(36, 'App\\Models\\User', 16, 'auth_token', 'fe12092ec2b08266b791176a83f7919dadd7361adb563779fb67ff04132abea0', '["*"]', NULL, NULL, '2026-04-19 22:16:40', '2026-04-19 22:16:40');

-- Dumping structure for table aquara_proyek2.posts
CREATE TABLE IF NOT EXISTS `posts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `author` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Admin Dinas',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.posts: ~0 rows (approximately)

-- Dumping structure for table aquara_proyek2.post_likes
CREATE TABLE IF NOT EXISTS `post_likes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `community_post_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_likes_user_id_community_post_id_unique` (`user_id`,`community_post_id`),
  KEY `post_likes_community_post_id_foreign` (`community_post_id`),
  CONSTRAINT `post_likes_community_post_id_foreign` FOREIGN KEY (`community_post_id`) REFERENCES `community_posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.post_likes: ~3 rows (approximately)
INSERT INTO `post_likes` (`id`, `user_id`, `community_post_id`, `created_at`, `updated_at`) VALUES
	(3, '15', 1, '2026-04-19 19:42:36', '2026-04-19 19:42:36'),
	(7, '15', 6, '2026-04-19 20:17:17', '2026-04-19 20:17:17');

-- Dumping structure for table aquara_proyek2.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.roles: ~2 rows (approximately)
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
	(1, 'super_admin', 'web', '2026-04-08 22:06:05', '2026-04-08 22:06:05'),
	(2, 'Admin Dinas', 'web', '2026-04-08 22:38:27', '2026-04-08 22:38:27');

-- Dumping structure for table aquara_proyek2.role_has_permissions
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.role_has_permissions: ~137 rows (approximately)
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
	(1, 1),
	(2, 1),
	(3, 1),
	(4, 1),
	(5, 1),
	(6, 1),
	(7, 1),
	(8, 1),
	(9, 1),
	(10, 1),
	(11, 1),
	(12, 1),
	(13, 1),
	(14, 1),
	(15, 1),
	(16, 1),
	(17, 1),
	(18, 1),
	(19, 1),
	(20, 1),
	(21, 1),
	(22, 1),
	(23, 1),
	(24, 1),
	(25, 1),
	(26, 1),
	(27, 1),
	(28, 1),
	(29, 1),
	(30, 1),
	(31, 1),
	(32, 1),
	(33, 1),
	(34, 1),
	(35, 1),
	(36, 1),
	(37, 1),
	(38, 1),
	(39, 1),
	(40, 1),
	(41, 1),
	(42, 1),
	(43, 1),
	(44, 1),
	(45, 1),
	(46, 1),
	(47, 1),
	(48, 1),
	(49, 1),
	(50, 1),
	(51, 1),
	(52, 1),
	(53, 1),
	(54, 1),
	(55, 1),
	(56, 1),
	(57, 1),
	(58, 1),
	(59, 1),
	(60, 1),
	(61, 1),
	(62, 1),
	(63, 1),
	(64, 1),
	(65, 1),
	(66, 1),
	(67, 1),
	(68, 1),
	(69, 1),
	(70, 1),
	(71, 1),
	(72, 1),
	(73, 1),
	(74, 1),
	(75, 1),
	(76, 1),
	(77, 1),
	(78, 1),
	(79, 1),
	(80, 1),
	(81, 1),
	(82, 1),
	(83, 1),
	(84, 1),
	(85, 1),
	(86, 1),
	(87, 1),
	(88, 1),
	(89, 1),
	(90, 1),
	(91, 1),
	(92, 1),
	(93, 1),
	(94, 1),
	(95, 1),
	(96, 1),
	(97, 1),
	(98, 1),
	(99, 1),
	(100, 1),
	(101, 1),
	(102, 1),
	(103, 1),
	(104, 1),
	(105, 1),
	(106, 1),
	(107, 1),
	(108, 1),
	(109, 1),
	(110, 1),
	(1, 2),
	(2, 2),
	(9, 2),
	(13, 2),
	(14, 2),
	(15, 2),
	(16, 2),
	(21, 2),
	(25, 2),
	(26, 2),
	(33, 2),
	(37, 2),
	(38, 2),
	(39, 2),
	(40, 2),
	(45, 2),
	(55, 2),
	(56, 2),
	(57, 2),
	(58, 2),
	(63, 2),
	(67, 2),
	(68, 2),
	(69, 2),
	(70, 2),
	(71, 2),
	(72, 2);

-- Dumping structure for table aquara_proyek2.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.sessions: ~1 rows (approximately)
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('ssYNUzRg4aL7sMcl9lHiN0mvXrkmocwQu0onERxT', 7, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiZk8wQkx6cDZKbnZmV01pS0xoclpSWDJqVFhBdHo0eXpISExQTnM3ayI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjQ0OiJodHRwOi8vYXF1YXJhX3Byb3llazIudGVzdDo4MDgwL2FkbWluL3Bhc2FycyI7czo1OiJyb3V0ZSI7czozNzoiZmlsYW1lbnQuYWRtaW4ucmVzb3VyY2VzLnBhc2Fycy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjc7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6Ijg2YzFhNGY1MjU4YzA3MWQyZmQ0ZjZjZDFjMTNhZmNhY2U5YjFkZjc0ZDhmZWMwNWRhYTU4NzFiMDM0ZmJiYWEiO30=', 1776653384);

-- Dumping structure for table aquara_proyek2.stocks
CREATE TABLE IF NOT EXISTS `stocks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fish_type_id` bigint unsigned NOT NULL,
  `pengepul_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount_kg` double NOT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stocks_fish_type_id_foreign` (`fish_type_id`),
  CONSTRAINT `stocks_fish_type_id_foreign` FOREIGN KEY (`fish_type_id`) REFERENCES `fish_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.stocks: ~0 rows (approximately)

-- Dumping structure for table aquara_proyek2.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `otp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp_expired_at` timestamp NULL DEFAULT NULL,
  `foto_profil` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fokus_budidaya` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_phone_unique` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table aquara_proyek2.users: ~2 rows (approximately)
INSERT INTO `users` (`id`, `name`, `email`, `google_id`, `phone`, `email_verified_at`, `password`, `otp`, `otp_expired_at`, `foto_profil`, `fokus_budidaya`, `remember_token`, `created_at`, `updated_at`) VALUES
	(7, 'Admin AQUARA', 'admin@aquara.test', NULL, NULL, NULL, '$2y$12$jXqhW91cgL8XvfdO/IC7WeX34vMm/SM8DdNmMYTq.Xtb6NrUJ47hi', NULL, NULL, NULL, NULL, 'a1sF2StnubuAXHLMK0hy4dE2RxY1CWSkZyJDKzFLs79kKQZt68NJqItUDuEO', '2026-04-05 01:21:19', '2026-04-05 01:21:19'),
	(8, 'Developer AQUARA', 'dev@aquara.com', NULL, NULL, NULL, '$2y$12$zMQfXBslLiSZOxaWllqIieBMQABCTJHCFft8aZyskpUmO98OB7SZu', NULL, NULL, NULL, NULL, 'fHJcESEzi3mpVbREOGgQFofqX8CnAGQhkKBlqTQPDXWXnalx1nxHPrOpmPaJ', '2026-04-08 22:15:19', '2026-04-08 22:15:19'),
	(15, 'Denny Caknan', 'alfajarislamia@gmail.com', NULL, '085161709807', '2026-04-19 10:14:07', '$2y$12$5a98.8ksUncXfCVYPIzUqOY7ni0e4skTDdrf8nMOn2e.cwHIOVQue', NULL, NULL, 'profil-fotos/pBTMfcnDH1669qzolb11kHyU1Ko5qeOjMZEHKyqJ.jpg', 'Gurame, Lele Sangkuriang, Patin', NULL, '2026-04-19 10:13:22', '2026-04-19 19:51:57'),
	(16, 'Seva', NULL, NULL, '087777426068', '2026-04-19 22:16:39', '$2y$12$aQQVCRZPrU2IyXi96XGvbeLHkWpY8LcaPNz1hJb7bjt4N0fxRa52m', NULL, NULL, NULL, NULL, NULL, '2026-04-19 22:15:47', '2026-04-19 22:16:39');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
