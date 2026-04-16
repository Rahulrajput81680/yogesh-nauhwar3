<?php
/**
 * Gallery Management - Edit Image
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_once dirname(dirname(__DIR__)) . '/core/uploader.php';
require_login();
require_module('gallery');
require_permission('gallery_edit');

$page_title = 'Edit Image';
$errors = [];

// Get image ID
$image_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$image_id) {
  set_flash('error', 'Invalid image ID.');
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
} catch (PDOException $e) {
  set_flash('error', 'Failed to fetch image.');
  redirect(ADMIN_URL . '/modules/gallery/index.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrf_token = $_POST['csrf_token'] ?? '';

  // Validate CSRF token
  if (!validate_csrf_token($csrf_token)) {
    $errors[] = 'Invalid request. Please try again.';
  } else {
    // Get and sanitize form data
    $title = sanitize_input($_POST['title'] ?? '');
    $category = sanitize_input($_POST['category'] ?? '');
    $status = sanitize_input($_POST['status'] ?? 'active');

    // Validation
    if (empty($title)) {
      $errors[] = 'Title is required.';
    }

    // Handle image upload (optional replacement)
    $image = $gallery_image['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
      $uploader = new FileUploader();
      $new_image = $uploader->upload($_FILES['image'], 'gallery');

      if ($new_image) {
        // Delete old image
        if (!empty($image)) {
          $uploader->delete($image);
        }
        $image = $new_image;
      } else {
        $errors = array_merge($errors, $uploader->getErrors());
      }
    }

    // Update if no errors
    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare("
                    UPDATE gallery 
                    SET title = ?, image = ?, category = ?, display_section = 'gallery', status = ?
                    WHERE id = ? AND display_section = 'gallery'
                ");

        $stmt->execute([
          $title,
          $image,
          $category,
          $status,
          $image_id
        ]);

        // Log activity
        log_activity('update', 'gallery', $image_id, "Updated image: $title");

        set_flash('success', 'Image updated successfully!');
        redirect(ADMIN_URL . '/modules/gallery/index.php');
      } catch (PDOException $e) {
        $errors[] = 'Failed to update image. ' . $e->getMessage();
      }
    }
  }
} else {
  // Populate form with existing data
  $_POST = $gallery_image;
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header">
  <h1><i class="bi bi-pencil me-2"></i>Edit Image</h1>
</div>

<div class="row">
  <div class="col-md-8 mx-auto">
    <div class="card">
      <div class="card-body">
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <strong>Error:</strong>
            <ul class="mb-0">
              <?php foreach ($errors as $error): ?>
                <li><?php echo escape($error); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <!-- Current Image -->
        <div class="mb-4">
          <label class="form-label">Current Image</label>
          <div>
            <img src="<?php echo UPLOAD_URL . '/' . escape($gallery_image['image']); ?>"
              alt="<?php echo escape($gallery_image['title']); ?>" class="img-fluid image-preview preview-image-large">
          </div>
        </div>

        <form method="POST" action="" enctype="multipart/form-data">
          <?php echo csrf_field(); ?>

          <!-- Replace Image -->
          <div class="mb-4">
            <label for="image" class="form-label">Replace Image (Optional)</label>
            <input type="file" class="form-control" id="image" name="image" accept="image/webp">
            <small class="text-muted">
              Leave empty to keep current image | Max size: <?php echo format_file_size(MAX_UPLOAD_SIZE); ?>
            </small>
          </div>

          <!-- Title -->
          <div class="mb-3">
            <label for="title" class="form-label">Title *</label>
            <input type="text" class="form-control" id="title" name="title"
              value="<?php echo escape($_POST['title'] ?? ''); ?>" required>
          </div>

          <!-- Category -->
          <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <input type="text" class="form-control" id="category" name="category"
              value="<?php echo escape($_POST['category'] ?? ''); ?>" placeholder="e.g., Nature, Architecture, People">
          </div>
          <!-- Status -->
          <div class="mb-4">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status">
              <option value="active" <?php echo (isset($_POST['status']) && $_POST['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
              <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
            </select>
          </div>

          <!-- Submit Buttons -->
          <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="<?php echo ADMIN_URL; ?>/modules/gallery/index.php" class="btn btn-secondary">
              <i class="bi bi-x-lg me-2"></i>Cancel
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-2"></i>Update Image
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>