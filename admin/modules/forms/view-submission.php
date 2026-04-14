<?php
/**
 * Form Builder - View Single Submission
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_login();
require_module('forms');

$sub_id  = (int)($_GET['id']      ?? 0);
$form_id = (int)($_GET['form_id'] ?? 0);

if (!$sub_id || !$form_id) {
    set_flash('error', 'Invalid request.');
    redirect(ADMIN_URL . '/modules/forms/index.php');
}

try {
    $stmt = $pdo->prepare("SELECT * FROM form_submissions WHERE id = ? AND form_id = ? LIMIT 1");
    $stmt->execute([$sub_id, $form_id]);
    $submission = $stmt->fetch();
} catch (PDOException $e) { $submission = null; }

if (!$submission) { set_flash('error', 'Submission not found.'); redirect(ADMIN_URL . '/modules/forms/submissions.php?form_id=' . $form_id); }

// Load form fields for labels
$fieldsStmt = $pdo->prepare("SELECT * FROM form_fields WHERE form_id = ? ORDER BY field_order ASC");
$fieldsStmt->execute([$form_id]);
$formFields = $fieldsStmt->fetchAll();

// Format field lookup
$fieldMap = [];
foreach ($formFields as $f) {
    $fieldMap[$f['field_name']] = $f;
}

$data       = json_decode($submission['data'], true) ?? [];
$page_title = 'Submission #' . $sub_id;

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
  <h1><i class="bi bi-file-earmark-text me-2"></i>Submission #<?php echo $sub_id; ?></h1>
  <a href="<?php echo ADMIN_URL; ?>/modules/forms/submissions.php?form_id=<?php echo $form_id; ?>" class="btn btn-secondary">
    <i class="bi bi-arrow-left me-2"></i>Back to Submissions
  </a>
</div>

<div class="row">
  <div class="col-lg-8 mx-auto">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Submitted Data</h5>
        <small class="text-muted"><?php echo format_datetime($submission['created_at']); ?></small>
      </div>
      <div class="card-body">
        <dl class="row mb-0">
          <?php foreach ($formFields as $field): ?>
            <?php $val = $data[$field['field_name']] ?? null; ?>
            <dt class="col-sm-4 text-muted"><?php echo escape(html_entity_decode($field['label'], ENT_QUOTES, 'UTF-8')); ?></dt>
            <dd class="col-sm-8">
              <?php if ($val === null || $val === ''): ?>
                <em class="text-muted">—</em>
              <?php elseif ($field['field_type'] === 'file'): ?>
                <?php if (file_exists(UPLOAD_DIR . '/' . ltrim($val, '/'))): ?>
                  <a href="<?php echo escape(UPLOAD_URL . '/' . ltrim($val, '/')); ?>" target="_blank" rel="noopener">
                    <i class="bi bi-download me-1"></i><?php echo escape(basename($val)); ?>
                  </a>
                <?php else: ?>
                  <span class="text-muted"><?php echo escape(basename($val)); ?> (file not found)</span>
                <?php endif; ?>
              <?php elseif ($field['field_type'] === 'textarea'): ?>
                <span style="white-space:pre-wrap;"><?php echo escape($val); ?></span>
              <?php elseif (is_array($val)): ?>
                <?php echo escape(implode(', ', $val)); ?>
              <?php else: ?>
                <?php echo escape((string)$val); ?>
              <?php endif; ?>
            </dd>
          <?php endforeach; ?>

          <!-- Show any extra submitted keys not in current field definitions -->
          <?php foreach ($data as $key => $val): ?>
            <?php if (!isset($fieldMap[$key])): ?>
              <dt class="col-sm-4 text-muted"><code><?php echo escape($key); ?></code></dt>
              <dd class="col-sm-8"><?php echo escape(is_array($val) ? implode(', ', $val) : (string)$val); ?></dd>
            <?php endif; ?>
          <?php endforeach; ?>
        </dl>

        <?php if ($submission['ip_address']): ?>
          <hr>
          <small class="text-muted">Submitted from IP: <?php echo escape($submission['ip_address']); ?></small>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
