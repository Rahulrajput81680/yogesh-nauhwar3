<?php
/**
 * Form Builder - List Submissions for a Form
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_login();
require_module('forms');

$form_id = (int) ($_GET['form_id'] ?? 0);
if (!$form_id) {
  set_flash('error', 'Invalid form.');
  redirect(ADMIN_URL . '/modules/forms/index.php');
}

// Load form
$stmt = $pdo->prepare("SELECT * FROM forms WHERE id = ? LIMIT 1");
$stmt->execute([$form_id]);
$form = $stmt->fetch();
if (!$form) {
  set_flash('error', 'Form not found.');
  redirect(ADMIN_URL . '/modules/forms/index.php');
}

// Load fields for column headers
$fieldsStmt = $pdo->prepare("SELECT * FROM form_fields WHERE form_id = ? ORDER BY field_order ASC, id ASC");
$fieldsStmt->execute([$form_id]);
$formFields = $fieldsStmt->fetchAll();

$page_title = 'Submissions – ' . escape($form['name']);

// Handle delete submission
if (isset($_GET['delete']) && isset($_GET['csrf'])) {
  $sid = (int) $_GET['delete'];
  if ($sid && validate_csrf_token($_GET['csrf'])) {
    try {
      $pdo->prepare("DELETE FROM form_submissions WHERE id = ? AND form_id = ?")->execute([$sid, $form_id]);
      set_flash('success', 'Submission deleted.');
    } catch (PDOException $e) {
      set_flash('error', 'Failed to delete.');
    }
  }
  redirect(ADMIN_URL . '/modules/forms/submissions.php?form_id=' . $form_id);
}

// Pagination
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;

try {
  $countStmt = $pdo->prepare("SELECT COUNT(*) FROM form_submissions WHERE form_id = ?");
  $countStmt->execute([$form_id]);
  $total = (int) $countStmt->fetchColumn();
  $totalPages = max(1, ceil($total / $perPage));

  $submissions = $pdo->prepare("SELECT * FROM form_submissions WHERE form_id = ? ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
  $submissions->execute([$form_id]);
  $submissions = $submissions->fetchAll();
} catch (PDOException $e) {
  $submissions = [];
  $total = 0;
  $totalPages = 1;
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <h1><i class="bi bi-inbox me-2"></i>Submissions</h1>
    <p class="text-muted mb-0">Form: <strong>
        <?php echo escape($form['name']); ?>
      </strong></p>
  </div>
  <?php if (has_permission('forms_edit')): ?>
  <a href="<?php echo ADMIN_URL; ?>/modules/forms/edit.php?id=<?php echo $form_id; ?>" class="btn btn-secondary">
    <i class="bi bi-arrow-left me-2"></i>Back to Form
  </a>
  <?php else: ?>
  <a href="<?php echo ADMIN_URL; ?>/modules/forms/index.php" class="btn btn-secondary">
    <i class="bi bi-arrow-left me-2"></i>Back to Forms
  </a>
  <?php endif; ?>
</div>

<?php $flash = get_flash(); ?>
<?php if ($flash): ?>
  <div class="alert alert-<?php echo escape($flash['type']); ?> alert-dismissible fade show">
    <?php echo escape($flash['message']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-body p-0">
    <?php if (empty($submissions)): ?>
      <div class="text-center py-5 text-muted">
        <i class="bi bi-inbox fs-1 mb-3 d-block"></i>
        <p>No submissions yet for this form.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <!-- Show first 3 field labels as columns -->
              <?php foreach (array_slice($formFields, 0, 3) as $fld): ?>
                <th>
                  <?php echo escape(html_entity_decode($fld['label'], ENT_QUOTES, 'UTF-8')); ?>
                </th>
              <?php endforeach; ?>
              <th>Submitted</th>
              <th width="120">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($submissions as $sub): ?>
              <?php $data = json_decode($sub['data'], true) ?? []; ?>
              <tr>
                <td>
                  <?php echo $sub['id']; ?>
                </td>
                <?php foreach (array_slice($formFields, 0, 3) as $fld): ?>
                  <td>
                    <?php echo escape(truncate((string) ($data[$fld['field_name']] ?? '—'), 50)); ?>
                  </td>
                <?php endforeach; ?>
                <td>
                  <?php echo format_datetime($sub['created_at']); ?>
                </td>
                <td style="white-space:nowrap;">
                  <a href="<?php echo ADMIN_URL; ?>/modules/forms/view-submission.php?id=<?php echo $sub['id']; ?>&form_id=<?php echo $form_id; ?>"
                    class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye"></i>
                  </a>
                  <?php if (has_permission('forms_delete')): ?>
                  <a href="?form_id=<?php echo $form_id; ?>&delete=<?php echo $sub['id']; ?>&csrf=<?php echo generate_csrf_token(); ?>"
                    class="btn btn-sm delete-btn">
                    <i class="bi bi-trash"></i>
                  </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="px-3 py-2 d-flex justify-content-between align-items-center border-top flex-wrap gap-2">
        <span class="text-muted small">Total:
          <?php echo $total; ?> submission(s)
        </span>
        <?php echo create_pagination($page, $totalPages, ADMIN_URL . '/modules/forms/submissions.php?form_id=' . $form_id); ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>