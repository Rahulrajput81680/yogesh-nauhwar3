<?php
/**
 * Media Coverage Management - Upload New Image
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_once dirname(dirname(__DIR__)) . '/core/uploader.php';
require_login();
require_module('media');
require_permission('media_create');

$page_title = 'Upload Media Image';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrf_token = $_POST['csrf_token'] ?? '';

  if (!validate_csrf_token($csrf_token)) {
    $errors[] = 'Invalid request. Please try again.';
  } else {
    $title = sanitize_input($_POST['title'] ?? '');
    $category = sanitize_input($_POST['category'] ?? '');
    $status = sanitize_input($_POST['status'] ?? 'active');

    if (empty($title)) {
      $errors[] = 'Title is required.';
    }

    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
      $uploader = new FileUploader();
      $image = $uploader->upload($_FILES['image'], 'media');
      if (!$image) {
        $errors = array_merge($errors, $uploader->getErrors());
      }
    } else {
      $errors[] = 'Please select an image to upload.';
    }

    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare("INSERT INTO gallery (title, image, category, display_section, status, uploaded_by, created_at) VALUES (?, ?, ?, 'media_coverage', ?, ?, NOW())");
        $stmt->execute([$title, $image, $category, $status, $_SESSION['admin_id']]);

        $image_id = $pdo->lastInsertId();
        log_activity('create', 'media', $image_id, "Uploaded media image: $title");

        set_flash('success', 'Media image uploaded successfully!');
        redirect(ADMIN_URL . '/modules/media/index.php');
      } catch (PDOException $e) {
        $errors[] = 'Failed to upload image. ' . $e->getMessage();
      }
    }
  }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header">
  <h1><i class="bi bi-cloud-upload me-2"></i>Upload Media Image</h1>
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

          <div class="mb-4">
            <label for="image" class="form-label">Select Image *</label>
            <input type="file" class="form-control" id="image" name="image" accept="image/webp" required>
            <small class="text-muted">Allowed types: <?php echo implode(', ', ALLOWED_IMAGE_TYPES); ?> | Max size: <?php echo format_file_size(MAX_UPLOAD_SIZE); ?></small>
          </div>

          <div class="mb-3">
            <label for="title" class="form-label">Title *</label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo escape($_POST['title'] ?? ''); ?>" required>
          </div>

          <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <input type="text" class="form-control" id="category" name="category" value="<?php echo escape($_POST['category'] ?? ''); ?>">
          </div>

          <div class="mb-4">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status">
              <option value="active" <?php echo (isset($_POST['status']) && $_POST['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
              <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
            </select>
          </div>

          <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="<?php echo ADMIN_URL; ?>/modules/media/index.php" class="btn btn-secondary"><i class="bi bi-x-lg me-2"></i>Cancel</a>
            <button type="submit" class="btn"><i class="bi bi-cloud-upload me-2"></i>Upload Image</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
