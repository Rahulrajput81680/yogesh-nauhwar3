<?php
/**
 * Tour Attributes — flexible key/value pairs per tour.
 *
 * Examples: "Group Size → Max 15", "Transport Type → AC Bus",
 *           "Hotel Rating → 3-Star", "Difficulty → Easy"
 *
 * GET  ?tour_id=X               – list + add-form
 * GET  ?tour_id=X&edit_id=Y     – list + pre-filled edit-form
 * GET  ?tour_id=X&delete_id=Y&csrf=… – delete
 * POST ?tour_id=X               – add
 * POST ?tour_id=X&edit_id=Y     – update
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_login();
require_module('tours');
require_permission('tours_edit');

$tour_id = isset($_GET['tour_id']) ? (int) $_GET['tour_id'] : 0;
if (!$tour_id) {
  set_flash('error', 'Invalid tour ID.');
  redirect(ADMIN_URL . '/modules/tours/index.php');
}

try {
  $tStmt = $pdo->prepare("SELECT id, title FROM tours WHERE id = ? AND deleted_at IS NULL");
  $tStmt->execute([$tour_id]);
  $tour = $tStmt->fetch();
} catch (PDOException $e) {
  set_flash('error', 'Database error.');
  redirect(ADMIN_URL . '/modules/tours/index.php');
}

if (!$tour) {
  set_flash('error', 'Tour not found.');
  redirect(ADMIN_URL . '/modules/tours/index.php');
}

$page_title = 'Attributes — ' . $tour['title'];
$tour_title = $tour['title'];
$active_tab = 'attributes';
$errors = [];
$edit_item = null;
$edit_id = isset($_GET['edit_id']) ? (int) $_GET['edit_id'] : 0;

// --- DELETE ---
if (isset($_GET['delete_id'])) {
  $del_id = (int) $_GET['delete_id'];
  if (!validate_csrf_token($_GET['csrf'] ?? '')) {
    set_flash('error', 'Invalid security token.');
    redirect(ADMIN_URL . '/modules/tours/attributes.php?tour_id=' . $tour_id);
  }
  try {
    $pdo->prepare("DELETE FROM tour_attributes WHERE id = ? AND tour_id = ?")
      ->execute([$del_id, $tour_id]);
    log_activity('delete', 'tour_attributes', $del_id, "Deleted attribute from tour #{$tour_id}");
    set_flash('success', 'Attribute deleted.');
  } catch (PDOException $e) {
    set_flash('error', 'Could not delete attribute.');
  }
  redirect(ADMIN_URL . '/modules/tours/attributes.php?tour_id=' . $tour_id);
}

// --- LOAD EDIT ITEM ---
if ($edit_id) {
  try {
    $eStmt = $pdo->prepare("SELECT * FROM tour_attributes WHERE id = ? AND tour_id = ?");
    $eStmt->execute([$edit_id, $tour_id]);
    $edit_item = $eStmt->fetch();
  } catch (PDOException $e) {
  }
  if (!$edit_item) {
    set_flash('error', 'Attribute not found.');
    redirect(ADMIN_URL . '/modules/tours/attributes.php?tour_id=' . $tour_id);
  }
}

// --- POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    $errors[] = 'Invalid request.';
  } else {
    $attr_name = sanitize_input($_POST['attr_name'] ?? '');
    $attr_value = sanitize_input($_POST['attr_value'] ?? '');
    $sort_order = (int) ($_POST['sort_order'] ?? 0);
    $post_edit = (int) ($_POST['edit_id'] ?? 0);

    if (empty($attr_name)) {
      $errors[] = 'Attribute name is required.';
    }
    if (empty($attr_value)) {
      $errors[] = 'Attribute value is required.';
    }

    if (empty($errors)) {
      try {
        if ($post_edit) {
          $pdo->prepare("UPDATE tour_attributes SET attr_name = ?, attr_value = ?, sort_order = ? WHERE id = ? AND tour_id = ?")
            ->execute([$attr_name, $attr_value, $sort_order, $post_edit, $tour_id]);
          log_activity('update', 'tour_attributes', $post_edit, "Updated attribute in tour #{$tour_id}");
          set_flash('success', 'Attribute updated.');
        } else {
          $pdo->prepare("INSERT INTO tour_attributes (tour_id, attr_name, attr_value, sort_order, created_at) VALUES (?, ?, ?, ?, NOW())")
            ->execute([$tour_id, $attr_name, $attr_value, $sort_order]);
          log_activity('create', 'tour_attributes', $pdo->lastInsertId(), "Added attribute to tour #{$tour_id}");
          set_flash('success', 'Attribute added.');
        }
        redirect(ADMIN_URL . '/modules/tours/attributes.php?tour_id=' . $tour_id);
      } catch (PDOException $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
      }
    }
  }
}

// --- Load all attributes ---
try {
  $atStmt = $pdo->prepare("SELECT * FROM tour_attributes WHERE tour_id = ? ORDER BY sort_order ASC, id ASC");
  $atStmt->execute([$tour_id]);
  $attributes = $atStmt->fetchAll();
} catch (PDOException $e) {
  $attributes = [];
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header">
  <h1><i class="bi bi-tags me-2"></i>Tour Attributes</h1>
</div>

<?php include __DIR__ . '/_tour_nav.php'; ?>

<div class="alert alert-info py-2 small">
  <i class="bi bi-info-circle me-1"></i>
  Attributes are flexible key/value pairs (e.g. <em>Group Size → Max 15</em>, <em>Transport → AC Bus</em>).
  Each frontend website decides which ones to display and how.
</div>

<div class="row g-4">
  <!-- Add / Edit Form -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">
        <strong><?php echo $edit_item ? 'Edit Attribute' : 'Add Attribute'; ?></strong>
      </div>
      <div class="card-body">
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger py-2">
            <?php foreach ($errors as $e): ?>
              <div><?php echo escape($e); ?></div><?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="POST"
          action="<?php echo ADMIN_URL . '/modules/tours/attributes.php?tour_id=' . $tour_id . ($edit_item ? '&edit_id=' . $edit_item['id'] : ''); ?>">
          <?php echo csrf_field(); ?>
          <?php if ($edit_item): ?>
            <input type="hidden" name="edit_id" value="<?php echo $edit_item['id']; ?>">
          <?php endif; ?>

          <div class="mb-3">
            <label class="form-label fw-semibold">Attribute Name <span class="text-danger">*</span></label>
            <input type="text" name="attr_name" class="form-control"
              value="<?php echo escape($edit_item['attr_name'] ?? ($_POST['attr_name'] ?? '')); ?>"
              placeholder="e.g. Group Size, Transport, Hotel Type" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Attribute Value <span class="text-danger">*</span></label>
            <input type="text" name="attr_value" class="form-control"
              value="<?php echo escape($edit_item['attr_value'] ?? ($_POST['attr_value'] ?? '')); ?>"
              placeholder="e.g. Max 15 pax, AC Bus, 3-Star" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" min="0"
              value="<?php echo (int) ($edit_item['sort_order'] ?? ($_POST['sort_order'] ?? 0)); ?>">
          </div>

          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-1"></i>
              <?php echo $edit_item ? 'Update Attribute' : 'Add Attribute'; ?>
            </button>
            <?php if ($edit_item): ?>
              <a href="attributes.php?tour_id=<?php echo $tour_id; ?>" class="btn btn-outline-secondary">Cancel</a>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>

    <!-- Common attribute name suggestions -->
    <div class="card mt-3">
      <div class="card-header"><small class="fw-semibold">Common Attribute Names</small></div>
      <div class="card-body py-2">
        <div class="d-flex flex-wrap gap-1">
          <?php
          $suggestions = [
            'Group Size',
            'Starting Location',
            'Transport Mode',
            'Hotel Type',
            'Meal Plan',
            'Difficulty Level',
            'Min Age',
            'Guide Language',
            'Pickup Point',
            'Tour Type'
          ];
          foreach ($suggestions as $s):
            ?>
            <button type="button" class="btn btn-sm btn-outline-secondary py-0 suggestion-btn"
              data-name="<?php echo escape($s); ?>">
              <?php echo escape($s); ?>
            </button>
          <?php endforeach; ?>
        </div>
        <small class="text-muted d-block mt-1">Click to pre-fill the name field.</small>
      </div>
    </div>
  </div>

  <!-- Attributes List -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <strong>Attributes <span class="badge bg-secondary ms-1"><?php echo count($attributes); ?></span></strong>
      </div>
      <div class="card-body p-0">
        <?php if (empty($attributes)): ?>
          <p class="text-muted text-center py-4 mb-0">
            <i class="bi bi-tags d-block fs-3 mb-2"></i>No attributes yet.
          </p>
        <?php else: ?>
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Name</th>
                <th>Value</th>
                <th>Order</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($attributes as $attr): ?>
                <tr>
                  <td class="fw-semibold"><?php echo escape($attr['attr_name']); ?></td>
                  <td><?php echo escape($attr['attr_value']); ?></td>
                  <td><?php echo (int) $attr['sort_order']; ?></td>
                  <td class="text-end">
                    <a href="attributes.php?tour_id=<?php echo $tour_id; ?>&edit_id=<?php echo $attr['id']; ?>"
                      class="btn btn-sm btn-outline-primary" title="Edit">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <a href="attributes.php?tour_id=<?php echo $tour_id; ?>&delete_id=<?php echo $attr['id']; ?>&csrf=<?php echo generate_csrf_token(); ?>"
                      class="btn btn-sm " title="Delete" onclick="return confirm('Delete this attribute?')">
                      <i class="bi bi-trash"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php
$extra_js = <<<JS
<script>
document.querySelectorAll('.suggestion-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelector('[name="attr_name"]').value = this.dataset.name;
        document.querySelector('[name="attr_name"]').focus();
    });
});
</script>
JS;
?>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>