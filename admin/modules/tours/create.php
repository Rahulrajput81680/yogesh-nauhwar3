<?php
/**
 * Tour Management - Create New Tour
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_once dirname(dirname(__DIR__)) . '/core/uploader.php';
require_login();
require_module('tours');
require_permission('tours_create');

$page_title = 'Create Tour';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrf_token = $_POST['csrf_token'] ?? '';

  if (!validate_csrf_token($csrf_token)) {
    $errors[] = 'Invalid request. Please try again.';
  } else {
    // Collect & sanitize
    $title = sanitize_input($_POST['title'] ?? '');
    $slug = sanitize_input($_POST['slug'] ?? '');
    $category = sanitize_input($_POST['category'] ?? '');
    $destination = sanitize_input($_POST['destination'] ?? '');
    $duration_days = (int) ($_POST['duration_days'] ?? 1);
    $duration_nights = (int) ($_POST['duration_nights'] ?? 0);
    $rating = $_POST['rating'] !== '' ? (float) $_POST['rating'] : null;
    $price = $_POST['price'] !== '' ? (float) $_POST['price'] : null;
    $short_description = sanitize_input($_POST['short_description'] ?? '');
    $full_description = $_POST['full_description'] ?? '';
    $status = in_array($_POST['status'] ?? '', ['draft', 'published']) ? $_POST['status'] : 'draft';

    // Validation
    if (empty($title)) {
      $errors[] = 'Title is required.';
    }

    $slug = !empty($slug) ? generate_slug($slug) : generate_slug($title);

    if (!empty($slug)) {
      $chk = $pdo->prepare("SELECT id FROM tours WHERE slug = ?");
      $chk->execute([$slug]);
      if ($chk->fetch()) {
        $errors[] = 'Slug already exists. Please choose a different slug.';
      }
    }

    if ($rating !== null && ($rating < 0 || $rating > 5)) {
      $errors[] = 'Rating must be between 0 and 5.';
    }

    // Featured image upload
    $featured_image = null;
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] !== UPLOAD_ERR_NO_FILE) {
      $uploader = new FileUploader();
      $featured_image = $uploader->upload($_FILES['featured_image'], 'tours');
      if (!$featured_image) {
        $errors = array_merge($errors, $uploader->getErrors());
      }
    }

    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare("
                    INSERT INTO tours
                        (title, slug, category, destination, duration_days, duration_nights,
                         rating, price, short_description, full_description, featured_image,
                         status, author_id, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
        $stmt->execute([
          $title,
          $slug,
          $category,
          $destination,
          $duration_days,
          $duration_nights,
          $rating,
          $price,
          $short_description,
          $full_description,
          $featured_image,
          $status,
          $_SESSION['admin_id'],
        ]);
        $tour_id = $pdo->lastInsertId();
        log_activity('create', 'tours', $tour_id, "Created tour: {$title}");

        set_flash('success', 'Tour created! Now add highlights, itinerary, and other details below.');
        redirect(ADMIN_URL . '/modules/tours/edit.php?id=' . $tour_id);
      } catch (PDOException $e) {
        $errors[] = 'Failed to create tour: ' . $e->getMessage();
      }
    }
  }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
  <h1><i class="bi bi-plus-lg me-2"></i>Create Tour</h1>
  <a href="<?php echo ADMIN_URL; ?>/modules/tours/index.php" class="btn btn-secondary">
    <i class="bi bi-arrow-left me-2"></i>Back to Tours
  </a>
</div>

<form method="POST" action="" enctype="multipart/form-data">
  <?php echo csrf_field(); ?>
  <div class="row g-4">

    <!-- Left column: main fields -->
    <div class="col-lg-8">
      <div class="card mb-4">
        <div class="card-header"><strong>Tour Information</strong></div>
        <div class="card-body">

          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
              <strong>Please fix the following:</strong>
              <ul class="mb-0 mt-1">
                <?php foreach ($errors as $e): ?>
                  <li><?php echo escape($e); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <div class="mb-3">
            <label class="form-label fw-semibold">Tour Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" value="<?php echo escape($_POST['title'] ?? ''); ?>"
              required placeholder="e.g. Golden Triangle Tour">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">URL Slug</label>
            <input type="text" name="slug" class="form-control" value="<?php echo escape($_POST['slug'] ?? ''); ?>"
              placeholder="Auto-generated from title">
            <small class="text-muted">Leave empty to auto-generate.</small>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Category</label>
              <input type="text" name="category" class="form-control"
                value="<?php echo escape($_POST['category'] ?? ''); ?>"
                placeholder="e.g. Adventure, Heritage, Wildlife">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Destination</label>
              <input type="text" name="destination" class="form-control"
                value="<?php echo escape($_POST['destination'] ?? ''); ?>" placeholder="e.g. Rajasthan, India">
            </div>
          </div>

          <div class="row g-3 mt-1">
            <div class="col-md-3">
              <label class="form-label fw-semibold">Days</label>
              <input type="number" name="duration_days" class="form-control" min="1"
                value="<?php echo (int) ($_POST['duration_days'] ?? 1); ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Nights</label>
              <input type="number" name="duration_nights" class="form-control" min="0"
                value="<?php echo (int) ($_POST['duration_nights'] ?? 0); ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Rating <small class="text-muted">(0–5)</small></label>
              <input type="number" name="rating" class="form-control" min="0" max="5" step="0.1"
                value="<?php echo escape($_POST['rating'] ?? ''); ?>" placeholder="e.g. 4.5">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Price (₹)</label>
              <input type="number" name="price" class="form-control" min="0" step="0.01"
                value="<?php echo escape($_POST['price'] ?? ''); ?>" placeholder="e.g. 12999">
            </div>
          </div>

          <div class="mb-3 mt-3">
            <label class="form-label fw-semibold">Short Description</label>
            <textarea name="short_description" class="form-control" rows="2"
              placeholder="One or two lines shown in tour cards and listings"><?php echo escape($_POST['short_description'] ?? ''); ?></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Full Description</label>
            <textarea name="full_description" id="full_description" class="form-control" rows="8"
              placeholder="Detailed tour description for the tour detail page"><?php echo escape($_POST['full_description'] ?? ''); ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- Right column: image, status -->
    <div class="col-lg-4">
      <div class="card mb-4">
        <div class="card-header"><strong>Publish</strong></div>
        <div class="card-body">
          <label class="form-label fw-semibold">Status</label>
          <select name="status" class="form-select">
            <option value="draft" <?php echo ($_POST['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>Draft
            </option>
            <option value="published" <?php echo ($_POST['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published
            </option>
          </select>
          <div class="d-grid mt-3">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-2"></i>Create Tour
            </button>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><strong>Featured Image</strong></div>
        <div class="card-body">
          <input type="file" name="featured_image" class="form-control" accept="image/webp"
                 id="featuredImageInput" data-no-generic-preview="1">
          <small class="text-muted d-block mt-1">
            Accepted: WebP only. Max <?php echo format_file_size(MAX_UPLOAD_SIZE); ?>.
          </small>
          <div id="featuredImagePreview" class="mt-2 d-none">
            <img id="featuredImagePreviewImg" src="" alt="Preview" class="img-fluid rounded" style="max-height:180px">
          </div>
        </div>
      </div>
    </div>

  </div><!-- row -->
</form>

<?php
$extra_js = <<<JS
<script>
// Live slug generation from title
document.querySelector('[name="title"]').addEventListener('input', function () {
    var slugField = document.querySelector('[name="slug"]');
    if (slugField.value === '') {
        slugField.placeholder = this.value.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .trim().replace(/[\s_-]+/g, '-');
    }
});
// Image preview
document.getElementById('featuredImageInput').addEventListener('change', function () {
    var preview = document.getElementById('featuredImagePreview');
    var img     = document.getElementById('featuredImagePreviewImg');
    if (this.files && this.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) { img.src = e.target.result; preview.classList.remove('d-none'); };
        reader.readAsDataURL(this.files[0]);
    }
});
</script>
JS;
?>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>