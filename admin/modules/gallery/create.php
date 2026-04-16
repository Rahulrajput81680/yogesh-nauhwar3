<?php
/**
 * Gallery Management - Upload New Image
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_once dirname(dirname(__DIR__)) . '/core/uploader.php';
require_login();
require_module('gallery');
require_permission('gallery_create');

$page_title = 'Upload Image';
$errors = [];

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
    $display_section = 'gallery';
    $status = sanitize_input($_POST['status'] ?? 'active');

    // Validation
    if (empty($title)) {
      $errors[] = 'Title is required.';
    }

    // Handle image upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
      $uploader = new FileUploader();
      $image = $uploader->upload($_FILES['image'], 'gallery');

      if (!$image) {
        $errors = array_merge($errors, $uploader->getErrors());
      }
    } else {
      $errors[] = 'Please select an image to upload.';
    }

    // Insert if no errors
    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare("
                    INSERT INTO gallery (title, image, category, display_section, status, uploaded_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");

        $stmt->execute([
          $title,
          $image,
          $category,
          $display_section,
          $status,
          $_SESSION['admin_id']
        ]);

        $image_id = $pdo->lastInsertId();

        // Log activity
        log_activity('create', 'gallery', $image_id, "Uploaded image: $title");

        set_flash('success', 'Image uploaded successfully!');
        redirect(ADMIN_URL . '/modules/gallery/index.php');
      } catch (PDOException $e) {
        $errors[] = 'Failed to upload image. ' . $e->getMessage();
      }
    }
  }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header">
  <h1><i class="bi bi-cloud-upload me-2"></i>Upload Image</h1>
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

        <form method="POST" action="" enctype="multipart/form-data">
          <?php echo csrf_field(); ?>

          <!-- Image Upload -->
          <div class="mb-4">
            <label for="image" class="form-label">Select Image *</label>
            <input type="file" class="form-control" id="image" name="image" accept="image/webp" required>
            <small class="text-muted">
              Allowed types: <?php echo implode(', ', ALLOWED_IMAGE_TYPES); ?>
              | Max size: <?php echo format_file_size(MAX_UPLOAD_SIZE); ?>
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
            <a href="<?php echo ADMIN_URL; ?>/modules/gallery/index.php" class="btn">
              <i class="bi bi-x-lg me-2"></i>Cancel
            </a>
            <button type="submit" class="btn">
              <i class="bi bi-cloud-upload me-2"></i>Upload Image
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>