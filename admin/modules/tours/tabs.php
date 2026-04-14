<?php
/**
 * Tour Content Tabs — dynamic tab sections per tour.
 *
 * Common examples: Overview, Itinerary Summary, What's Included,
 *                  What's Excluded, FAQ, Terms & Conditions.
 * Each frontend website renders the tabs it needs.
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

$page_title = 'Content Tabs — ' . $tour['title'];
$tour_title = $tour['title'];
$active_tab = 'tabs';
$errors = [];
$edit_item = null;
$edit_id = isset($_GET['edit_id']) ? (int) $_GET['edit_id'] : 0;

// --- DELETE ---
if (isset($_GET['delete_id'])) {
  $del_id = (int) $_GET['delete_id'];
  if (!validate_csrf_token($_GET['csrf'] ?? '')) {
    set_flash('error', 'Invalid security token.');
    redirect(ADMIN_URL . '/modules/tours/tabs.php?tour_id=' . $tour_id);
  }
  try {
    $pdo->prepare("DELETE FROM tour_tabs WHERE id = ? AND tour_id = ?")
      ->execute([$del_id, $tour_id]);
    log_activity('delete', 'tour_tabs', $del_id, "Deleted tab from tour #{$tour_id}");
    set_flash('success', 'Tab deleted.');
  } catch (PDOException $e) {
    set_flash('error', 'Could not delete tab.');
  }
  redirect(ADMIN_URL . '/modules/tours/tabs.php?tour_id=' . $tour_id);
}

// --- LOAD EDIT ITEM ---
if ($edit_id) {
  try {
    $eStmt = $pdo->prepare("SELECT * FROM tour_tabs WHERE id = ? AND tour_id = ?");
    $eStmt->execute([$edit_id, $tour_id]);
    $edit_item = $eStmt->fetch();
  } catch (PDOException $e) {
  }
  if (!$edit_item) {
    set_flash('error', 'Tab not found.');
    redirect(ADMIN_URL . '/modules/tours/tabs.php?tour_id=' . $tour_id);
  }
}

// --- POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    $errors[] = 'Invalid request.';
  } else {
    $tab_title = sanitize_input($_POST['tab_title'] ?? '');
    $tab_content = $_POST['tab_content'] ?? '';
    $sort_order = (int) ($_POST['sort_order'] ?? 0);
    $status = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';
    $post_edit = (int) ($_POST['edit_id'] ?? 0);

    if (empty($tab_title)) {
      $errors[] = 'Tab title is required.';
    }

    if (empty($errors)) {
      try {
        if ($post_edit) {
          $pdo->prepare("
                        UPDATE tour_tabs
                        SET tab_title = ?, tab_content = ?, sort_order = ?, status = ?, updated_at = NOW()
                        WHERE id = ? AND tour_id = ?
                    ")->execute([$tab_title, $tab_content, $sort_order, $status, $post_edit, $tour_id]);
          log_activity('update', 'tour_tabs', $post_edit, "Updated tab in tour #{$tour_id}");
          set_flash('success', 'Tab updated.');
        } else {
          $pdo->prepare("
                        INSERT INTO tour_tabs (tour_id, tab_title, tab_content, sort_order, status, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                    ")->execute([$tour_id, $tab_title, $tab_content, $sort_order, $status]);
          log_activity('create', 'tour_tabs', $pdo->lastInsertId(), "Added tab to tour #{$tour_id}");
          set_flash('success', 'Tab created.');
        }
        redirect(ADMIN_URL . '/modules/tours/tabs.php?tour_id=' . $tour_id);
      } catch (PDOException $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
      }
    }
  }
}

// --- Load all tabs ---
try {
  $tabStmt = $pdo->prepare("SELECT * FROM tour_tabs WHERE tour_id = ? ORDER BY sort_order ASC, id ASC");
  $tabStmt->execute([$tour_id]);
  $tabs = $tabStmt->fetchAll();
} catch (PDOException $e) {
  $tabs = [];
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header">
  <h1><i class="bi bi-layout-tabs me-2"></i>Tour Content Tabs</h1>
</div>

<?php include __DIR__ . '/_tour_nav.php'; ?>

<div class="alert alert-info py-2 small">
  <i class="bi bi-info-circle me-1"></i>
  Content tabs are dynamic sections like <em>Overview</em>, <em>Includes</em>, <em>Excludes</em>, <em>FAQ</em>.
  Each frontend website selects and renders the tabs relevant to its layout.
</div>

<div class="row g-4">
  <!-- Add / Edit Form -->
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header">
        <strong><?php echo $edit_item ? 'Edit Tab' : 'Add Tab'; ?></strong>
      </div>
      <div class="card-body">
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger py-2">
            <?php foreach ($errors as $e): ?>
              <div><?php echo escape($e); ?></div><?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="POST"
          action="<?php echo ADMIN_URL . '/modules/tours/tabs.php?tour_id=' . $tour_id . ($edit_item ? '&edit_id=' . $edit_item['id'] : ''); ?>">
          <?php echo csrf_field(); ?>
          <?php if ($edit_item): ?>
            <input type="hidden" name="edit_id" value="<?php echo $edit_item['id']; ?>">
          <?php endif; ?>

          <div class="mb-3">
            <label class="form-label fw-semibold">Tab Title <span class="text-danger">*</span></label>
            <input type="text" name="tab_title" class="form-control"
              value="<?php echo escape($edit_item['tab_title'] ?? ($_POST['tab_title'] ?? '')); ?>"
              placeholder="e.g. Overview, What's Included, FAQ" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Content</label>
            <textarea name="tab_content" class="form-control" rows="8"
              placeholder="HTML or plain text content for this section"><?php echo escape($edit_item['tab_content'] ?? ($_POST['tab_content'] ?? '')); ?></textarea>
            <small class="text-muted">Supports HTML markup.</small>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Sort Order</label>
              <input type="number" name="sort_order" class="form-control" min="0"
                value="<?php echo (int) ($edit_item['sort_order'] ?? ($_POST['sort_order'] ?? 0)); ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" class="form-select">
                <option value="active" <?php echo ($edit_item['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>
                  Active</option>
                <option value="inactive" <?php echo ($edit_item['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>
                  Inactive</option>
              </select>
            </div>
          </div>

          <div class="d-grid gap-2 mt-3">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-1"></i>
              <?php echo $edit_item ? 'Update Tab' : 'Add Tab'; ?>
            </button>
            <?php if ($edit_item): ?>
              <a href="tabs.php?tour_id=<?php echo $tour_id; ?>" class="btn btn-outline-secondary">Cancel</a>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>

    <!-- Common tab title suggestions -->
    <div class="card mt-3">
      <div class="card-header"><small class="fw-semibold">Common Tab Names</small></div>
      <div class="card-body py-2">
        <div class="d-flex flex-wrap gap-1">
          <?php
          $tabSuggestions = [
            'Overview',
            "What's Included",
            "What's Excluded",
            'Itinerary',
            'FAQ',
            'Terms & Conditions',
            'Hotel Details',
            'Transport Details',
            'Important Notes'
          ];
          foreach ($tabSuggestions as $ts):
            ?>
            <button type="button" class="btn btn-sm btn-outline-secondary py-0 tab-suggestion"
              data-name="<?php echo escape($ts); ?>">
              <?php echo escape($ts); ?>
            </button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabs List -->
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header">
        <strong>Tabs <span class="badge bg-secondary ms-1"><?php echo count($tabs); ?></span></strong>
      </div>
      <div class="card-body p-0">
        <?php if (empty($tabs)): ?>
          <p class="text-muted text-center py-4 mb-0">
            <i class="bi bi-layout-tabs d-block fs-3 mb-2"></i>No tabs yet.
          </p>
        <?php else: ?>
          <div class="accordion accordion-flush" id="tabsAccordion">
            <?php foreach ($tabs as $idx => $tab): ?>
              <div class="accordion-item">
                <h2 class="accordion-header">
                  <button class="accordion-button <?php echo $idx > 0 ? 'collapsed' : ''; ?>" type="button"
                    data-bs-toggle="collapse" data-bs-target="#tab<?php echo $tab['id']; ?>">
                    <span class="me-2 small text-muted">#<?php echo (int) $tab['sort_order']; ?></span>
                    <?php echo escape($tab['tab_title']); ?>
                    <?php if ($tab['status'] === 'inactive'): ?>
                      <span class="badge bg-secondary ms-2 small">Inactive</span>
                    <?php endif; ?>
                  </button>
                </h2>
                <div id="tab<?php echo $tab['id']; ?>"
                  class="accordion-collapse collapse <?php echo $idx === 0 ? 'show' : ''; ?>">
                  <div class="accordion-body">
                    <div class="text-muted small mb-3 border rounded p-2 bg-light" style="max-height:120px;overflow:auto">
                      <?php
                      $preview = strip_tags($tab['tab_content'] ?? '');
                      echo escape(truncate($preview, 300));
                      ?>
                    </div>
                    <div class="d-flex gap-2">
                      <a href="tabs.php?tour_id=<?php echo $tour_id; ?>&edit_id=<?php echo $tab['id']; ?>"
                        class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i>Edit
                      </a>
                      <a href="tabs.php?tour_id=<?php echo $tour_id; ?>&delete_id=<?php echo $tab['id']; ?>&csrf=<?php echo generate_csrf_token(); ?>"
                        class="btn btn-sm "
                        onclick="return confirm('Delete the tab &quot;<?php echo escape(addslashes($tab['tab_title'])); ?>&quot;?')">
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

<?php
$extra_js = <<<JS
<script>
document.querySelectorAll('.tab-suggestion').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelector('[name="tab_title"]').value = this.dataset.name;
        document.querySelector('[name="tab_title"]').focus();
    });
});
</script>
JS;
?>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>