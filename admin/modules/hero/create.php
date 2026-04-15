<?php
/**
 * Hero Section Management – Create a new hero item
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_once dirname(dirname(__DIR__)) . '/core/uploader.php';
require_login();
require_module('hero');
require_permission('hero_create');

$page_title = 'Add Hero Item';
$errors = [];

// ── Defaults ─────────────────────────────────────────────────────────────────
$item = [
  'heading' => '',
  'description' => '',
  'button_text' => '',
  'button_link' => '',
  'background_image' => null,
  'slide_order' => 0,
  'status' => 'active',
];

// ── Handle POST ──────────────────────────────────────────────────────────────
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

    // Handle background image upload
    $background_image = null;
    if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] !== UPLOAD_ERR_NO_FILE) {
      $up = new FileUploader();
      $uploaded = $up->upload($_FILES['background_image'], 'hero');
      if ($uploaded) {
        $background_image = $uploaded;
      } else {
        $errors = array_merge($errors, $up->getErrors());
      }
    }

    if (empty($errors)) {
      try {
        $pdo->prepare("
            INSERT INTO hero_items
                (heading, description, button_text, button_link, background_image, slide_order, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ")->execute([
              $heading,
              $description,
              $button_text,
              $button_link,
              $background_image,
              $slide_order,
              $status
            ]);

        log_activity('create', 'hero', $pdo->lastInsertId(), 'Created hero item: ' . $heading);
        set_flash('success', 'Hero item added successfully!');
        redirect(ADMIN_URL . '/modules/hero/index.php');
      } catch (PDOException $e) {
        $errors[] = 'Failed to save: ' . $e->getMessage();
      }
    }

    // Restore POST values on error
    if (!empty($errors)) {
      $item = compact('heading', 'description', 'button_text', 'button_link', 'slide_order', 'status');
      $item['background_image'] = null;
    }
  }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
  <h1><i class="bi bi-plus-lg me-2"></i>Add Hero Item</h1>
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
            <input type="file" class="form-control" name="background_image" accept="image/webp">
            <small class="text-muted">WebP only | Max size: <?php echo format_file_size(MAX_UPLOAD_SIZE); ?></small>
          </div>

          <!-- Heading -->
          <div class="mb-3">
            <label class="form-label fw-bold">Heading <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="heading" value="<?php echo escape($item['heading']); ?>"
              placeholder="e.g. Welcome to Our Website" required maxlength="255">
          </div>

          <!-- Description -->
          <div class="mb-3">
            <label class="form-label fw-bold">Description</label>
            <textarea class="form-control" name="description" rows="3" maxlength="500"
              placeholder="A short tagline shown below the heading"><?php echo escape($item['description']); ?></textarea>
          </div>

          <!-- Button -->
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label fw-bold">Button Text</label>
              <input type="text" class="form-control" name="button_text"
                value="<?php echo escape($item['button_text']); ?>" placeholder="e.g. Get Started" maxlength="100">
            </div>
            <div class="col-md-8">
              <label class="form-label fw-bold">Button Link</label>
              <input type="text" class="form-control" name="button_link"
                value="<?php echo escape($item['button_link']); ?>" placeholder="https://example.com  or  #contact">
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
              <i class="bi bi-check-lg me-2"></i>Add Hero Item
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>