<?php
/**
 * Admin Panel Initialization
 * 
 * This file initializes the admin panel system,
 * starts sessions, loads configuration, and includes core files.
 * Include this file at the top of every admin page.
 */

// Define admin initialization constant
define('ADMIN_INIT', true);

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
  ini_set('session.cookie_httponly', 1);
  ini_set('session.use_only_cookies', 1);
  ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
  ini_set('session.cookie_samesite', 'Strict');

  session_start();

  // Regenerate session ID periodically for security
  if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
  } elseif (time() - $_SESSION['created'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
  }
}

// Load configuration
$configPath = __DIR__ . '/config/config.php';
if (!is_file($configPath)) {
  die('Missing admin/config/config.php. Create it before running admin panel.');
}
require_once $configPath;

// Load module activation configuration
$modules = require_once __DIR__ . '/config/modules.php';

// Load core files
require_once __DIR__ . '/core/security.php';
require_once __DIR__ . '/core/functions.php';
require_once __DIR__ . '/core/permissions.php';
require_once __DIR__ . '/core/mailer.php';
require_once __DIR__ . '/core/password_reset.php';
require_once __DIR__ . '/core/schema_bootstrap.php';

// Ensure critical tables/columns exist for mixed or partial DB imports.
if (isset($pdo) && $pdo instanceof PDO) {
  admin_ensure_runtime_schema($pdo);
}

// Check session timeout for logged-in users
if (is_logged_in()) {
  check_session_timeout();
}
