<?php
/**
 * Events Management - Create
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_once dirname(dirname(__DIR__)) . '/core/uploader.php';
require_login();
require_module('events');
require_permission('events_create');

$page_title = 'Create Event';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    $errors[] = 'Invalid request. Please try again.';
  } else {
    $title = sanitize_input($_POST['title'] ?? '');
    $slug = sanitize_input($_POST['slug'] ?? '');
    $category = sanitize_input($_POST['category'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $event_type = in_array($_POST['event_type'] ?? '', ['upcoming', 'past'], true) ? $_POST['event_type'] : 'upcoming';
    $event_date = sanitize_input($_POST['event_date'] ?? '');
    $status = in_array($_POST['status'] ?? '', ['active', 'inactive'], true) ? $_POST['status'] : 'active';

    if ($title === '') {
      $errors[] = 'Title is required.';
    }

    $slug = $slug !== '' ? generate_slug($slug) : generate_slug($title);

    if ($slug !== '') {
      $slugStmt = $pdo->prepare('SELECT id FROM events WHERE slug = ?');
      $slugStmt->execute([$slug]);
      if ($slugStmt->fetch()) {
        $errors[] = 'Slug already exists. Please choose another.';
      }
    }

    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
      $uploader = new FileUploader();
      $image = $uploader->upload($_FILES['image'], 'events');
      if (!$image) {
        $errors = array_merge($errors, $uploader->getErrors());
      }
    }

    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare('INSERT INTO events (title, slug, description, image, category, event_type, event_date, status, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
        $stmt->execute([
          $title,
          $slug,
          $description,
          $image,
          $category,
          $event_type,
          $event_date !== '' ? $event_date : null,
          $status,
          $_SESSION['admin_id'] ?? null,
        ]);

        log_activity('create', 'events', $pdo->lastInsertId(), 'Created event: ' . $title);
        set_flash('success', 'Event created successfully.');
        redirect(ADMIN_URL . '/modules/events/index.php');
      } catch (PDOException $e) {
        $errors[] = 'Failed to save event: ' . $e->getMessage();
      }
    }
  }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header">
  <h1><i class="bi bi-plus-lg me-2"></i>Create Event</h1>
</div>

<div class="row">
  <div class="col-md-8 mx-auto">
    <div class="card">
      <div class="card-body">
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $error): ?>
                <li><?php echo escape($error); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
          <?php echo csrf_field(); ?>

          <div class="mb-3">
            <label class="form-label">Event Image</label>
            <input type="file" class="form-control" name="image" accept="image/webp">
          </div>

          <div class="mb-3">
            <label class="form-label">Title *</label>
            <input type="text" class="form-control" name="title" required
              value="<?php echo escape($_POST['title'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Slug</label>
            <input type="text" class="form-control" name="slug" value="<?php echo escape($_POST['slug'] ?? ''); ?>"
              placeholder="Auto-generated if left empty">
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Category</label>
              <input type="text" class="form-control" name="category"
                value="<?php echo escape($_POST['category'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Event Date</label>
              <input type="date" class="form-control" name="event_date"
                value="<?php echo escape($_POST['event_date'] ?? ''); ?>">
            </div>
          </div>

          <div class="row g-3 mt-1">
            <div class="col-md-6">
              <label class="form-label">Event Type *</label>
              <select class="form-select" name="event_type" required>
                <option value="upcoming" <?php echo (($_POST['event_type'] ?? 'upcoming') === 'upcoming') ? 'selected' : ''; ?>>Upcoming Event</option>
                <option value="past" <?php echo (($_POST['event_type'] ?? '') === 'past') ? 'selected' : ''; ?>>Past Event
                </option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select class="form-select" name="status">
                <option value="active" <?php echo (($_POST['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>>
                  Active</option>
                <option value="inactive" <?php echo (($_POST['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>
                  Inactive</option>
              </select>
            </div>
          </div>

          <div class="mb-3 mt-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description"
              rows="4"><?php echo escape($_POST['description'] ?? ''); ?></textarea>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn">Create Event</button>
            <a href="<?php echo ADMIN_URL; ?>/modules/events/index.php" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php';
