<?php
/**
 * Form Builder - List All Forms
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_login();
require_module('forms');
require_permission('forms_view');

$page_title = 'Forms';

try {
  $forms = $pdo->query("
        SELECT f.*,
               (SELECT COUNT(*) FROM form_fields ff WHERE ff.form_id = f.id) AS field_count,
               (SELECT COUNT(*) FROM form_submissions fs WHERE fs.form_id = f.id) AS submission_count
        FROM forms f
        ORDER BY f.created_at DESC
    ")->fetchAll();
} catch (PDOException $e) {
  $forms = [];
  set_flash('error', 'Could not load forms: ' . $e->getMessage());
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
  <h1><i class="bi bi-ui-checks-grid me-2"></i>Forms</h1>
  <?php if (has_permission('forms_create')): ?>
    <a href="<?php echo ADMIN_URL; ?>/modules/forms/create.php" class="btn btn-primary">
      <i class="bi bi-plus-circle me-2"></i>New Form
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

<?php if (empty($forms)): ?>
  <div class="card">
    <div class="card-body text-center py-5 text-muted">
      <i class="bi bi-ui-checks-grid fs-1 mb-3 d-block"></i>
      <p>No forms yet. <a href="<?php echo ADMIN_URL; ?>/modules/forms/create.php">Create your first form</a>.</p>
    </div>
  </div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($forms as $form): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <h5 class="card-title mb-0"><?php echo escape($form['name']); ?></h5>
              <?php echo get_status_badge($form['status']); ?>
            </div>
            <p class="text-muted small mb-3">
              Slug: <code><?php echo escape($form['slug']); ?></code>
            </p>
            <div class="d-flex gap-3 mb-3 text-muted small">
              <span><i class="bi bi-list-ul me-1"></i><?php echo $form['field_count']; ?> field(s)</span>
              <span><i class="bi bi-inbox me-1"></i><?php echo $form['submission_count']; ?> submission(s)</span>
            </div>
            <?php if ($form['description']): ?>
              <p class="small text-muted"><?php echo escape(truncate($form['description'], 80)); ?></p>
            <?php endif; ?>
          </div>
          <div class="card-footer d-flex gap-2 flex-wrap">
            <?php if (has_permission('forms_edit')): ?>
            <a href="<?php echo ADMIN_URL; ?>/modules/forms/edit.php?id=<?php echo $form['id']; ?>"
              class="btn btn-sm btn-outline-primary">
              <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <?php endif; ?>
            <a href="<?php echo ADMIN_URL; ?>/modules/forms/submissions.php?form_id=<?php echo $form['id']; ?>"
              class="btn btn-sm btn-outline-info">
              <i class="bi bi-inbox me-1"></i>Submissions
            </a>
            <?php if (has_permission('forms_delete')): ?>
            <a href="<?php echo ADMIN_URL; ?>/modules/forms/delete.php?id=<?php echo $form['id']; ?>&csrf=<?php echo generate_csrf_token(); ?>"
              class="btn btn-sm  delete-btn ms-auto">
              <i class="bi bi-trash"></i>
            </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>