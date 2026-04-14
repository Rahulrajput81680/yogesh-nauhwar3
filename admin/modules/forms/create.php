<?php
/**
 * Form Builder - Create New Form
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_login();
require_module('forms');
require_permission('forms_create');

$page_title = 'Create Form';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $name        = sanitize_input($_POST['name']        ?? '');
        $slug        = sanitize_input($_POST['slug']        ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $status      = sanitize_input($_POST['status']      ?? 'active');

        // Auto-generate slug
        if (empty($slug) && !empty($name)) {
            $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
        }

        if (empty($name)) $errors[] = 'Form name is required.';
        if (empty($slug)) $errors[] = 'Slug is required.';

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO forms (name, slug, description, status, created_at, updated_at)
                    VALUES (?, ?, ?, ?, NOW(), NOW())
                ");
                $stmt->execute([$name, $slug, $description, $status]);
                $form_id = $pdo->lastInsertId();
                log_activity('create', 'forms', $form_id, "Created form: $name");
                set_flash('success', 'Form created! Now add fields to it.');
                redirect(ADMIN_URL . '/modules/forms/edit.php?id=' . $form_id);
            } catch (PDOException $e) {
                if (str_contains($e->getMessage(), 'Duplicate') || str_contains($e->getMessage(), 'unique')) {
                    $errors[] = 'A form with this slug already exists. Please choose a different slug.';
                } else {
                    $errors[] = 'Failed to create form: ' . $e->getMessage();
                }
            }
        }
    }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
  <h1><i class="bi bi-plus-circle me-2"></i>Create Form</h1>
  <a href="<?php echo ADMIN_URL; ?>/modules/forms/index.php" class="btn btn-secondary">
    <i class="bi bi-arrow-left me-2"></i>Back
  </a>
</div>

<div class="row">
  <div class="col-md-7 mx-auto">
    <div class="card">
      <div class="card-body">
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?php echo escape($e); ?></li><?php endforeach; ?></ul>
          </div>
        <?php endif; ?>

        <form method="POST" action="">
          <?php echo csrf_field(); ?>

          <div class="mb-3">
            <label class="form-label fw-bold">Form Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="name" name="name"
              value="<?php echo escape($_POST['name'] ?? ''); ?>" required maxlength="255"
              placeholder="e.g. Admission Form, Job Application">
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Slug <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text text-muted">/form/</span>
              <input type="text" class="form-control" id="slug" name="slug"
                value="<?php echo escape($_POST['slug'] ?? ''); ?>"
                placeholder="admission-form" maxlength="255"
                pattern="[a-z0-9\-]+" title="Only lowercase letters, numbers and hyphens">
            </div>
            <small class="text-muted">Used in URL. Auto-generated from name if left empty.</small>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Description</label>
            <textarea class="form-control" name="description" rows="2"
              maxlength="500"><?php echo escape($_POST['description'] ?? ''); ?></textarea>
          </div>

          <div class="mb-4">
            <label class="form-label fw-bold">Status</label>
            <select class="form-select" name="status">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          <div class="d-flex justify-content-end gap-2">
            <a href="<?php echo ADMIN_URL; ?>/modules/forms/index.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-arrow-right-circle me-2"></i>Create & Add Fields
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
// Auto-generate slug from name
document.getElementById('name').addEventListener('blur', function () {
    const slugEl = document.getElementById('slug');
    if (!slugEl.value) {
        slugEl.value = this.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    }
});
</script>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
