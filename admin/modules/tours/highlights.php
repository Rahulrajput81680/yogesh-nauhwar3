<?php
/**
 * Tour Highlights — add / edit / delete highlight items for a tour.
 *
 * GET  ?tour_id=X               – list + add-form
 * GET  ?tour_id=X&edit_id=Y     – list + pre-filled edit-form
 * GET  ?tour_id=X&delete_id=Y&csrf=… – delete an item
 * POST ?tour_id=X               – add new highlight
 * POST ?tour_id=X&edit_id=Y     – update existing highlight
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

// Load parent tour
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

$page_title = 'Highlights — ' . $tour['title'];
$tour_title = $tour['title'];
$active_tab = 'highlights';
$errors = [];
$edit_item = null;
$edit_id = isset($_GET['edit_id']) ? (int) $_GET['edit_id'] : 0;

// --- DELETE ---
if (isset($_GET['delete_id'])) {
  $del_id = (int) $_GET['delete_id'];
  if (!validate_csrf_token($_GET['csrf'] ?? '')) {
    set_flash('error', 'Invalid security token.');
    redirect(ADMIN_URL . '/modules/tours/highlights.php?tour_id=' . $tour_id);
  }
  try {
    $pdo->prepare("DELETE FROM tour_highlights WHERE id = ? AND tour_id = ?")
      ->execute([$del_id, $tour_id]);
    log_activity('delete', 'tour_highlights', $del_id, "Deleted highlight from tour #{$tour_id}");
    set_flash('success', 'Highlight deleted.');
  } catch (PDOException $e) {
    set_flash('error', 'Could not delete highlight.');
  }
  redirect(ADMIN_URL . '/modules/tours/highlights.php?tour_id=' . $tour_id);
}

// --- LOAD EDIT ITEM ---
if ($edit_id) {
  try {
    $eStmt = $pdo->prepare("SELECT * FROM tour_highlights WHERE id = ? AND tour_id = ?");
    $eStmt->execute([$edit_id, $tour_id]);
    $edit_item = $eStmt->fetch();
  } catch (PDOException $e) {
  }
  if (!$edit_item) {
    set_flash('error', 'Highlight not found.');
    redirect(ADMIN_URL . '/modules/tours/highlights.php?tour_id=' . $tour_id);
  }
}

// --- POST (add or update) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    $errors[] = 'Invalid request.';
  } else {
    $highlight = sanitize_input($_POST['highlight'] ?? '');
    $sort_order = (int) ($_POST['sort_order'] ?? 0);
    $post_edit = (int) ($_POST['edit_id'] ?? 0);

    if (empty($highlight)) {
      $errors[] = 'Highlight text is required.';
    }

    if (empty($errors)) {
      try {
        if ($post_edit) {
          $pdo->prepare("UPDATE tour_highlights SET highlight = ?, sort_order = ? WHERE id = ? AND tour_id = ?")
            ->execute([$highlight, $sort_order, $post_edit, $tour_id]);
          log_activity('update', 'tour_highlights', $post_edit, "Updated highlight in tour #{$tour_id}");
          set_flash('success', 'Highlight updated.');
        } else {
          $pdo->prepare("INSERT INTO tour_highlights (tour_id, highlight, sort_order, created_at) VALUES (?, ?, ?, NOW())")
            ->execute([$tour_id, $highlight, $sort_order]);
          log_activity('create', 'tour_highlights', $pdo->lastInsertId(), "Added highlight to tour #{$tour_id}");
          set_flash('success', 'Highlight added.');
        }
        redirect(ADMIN_URL . '/modules/tours/highlights.php?tour_id=' . $tour_id);
      } catch (PDOException $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
      }
    }
  }
}

// --- Load all highlights ---
try {
  $hlStmt = $pdo->prepare("SELECT * FROM tour_highlights WHERE tour_id = ? ORDER BY sort_order ASC, id ASC");
  $hlStmt->execute([$tour_id]);
  $highlights = $hlStmt->fetchAll();
} catch (PDOException $e) {
  $highlights = [];
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
  <h1><i class="bi bi-star me-2"></i>Tour Highlights</h1>
</div>

<?php include __DIR__ . '/_tour_nav.php'; ?>

<div class="row g-4">
  <!-- Add / Edit Form -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">
        <strong><?php echo $edit_item ? 'Edit Highlight' : 'Add Highlight'; ?></strong>
      </div>
      <div class="card-body">
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger py-2">
            <?php foreach ($errors as $e): ?>
              <div><?php echo escape($e); ?></div><?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="POST"
          action="<?php echo ADMIN_URL . '/modules/tours/highlights.php?tour_id=' . $tour_id . ($edit_item ? '&edit_id=' . $edit_item['id'] : ''); ?>">
          <?php echo csrf_field(); ?>
          <?php if ($edit_item): ?>
            <input type="hidden" name="edit_id" value="<?php echo $edit_item['id']; ?>">
          <?php endif; ?>

          <div class="mb-3">
            <label class="form-label fw-semibold">Highlight <span class="text-danger">*</span></label>
            <input type="text" name="highlight" class="form-control"
              value="<?php echo escape($edit_item['highlight'] ?? ($_POST['highlight'] ?? '')); ?>"
              placeholder="e.g. Visit Taj Mahal at sunrise" required>
            <small class="text-muted">A place, activity, or attraction included in this tour.</small>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" min="0"
              value="<?php echo (int) ($edit_item['sort_order'] ?? ($_POST['sort_order'] ?? 0)); ?>">
            <small class="text-muted">Lower number = displayed first.</small>
          </div>

          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-1"></i><?php echo $edit_item ? 'Update Highlight' : 'Add Highlight'; ?>
            </button>
            <?php if ($edit_item): ?>
              <a href="highlights.php?tour_id=<?php echo $tour_id; ?>" class="btn btn-outline-secondary">Cancel</a>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Highlights List -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Highlights <span class="badge bg-secondary ms-1"><?php echo count($highlights); ?></span></strong>
      </div>
      <div class="card-body p-0">
        <?php if (empty($highlights)): ?>
          <p class="text-muted text-center py-4 mb-0">
            <i class="bi bi-star d-block fs-3 mb-2"></i>
            No highlights yet. Add your first one.
          </p>
        <?php else: ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($highlights as $hl): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <i class="bi bi-check-circle-fill text-success me-2"></i>
                  <?php echo escape($hl['highlight']); ?>
                  <small class="text-muted ms-2">(order: <?php echo (int) $hl['sort_order']; ?>)</small>
                </div>
                <div class="d-flex gap-1 flex-shrink-0 ms-2">
                  <a href="highlights.php?tour_id=<?php echo $tour_id; ?>&edit_id=<?php echo $hl['id']; ?>"
                    class="btn btn-sm btn-outline-primary" title="Edit">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <a href="highlights.php?tour_id=<?php echo $tour_id; ?>&delete_id=<?php echo $hl['id']; ?>&csrf=<?php echo generate_csrf_token(); ?>"
                    class="btn btn-sm " title="Delete" onclick="return confirm('Delete this highlight?')">
                    <i class="bi bi-trash"></i>
                  </a>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>