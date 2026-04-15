<?php
/**
 * Our Work Management - Delete Image
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_once dirname(dirname(__DIR__)) . '/core/uploader.php';
require_login();
require_module('our_work');
require_permission('our_work_delete');

$image_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$image_id) {
  set_flash('error', 'Invalid image ID.');
  redirect(ADMIN_URL . '/modules/our-work/index.php');
}

if (!isset($_GET['csrf']) || !validate_csrf_token($_GET['csrf'])) {
  set_flash('error', 'Invalid request. Please try again.');
  redirect(ADMIN_URL . '/modules/our-work/index.php');
}

try {
  $stmt = $pdo->prepare("SELECT * FROM gallery WHERE id = ? AND display_section IN ('our_work', 'our-work')");
  $stmt->execute([$image_id]);
  $gallery_image = $stmt->fetch();

  if (!$gallery_image) {
    set_flash('error', 'Image not found.');
    redirect(ADMIN_URL . '/modules/our-work/index.php');
  }

  if (!empty($gallery_image['image'])) {
    $uploader = new FileUploader();
    $uploader->delete($gallery_image['image']);
  }

  $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ? AND display_section IN ('our_work', 'our-work')");
  $stmt->execute([$image_id]);

  log_activity('delete', 'our_work', $image_id, "Deleted Our Work image: {$gallery_image['title']}");
  set_flash('success', 'Our Work image deleted successfully!');
} catch (PDOException $e) {
  set_flash('error', 'Failed to delete image.');
}

redirect(ADMIN_URL . '/modules/our-work/index.php');
