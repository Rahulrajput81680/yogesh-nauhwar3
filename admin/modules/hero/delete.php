<?php
/**
 * Hero Section Management – Delete a hero item
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_login();
require_module('hero');
require_permission('hero_delete');

$id = (int) ($_GET['id'] ?? 0);
$csrf = $_GET['csrf'] ?? '';

if (!$id || !validate_csrf_token($csrf)) {
  set_flash('error', 'Invalid or expired request.');
  redirect(ADMIN_URL . '/modules/hero/index.php');
}

try {
  $stmt = $pdo->prepare("SELECT * FROM hero_items WHERE id = ? LIMIT 1");
  $stmt->execute([$id]);
  $item = $stmt->fetch();

  if (!$item) {
    set_flash('error', 'Hero item not found.');
    redirect(ADMIN_URL . '/modules/hero/index.php');
  }

  // Remove physical image file
  if (!empty($item['background_image'])) {
    $path = UPLOAD_DIR . '/' . $item['background_image'];
    if (file_exists($path)) {
      @unlink($path);
    }
  }

  $pdo->prepare("DELETE FROM hero_items WHERE id = ?")->execute([$id]);
  log_activity('delete', 'hero', $id, 'Deleted hero item: ' . $item['heading']);
  set_flash('success', 'Hero item deleted.');

} catch (PDOException $e) {
  error_log('Hero delete error: ' . $e->getMessage());
  set_flash('error', 'Failed to delete hero item.');
}

redirect(ADMIN_URL . '/modules/hero/index.php');
