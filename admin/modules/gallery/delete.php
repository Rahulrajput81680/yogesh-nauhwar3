<?php
/**
 * Gallery Management - Delete Image
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_once dirname(dirname(__DIR__)) . '/core/uploader.php';
require_login();
require_module('gallery');
require_permission('gallery_delete');

// Get image ID
$image_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$image_id) {
  set_flash('error', 'Invalid image ID.');
  redirect(ADMIN_URL . '/modules/gallery/index.php');
}

// Validate CSRF token
if (!isset($_GET['csrf']) || !validate_csrf_token($_GET['csrf'])) {
  set_flash('error', 'Invalid request. Please try again.');
  redirect(ADMIN_URL . '/modules/gallery/index.php');
}

// Fetch image data
try {
  $stmt = $pdo->prepare("SELECT * FROM gallery WHERE id = ? AND display_section = 'gallery'");
  $stmt->execute([$image_id]);
  $gallery_image = $stmt->fetch();

  if (!$gallery_image) {
    set_flash('error', 'Image not found.');
    redirect(ADMIN_URL . '/modules/gallery/index.php');
  }

  // Delete image file
  if (!empty($gallery_image['image'])) {
    $uploader = new FileUploader();
    $uploader->delete($gallery_image['image']);
  }

  // Delete from database
  $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ? AND display_section = 'gallery'");
  $stmt->execute([$image_id]);

  // Log activity
  log_activity('delete', 'gallery', $image_id, "Deleted image: {$gallery_image['title']}");

  set_flash('success', 'Image deleted successfully!');
} catch (PDOException $e) {
  set_flash('error', 'Failed to delete image.');
}

redirect(ADMIN_URL . '/modules/gallery/index.php');
