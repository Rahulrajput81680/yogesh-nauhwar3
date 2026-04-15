<?php
/**
 * Our Work Management - Edit Image
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_once dirname(dirname(__DIR__)) . '/core/uploader.php';
require_login();
require_module('our_work');
require_permission('our_work_edit');

$page_title = 'Edit Our Work Image';
$errors = [];

$image_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$image_id) {
  set_flash('error', 'Invalid image ID.');
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
} catch (PDOException $e) {
  set_flash('error', 'Failed to fetch image.');
  redirect(ADMIN_URL . '/modules/our-work/index.php');
}

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

    $image = $gallery_image['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
      $uploader = new FileUploader();
      $new_image = $uploader->upload($_FILES['image'], 'our_work');

      if ($new_image) {
        if (!empty($image)) {
          $uploader->delete($image);
        }
        $image = $new_image;
      } else {
        $errors = array_merge($errors, $uploader->getErrors());
      }
    }

    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare("UPDATE gallery SET title = ?, image = ?, category = ?, display_section = 'our_work', status = ? WHERE id = ? AND display_section IN ('our_work', 'our-work')");
        $stmt->execute([$title, $image, $category, $status, $image_id]);

        log_activity('update', 'our_work', $image_id, "Updated Our Work image: $title");
        set_flash('success', 'Our Work image updated successfully!');
        redirect(ADMIN_URL . '/modules/our-work/index.php');
      } catch (PDOException $e) {
        $errors[] = 'Failed to update image. ' . $e->getMessage();
      }
    }
  }
} else {
  $_POST = $gallery_image;
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header">
  <h1><i class="bi bi-pencil me-2"></i>Edit Our Work Image</h1>
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

        <div class="mb-4">
          <label class="form-label">Current Image</label>
          <div>
            <img src="<?php echo UPLOAD_URL . '/' . escape($gallery_image['image']); ?>" alt="Current image" class="img-fluid image-preview preview-image-large">
          </div>
        </div>

        <form method="POST" action="" enctype="multipart/form-data">
          <?php echo csrf_field(); ?>

          <div class="mb-4">
            <label for="image" class="form-label">Replace Image (Optional)</label>
            <input type="file" class="form-control" id="image" name="image" accept="image/webp">
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
            <a href="<?php echo ADMIN_URL; ?>/modules/our-work/index.php" class="btn"><i class="bi bi-x-lg me-2"></i>Cancel</a>
            <button type="submit" class="btn"><i class="bi bi-check-lg me-2"></i>Update Image</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
