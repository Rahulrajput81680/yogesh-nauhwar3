<?php
/**
 * Form Builder - Edit Form + Manage Fields
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_login();
require_module('forms');
require_permission('forms_edit');

$form_id = (int)($_GET['id'] ?? 0);
if (!$form_id) { set_flash('error', 'Invalid form ID.'); redirect(ADMIN_URL . '/modules/forms/index.php'); }

// Load form
try {
    $stmt = $pdo->prepare("SELECT * FROM forms WHERE id = ? LIMIT 1");
    $stmt->execute([$form_id]);
    $form = $stmt->fetch();
} catch (PDOException $e) { $form = null; }
if (!$form) { set_flash('error', 'Form not found.'); redirect(ADMIN_URL . '/modules/forms/index.php'); }

$page_title = 'Edit Form – ' . escape($form['name']);
$errors     = [];
$fieldErrors= [];

// ─── Handle delete field ──────────────────────────────────────────────────────
if (isset($_GET['delete_field'])) {
    $fid  = (int)$_GET['delete_field'];
    $csrf = $_GET['csrf'] ?? '';
    if ($fid && validate_csrf_token($csrf)) {
        try {
            $pdo->prepare("DELETE FROM form_fields WHERE id = ? AND form_id = ?")->execute([$fid, $form_id]);
        } catch (PDOException $e) {}
    }
    redirect(ADMIN_URL . '/modules/forms/edit.php?id=' . $form_id . '#fields');
}

// ─── Handle POST ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $action = $_POST['action'] ?? '';

        // ── Update form metadata ──────────────────────────────────────────────
        if ($action === 'update_form') {
            $name        = sanitize_input($_POST['name']        ?? '');
            $slug        = sanitize_input($_POST['slug']        ?? '');
            $description = sanitize_input($_POST['description'] ?? '');
            $status      = sanitize_input($_POST['status']      ?? 'active');

            if (empty($name)) $errors[] = 'Form name is required.';
            if (empty($slug)) $errors[] = 'Slug is required.';

            if (empty($errors)) {
                try {
                    $pdo->prepare("UPDATE forms SET name=?,slug=?,description=?,status=?,updated_at=NOW() WHERE id=?")
                        ->execute([$name, $slug, $description, $status, $form_id]);
                    log_activity('update', 'forms', $form_id, "Updated form: $name");
                    set_flash('success', 'Form updated successfully.');
                    redirect(ADMIN_URL . '/modules/forms/edit.php?id=' . $form_id);
                } catch (PDOException $e) {
                    $errors[] = str_contains($e->getMessage(), 'Duplicate')
                        ? 'Slug already in use. Choose another.' : $e->getMessage();
                }
            }
        }

        // ── Add / update a field ──────────────────────────────────────────────
        if ($action === 'save_field') {
            $field_id    = (int)($_POST['field_id'] ?? 0);
            $label       = trim(stripslashes($_POST['label']       ?? ''));
            $field_name  = sanitize_input($_POST['field_name']  ?? '');
            $field_type  = sanitize_input($_POST['field_type']  ?? 'text');
            $is_required = !empty($_POST['is_required']) ? 1 : 0;
            $options     = sanitize_input($_POST['options']     ?? '');
            $field_order = (int)($_POST['field_order'] ?? 0);

            $allowed_types = ['text','email','number','phone','date','textarea','select','radio','checkbox','file'];
            if (!in_array($field_type, $allowed_types)) $field_type = 'text';

            // Auto field_name from label
            if (empty($field_name) && !empty($label)) {
                $field_name = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $label));
            }

            if (empty($label))      $fieldErrors[] = 'Field label is required.';
            if (empty($field_name)) $fieldErrors[] = 'Field name is required.';

            if (empty($fieldErrors)) {
                // Validate JSON options if provided
                $options_json = null;
                if ($options !== '' && in_array($field_type, ['select','radio','checkbox'])) {
                    $opts = array_filter(array_map('trim', explode("\n", $options)));
                    $options_json = json_encode(array_values($opts));
                }

                try {
                    if ($field_id) {
                        $pdo->prepare("UPDATE form_fields SET label=?,field_name=?,field_type=?,is_required=?,options=?,field_order=? WHERE id=? AND form_id=?")
                            ->execute([$label, $field_name, $field_type, $is_required, $options_json, $field_order, $field_id, $form_id]);
                    } else {
                        $pdo->prepare("INSERT INTO form_fields (form_id,label,field_name,field_type,is_required,options,field_order) VALUES(?,?,?,?,?,?,?)")
                            ->execute([$form_id, $label, $field_name, $field_type, $is_required, $options_json, $field_order]);
                    }
                    set_flash('success', 'Field saved.');
                    redirect(ADMIN_URL . '/modules/forms/edit.php?id=' . $form_id . '#fields');
                } catch (PDOException $e) {
                    $fieldErrors[] = 'Failed to save field: ' . $e->getMessage();
                }
            }
        }
    }
}

// Reload form after edits
$stmt = $pdo->prepare("SELECT * FROM forms WHERE id = ? LIMIT 1");
$stmt->execute([$form_id]);
$form = $stmt->fetch();

// Load fields
$fields = $pdo->prepare("SELECT * FROM form_fields WHERE form_id = ? ORDER BY field_order ASC, id ASC");
$fields->execute([$form_id]);
$fields = $fields->fetchAll();

$allowed_types = ['text','email','number','phone','date','textarea','select','radio','checkbox','file'];

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <h1><i class="bi bi-pencil-square me-2"></i><?php echo escape($form['name']); ?></h1>
  <div class="d-flex gap-2">
    <a href="<?php echo ADMIN_URL; ?>/modules/forms/submissions.php?form_id=<?php echo $form_id; ?>"
       class="btn btn-outline-info">
      <i class="bi bi-inbox me-2"></i>View Submissions
    </a>
    <a href="<?php echo ADMIN_URL; ?>/modules/forms/index.php" class="btn btn-secondary">
      <i class="bi bi-arrow-left me-2"></i>Back
    </a>
  </div>
</div>

<?php $flash = get_flash(); ?>
<?php if ($flash): ?>
  <div class="alert alert-<?php echo escape($flash['type']); ?> alert-dismissible fade show">
    <?php echo escape($flash['message']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<div class="row g-4">

  <!-- ── Form Settings ────────────────────────────────────────────────────── -->
  <div class="col-lg-4">
    <div class="card sticky-top" style="top:80px;">
      <div class="card-header"><h5 class="mb-0">Form Settings</h5></div>
      <div class="card-body">
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger py-2"><ul class="mb-0">
            <?php foreach ($errors as $e): ?><li><?php echo escape($e); ?></li><?php endforeach; ?>
          </ul></div>
        <?php endif; ?>

        <form method="POST" action="">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="update_form">

          <div class="mb-3">
            <label class="form-label fw-bold">Form Name</label>
            <input type="text" class="form-control" name="name"
              value="<?php echo escape($form['name']); ?>" required maxlength="255">
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Slug</label>
            <input type="text" class="form-control" name="slug"
              value="<?php echo escape($form['slug']); ?>" required maxlength="255">
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Description</label>
            <textarea class="form-control" name="description" rows="2" maxlength="500"><?php echo escape($form['description'] ?? ''); ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Status</label>
            <select class="form-select" name="status">
              <option value="active"   <?php echo $form['status']==='active'   ?'selected':''; ?>>Active</option>
              <option value="inactive" <?php echo $form['status']==='inactive' ?'selected':''; ?>>Inactive</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-check-lg me-2"></i>Save Settings
          </button>
        </form>

        <hr>
        <p class="text-muted small mb-1"><strong>Frontend Embed Slug:</strong></p>
        <code class="d-block bg-light p-2 rounded"><?php echo escape($form['slug']); ?></code>
        <small class="text-muted">Use this slug to load the form on your website.</small>
      </div>
    </div>
  </div>

  <!-- ── Field Management ──────────────────────────────────────────────────── -->
  <div class="col-lg-8" id="fields">

    <!-- Existing fields -->
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Fields (<?php echo count($fields); ?>)</h5>
      </div>
      <?php if (empty($fields)): ?>
        <div class="card-body text-muted text-center py-4">
          No fields yet. Add the first field below.
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th><th>Label</th><th>Name</th><th>Type</th><th>Required</th><th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($fields as $i => $field): ?>
                <tr>
                  <td><?php echo $field['field_order'] ?: ($i+1); ?></td>
                  <td><?php echo escape(html_entity_decode($field['label'], ENT_QUOTES, 'UTF-8')); ?></td>
                  <td><code><?php echo escape($field['field_name']); ?></code></td>
                  <td><span class="badge bg-secondary"><?php echo escape($field['field_type']); ?></span></td>
                  <td><?php echo $field['is_required'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-dash text-muted"></i>'; ?></td>
                  <td>
                    <button type="button" class="btn btn-sm btn-outline-primary edit-field-btn"
                      data-id="<?php echo $field['id']; ?>"
                      data-label="<?php echo escape(html_entity_decode($field['label'], ENT_QUOTES, 'UTF-8')); ?>"
                      data-name="<?php echo escape($field['field_name']); ?>"
                      data-type="<?php echo escape($field['field_type']); ?>"
                      data-required="<?php echo $field['is_required']; ?>"
                      data-options="<?php echo escape($field['options'] ? implode("\n", json_decode($field['options'], true) ?? []) : ''); ?>"
                      data-order="<?php echo $field['field_order']; ?>">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <a href="?id=<?php echo $form_id; ?>&delete_field=<?php echo $field['id']; ?>&csrf=<?php echo generate_csrf_token(); ?>"
                      class="btn btn-sm btn-outline-danger delete-btn" title="Remove field">
                      <i class="bi bi-trash"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- Add / Edit field form -->
    <div class="card">
      <div class="card-header"><h5 class="mb-0" id="fieldFormTitle">Add New Field</h5></div>
      <div class="card-body">
        <?php if (!empty($fieldErrors)): ?>
          <div class="alert alert-danger py-2"><ul class="mb-0">
            <?php foreach ($fieldErrors as $e): ?><li><?php echo escape($e); ?></li><?php endforeach; ?>
          </ul></div>
        <?php endif; ?>

        <form method="POST" action="?id=<?php echo $form_id; ?>#fields">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="save_field">
          <input type="hidden" name="field_id" id="fieldId" value="0">

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">Label <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="label" id="fieldLabel"
                placeholder="e.g. Full Name" maxlength="255" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Field Name (key)</label>
              <input type="text" class="form-control" name="field_name" id="fieldName"
                placeholder="e.g. full_name" maxlength="100"
                pattern="[a-z0-9_]+" title="Lowercase letters, numbers and underscores only">
              <small class="text-muted">Auto-generated from label if left empty.</small>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-bold">Type</label>
              <select class="form-select" name="field_type" id="fieldType">
                <?php foreach ($allowed_types as $t): ?>
                  <option value="<?php echo $t; ?>"><?php echo ucfirst($t); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-bold">Order</label>
              <input type="number" class="form-control" name="field_order" id="fieldOrder"
                value="<?php echo count($fields) + 1; ?>" min="0">
            </div>
            <div class="col-md-4 d-flex align-items-end">
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="is_required" id="fieldRequired" value="1">
                <label class="form-check-label" for="fieldRequired">Required</label>
              </div>
            </div>
            <div class="col-12" id="optionsRow" style="display:none;">
              <label class="form-label fw-bold">Options</label>
              <textarea class="form-control" name="options" id="fieldOptions" rows="4"
                placeholder="Enter one option per line:&#10;Option A&#10;Option B&#10;Option C"></textarea>
              <small class="text-muted">One option per line. Used for select, radio, checkbox fields.</small>
            </div>
          </div>

          <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-success">
              <i class="bi bi-check-lg me-2"></i><span id="fieldSubmitText">Add Field</span>
            </button>
            <button type="button" class="btn btn-outline-secondary" id="resetFieldForm">
              <i class="bi bi-x-lg me-2"></i>Clear
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>

<script>
// Show/hide options textarea based on field type
const fieldTypeEl   = document.getElementById('fieldType');
const optionsRow    = document.getElementById('optionsRow');
const needsOptions  = ['select','radio','checkbox'];
fieldTypeEl.addEventListener('change', function() {
  optionsRow.style.display = needsOptions.includes(this.value) ? 'block' : 'none';
});

// Auto field_name from label
document.getElementById('fieldLabel').addEventListener('blur', function() {
  const fn = document.getElementById('fieldName');
  if (!fn.value) {
    fn.value = this.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
  }
});

// Populate edit field form
document.querySelectorAll('.edit-field-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    const d = this.dataset;
    document.getElementById('fieldId').value          = d.id;
    document.getElementById('fieldLabel').value       = d.label;
    document.getElementById('fieldName').value        = d.name;
    document.getElementById('fieldType').value        = d.type;
    document.getElementById('fieldRequired').checked  = d.required === '1';
    document.getElementById('fieldOptions').value     = d.options;
    document.getElementById('fieldOrder').value       = d.order;
    document.getElementById('fieldFormTitle').textContent = 'Edit Field';
    document.getElementById('fieldSubmitText').textContent = 'Update Field';
    optionsRow.style.display = needsOptions.includes(d.type) ? 'block' : 'none';
    document.getElementById('fields').scrollIntoView({behavior:'smooth'});
  });
});

// Reset form
document.getElementById('resetFieldForm').addEventListener('click', function() {
  document.getElementById('fieldId').value          = '0';
  document.getElementById('fieldLabel').value       = '';
  document.getElementById('fieldName').value        = '';
  document.getElementById('fieldType').value        = 'text';
  document.getElementById('fieldRequired').checked  = false;
  document.getElementById('fieldOptions').value     = '';
  document.getElementById('fieldOrder').value       = '<?php echo count($fields) + 1; ?>';
  document.getElementById('fieldFormTitle').textContent = 'Add New Field';
  document.getElementById('fieldSubmitText').textContent = 'Add Field';
  optionsRow.style.display = 'none';
});
</script>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
