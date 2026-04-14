<?php
/**
 * Media Coverage Management - Delete Image
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_once dirname(dirname(__DIR__)) . '/core/uploader.php';
require_login();
require_module('media');
require_permission('media_delete');

$image_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$image_id) {
  set_flash('error', 'Invalid image ID.');
  redirect(ADMIN_URL . '/modules/media/index.php');
}

if (!isset($_GET['csrf']) || !validate_csrf_token($_GET['csrf'])) {
  set_flash('error', 'Invalid request. Please try again.');
  redirect(ADMIN_URL . '/modules/media/index.php');
}

try {
  $stmt = $pdo->prepare("SELECT * FROM gallery WHERE id = ? AND display_section = 'media_coverage'");
  $stmt->execute([$image_id]);
  $gallery_image = $stmt->fetch();

  if (!$gallery_image) {
    set_flash('error', 'Image not found.');
    redirect(ADMIN_URL . '/modules/media/index.php');
  }

  if (!empty($gallery_image['image'])) {
    $uploader = new FileUploader();
    $uploader->delete($gallery_image['image']);
  }

  $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ? AND display_section = 'media_coverage'");
  $stmt->execute([$image_id]);

  log_activity('delete', 'media', $image_id, "Deleted media image: {$gallery_image['title']}");
  set_flash('success', 'Media image deleted successfully!');
} catch (PDOException $e) {
  set_flash('error', 'Failed to delete image.');
}

redirect(ADMIN_URL . '/modules/media/index.php');
