<?php
/**
 * Admin Logout
 */

require_once __DIR__ . '/init.php';

// Check if user is logged in
if (is_logged_in()) {
  // Log activity before destroying session
  log_activity('logout', 'authentication', $_SESSION['admin_id'], 'User logged out');

  // Destroy session
  session_unset();
  session_destroy();
}

// Redirect to login page (without flash message to avoid showing on next login)
redirect(ADMIN_URL . '/login.php');
