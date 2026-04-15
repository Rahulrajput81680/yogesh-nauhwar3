<?php

if (!defined('ADMIN_INIT')) {
  die('Direct access not permitted');
}

if (!function_exists('admin_schema_column_exists')) {
  function admin_schema_column_exists(PDO $pdo, string $table, string $column): bool
  {
    try {
      $stmt = $pdo->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
      $stmt->execute([$table, $column]);
      return (int) $stmt->fetchColumn() > 0;
    } catch (Throwable $e) {
      return false;
    }
  }
}

if (!function_exists('admin_ensure_runtime_schema')) {
  function admin_ensure_runtime_schema(PDO $pdo): void
  {
    static $bootstrapped = false;

    if ($bootstrapped) {
      return;
    }
    $bootstrapped = true;

    try {
      $pdo->exec("CREATE TABLE IF NOT EXISTS `contact_messages` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `email` varchar(100) NOT NULL,
        `phone` varchar(20) DEFAULT NULL,
        `location` varchar(255) DEFAULT NULL,
        `date_of_birth` varchar(50) DEFAULT NULL,
        `occupation` varchar(100) DEFAULT NULL,
        `subject` varchar(255) DEFAULT NULL,
        `message` text NOT NULL,
        `status` enum('unread','read') DEFAULT 'unread',
        `ip_address` varchar(45) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_status` (`status`),
        KEY `idx_email` (`email`),
        KEY `idx_created_at` (`created_at`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

      $pdo->exec("CREATE TABLE IF NOT EXISTS `events` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `slug` varchar(255) NOT NULL,
        `description` text,
        `image` varchar(255) DEFAULT NULL,
        `category` varchar(120) DEFAULT NULL,
        `event_type` enum('upcoming','past') NOT NULL DEFAULT 'upcoming',
        `event_date` date DEFAULT NULL,
        `status` enum('active','inactive') NOT NULL DEFAULT 'active',
        `created_by` int(11) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `slug` (`slug`),
        KEY `idx_events_type` (`event_type`),
        KEY `idx_events_status` (`status`),
        KEY `idx_events_date` (`event_date`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

      if (!admin_schema_column_exists($pdo, 'events', 'slug')) {
        $pdo->exec("ALTER TABLE `events` ADD COLUMN `slug` varchar(255) NOT NULL DEFAULT '' AFTER `title`");
      }
      if (!admin_schema_column_exists($pdo, 'events', 'event_type')) {
        $pdo->exec("ALTER TABLE `events` ADD COLUMN `event_type` enum('upcoming','past') NOT NULL DEFAULT 'upcoming' AFTER `category`");
      }
      if (!admin_schema_column_exists($pdo, 'events', 'event_date')) {
        $pdo->exec("ALTER TABLE `events` ADD COLUMN `event_date` date DEFAULT NULL AFTER `event_type`");
      }
      if (!admin_schema_column_exists($pdo, 'events', 'status')) {
        $pdo->exec("ALTER TABLE `events` ADD COLUMN `status` enum('active','inactive') NOT NULL DEFAULT 'active' AFTER `event_date`");
      }

      $pdo->exec("CREATE TABLE IF NOT EXISTS `blogs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `slug` varchar(255) NOT NULL,
        `content` text NOT NULL,
        `thumbnail` varchar(255) DEFAULT NULL,
        `category` varchar(100) DEFAULT NULL,
        `status` enum('draft','published') DEFAULT 'draft',
        `seo_title` varchar(255) DEFAULT NULL,
        `seo_description` text,
        `meta_keywords` varchar(255) DEFAULT NULL,
        `tags` varchar(500) DEFAULT NULL,
        `author_id` int(11) DEFAULT NULL,
        `deleted_at` datetime DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `slug` (`slug`),
        KEY `idx_slug` (`slug`),
        KEY `idx_status` (`status`),
        KEY `idx_category` (`category`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

      if (!admin_schema_column_exists($pdo, 'blogs', 'thumbnail')) {
        $pdo->exec("ALTER TABLE `blogs` ADD COLUMN `thumbnail` varchar(255) DEFAULT NULL AFTER `content`");
      }
      if (!admin_schema_column_exists($pdo, 'blogs', 'seo_title')) {
        $pdo->exec("ALTER TABLE `blogs` ADD COLUMN `seo_title` varchar(255) DEFAULT NULL AFTER `status`");
      }
      if (!admin_schema_column_exists($pdo, 'blogs', 'seo_description')) {
        $pdo->exec("ALTER TABLE `blogs` ADD COLUMN `seo_description` text AFTER `seo_title`");
      }
      if (!admin_schema_column_exists($pdo, 'blogs', 'meta_keywords')) {
        $pdo->exec("ALTER TABLE `blogs` ADD COLUMN `meta_keywords` varchar(255) DEFAULT NULL AFTER `seo_description`");
      }
      if (!admin_schema_column_exists($pdo, 'blogs', 'tags')) {
        $pdo->exec("ALTER TABLE `blogs` ADD COLUMN `tags` varchar(500) DEFAULT NULL AFTER `meta_keywords`");
      }
      if (!admin_schema_column_exists($pdo, 'blogs', 'deleted_at')) {
        $pdo->exec("ALTER TABLE `blogs` ADD COLUMN `deleted_at` datetime DEFAULT NULL AFTER `author_id`");
      }
      if (admin_schema_column_exists($pdo, 'blogs', 'featured_image') && admin_schema_column_exists($pdo, 'blogs', 'thumbnail')) {
        $pdo->exec("UPDATE `blogs` SET `thumbnail` = `featured_image` WHERE (`thumbnail` IS NULL OR `thumbnail` = '') AND `featured_image` IS NOT NULL AND `featured_image` != ''");
      }

      $pdo->exec("CREATE TABLE IF NOT EXISTS `gallery` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `image` varchar(255) NOT NULL,
        `category` varchar(100) DEFAULT NULL,
        `display_section` enum('gallery','our_work','media_coverage') NOT NULL DEFAULT 'gallery',
        `status` enum('active','inactive') DEFAULT 'active',
        `uploaded_by` int(11) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_status` (`status`),
        KEY `idx_category` (`category`),
        KEY `idx_display_section` (`display_section`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

      if (!admin_schema_column_exists($pdo, 'gallery', 'category')) {
        $pdo->exec("ALTER TABLE `gallery` ADD COLUMN `category` varchar(100) DEFAULT NULL AFTER `image`");
      }
      if (!admin_schema_column_exists($pdo, 'gallery', 'display_section')) {
        $pdo->exec("ALTER TABLE `gallery` ADD COLUMN `display_section` enum('gallery','our_work','media_coverage') NOT NULL DEFAULT 'gallery' AFTER `category`");
      }
      if (!admin_schema_column_exists($pdo, 'gallery', 'status')) {
        $pdo->exec("ALTER TABLE `gallery` ADD COLUMN `status` enum('active','inactive') DEFAULT 'active' AFTER `display_section`");
      }
      if (!admin_schema_column_exists($pdo, 'gallery', 'uploaded_by')) {
        $pdo->exec("ALTER TABLE `gallery` ADD COLUMN `uploaded_by` int(11) DEFAULT NULL AFTER `status`");
      }

      $pdo->exec("CREATE TABLE IF NOT EXISTS `admin_settings` (
        `setting_key` varchar(100) NOT NULL,
        `setting_value` text DEFAULT NULL,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`setting_key`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (Throwable $e) {
      error_log('Schema bootstrap error: ' . $e->getMessage());
    }
  }
}
