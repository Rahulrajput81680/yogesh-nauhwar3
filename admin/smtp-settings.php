<?php
/**
 * SMTP Settings
 */

require_once __DIR__ . '/init.php';
require_login();

$page_title = 'SMTP Settings';
$errors = [];
$success = '';

$settings = [
  'smtp_from_name' => (string) get_admin_setting('smtp_from_name', defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : PROJECT_NAME),
  'smtp_from_email' => (string) get_admin_setting('smtp_from_email', defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : ''),
  'smtp_host' => (string) get_admin_setting('smtp_host', defined('MAIL_SMTP_HOST') ? MAIL_SMTP_HOST : ''),
  'smtp_port' => (string) get_admin_setting('smtp_port', (string) (defined('MAIL_SMTP_PORT') ? (int) MAIL_SMTP_PORT : 587)),
  'smtp_user' => (string) get_admin_setting('smtp_user', defined('MAIL_SMTP_USER') ? MAIL_SMTP_USER : ''),
  'smtp_pass' => (string) get_admin_setting('smtp_pass', ''),
  'smtp_encryption' => (string) get_admin_setting('smtp_encryption', 'tls'),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrfToken = $_POST['csrf_token'] ?? '';

  if (!validate_csrf_token($csrfToken)) {
    $errors[] = 'Invalid request. Please try again.';
  } else {
    $settings['smtp_from_name'] = trim((string) ($_POST['smtp_from_name'] ?? ''));
    $settings['smtp_from_email'] = trim((string) ($_POST['smtp_from_email'] ?? ''));
    $settings['smtp_host'] = trim((string) ($_POST['smtp_host'] ?? ''));
    $settings['smtp_port'] = trim((string) ($_POST['smtp_port'] ?? '587'));
    $settings['smtp_user'] = trim((string) ($_POST['smtp_user'] ?? ''));
    $settings['smtp_pass'] = (string) ($_POST['smtp_pass'] ?? '');
    $settings['smtp_encryption'] = strtolower(trim((string) ($_POST['smtp_encryption'] ?? 'tls')));

    if ($settings['smtp_from_name'] === '') {
      $errors[] = 'From name is required.';
    }

    if ($settings['smtp_from_email'] === '' || !filter_var($settings['smtp_from_email'], FILTER_VALIDATE_EMAIL)) {
      $errors[] = 'A valid from email is required.';
    }

    if ($settings['smtp_host'] !== '') {
      if (!ctype_digit($settings['smtp_port']) || (int) $settings['smtp_port'] <= 0 || (int) $settings['smtp_port'] > 65535) {
        $errors[] = 'SMTP port must be a number between 1 and 65535.';
      }
      if ($settings['smtp_user'] === '') {
        $errors[] = 'SMTP username is required when SMTP host is provided.';
      }
      if ($settings['smtp_pass'] === '') {
        $errors[] = 'SMTP password is required when SMTP host is provided.';
      }
    }

    if (!in_array($settings['smtp_encryption'], ['tls', 'ssl', 'none'], true)) {
      $errors[] = 'Invalid encryption type.';
    }

    if (empty($errors)) {
      $ok = true;
      foreach ($settings as $settingKey => $settingValue) {
        if (!set_admin_setting($settingKey, $settingValue)) {
          $ok = false;
          break;
        }
      }

      if ($ok) {
        log_activity('update', 'smtp', null, 'Updated SMTP settings');
        $success = 'SMTP settings saved successfully.';
      } else {
        $errors[] = 'Failed to save SMTP settings. Please try again.';
      }
    }
  }
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <h1><i class="bi bi-envelope-gear me-2"></i>SMTP Settings</h1>
</div>

<div class="row">
  <div class="col-md-8 mx-auto">
    <div class="card">
      <div class="card-body">
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <strong>Error:</strong>
            <ul class="mb-0">
              <?php foreach ($errors as $error): ?>
                <li><?php echo escape($error); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
          <div class="alert alert-success"><?php echo escape($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
          <?php echo csrf_field(); ?>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="smtp_from_name" class="form-label">From Name *</label>
              <input type="text" class="form-control" id="smtp_from_name" name="smtp_from_name" value="<?php echo escape($settings['smtp_from_name']); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="smtp_from_email" class="form-label">From Email *</label>
              <input type="email" class="form-control" id="smtp_from_email" name="smtp_from_email" value="<?php echo escape($settings['smtp_from_email']); ?>" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="smtp_host" class="form-label">SMTP Host</label>
            <input type="text" class="form-control" id="smtp_host" name="smtp_host" value="<?php echo escape($settings['smtp_host']); ?>" placeholder="smtp.gmail.com">
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label for="smtp_port" class="form-label">SMTP Port</label>
              <input type="number" class="form-control" id="smtp_port" name="smtp_port" min="1" max="65535" value="<?php echo escape($settings['smtp_port']); ?>">
            </div>
            <div class="col-md-8 mb-3">
              <label for="smtp_encryption" class="form-label">Encryption</label>
              <select class="form-select" id="smtp_encryption" name="smtp_encryption">
                <option value="tls" <?php echo $settings['smtp_encryption'] === 'tls' ? 'selected' : ''; ?>>TLS (Recommended)</option>
                <option value="ssl" <?php echo $settings['smtp_encryption'] === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                <option value="none" <?php echo $settings['smtp_encryption'] === 'none' ? 'selected' : ''; ?>>None</option>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="smtp_user" class="form-label">SMTP Username</label>
              <input type="text" class="form-control" id="smtp_user" name="smtp_user" value="<?php echo escape($settings['smtp_user']); ?>" placeholder="your@gmail.com">
            </div>
            <div class="col-md-6 mb-3">
              <label for="smtp_pass" class="form-label">SMTP Password / App Password</label>
              <input type="password" class="form-control" id="smtp_pass" name="smtp_pass" value="<?php echo escape($settings['smtp_pass']); ?>">
              <small class="text-muted">For Gmail, use an App Password (not your account password).</small>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2">
            <a href="<?php echo ADMIN_URL; ?>/dashboard.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn"><i class="bi bi-check-lg me-2"></i>Save SMTP Settings</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
