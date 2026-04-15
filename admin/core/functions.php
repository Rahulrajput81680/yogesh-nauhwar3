<?php
/**
 * Core Helper Functions
 * 
 * General utility functions used throughout the admin panel.
 */

// Prevent direct access
if (!defined('ADMIN_INIT')) {
  die('Direct access not permitted');
}

function format_date($date, $format = 'M d, Y')
{
  return date($format, strtotime($date));
}

/**
 * Format DateTime
 */
function format_datetime($datetime, $format = 'M d, Y h:i A')
{
  return date($format, strtotime($datetime));
}

/**
 * Truncate String
 */
function truncate($string, $length = 100, $suffix = '...')
{
  if (strlen($string) <= $length) {
    return $string;
  }
  return substr($string, 0, $length) . $suffix;
}

/**
 * Get File Extension
 */
function get_file_extension($filename)
{
  return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Format File Size
 */
function format_file_size($bytes)
{
  $units = ['B', 'KB', 'MB', 'GB'];
  $i = 0;
  while ($bytes >= 1024 && $i < count($units) - 1) {
    $bytes /= 1024;
    $i++;
  }
  return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Generate Unique Filename
 */
function generate_unique_filename($originalName)
{
  $extension = get_file_extension($originalName);
  $basename = pathinfo($originalName, PATHINFO_FILENAME);
  $basename = sanitize_filename($basename);
  return $basename . '_' . time() . '_' . uniqid() . '.' . $extension;
}

/**
 * Create Pagination
 */
function create_pagination($currentPage, $totalPages, $baseUrl)
{
  if ($totalPages <= 1) {
    return '';
  }

  $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';

  // Previous button
  if ($currentPage > 1) {
    $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($currentPage - 1) . '">Previous</a></li>';
  } else {
    $html .= '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
  }

  // Page numbers
  $range = 2;
  for ($i = 1; $i <= $totalPages; $i++) {
    if ($i == 1 || $i == $totalPages || ($i >= $currentPage - $range && $i <= $currentPage + $range)) {
      if ($i == $currentPage) {
        $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
      } else {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a></li>';
      }
    } elseif ($i == $currentPage - $range - 1 || $i == $currentPage + $range + 1) {
      $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
  }

  // Next button
  if ($currentPage < $totalPages) {
    $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($currentPage + 1) . '">Next</a></li>';
  } else {
    $html .= '<li class="page-item disabled"><span class="page-link">Next</span></li>';
  }

  $html .= '</ul></nav>';
  return $html;
}

/**
 * Log Activity
 */
function log_activity($action, $module, $item_id = null, $details = null)
{
  global $pdo;

  try {
    $stmt = $pdo->prepare("
            INSERT INTO activity_log (admin_id, action, module, item_id, details, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

    $admin_id = $_SESSION['admin_id'] ?? null;
    $stmt->execute([$admin_id, $action, $module, $item_id, $details]);
  } catch (PDOException $e) {
    // Silently fail to not disrupt the main operation
    error_log('Activity log error: ' . $e->getMessage());
  }
}

/**
 * Get Recent Activities
 */
function get_recent_activities($limit = 10)
{
  global $pdo;

  try {
    $stmt = $pdo->prepare("
            SELECT al.*, au.username 
            FROM activity_log al
            LEFT JOIN admin_users au ON al.admin_id = au.id
            ORDER BY al.created_at DESC
            LIMIT ?
        ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
  } catch (PDOException $e) {
    return [];
  }
}

/**
 * Count Records in Table
 */
function count_records($table, $where = '1=1', $params = [])
{
  global $pdo;

  try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE $where");
    $stmt->execute($params);
    return $stmt->fetchColumn();
  } catch (PDOException $e) {
    return 0;
  }
}

/**
 * Get Status Badge HTML
 */
function get_status_badge($status)
{
  $class = $status === 'published' || $status === 'active' ? 'success' : 'secondary';
  $text = ucfirst($status);
  return '<span class="badge bg-' . $class . '">' . escape($text) . '</span>';
}

// =============================================================================
// MODULE ACTIVATION HELPERS
// =============================================================================

/**
 * Check whether a module is enabled in the modules configuration.
 *
 * Usage:
 *   if (is_module_enabled('hero')) { ... }
 *
 * @param string $moduleName  Key as defined in /config/modules.php
 * @return bool
 */
function is_module_enabled(string $moduleName): bool
{
  global $modules;

  if (!is_array($modules)) {
    return false;
  }

  return !empty($modules[$moduleName]);
}

/**
 * Enforce that a module is enabled before rendering a page.
 *
 * Call this at the top of every module page (e.g. modules/hero/index.php).
 * If the module is disabled the visitor is redirected to the dashboard with
 * a flash message. Execution stops immediately.
 *
 * @param string $moduleName  Key as defined in /config/modules.php
 * @return void
 */
function require_module(string $moduleName): void
{
  if (!is_module_enabled($moduleName)) {
    set_flash('error', 'The "' . ucfirst($moduleName) . '" module is currently disabled.');
    redirect(ADMIN_URL . '/dashboard.php');
    exit;
  }
}

/**
 * Get a persisted admin setting value.
 */
function get_admin_setting(string $key, ?string $default = null): ?string
{
  static $cache = [];

  if (array_key_exists($key, $cache)) {
    return $cache[$key];
  }

  global $pdo;
  if (!isset($pdo) || !($pdo instanceof PDO)) {
    return $default;
  }

  try {
    $stmt = $pdo->prepare('SELECT setting_value FROM admin_settings WHERE setting_key = ? LIMIT 1');
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();
    if ($value === false) {
      $cache[$key] = $default;
      return $default;
    }
    $cache[$key] = (string) $value;
    return $cache[$key];
  } catch (Throwable $e) {
    return $default;
  }
}

/**
 * Persist an admin setting value.
 */
function set_admin_setting(string $key, string $value): bool
{
  static $cache = [];

  global $pdo;
  if (!isset($pdo) || !($pdo instanceof PDO)) {
    return false;
  }

  try {
    $stmt = $pdo->prepare('INSERT INTO admin_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP');
    $ok = $stmt->execute([$key, $value]);
    if ($ok) {
      $cache[$key] = $value;
    }
    return $ok;
  } catch (Throwable $e) {
    return false;
  }
}
