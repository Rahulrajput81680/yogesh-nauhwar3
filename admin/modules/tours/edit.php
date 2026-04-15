<?php
/**
 * Tour Management - Edit Tour Basic Info
 *
 * Acts as the hub for all tour sub-sections.
 * Other sections (highlights, itinerary, etc.) are accessed via the tab nav.
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_once dirname(dirname(__DIR__)) . '/core/uploader.php';
require_login();
require_module('tours');
require_permission('tours_edit');

$page_title = 'Edit Tour';
$errors = [];

$tour_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$tour_id) {
  set_flash('error', 'Invalid tour ID.');
  redirect(ADMIN_URL . '/modules/tours/index.php');
}

// Fetch tour
try {
  $stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ? AND deleted_at IS NULL");
  $stmt->execute([$tour_id]);
  $tour = $stmt->fetch();
} catch (PDOException $e) {
  set_flash('error', 'Database error.');
  redirect(ADMIN_URL . '/modules/tours/index.php');
}

if (!$tour) {
  set_flash('error', 'Tour not found.');
  redirect(ADMIN_URL . '/modules/tours/index.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrf_token = $_POST['csrf_token'] ?? '';

  if (!validate_csrf_token($csrf_token)) {
    $errors[] = 'Invalid request. Please try again.';
  } else {
    $title = sanitize_input($_POST['title'] ?? '');
    $slug = sanitize_input($_POST['slug'] ?? '');
    $category = sanitize_input($_POST['category'] ?? '');
    $destination = sanitize_input($_POST['destination'] ?? '');
    $duration_days = (int) ($_POST['duration_days'] ?? 1);
    $duration_nights = (int) ($_POST['duration_nights'] ?? 0);
    $rating = ($_POST['rating'] ?? '') !== '' ? (float) $_POST['rating'] : null;
    $price = ($_POST['price'] ?? '') !== '' ? (float) $_POST['price'] : null;
    $short_description = sanitize_input($_POST['short_description'] ?? '');
    $full_description = $_POST['full_description'] ?? '';
    $status = in_array($_POST['status'] ?? '', ['draft', 'published']) ? $_POST['status'] : 'draft';

    if (empty($title)) {
      $errors[] = 'Title is required.';
    }

    $slug = !empty($slug) ? generate_slug($slug) : generate_slug($title);

    if (!empty($slug)) {
      $chk = $pdo->prepare("SELECT id FROM tours WHERE slug = ? AND id != ?");
      $chk->execute([$slug, $tour_id]);
      if ($chk->fetch()) {
        $errors[] = 'Slug already exists. Please choose a different slug.';
      }
    }

    if ($rating !== null && ($rating < 0 || $rating > 5)) {
      $errors[] = 'Rating must be between 0 and 5.';
    }

    // Featured image
    $featured_image = $tour['featured_image'];
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] !== UPLOAD_ERR_NO_FILE) {
      $uploader = new FileUploader();
      $new_image = $uploader->upload($_FILES['featured_image'], 'tours');
      if ($new_image) {
        if (!empty($featured_image)) {
          $uploader->delete($featured_image);
        }
        $featured_image = $new_image;
      } else {
        $errors = array_merge($errors, $uploader->getErrors());
      }
    }

    // Remove existing image if checkbox ticked
    if (!empty($_POST['remove_featured_image']) && !empty($featured_image)) {
      $uploader = new FileUploader();
      $uploader->delete($featured_image);
      $featured_image = null;
    }

    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare("
                    UPDATE tours
                    SET title = ?, slug = ?, category = ?, destination = ?,
                        duration_days = ?, duration_nights = ?,
                        rating = ?, price = ?,
                        short_description = ?, full_description = ?,
                        featured_image = ?, status = ?, updated_at = NOW()
                    WHERE id = ?
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
          $tour_id,
        ]);
        log_activity('update', 'tours', $tour_id, "Updated tour: {$title}");
        set_flash('success', 'Tour updated successfully!');
        redirect(ADMIN_URL . '/modules/tours/edit.php?id=' . $tour_id);
      } catch (PDOException $e) {
        $errors[] = 'Failed to update tour: ' . $e->getMessage();
      }
    }

    // Re-populate $tour for redisplay on error
    $tour = array_merge($tour, [
      'title' => $title,
      'slug' => $slug,
      'category' => $category,
      'destination' => $destination,
      'duration_days' => $duration_days,
      'duration_nights' => $duration_nights,
      'rating' => $rating,
      'price' => $price,
      'short_description' => $short_description,
      'full_description' => $full_description,
      'status' => $status,
      'featured_image' => $featured_image,
    ]);
  }
}

// Quick counts for the tab badges
try {
  $counts_stmt = $pdo->prepare("
        SELECT
            (SELECT COUNT(*) FROM tour_highlights  WHERE tour_id = :id) AS hl,
            (SELECT COUNT(*) FROM tour_itinerary   WHERE tour_id = :id) AS it,
            (SELECT COUNT(*) FROM tour_attributes  WHERE tour_id = :id) AS at,
            (SELECT COUNT(*) FROM tour_gallery     WHERE tour_id = :id) AS ga,
            (SELECT COUNT(*) FROM tour_tabs        WHERE tour_id = :id) AS tb
    ");
  $counts_stmt->execute([':id' => $tour_id]);
  $counts = $counts_stmt->fetch();
} catch (PDOException $e) {
  $counts = ['hl' => 0, 'it' => 0, 'at' => 0, 'ga' => 0, 'tb' => 0];
}

$tour_title = $tour['title'];
$active_tab = 'basic';

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
  <h1><i class="bi bi-pencil me-2"></i>Edit Tour</h1>
  <!-- <a href="<?php echo ADMIN_URL; ?>/modules/tours/index.php" class="btn btn-secondary">
    <i class="bi bi-arrow-left me-2"></i>All Tours
  </a> -->
</div>

<?php include __DIR__ . '/_tour_nav.php'; ?>

<!-- Section summary cards -->
<div class="row g-3 mb-4">
  <?php
  $summary_items = [
    ['label' => 'Highlights', 'count' => $counts['hl'], 'icon' => 'bi-star', 'tab' => 'highlights'],
    ['label' => 'Itinerary Days', 'count' => $counts['it'], 'icon' => 'bi-calendar3', 'tab' => 'itinerary'],
    ['label' => 'Attributes', 'count' => $counts['at'], 'icon' => 'bi-tags', 'tab' => 'attributes'],
    ['label' => 'Gallery Images', 'count' => $counts['ga'], 'icon' => 'bi-images', 'tab' => 'gallery'],
    ['label' => 'Content Tabs', 'count' => $counts['tb'], 'icon' => 'bi-layout-text-sidebar', 'tab' => 'tabs'],
  ];
  $tab_files = [
    'highlights' => 'highlights.php',
    'itinerary' => 'itinerary.php',
    'attributes' => 'attributes.php',
    'gallery' => 'gallery.php',
    'tabs' => 'tabs.php',
  ];
  foreach ($summary_items as $si):
    $href = ADMIN_URL . '/modules/tours/' . $tab_files[$si['tab']] . '?tour_id=' . $tour_id;
    ?>
    <div class="col-6 col-md-4 col-lg-2-4">
      <a href="<?php echo $href; ?>" class="card text-decoration-none h-100 border-0 shadow-sm">
        <div class="card-body text-center py-3">
          <i class="bi <?php echo $si['icon']; ?> fs-3 text-primary mb-1 d-block"></i>
          <div class="fs-4 fw-bold"><?php echo $si['count']; ?></div>
          <small class="text-muted"><?php echo $si['label']; ?></small>
        </div>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<!-- Basic info form -->
<form method="POST" action="" enctype="multipart/form-data">
  <?php echo csrf_field(); ?>
  <div class="row g-4">

    <div class="col-lg-8">
      <div class="card mb-4">
        <div class="card-header"><strong>Tour Information</strong></div>
        <div class="card-body">
          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
              <strong>Please fix the following:</strong>
              <ul class="mb-0 mt-1">
                <?php foreach ($errors as $err): ?>
                  <li><?php echo escape($err); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <div class="mb-3">
            <label class="form-label fw-semibold">Tour Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" value="<?php echo escape($tour['title']); ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">URL Slug</label>
            <input type="text" name="slug" class="form-control" value="<?php echo escape($tour['slug']); ?>">
            <small class="text-muted">Changing the slug will break existing links to this tour.</small>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Category</label>
              <input type="text" name="category" class="form-control"
                value="<?php echo escape($tour['category'] ?? ''); ?>" placeholder="e.g. Adventure, Heritage, Wildlife">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Destination</label>
              <input type="text" name="destination" class="form-control"
                value="<?php echo escape($tour['destination'] ?? ''); ?>" placeholder="e.g. Rajasthan, India">
            </div>
          </div>

          <div class="row g-3 mt-1">
            <div class="col-md-3">
              <label class="form-label fw-semibold">Days</label>
              <input type="number" name="duration_days" class="form-control" min="1"
                value="<?php echo (int) ($tour['duration_days'] ?? 1); ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Nights</label>
              <input type="number" name="duration_nights" class="form-control" min="0"
                value="<?php echo (int) ($tour['duration_nights'] ?? 0); ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Rating <small class="text-muted">(0–5)</small></label>
              <input type="number" name="rating" class="form-control" min="0" max="5" step="0.1"
                value="<?php echo escape($tour['rating'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Price (₹)</label>
              <input type="number" name="price" class="form-control" min="0" step="0.01"
                value="<?php echo escape($tour['price'] ?? ''); ?>">
            </div>
          </div>

          <div class="mb-3 mt-3">
            <label class="form-label fw-semibold">Short Description</label>
            <textarea name="short_description" class="form-control" rows="2"
              placeholder="Shown in tour listing cards"><?php echo escape($tour['short_description'] ?? ''); ?></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Full Description</label>
            <textarea name="full_description" id="full_description" class="form-control"
              rows="9"><?php echo escape($tour['full_description'] ?? ''); ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card mb-4">
        <div class="card-header"><strong>Publish</strong></div>
        <div class="card-body">
          <label class="form-label fw-semibold">Status</label>
          <select name="status" class="form-select">
            <option value="draft" <?php echo $tour['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
            <option value="published" <?php echo $tour['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
          </select>
          <div class="d-grid mt-3">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-2"></i>Update Tour
            </button>
          </div>
          <?php if (has_permission('tours_delete')): ?>
            <hr>
            <a href="delete.php?id=<?php echo $tour_id; ?>&action=delete&csrf=<?php echo generate_csrf_token(); ?>"
              class="btn btn-sm  w-100" onclick="return confirm('Move this tour to trash?')">
              <i class="bi bi-trash me-1"></i>Move to Trash
            </a>
          <?php endif; ?>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><strong>Featured Image</strong></div>
        <div class="card-body">
          <?php if (!empty($tour['featured_image'])): ?>
            <img src="<?php echo UPLOAD_URL . '/' . escape($tour['featured_image']); ?>" alt="Featured image"
              class="img-fluid rounded mb-2" style="max-height:180px">
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" name="remove_featured_image" id="removeFeaturedImage"
                value="1">
              <label class="form-check-label text-danger small" for="removeFeaturedImage">
                Remove current image
              </label>
            </div>
          <?php endif; ?>
          <input type="file" name="featured_image" class="form-control" accept="image/webp"
                 id="featuredImageInput" data-no-generic-preview="1">
          <small class="text-muted d-block mt-1">
            Accepted: WebP only. Max <?php echo format_file_size(MAX_UPLOAD_SIZE); ?>.
          </small>
          <div id="featuredImagePreview" class="mt-2 d-none">
            <img id="featuredImagePreviewImg" src="" alt="New preview" class="img-fluid rounded"
              style="max-height:150px">
          </div>
        </div>
      </div>

    </div>
  </div>
</form>

<?php
$extra_js = <<<JS
<script>
document.getElementById('featuredImageInput').addEventListener('change', function () {
    var preview = document.getElementById('featuredImagePreview');
    var img     = document.getElementById('featuredImagePreviewImg');
    if (this.files && this.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e){ img.src = e.target.result; preview.classList.remove('d-none'); };
        reader.readAsDataURL(this.files[0]);
    }
});
</script>
JS;
?>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>