<?php
/**
 * Hero Section Management â€“ Edit an existing hero item
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_once dirname(dirname(__DIR__)) . '/core/uploader.php';
require_login();
require_module('hero');
require_permission('hero_edit');

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
  set_flash('error', 'Invalid hero item ID.');
  redirect(ADMIN_URL . '/modules/hero/index.php');
}

$errors = [];

// â”€â”€ Load item â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
try {
  $stmt = $pdo->prepare("SELECT * FROM hero_items WHERE id = ? LIMIT 1");
  $stmt->execute([$id]);
  $item = $stmt->fetch();
} catch (PDOException $e) {
  set_flash('error', 'Could not load hero item.');
  redirect(ADMIN_URL . '/modules/hero/index.php');
}
if (!$item) {
  set_flash('error', 'Hero item not found.');
  redirect(ADMIN_URL . '/modules/hero/index.php');
}

$page_title = 'Edit Hero Item â€“ ' . escape($item['heading']);

// â”€â”€ Handle POST â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    $errors[] = 'Invalid request. Please try again.';
  } else {
    $heading = sanitize_input($_POST['heading'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $button_text = sanitize_input($_POST['button_text'] ?? '');
    $button_link = sanitize_input($_POST['button_link'] ?? '');
    $slide_order = (int) ($_POST['slide_order'] ?? 0);
    $status = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';

    if (empty($heading))
      $errors[] = 'Heading is required.';

    // Handle background image
    $background_image = $item['background_image'];

    if (!empty($_POST['remove_background_image']) && $background_image) {
      $old = UPLOAD_DIR . '/' . $background_image;
      if (file_exists($old))
        @unlink($old);
      $background_image = null;
    }

    if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] !== UPLOAD_ERR_NO_FILE) {
      $up = new FileUploader();
      $uploaded = $up->upload($_FILES['background_image'], 'hero');
      if ($uploaded) {
        if ($background_image) {
          $old = UPLOAD_DIR . '/' . $background_image;
          if (file_exists($old))
            @unlink($old);
        }
        $background_image = $uploaded;
      } else {
        $errors = array_merge($errors, $up->getErrors());
      }
    }

    if (empty($errors)) {
      try {
        $pdo->prepare("UPDATE hero_items SET heading=?, description=?, button_text=?, button_link=?,
                        background_image=?, slide_order=?, status=?, updated_at=NOW() WHERE id=?")
          ->execute([
            $heading,
            $description,
            $button_text,
            $button_link,
            $background_image,
            $slide_order,
            $status,
            $id
          ]);
        log_activity('update', 'hero', $id, 'Updated hero item: ' . $heading);
        set_flash('success', 'Hero item updated successfully!');
        redirect(ADMIN_URL . '/modules/hero/index.php');
      } catch (PDOException $e) {
        $errors[] = 'Failed to save: ' . $e->getMessage();
      }
    }

    // Restore POST values on error
    if (!empty($errors)) {
      $item = array_merge($item, [
        'heading' => $_POST['heading'] ?? '',
        'description' => $_POST['description'] ?? '',
        'button_text' => $_POST['button_text'] ?? '',
        'button_link' => $_POST['button_link'] ?? '',
        'slide_order' => $_POST['slide_order'] ?? 0,
        'status' => $_POST['status'] ?? 'active',
      ]);
    }
  }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
  <h1><i class="bi bi-pencil me-2"></i>Edit Hero Item</h1>
  <a href="<?php echo ADMIN_URL; ?>/modules/hero/index.php" class="btn btn-secondary">
    <i class="bi bi-arrow-left me-2"></i>Back
  </a>
</div>

<div class="row">
  <div class="col-md-9 mx-auto">
    <div class="card">
      <div class="card-body">

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-1">
              <?php foreach ($errors as $e): ?>
                <li><?php echo escape($e); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
          <?php echo csrf_field(); ?>

          <!-- Background Image -->
          <div class="mb-4">
            <label class="form-label fw-bold">Background Image</label>
            <?php if (!empty($item['background_image'])): ?>
              <div class="mb-2">
                <img src="<?php echo escape(UPLOAD_URL . '/' . $item['background_image']); ?>" class="img-fluid rounded"
                  style="max-height:200px;object-fit:cover;width:100%;" alt="">
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="remove_bg" name="remove_background_image" value="1">
                <label class="form-check-label text-danger" for="remove_bg">
                  <i class="bi bi-trash me-1"></i>Remove current image
                </label>
              </div>
            <?php endif; ?>
            <input type="file" class="form-control" name="background_image" accept="image/webp">
            <small class="text-muted">WebP only | Max size: <?php echo format_file_size(MAX_UPLOAD_SIZE); ?></small>
          </div>

          <!-- Heading -->
          <div class="mb-3">
            <label class="form-label fw-bold">Heading <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="heading" value="<?php echo escape($item['heading']); ?>"
              required maxlength="255">
          </div>

          <!-- Description -->
          <div class="mb-3">
            <label class="form-label fw-bold">Description</label>
            <textarea class="form-control" name="description" rows="3"
              maxlength="500"><?php echo escape($item['description'] ?? ''); ?></textarea>
          </div>

          <!-- Button -->
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label fw-bold">Button Text</label>
              <input type="text" class="form-control" name="button_text"
                value="<?php echo escape($item['button_text'] ?? ''); ?>" maxlength="100">
            </div>
            <div class="col-md-8">
              <label class="form-label fw-bold">Button Link</label>
              <input type="text" class="form-control" name="button_link"
                value="<?php echo escape($item['button_link'] ?? ''); ?>"
                placeholder="https://example.com/contact  or  #contact">
              <small class="text-muted">Leave both empty to hide the button.</small>
            </div>
          </div>

          <!-- Slide Order & Status -->
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label class="form-label fw-bold">Slide Order</label>
              <input type="number" class="form-control" name="slide_order"
                value="<?php echo (int) $item['slide_order']; ?>" min="0" max="9999">
              <small class="text-muted">Lower number = displayed first.</small>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-bold">Status</label>
              <select class="form-select" name="status">
                <option value="active" <?php echo $item['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $item['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
              </select>
            </div>
          </div>

          <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="<?php echo ADMIN_URL; ?>/modules/hero/index.php" class="btn btn-secondary">
              <i class="bi bi-x-lg me-2"></i>Cancel
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-2"></i>Save Changes
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>