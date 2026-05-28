-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 29, 2026 at 02:12 PM
-- Server version: 8.0.41
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `position`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('4jOvyF1JbsDBVJGtrosJhmwLzLBzSHPavfEzd7wL', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiOU0zMTB4MzNDcjBsZ21LcnhLMHBIaEk4VUEwTzZyaFNsendrVDdVSSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly9wb3NpdGlvbi50ZXN0L21hcmtvdj9tb3ZlX3RvPTQiO3M6NToicm91dGUiO3M6NjoibWFya292Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czoxNDoibWFya292X2hpc3RvcnkiO2E6NTp7aTowO2k6MDtpOjE7aTo0O2k6MjtpOjA7aTozO2k6MDtpOjQ7aTo0O31zOjEyOiJsYXN0X2NlbGxfaWQiO2k6NDtzOjEzOiJtYXJrb3ZfbWF0cml4IjthOjY6e2k6MDthOjc6e2k6MDtPOjg6InN0ZENsYXNzIjoyOntzOjI6Im5iIjtpOjE7czo0OiJzdGF0IjtkOjAuMzMzMzMzMzMzMzMzMzMzMzt9aToxO086ODoic3RkQ2xhc3MiOjI6e3M6MjoibmIiO2k6MDtzOjQ6InN0YXQiO2k6MDt9aToyO086ODoic3RkQ2xhc3MiOjI6e3M6MjoibmIiO2k6MDtzOjQ6InN0YXQiO2k6MDt9aTozO086ODoic3RkQ2xhc3MiOjI6e3M6MjoibmIiO2k6MDtzOjQ6InN0YXQiO2k6MDt9aTo0O086ODoic3RkQ2xhc3MiOjI6e3M6MjoibmIiO2k6MjtzOjQ6InN0YXQiO2Q6MC42NjY2NjY2NjY2NjY2NjY2O31pOjU7Tzo4OiJzdGRDbGFzcyI6Mjp7czoyOiJuYiI7aTowO3M6NDoic3RhdCI7aTowO31pOjY7Tzo4OiJzdGRDbGFzcyI6Mjp7czoyOiJuYiI7aTozO3M6NDoic3RhdCI7ZDowO319aToxO2E6Nzp7aTowO086ODoic3RkQ2xhc3MiOjI6e3M6MjoibmIiO2k6MDtzOjQ6InN0YXQiO2Q6MDt9aToxO086ODoic3RkQ2xhc3MiOjI6e3M6MjoibmIiO2k6MDtzOjQ6InN0YXQiO2Q6MDt9aToyO086ODoic3RkQ2xhc3MiOjI6e3M6MjoibmIiO2k6MDtzOjQ6InN0YXQiO2Q6MDt9aTozO086ODoic3RkQ2xhc3MiOjI6e3M6MjoibmIiO2k6MDtzOjQ6InN0YXQiO2Q6MDt9aTo0O086ODoic3RkQ2xhc3MiOjI6e3M6MjoibmIiO2k6MDtzOjQ6InN0YXQiO2Q6MDt9aTo1O086ODoic3RkQ2xhc3MiOjI6e3M6MjoibmIiO2k6MDtzOjQ6InN0YXQiO2Q6MDt9aTo2O086ODoic3RkQ2xhc3MiOjI6e3M6MjoibmIiO2k6MDtzOjQ6InN0YXQiO2Q6MDt9fWk6MjthOjc6e2k6MDtPOjg6InN0ZENsYXNzIjoyOntzOjI6Im5iIjtpOjA7czo0OiJzdGF0IjtkOjA7fWk6MTtPOjg6InN0ZENsYXNzIjoyOntzOjI6Im5iIjtpOjA7czo0OiJzdGF0IjtkOjA7fWk6MjtPOjg6InN0ZENsYXNzIjoyOntzOjI6Im5iIjtpOjA7czo0OiJzdGF0IjtkOjA7fWk6MztPOjg6InN0ZENsYXNzIjoyOntzOjI6Im5iIjtpOjA7czo0OiJzdGF0IjtkOjA7fWk6NDtPOjg6InN0ZENsYXNzIjoyOntzOjI6Im5iIjtpOjA7czo0OiJzdGF0IjtkOjA7fWk6NTtPOjg6InN0ZENsYXNzIjoyOntzOjI6Im5iIjtpOjA7czo0OiJzdGF0IjtkOjA7fWk6NjtPOjg6InN0ZENsYXNzIjoyOntzOjI6Im5iIjtpOjA7czo0OiJzdGF0IjtkOjA7fX1pOjM7YTo3OntpOjA7Tzo4OiJzdGRDbGFzcyI6Mjp7czoyOiJuYiI7aTowO3M6NDoic3RhdCI7ZDowO31pOjE7Tzo4OiJzdGRDbGFzcyI6Mjp7czoyOiJuYiI7aTowO3M6NDoic3RhdCI7ZDowO31pOjI7Tzo4OiJzdGRDbGFzcyI6Mjp7czoyOiJuYiI7aTowO3M6NDoic3RhdCI7ZDowO31pOjM7Tzo4OiJzdGRDbGFzcyI6Mjp7czoyOiJuYiI7aTowO3M6NDoic3RhdCI7ZDowO31pOjQ7Tzo4OiJzdGRDbGFzcyI6Mjp7czoyOiJuYiI7aTowO3M6NDoic3RhdCI7ZDowO31pOjU7Tzo4OiJzdGRDbGFzcyI6Mjp7czoyOiJuYiI7aTowO3M6NDoic3RhdCI7ZDowO31pOjY7Tzo4OiJzdGRDbGFzcyI6Mjp7czoyOiJuYiI7aTowO3M6NDoic3RhdCI7ZDowO319aTo0O2E6Nzp7aTowO086ODoic3RkQ2xhc3MiOjI6e3M6MjoibmIiO2k6MTtzOjQ6InN0YXQiO2k6MTt9aToxO086ODoic3RkQ2xhc3MiOjI6e3M6MjoibmIiO2k6MDtzOjQ6InN0YXQiO2k6MDt9aToyO086ODoic3RkQ2xhc3MiOjI6e3M6MjoibmIiO2k6MDtzOjQ6InN0YXQiO2k6MDt9aTozO086ODoic3RkQ2xhc3MiOjI6e3M6MjoibmIiO2k6MDtzOjQ6InN0YXQiO2k6MDt9aTo0O086ODoic3RkQ2xhc3MiOjI6e3M6MjoibmIiO2k6MDtzOjQ6InN0YXQiO2k6MDt9aTo1O086ODoic3RkQ2xhc3MiOjI6e3M6MjoibmIiO2k6MDtzOjQ6InN0YXQiO2k6MDt9aTo2O086ODoic3RkQ2xhc3MiOjI6e3M6MjoibmIiO2k6MTtzOjQ6InN0YXQiO2Q6MDt9fWk6NTthOjc6e2k6MDtPOjg6InN0ZENsYXNzIjoyOntzOjI6Im5iIjtpOjA7czo0OiJzdGF0IjtkOjA7fWk6MTtPOjg6InN0ZENsYXNzIjoyOntzOjI6Im5iIjtpOjA7czo0OiJzdGF0IjtkOjA7fWk6MjtPOjg6InN0ZENsYXNzIjoyOntzOjI6Im5iIjtpOjA7czo0OiJzdGF0IjtkOjA7fWk6MztPOjg6InN0ZENsYXNzIjoyOntzOjI6Im5iIjtpOjA7czo0OiJzdGF0IjtkOjA7fWk6NDtPOjg6InN0ZENsYXNzIjoyOntzOjI6Im5iIjtpOjA7czo0OiJzdGF0IjtkOjA7fWk6NTtPOjg6InN0ZENsYXNzIjoyOntzOjI6Im5iIjtpOjA7czo0OiJzdGF0IjtkOjA7fWk6NjtPOjg6InN0ZENsYXNzIjoyOntzOjI6Im5iIjtpOjA7czo0OiJzdGF0IjtkOjA7fX19fQ==', 1774281787),
('4z1TqzBctkqNfJDTreEHPHbj6080EMiBO8YPUvbw', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSjk1QmczVUVyOWtVV1U2Wm1FcHpZeDlmZGxFcWJ6MHEzVXhaR3F2TSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly9wb3NpdGlvbi50ZXN0IjtzOjU6InJvdXRlIjtzOjk6ImRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1774348570),
('J3aCL07RbwWGn992ZssiefhEXu4wuLgsGpj0RIMt', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Herd/1.26.0 Chrome/120.0.6099.291 Electron/28.2.5 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQjltS0NPVE1IU01SQlZKSlAzaGl5bFRZY3dKZnVobDJROFA4WHNKbCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly9wb3NpdGlvbi50ZXN0Lz9oZXJkPXByZXZpZXciO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1774796335),
('OH9EjH1UngAe38b2LmmrzMm07sIPlZruzjIFUoPo', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Herd/1.26.0 Chrome/120.0.6099.291 Electron/28.2.5 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVHJXNkNtYzEwdHFueDl1bTVjZGUwUHhyUTZMZjJYR2NSOEpVT3JCVCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly9wb3NpdGlvbi50ZXN0Lz9oZXJkPXByZXZpZXciO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1774796353),
('qxWV6YXhLzF2BRFHc0gIooh8ztRivKGtwpsWpDdt', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Herd/1.26.0 Chrome/120.0.6099.291 Electron/28.2.5 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidmNOVGF5UWt2Z1RSVVNBa2d3TXB3YlpZV1NRbFdnMEU2TDFTZnQwdCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly9wb3NpdGlvbi50ZXN0Lz9oZXJkPXByZXZpZXciO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1774796356),
('RxR6mZCdJyfQwlNvUpFZD4VurPBvwUTWrWvasSJa', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Herd/1.26.0 Chrome/120.0.6099.291 Electron/28.2.5 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibDJJVnFoeWtUbGZXQThXMkdWWmk4T2Q2Z2laZVBia090cXBWZHZBUiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly9wb3NpdGlvbi50ZXN0Lz9oZXJkPXByZXZpZXciO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1774789050);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
