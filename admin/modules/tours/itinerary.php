<?php
/**
 * Tour Itinerary — add / edit / delete day-by-day schedule entries for a tour.
 *
 * GET  ?tour_id=X               – list + add-form
 * GET  ?tour_id=X&edit_id=Y     – list + pre-filled edit-form
 * GET  ?tour_id=X&delete_id=Y&csrf=… – delete an entry
 * POST ?tour_id=X               – add new entry
 * POST ?tour_id=X&edit_id=Y     – update existing entry
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

$page_title = 'Itinerary — ' . $tour['title'];
$tour_title = $tour['title'];
$active_tab = 'itinerary';
$errors = [];
$edit_item = null;
$edit_id = isset($_GET['edit_id']) ? (int) $_GET['edit_id'] : 0;

// --- DELETE ---
if (isset($_GET['delete_id'])) {
  $del_id = (int) $_GET['delete_id'];
  if (!validate_csrf_token($_GET['csrf'] ?? '')) {
    set_flash('error', 'Invalid security token.');
    redirect(ADMIN_URL . '/modules/tours/itinerary.php?tour_id=' . $tour_id);
  }
  try {
    $pdo->prepare("DELETE FROM tour_itinerary WHERE id = ? AND tour_id = ?")
      ->execute([$del_id, $tour_id]);
    log_activity('delete', 'tour_itinerary', $del_id, "Deleted itinerary from tour #{$tour_id}");
    set_flash('success', 'Itinerary entry deleted.');
  } catch (PDOException $e) {
    set_flash('error', 'Could not delete entry.');
  }
  redirect(ADMIN_URL . '/modules/tours/itinerary.php?tour_id=' . $tour_id);
}

// --- LOAD EDIT ITEM ---
if ($edit_id) {
  try {
    $eStmt = $pdo->prepare("SELECT * FROM tour_itinerary WHERE id = ? AND tour_id = ?");
    $eStmt->execute([$edit_id, $tour_id]);
    $edit_item = $eStmt->fetch();
  } catch (PDOException $e) {
  }
  if (!$edit_item) {
    set_flash('error', 'Itinerary entry not found.');
    redirect(ADMIN_URL . '/modules/tours/itinerary.php?tour_id=' . $tour_id);
  }
}

// --- POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    $errors[] = 'Invalid request.';
  } else {
    $day_number = max(1, (int) ($_POST['day_number'] ?? 1));
    $title = sanitize_input($_POST['title'] ?? '');
    $description = $_POST['description'] ?? '';
    $sort_order = (int) ($_POST['sort_order'] ?? 0);
    $post_edit = (int) ($_POST['edit_id'] ?? 0);

    if (empty($title)) {
      $errors[] = 'Day title is required.';
    }

    if (empty($errors)) {
      try {
        if ($post_edit) {
          $pdo->prepare("
                        UPDATE tour_itinerary
                        SET day_number = ?, title = ?, description = ?, sort_order = ?
                        WHERE id = ? AND tour_id = ?
                    ")->execute([$day_number, $title, $description, $sort_order, $post_edit, $tour_id]);
          log_activity('update', 'tour_itinerary', $post_edit, "Updated itinerary in tour #{$tour_id}");
          set_flash('success', 'Itinerary entry updated.');
        } else {
          $pdo->prepare("
                        INSERT INTO tour_itinerary (tour_id, day_number, title, description, sort_order, created_at)
                        VALUES (?, ?, ?, ?, ?, NOW())
                    ")->execute([$tour_id, $day_number, $title, $description, $sort_order]);
          log_activity('create', 'tour_itinerary', $pdo->lastInsertId(), "Added itinerary to tour #{$tour_id}");
          set_flash('success', 'Itinerary entry added.');
        }
        redirect(ADMIN_URL . '/modules/tours/itinerary.php?tour_id=' . $tour_id);
      } catch (PDOException $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
      }
    }
  }
}

// --- Load all itinerary entries ---
try {
  $itStmt = $pdo->prepare("SELECT * FROM tour_itinerary WHERE tour_id = ? ORDER BY sort_order ASC, day_number ASC, id ASC");
  $itStmt->execute([$tour_id]);
  $itinerary = $itStmt->fetchAll();
} catch (PDOException $e) {
  $itinerary = [];
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header">
  <h1><i class="bi bi-calendar3 me-2"></i>Tour Itinerary</h1>
</div>

<?php include __DIR__ . '/_tour_nav.php'; ?>

<div class="row g-4">
  <!-- Add / Edit Form -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">
        <strong><?php echo $edit_item ? 'Edit Itinerary Entry' : 'Add Itinerary Entry'; ?></strong>
      </div>
      <div class="card-body">
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger py-2">
            <?php foreach ($errors as $e): ?>
              <div><?php echo escape($e); ?></div><?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="POST"
          action="<?php echo ADMIN_URL . '/modules/tours/itinerary.php?tour_id=' . $tour_id . ($edit_item ? '&edit_id=' . $edit_item['id'] : ''); ?>">
          <?php echo csrf_field(); ?>
          <?php if ($edit_item): ?>
            <input type="hidden" name="edit_id" value="<?php echo $edit_item['id']; ?>">
          <?php endif; ?>

          <div class="row g-2 mb-3">
            <div class="col-4">
              <label class="form-label fw-semibold">Day #</label>
              <input type="number" name="day_number" class="form-control" min="1"
                value="<?php echo (int) ($edit_item['day_number'] ?? ($_POST['day_number'] ?? (count($itinerary) + 1))); ?>">
            </div>
            <div class="col-8">
              <label class="form-label fw-semibold">Sort Order</label>
              <input type="number" name="sort_order" class="form-control" min="0"
                value="<?php echo (int) ($edit_item['sort_order'] ?? ($_POST['sort_order'] ?? 0)); ?>">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control"
              value="<?php echo escape($edit_item['title'] ?? ($_POST['title'] ?? '')); ?>"
              placeholder="e.g. Arrival in Delhi" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Description</label>
            <textarea name="description" class="form-control" rows="5"
              placeholder="Activities, transfers, accommodation, meals…"><?php echo escape($edit_item['description'] ?? ($_POST['description'] ?? '')); ?></textarea>
          </div>

          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-1"></i>
              <?php echo $edit_item ? 'Update Entry' : 'Add Entry'; ?>
            </button>
            <?php if ($edit_item): ?>
              <a href="itinerary.php?tour_id=<?php echo $tour_id; ?>" class="btn btn-outline-secondary">Cancel</a>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Itinerary List -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <strong>Itinerary <span class="badge bg-secondary ms-1"><?php echo count($itinerary); ?> days</span></strong>
      </div>
      <div class="card-body p-0">
        <?php if (empty($itinerary)): ?>
          <p class="text-muted text-center py-4 mb-0">
            <i class="bi bi-calendar3 d-block fs-3 mb-2"></i>
            No itinerary entries yet.
          </p>
        <?php else: ?>
          <div class="accordion accordion-flush" id="itineraryAccordion">
            <?php foreach ($itinerary as $idx => $it): ?>
              <div class="accordion-item">
                <h2 class="accordion-header">
                  <button class="accordion-button <?php echo $idx > 0 ? 'collapsed' : ''; ?>" type="button"
                    data-bs-toggle="collapse" data-bs-target="#it<?php echo $it['id']; ?>">
                    <span class="badge bg-primary me-2">Day <?php echo (int) $it['day_number']; ?></span>
                    <?php echo escape($it['title']); ?>
                  </button>
                </h2>
                <div id="it<?php echo $it['id']; ?>"
                  class="accordion-collapse collapse <?php echo $idx === 0 ? 'show' : ''; ?>">
                  <div class="accordion-body">
                    <p class="text-muted mb-3" style="white-space:pre-wrap"><?php echo escape($it['description'] ?? ''); ?>
                    </p>
                    <div class="d-flex gap-2">
                      <a href="itinerary.php?tour_id=<?php echo $tour_id; ?>&edit_id=<?php echo $it['id']; ?>"
                        class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i>Edit
                      </a>
                      <a href="itinerary.php?tour_id=<?php echo $tour_id; ?>&delete_id=<?php echo $it['id']; ?>&csrf=<?php echo generate_csrf_token(); ?>"
                        class="btn btn-sm " onclick="return confirm('Delete Day <?php echo (int) $it['day_number']; ?>?')">
                        <i class="bi bi-trash me-1"></i>Delete
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>