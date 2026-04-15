<?php
/**
 * Blog Management - Delete / Restore
 *
 * Actions:
 *   ?action=delete  (default) – hard delete: removes record + file
 *   ?action=restore            – restore trashed row (legacy compatibility)
 *   ?action=purge              – alias of hard delete
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_once dirname(dirname(__DIR__)) . '/core/uploader.php';
require_login();
require_module('blog');

$blog_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$action = in_array($_GET['action'] ?? '', ['delete', 'restore', 'purge'])
  ? $_GET['action']
  : 'delete';

// Permission check per action
if ($action === 'restore') {
  require_permission('blog_restore');
} else {
  require_permission('blog_delete');
}

// CSRF check
if (!validate_csrf_token($_GET['csrf'] ?? '')) {
  set_flash('error', 'Invalid security token.');
  redirect(ADMIN_URL . '/modules/blog/index.php');
}

if (!$blog_id) {
  set_flash('error', 'Invalid blog ID.');
  redirect(ADMIN_URL . '/modules/blog/index.php');
}

try {
  $stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ?");
  $stmt->execute([$blog_id]);
  $blog = $stmt->fetch();

  if (!$blog) {
    set_flash('error', 'Blog post not found.');
    redirect(ADMIN_URL . '/modules/blog/index.php');
  }

  if ($action === 'restore') {
    // Restore from trash
    $pdo->prepare("UPDATE blogs SET deleted_at = NULL WHERE id = ?")
      ->execute([$blog_id]);
    log_activity('restore', 'blog', $blog_id, "Restored blog: {$blog['title']}");
    set_flash('success', 'Blog post restored successfully.');
    redirect(ADMIN_URL . '/modules/blog/index.php?view=trash');

  } elseif ($action === 'delete' || $action === 'purge') {
    // Permanently delete record + thumbnail
    if (!empty($blog['thumbnail'])) {
      $uploader = new FileUploader();
      $uploader->delete($blog['thumbnail']);
    }
    $pdo->prepare("DELETE FROM blogs WHERE id = ?")
      ->execute([$blog_id]);
    log_activity('delete', 'blog', $blog_id, "Deleted blog: {$blog['title']}");
    set_flash('success', 'Blog post deleted successfully.');
    redirect(ADMIN_URL . '/modules/blog/index.php');
  }

} catch (PDOException $e) {
  set_flash('error', 'Operation failed: ' . $e->getMessage());
  redirect(ADMIN_URL . '/modules/blog/index.php');
}
