<?php


// Prevent direct access
if (!defined('ADMIN_INIT')) {
  die('Direct access not permitted');
}

/**
 * Generate CSRF Token
 */
function generate_csrf_token()
{
  if (
    !isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) ||
    (time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_EXPIRE
  ) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_token_time'] = time();
  }
  return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF Token
 */
function validate_csrf_token($token)
{
  if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
    return false;
  }

  if ((time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_EXPIRE) {
    return false;
  }

  return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Output CSRF Token Field
 */
function csrf_field()
{
  $token = generate_csrf_token();
  return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Sanitize Input String
 */
function sanitize_input($data)
{
  if (is_array($data)) {
    return array_map('sanitize_input', $data);
  }
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
  return $data;
}

/**
 * Escape Output (Prevent XSS)
 */
function escape($data)
{
  if (is_array($data)) {
    return array_map('escape', $data);
  }
  return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize Filename
 */
function sanitize_filename($filename)
{
  $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
  $filename = preg_replace('/_+/', '_', $filename);
  return $filename;
}

/**
 * Generate Slug from String
 */
function generate_slug($string)
{
  $string = strtolower(trim($string));
  $string = preg_replace('/[^a-z0-9-]/', '-', $string);
  $string = preg_replace('/-+/', '-', $string);
  $string = trim($string, '-');
  return $string;
}

/**
 * Redirect to URL
 */
function redirect($url)
{
  header("Location: " . $url);
  exit();
}

/**
 * Check if User is Logged In
 */
function is_logged_in()
{
  return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require Login (Redirect if Not Logged In)
 */
function require_login()
{
  if (!is_logged_in()) {
    redirect(ADMIN_URL . '/login.php');
  }
}

/**
 * Check Session Timeout
 */
function check_session_timeout()
{
  if (isset($_SESSION['last_activity'])) {
    if ((time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
      session_destroy();
      redirect(ADMIN_URL . '/login.php?timeout=1');
    }
  }
  $_SESSION['last_activity'] = time();
}

/**
 * Set Flash Message
 */
function set_flash($type, $message)
{
  $_SESSION['flash_type'] = $type;
  $_SESSION['flash_message'] = $message;
}

/**
 * Get and Clear Flash Message
 */
function get_flash()
{
  if (isset($_SESSION['flash_message'])) {
    $flash = [
      'type' => $_SESSION['flash_type'],
      'message' => $_SESSION['flash_message']
    ];
    unset($_SESSION['flash_type']);
    unset($_SESSION['flash_message']);
    return $flash;
  }
  return null;
}

/**
 * Display Flash Message HTML
 */
function display_flash()
{
  $flash = get_flash();
  if ($flash) {
    $alertClass = $flash['type'] === 'success' ? 'alert-success' :
      ($flash['type'] === 'error' ? 'alert-danger' : 'alert-info');
    echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
    echo escape($flash['message']);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    echo '</div>';
  }
}

/**
 * Validate Email
 */
function is_valid_email($email)
{
  return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash Password
 */
function hash_password($password)
{
  return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verify_password($password, $hash)
{
  return password_verify($password, $hash);
}
