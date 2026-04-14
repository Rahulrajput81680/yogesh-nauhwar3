<?php
/**
 * Frontend DB bootstrap for public pages.
 */

if (!function_exists('frontend_db')) {
  function frontend_db(): PDO
  {
    static $pdo = null;

    if ($pdo instanceof PDO) {
      return $pdo;
    }

    if (!defined('ADMIN_INIT')) {
      define('ADMIN_INIT', true);
    }

    $configPath = __DIR__ . '/../admin/config/config.php';
    if (!is_file($configPath)) {
      $configPath = __DIR__ . '/../admin/config/config.example.php';
    }
    require_once $configPath;

    if (isset($pdo) && $pdo instanceof PDO) {
      $GLOBALS['pdo'] = $pdo;
    } elseif (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
      $pdo = $GLOBALS['pdo'];
    } else {
      throw new RuntimeException('Database connection is not available.');
    }

    $schemaBootstrap = __DIR__ . '/../admin/core/schema_bootstrap.php';
    if (is_file($schemaBootstrap)) {
      require_once $schemaBootstrap;
      if (function_exists('admin_ensure_runtime_schema')) {
        admin_ensure_runtime_schema($pdo);
      }
    }

    return $pdo;
  }
}

if (!function_exists('frontend_escape')) {
  function frontend_escape($value): string
  {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
  }
}

if (!function_exists('frontend_display_text')) {
  function frontend_display_text($value): string
  {
    return frontend_escape(html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8'));
  }
}

if (!function_exists('frontend_plain_text')) {
  function frontend_plain_text($value): string
  {
    $decoded = html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');
    return trim(preg_replace('/\s+/u', ' ', strip_tags($decoded)));
  }
}

if (!function_exists('frontend_upload_url')) {
  function frontend_upload_url(string $path): string
  {
    $normalized = str_replace('\\', '/', $path);
    $clean = ltrim($normalized, '/');
    if (strpos($clean, 'uploads/') === 0) {
      return $clean;
    }
    return 'uploads/' . $clean;
  }
}

if (!function_exists('frontend_gallery_items')) {
  function frontend_gallery_items(PDO $pdo, string $section = 'gallery', int $limit = 0, int $offset = 0): array
  {
    $where = "status = 'active'";
    $params = [];

    if (frontend_has_column($pdo, 'gallery', 'display_section')) {
      $where .= ' AND display_section = ?';
      $params[] = $section;
    }

    $sql = "SELECT title, image, category FROM gallery WHERE {$where} ORDER BY created_at DESC, id DESC";
    if ($limit > 0) {
      $sql .= ' LIMIT ' . (int) $limit;
    }
    if ($offset > 0) {
      $sql .= ' OFFSET ' . (int) $offset;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}

if (!function_exists('frontend_gallery_count')) {
  function frontend_gallery_count(PDO $pdo, string $section = 'gallery'): int
  {
    $where = "status = 'active'";
    $params = [];

    if (frontend_has_column($pdo, 'gallery', 'display_section')) {
      $where .= ' AND display_section = ?';
      $params[] = $section;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM gallery WHERE {$where}");
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
  }
}

if (!function_exists('frontend_has_column')) {
  function frontend_has_column(PDO $pdo, string $table, string $column): bool
  {
    static $cache = [];
    $cacheKey = $table . '.' . $column;

    if (array_key_exists($cacheKey, $cache)) {
      return $cache[$cacheKey];
    }

    try {
      $stmt = $pdo->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
      $stmt->execute([$table, $column]);
      $cache[$cacheKey] = (int) $stmt->fetchColumn() > 0;
    } catch (Throwable $e) {
      $cache[$cacheKey] = false;
    }

    return $cache[$cacheKey];
  }
}
