<?php


require_once __DIR__ . '/init.php';

// Check if user is logged in
if (is_logged_in()) {
  redirect(ADMIN_URL . '/dashboard.php');
} else {
  redirect(ADMIN_URL . '/login.php');
}
