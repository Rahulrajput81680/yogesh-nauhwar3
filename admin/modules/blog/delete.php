<?php
/**
 * Blog Management - Soft Delete / Restore / Purge
 *
 * Actions:
 *   ?action=delete  (default) – soft-delete: sets deleted_at = NOW()
 *   ?action=restore            – restore:     sets deleted_at = NULL
 *   ?action=purge              – hard delete: removes record + file
 *
 * delete  requires permission: blog_delete
 * restore requires permission: blog_restore
 * purge   requires permission: blog_restore
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
if ($action === 'delete') {
  require_permission('blog_delete');
} else {
  require_permission('blog_restore'); // restore + purge
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

  if ($action === 'delete') {
    // Soft delete
    $pdo->prepare("UPDATE blogs SET deleted_at = NOW() WHERE id = ?")
      ->execute([$blog_id]);
    log_activity('delete', 'blog', $blog_id, "Soft-deleted blog: {$blog['title']}");
    set_flash('success', 'Blog post moved to trash.');
    redirect(ADMIN_URL . '/modules/blog/index.php');

  } elseif ($action === 'restore') {
    // Restore from trash
    $pdo->prepare("UPDATE blogs SET deleted_at = NULL WHERE id = ?")
      ->execute([$blog_id]);
    log_activity('restore', 'blog', $blog_id, "Restored blog: {$blog['title']}");
    set_flash('success', 'Blog post restored successfully.');
    redirect(ADMIN_URL . '/modules/blog/index.php?view=trash');

  } elseif ($action === 'purge') {
    // Permanently delete record + thumbnail
    if (!empty($blog['thumbnail'])) {
      $uploader = new FileUploader();
      $uploader->delete($blog['thumbnail']);
    }
    $pdo->prepare("DELETE FROM blogs WHERE id = ?")
      ->execute([$blog_id]);
    log_activity('purge', 'blog', $blog_id, "Permanently deleted blog: {$blog['title']}");
    set_flash('success', 'Blog post permanently deleted.');
    redirect(ADMIN_URL . '/modules/blog/index.php?view=trash');
  }

} catch (PDOException $e) {
  set_flash('error', 'Operation failed: ' . $e->getMessage());
  redirect(ADMIN_URL . '/modules/blog/index.php');
}
