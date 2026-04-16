<?php
/**
 * Frontend DB bootstrap for public pages.
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

if (!function_exists('frontend_translation_data')) {
  function frontend_translation_data(): array
  {
    static $data = null;
    if (is_array($data)) {
      return $data;
    }

    $path = __DIR__ . '/translations.php';
    if (!is_file($path)) {
      $data = ['keys' => [], 'phrases' => []];
      return $data;
    }

    $loaded = require $path;
    $data = is_array($loaded) ? $loaded : ['keys' => [], 'phrases' => []];
    if (!isset($data['keys']) || !is_array($data['keys'])) {
      $data['keys'] = [];
    }
    if (!isset($data['phrases']) || !is_array($data['phrases'])) {
      $data['phrases'] = [];
    }

    if (!isset($data['content']) || !is_array($data['content'])) {
      $data['content'] = [];
    }

    if (isset($data['content']) && is_array($data['content'])) {
      $derived = frontend_content_phrase_map($data['content']);
      $data['phrases'] = $derived + $data['phrases'];
    }

    return $data;
  }
}

if (!function_exists('frontend_content_phrase_map')) {
  function frontend_content_phrase_map(array $node): array
  {
    $map = [];

    $walk = function ($value) use (&$walk, &$map): void {
      if (!is_array($value)) {
        return;
      }

      if (isset($value['en'], $value['hi']) && is_string($value['en']) && is_string($value['hi']) && $value['en'] !== '' && $value['hi'] !== '') {
        $map[$value['en']] = $value['hi'];
      }

      foreach ($value as $child) {
        if (is_array($child)) {
          $walk($child);
        }
      }
    };

    $walk($node);
    return $map;
  }
}

if (!function_exists('frontend_supported_languages')) {
  function frontend_supported_languages(): array
  {
    return ['en', 'hi'];
  }
}

if (!function_exists('frontend_current_lang')) {
  function frontend_current_lang(): string
  {
    static $lang = null;
    if ($lang !== null) {
      return $lang;
    }

    $allowed = frontend_supported_languages();

    if (isset($_GET['lang']) && in_array($_GET['lang'], $allowed, true)) {
      $lang = $_GET['lang'];
      $_SESSION['site_lang'] = $lang;
      setcookie('site_lang', $lang, time() + (86400 * 30), '/');
      return $lang;
    }

    if (isset($_SESSION['site_lang']) && in_array($_SESSION['site_lang'], $allowed, true)) {
      $lang = $_SESSION['site_lang'];
      return $lang;
    }

    if (isset($_COOKIE['site_lang']) && in_array($_COOKIE['site_lang'], $allowed, true)) {
      $lang = $_COOKIE['site_lang'];
      $_SESSION['site_lang'] = $lang;
      return $lang;
    }

    $lang = 'en';
    $_SESSION['site_lang'] = $lang;
    return $lang;
  }
}

if (!function_exists('frontend_translate')) {
  function frontend_translate(string $key, string $fallback = ''): string
  {
    $data = frontend_translation_data();
    $lang = frontend_current_lang();
    $defaultText = $fallback !== '' ? $fallback : $key;

    if (!isset($data['keys'][$key]) || !is_array($data['keys'][$key])) {
      return $defaultText;
    }

    $entry = $data['keys'][$key];
    if (isset($entry[$lang]) && $entry[$lang] !== '') {
      return (string) $entry[$lang];
    }

    if (isset($entry['en']) && $entry['en'] !== '') {
      return (string) $entry['en'];
    }

    return $defaultText;
  }
}

if (!function_exists('translate')) {
  function translate(string $key, string $fallback = ''): string
  {
    return frontend_translate($key, $fallback);
  }
}

if (!function_exists('frontend_translate_raw_phrase')) {
  function frontend_translate_raw_phrase(string $text): string
  {
    if (frontend_current_lang() !== 'hi') {
      return $text;
    }

    $data = frontend_translation_data();
    $phrases = $data['phrases'];
    if (empty($phrases)) {
      return $text;
    }

    static $patterns = null;
    if ($patterns === null) {
      $patterns = [];
      $sorted = $phrases;
      uksort($sorted, static function ($a, $b) {
        return strlen($b) <=> strlen($a);
      });

      foreach ($sorted as $source => $target) {
        if (!is_string($source) || !is_string($target) || $source === '') {
          continue;
        }

        $escaped = preg_quote($source, '~');
        $escaped = preg_replace('~\\s+~u', '\\s+', $escaped);
        $patterns[] = ['pattern' => '~' . $escaped . '~u', 'replace' => $target];
      }
    }

    foreach ($patterns as $rule) {
      $text = preg_replace($rule['pattern'], $rule['replace'], $text);
    }

    return $text;
  }
}

if (!function_exists('frontend_translate_output_buffer')) {
  function frontend_translate_output_buffer(string $html): string
  {
    if (frontend_current_lang() !== 'hi') {
      return $html;
    }

    return frontend_translate_raw_phrase($html);
  }
}

if (!function_exists('frontend_content')) {
  function frontend_content(string $path, string $lang = '')
  {
    $data = frontend_translation_data();
    $lang = $lang !== '' ? $lang : frontend_current_lang();
    $segments = array_values(array_filter(explode('.', $path), static function ($segment) {
      return $segment !== '';
    }));

    $cursor = $data['content'] ?? [];
    foreach ($segments as $segment) {
      if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
        return null;
      }
      $cursor = $cursor[$segment];
    }

    if (is_array($cursor) && isset($cursor[$lang])) {
      return $cursor[$lang];
    }

    return $cursor;
  }
}

if (!function_exists('frontend_content_list')) {
  function frontend_content_list(string $path, string $lang = ''): array
  {
    $lang = $lang !== '' ? $lang : frontend_current_lang();
    $segments = array_values(array_filter(explode('.', $path), static function ($segment) {
      return $segment !== '';
    }));

    $cursor = frontend_translation_data()['content'] ?? [];
    foreach ($segments as $segment) {
      if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
        return [];
      }
      $cursor = $cursor[$segment];
    }

    if (is_array($cursor) && isset($cursor[$lang]) && is_array($cursor[$lang])) {
      $cursor = $cursor[$lang];
    } elseif (is_array($cursor) && isset($cursor['en']) && is_array($cursor['en'])) {
      $cursor = $cursor['en'];
    }

    if (!is_array($cursor)) {
      return [];
    }

    $items = [];
    foreach ($cursor as $entry) {
      if (is_string($entry)) {
        $text = trim($entry);
        if ($text !== '') {
          $items[] = $text;
        }
        continue;
      }

      if (!is_array($entry)) {
        continue;
      }

      $text = '';
      if (isset($entry[$lang]) && is_string($entry[$lang])) {
        $text = $entry[$lang];
      } elseif (isset($entry['en']) && is_string($entry['en'])) {
        $text = $entry['en'];
      } elseif (isset($entry['hi']) && is_string($entry['hi'])) {
        $text = $entry['hi'];
      }

      $text = trim($text);
      if ($text !== '') {
        $items[] = $text;
      }
    }

    return $items;
  }
}

if (!defined('FRONTEND_LANG_BUFFER_STARTED')) {
  define('FRONTEND_LANG_BUFFER_STARTED', true);
  if (frontend_current_lang() === 'hi' && PHP_SAPI !== 'cli') {
    ob_start('frontend_translate_output_buffer');
  }
}

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
      throw new RuntimeException('Missing admin/config/config.php. Create it before running frontend pages.');
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
    $plain = trim(preg_replace('/\s+/u', ' ', strip_tags($decoded)));
    return frontend_translate_raw_phrase($plain);
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
