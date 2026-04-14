<?php


// Prevent direct access
if (!defined('ADMIN_INIT')) {
  die('Direct access not permitted');
}

define('DB_HOST', 'localhost');
define('DB_NAME', 'shared-admin-panel');        // ← CHANGE THIS
define('DB_USER', 'your_database_user');        // ← CHANGE THIS
define('DB_PASS', 'root');    // ← CHANGE THIS
define('DB_CHARSET', 'utf8mb4');


// Update with your project URL (no trailing slash)
define('BASE_URL', 'http://localhost/your-project');  // ← CHANGE THIS
define('ADMIN_URL', BASE_URL . '/admin');

// Project name (displayed in admin panel header and titles)
define('PROJECT_NAME', 'Admin Panel');  // ← CHANGE THIS

// Admin session name (change for each project for security)
// This should be unique for each website to prevent session conflicts
define('SESSION_NAME', 'admin_session_' . md5(BASE_URL));

// Session timeout in seconds
// Default: 3600 = 1 hour
// Increase for longer sessions, decrease for more security
define('SESSION_TIMEOUT', 3600);


// Upload directory (absolute path)
define('UPLOAD_DIR', dirname(dirname(__DIR__)) . '/uploads');

// Upload URL (for displaying images)
define('UPLOAD_URL', BASE_URL . '/uploads');

define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);

define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

define('THUMB_WIDTH', 300);
define('THUMB_HEIGHT', 300);


// Number of items to display per page in listings
define('ITEMS_PER_PAGE', 10);


// CSRF token expiration time in seconds
define('CSRF_TOKEN_EXPIRE', 3600);

// Minimum password length for admin users
define('MIN_PASSWORD_LENGTH', 8);


date_default_timezone_set('UTC');


// Development: Show all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);


try {
  $pdo = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
    DB_USER,
    DB_PASS,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false
    ]
  );
} catch (PDOException $e) {
  // In production, you might want to display a generic error message
  die('Database connection failed: ' . $e->getMessage());
}


