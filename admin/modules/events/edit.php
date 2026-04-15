<?php
/**
 * Events Management - Edit
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_once dirname(dirname(__DIR__)) . '/core/uploader.php';
require_login();
require_module('events');
require_permission('events_edit');

$page_title = 'Edit Event';
$errors = [];

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
  set_flash('error', 'Invalid event ID.');
  redirect(ADMIN_URL . '/modules/events/index.php');
}

try {
  $stmt = $pdo->prepare('SELECT * FROM events WHERE id = ?');
  $stmt->execute([$id]);
  $event = $stmt->fetch();
} catch (PDOException $e) {
  $event = false;
}

if (!$event) {
  set_flash('error', 'Event not found.');
  redirect(ADMIN_URL . '/modules/events/index.php');
}

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
      $slugStmt = $pdo->prepare('SELECT id FROM events WHERE slug = ? AND id != ?');
      $slugStmt->execute([$slug, $id]);
      if ($slugStmt->fetch()) {
        $errors[] = 'Slug already exists. Please choose another.';
      }
    }

    $image = $event['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
      $uploader = new FileUploader();
      $newImage = $uploader->upload($_FILES['image'], 'events');
      if ($newImage) {
        if (!empty($image)) {
          $uploader->delete($image);
        }
        $image = $newImage;
      } else {
        $errors = array_merge($errors, $uploader->getErrors());
      }
    }

    if (empty($errors)) {
      try {
        $update = $pdo->prepare('UPDATE events SET title = ?, slug = ?, description = ?, image = ?, category = ?, event_type = ?, event_date = ?, status = ?, updated_at = NOW() WHERE id = ?');
        $update->execute([
          $title,
          $slug,
          $description,
          $image,
          $category,
          $event_type,
          $event_date !== '' ? $event_date : null,
          $status,
          $id,
        ]);

        log_activity('update', 'events', $id, 'Updated event: ' . $title);
        set_flash('success', 'Event updated successfully.');
        redirect(ADMIN_URL . '/modules/events/index.php');
      } catch (PDOException $e) {
        $errors[] = 'Failed to update event: ' . $e->getMessage();
      }
    }

    $event = array_merge($event, [
      'title' => $title,
      'slug' => $slug,
      'description' => $description,
      'image' => $image,
      'category' => $category,
      'event_type' => $event_type,
      'event_date' => $event_date,
      'status' => $status,
    ]);
  }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header">
  <h1><i class="bi bi-pencil me-2"></i>Edit Event</h1>
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

          <?php if (!empty($event['image'])): ?>
            <div class="mb-3">
              <label class="form-label">Current Image</label><br>
              <img src="<?php echo UPLOAD_URL . '/' . escape($event['image']); ?>" alt="event image"
                class="img-fluid preview-image">
            </div>
          <?php endif; ?>

          <div class="mb-3">
            <label class="form-label">Change Image</label>
            <input type="file" class="form-control" name="image" accept="image/webp">
          </div>

          <div class="mb-3">
            <label class="form-label">Title *</label>
            <input type="text" class="form-control" name="title" required
              value="<?php echo escape($event['title']); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Slug</label>
            <input type="text" class="form-control" name="slug" value="<?php echo escape($event['slug']); ?>">
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Category</label>
              <input type="text" class="form-control" name="category" value="<?php echo escape($event['category']); ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Event Date</label>
              <input type="date" class="form-control" name="event_date"
                value="<?php echo escape($event['event_date']); ?>">
            </div>
          </div>

          <div class="row g-3 mt-1">
            <div class="col-md-6">
              <label class="form-label">Event Type *</label>
              <select class="form-select" name="event_type" required>
                <option value="upcoming" <?php echo $event['event_type'] === 'upcoming' ? 'selected' : ''; ?>>Upcoming
                  Event</option>
                <option value="past" <?php echo $event['event_type'] === 'past' ? 'selected' : ''; ?>>Past Event</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select class="form-select" name="status">
                <option value="active" <?php echo $event['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $event['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive
                </option>
              </select>
            </div>
          </div>

          <div class="mb-3 mt-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description"
              rows="4"><?php echo escape($event['description']); ?></textarea>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn">Update Event</button>
            <a href="<?php echo ADMIN_URL; ?>/modules/events/index.php" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php';
