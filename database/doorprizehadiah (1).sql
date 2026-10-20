-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 19 Okt 2026 pada 16.23
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `doorprizehadiah`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba', 'i:1;', 1791971477),
('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba:timer', 'i:1791971477;', 1791971477);

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `exclusion_groups`
--

CREATE TABLE `exclusion_groups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `exclusion_group_participant`
--

CREATE TABLE `exclusion_group_participant` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `exclusion_group_id` bigint(20) UNSIGNED NOT NULL,
  `participant_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `exclusion_group_prize`
--

CREATE TABLE `exclusion_group_prize` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `exclusion_group_id` bigint(20) UNSIGNED NOT NULL,
  `prize_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2024_01_01_000001_create_settings_table', 1),
(5, '2024_01_01_000002_create_participants_table', 1),
(6, '2024_01_01_000003_create_prizes_table', 1),
(7, '2024_01_01_000004_create_winners_table', 1),
(8, '2024_01_01_000005_create_exclusion_groups_tables', 1),
(9, '2024_01_01_000006_create_prize_picks_table', 1),
(10, '2024_01_01_000007_drop_prize_mode', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `participants`
--

CREATE TABLE `participants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nip` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `department` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `prizes`
--

CREATE TABLE `prizes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `sponsor` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `quota` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `per_draw` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_selected` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `prizes`
--

INSERT INTO `prizes` (`id`, `name`, `sponsor`, `image_path`, `quota`, `per_draw`, `sort_order`, `is_selected`, `created_at`, `updated_at`) VALUES
(2, 'Scooter Listrik', NULL, 'hadiah/rIJJULSd2doCVF5469LSiDLo92bmLZLKkYsoTCbE.png', 1, 1, 1, 0, '2026-10-14 02:51:43', '2026-10-14 02:51:43');

-- --------------------------------------------------------

--
-- Struktur dari tabel `prize_picks`
--

CREATE TABLE `prize_picks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `prize_id` bigint(20) UNSIGNED NOT NULL,
  `participant_id` bigint(20) UNSIGNED NOT NULL,
  `position` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('CJQQ8Y5dXvfKLWLbgeFW4lFRMOiqzP26hrMXD5A9', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiR3BQaTZuZjhMelk0WlJHQ0VBMGhUMWdnQlNGWUI4Q1VIMjVmRnhyZyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9tYXN1ayI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1791971410),
('M531C1HwTn1Hk5iRkVcvDmKeDukVQBjbv03yHlCw', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36 Edg/155.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZXE0cklTTnUzM2VqYVBiNlZHVnJjOWY5V1RRdkJoam5HQUxCRXV5USI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9tYXN1ayI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=', 1791971259),
('RI3X3yqTX8bpgIfemamSqs2CpOPqSeo1hCJP6Wsx', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiYnFZSzJiUWRGSVA4VGh4Z1FpZXlNSmlQdXlGRG9qZmJRU3F6NmlUUiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo0OiJkcmF3Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czoxNToiZG9vcnByaXplX2FkbWluIjtzOjc6InBhbml0aWEiO30=', 1791971882),
('s4gpT0YU4Nbj9Wga7zYNa6FpPMFJzP0S3Nib4y3A', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36 Edg/155.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVGJNNkJFZDBOaUJ4ckJrRXZHWjM5b1R4anZ4dVZYekZZaTlaOTJySCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9wYW5lbC9oYWRpYWgiO3M6NToicm91dGUiO3M6MTg6ImFkbWluLnByaXplcy5pbmRleCI7fXM6MTU6ImRvb3Jwcml6ZV9hZG1pbiI7czo3OiJwYW5pdGlhIjt9', 1791971342);

-- --------------------------------------------------------

--
-- Struktur dari tabel `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'background_path', 'tampilan/LBUMQt6Tg6ZZuyfsgZfJDjd7CkbbT7vunwHLUEWh.jpg', '2026-10-14 02:51:16', '2026-10-14 02:51:16'),
(2, 'event_title', NULL, '2026-10-14 02:51:16', '2026-10-14 02:51:16'),
(3, 'event_subtitle', NULL, '2026-10-14 02:51:16', '2026-10-14 02:51:16'),
(4, 'background_color', '#8B5CF6', '2026-10-14 02:51:16', '2026-10-14 02:51:16'),
(5, 'name_columns', 'auto', '2026-10-14 02:51:16', '2026-10-14 02:51:16'),
(6, 'board_mode', 'daftar', '2026-10-14 02:51:16', '2026-10-14 02:51:16'),
(7, 'stop_style', 'langsung', '2026-10-14 02:51:16', '2026-10-14 02:51:16'),
(8, 'overflow_mode', 'gulir_manual', '2026-10-14 02:51:16', '2026-10-14 02:51:16'),
(9, 'min_font', '16', '2026-10-14 02:51:16', '2026-10-14 02:51:16'),
(10, 'draw_moment', 'start', '2026-10-14 02:51:16', '2026-10-14 02:51:16'),
(11, 'layout_mode', 'tegak', '2026-10-14 02:51:16', '2026-10-14 02:55:55'),
(12, 'name_caption', 'tidak', '2026-10-14 02:51:16', '2026-10-14 02:51:16'),
(13, 'winner_refresh', '20', '2026-10-14 02:51:16', '2026-10-14 02:51:16'),
(14, 'name_case', 'asli', '2026-10-14 02:51:16', '2026-10-14 02:51:16'),
(15, 'auto_stop', '', '2026-10-14 02:51:16', '2026-10-14 02:51:16'),
(16, 'spin_duration', '5', '2026-10-14 02:51:16', '2026-10-14 02:51:16'),
(17, 'sound_enabled', '', '2026-10-14 02:51:16', '2026-10-14 02:51:16'),
(18, 'one_win_per_person', '', '2026-10-14 02:51:16', '2026-10-14 02:51:16'),
(19, 'show_title', '', '2026-10-14 02:51:16', '2026-10-14 02:55:55'),
(20, 'lock_screen_menu', '', '2026-10-14 02:51:16', '2026-10-14 02:51:16');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `winners`
--

CREATE TABLE `winners` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `prize_id` bigint(20) UNSIGNED NOT NULL,
  `participant_id` bigint(20) UNSIGNED NOT NULL,
  `round` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `is_claimed` tinyint(1) NOT NULL DEFAULT 0,
  `drawn_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `exclusion_groups`
--
ALTER TABLE `exclusion_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `exclusion_group_participant`
--
ALTER TABLE `exclusion_group_participant`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `grup_peserta_unik` (`exclusion_group_id`,`participant_id`),
  ADD KEY `exclusion_group_participant_participant_id_foreign` (`participant_id`);

--
-- Indeks untuk tabel `exclusion_group_prize`
--
ALTER TABLE `exclusion_group_prize`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `grup_hadiah_unik` (`exclusion_group_id`,`prize_id`),
  ADD KEY `exclusion_group_prize_prize_id_foreign` (`prize_id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `participants_nip_unique` (`nip`),
  ADD KEY `participants_name_index` (`name`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `prizes`
--
ALTER TABLE `prizes`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `prize_picks`
--
ALTER TABLE `prize_picks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prize_picks_prize_id_participant_id_unique` (`prize_id`,`participant_id`),
  ADD KEY `prize_picks_participant_id_foreign` (`participant_id`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indeks untuk tabel `winners`
--
ALTER TABLE `winners`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `winners_prize_id_participant_id_unique` (`prize_id`,`participant_id`),
  ADD KEY `winners_participant_id_foreign` (`participant_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `exclusion_groups`
--
ALTER TABLE `exclusion_groups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `exclusion_group_participant`
--
ALTER TABLE `exclusion_group_participant`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `exclusion_group_prize`
--
ALTER TABLE `exclusion_group_prize`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `participants`
--
ALTER TABLE `participants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `prizes`
--
ALTER TABLE `prizes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `prize_picks`
--
ALTER TABLE `prize_picks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `winners`
--
ALTER TABLE `winners`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `exclusion_group_participant`
--
ALTER TABLE `exclusion_group_participant`
  ADD CONSTRAINT `exclusion_group_participant_exclusion_group_id_foreign` FOREIGN KEY (`exclusion_group_id`) REFERENCES `exclusion_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exclusion_group_participant_participant_id_foreign` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `exclusion_group_prize`
--
ALTER TABLE `exclusion_group_prize`
  ADD CONSTRAINT `exclusion_group_prize_exclusion_group_id_foreign` FOREIGN KEY (`exclusion_group_id`) REFERENCES `exclusion_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exclusion_group_prize_prize_id_foreign` FOREIGN KEY (`prize_id`) REFERENCES `prizes` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `prize_picks`
--
ALTER TABLE `prize_picks`
  ADD CONSTRAINT `prize_picks_participant_id_foreign` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prize_picks_prize_id_foreign` FOREIGN KEY (`prize_id`) REFERENCES `prizes` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `winners`
--
ALTER TABLE `winners`
  ADD CONSTRAINT `winners_participant_id_foreign` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `winners_prize_id_foreign` FOREIGN KEY (`prize_id`) REFERENCES `prizes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
