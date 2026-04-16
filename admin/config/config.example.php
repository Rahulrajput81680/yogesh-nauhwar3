<?php
/**
 * Committable configuration template.
 * Copy this file to config.php and update secrets/credentials.
 */

if (!function_exists('admin_detect_base_url')) {
  function admin_detect_base_url(): string
  {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    $projectRoot = realpath(dirname(__DIR__, 2));
    $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;

    $basePath = '';
    if ($projectRoot && $documentRoot) {
      $normalizedProject = str_replace('\\', '/', $projectRoot);
      $normalizedDocument = rtrim(str_replace('\\', '/', $documentRoot), '/');

      if (strpos($normalizedProject, $normalizedDocument) === 0) {
        $basePath = substr($normalizedProject, strlen($normalizedDocument));
      }
    }

    if ($basePath === '' && isset($_SERVER['SCRIPT_NAME'])) {
      $scriptDir = str_replace('\\', '/', dirname((string) $_SERVER['SCRIPT_NAME']));
      $basePath = preg_replace('#/admin(?:/.*)?$#', '', $scriptDir) ?: '';
    }

    $basePath = '/' . trim((string) $basePath, '/');
    if ($basePath === '/') {
      $basePath = '';
    }

    return $scheme . '://' . $host . $basePath;
  }
}

$dbHost = 'localhost';
$dbName = 'shared-admin-panel';
$dbUser = 'root';
$dbPass = 'root';

$baseUrl = rtrim(admin_detect_base_url(), '/');
$projectName = 'Admin Panel';

if (!defined('DB_HOST'))
  define('DB_HOST', $dbHost);
if (!defined('DB_NAME'))
  define('DB_NAME', $dbName);
if (!defined('DB_USER'))
  define('DB_USER', $dbUser);
if (!defined('DB_PASS'))
  define('DB_PASS', $dbPass);

if (!defined('BASE_URL'))
  define('BASE_URL', $baseUrl);
if (!defined('ADMIN_URL'))
  define('ADMIN_URL', BASE_URL . '/admin');
if (!defined('PROJECT_NAME'))
  define('PROJECT_NAME', $projectName);
if (!defined('SESSION_NAME'))
  define('SESSION_NAME', md5(BASE_URL));
if (!defined('SESSION_TIMEOUT'))
  define('SESSION_TIMEOUT', 3600);

$uploadDir = dirname(__DIR__, 2) . '/uploads';
if (!defined('UPLOAD_DIR'))
  define('UPLOAD_DIR', $uploadDir);
if (!defined('UPLOAD_URL'))
  define('UPLOAD_URL', BASE_URL . '/uploads');
if (!defined('MAX_UPLOAD_SIZE'))
  define('MAX_UPLOAD_SIZE', 1 * 1024 * 1024);
if (!defined('ALLOWED_IMAGE_TYPES'))
  define('ALLOWED_IMAGE_TYPES', ['webp']);
if (!defined('THUMB_WIDTH'))
  define('THUMB_WIDTH', 300);
if (!defined('THUMB_HEIGHT'))
  define('THUMB_HEIGHT', 300);
if (!defined('ITEMS_PER_PAGE'))
  define('ITEMS_PER_PAGE', 10);
if (!defined('CSRF_TOKEN_EXPIRE'))
  define('CSRF_TOKEN_EXPIRE', 3600);
if (!defined('MIN_PASSWORD_LENGTH'))
  define('MIN_PASSWORD_LENGTH', 8);

$hostOnly = preg_replace('/:\\d+$/', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
if (!defined('MAIL_FROM_ADDRESS'))
  define('MAIL_FROM_ADDRESS', 'noreply@' . $hostOnly);
if (!defined('MAIL_FROM_NAME'))
  define('MAIL_FROM_NAME', PROJECT_NAME);
if (!defined('MAIL_SMTP_HOST'))
  define('MAIL_SMTP_HOST', '');
if (!defined('MAIL_SMTP_PORT'))
  define('MAIL_SMTP_PORT', 587);
if (!defined('MAIL_SMTP_USER'))
  define('MAIL_SMTP_USER', '');
if (!defined('MAIL_SMTP_PASS'))
  define('MAIL_SMTP_PASS', '');

if (!is_dir(UPLOAD_DIR)) {
  @mkdir(UPLOAD_DIR, 0755, true);
}

try {
  $pdo = new PDO(
    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
    DB_USER,
    DB_PASS,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    ]
  );

  $GLOBALS['pdo'] = $pdo;
} catch (PDOException $e) {
  die('Database connection failed. Copy config.example.php to config.php and set credentials. Details: ' . $e->getMessage());
}
