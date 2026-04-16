<?php
/**
 * Forgot Password - Request Password Reset
 * 
 * This page allows admin users to request a password reset link
 * by entering their registered email address.
 */

require_once __DIR__ . '/init.php';

// Redirect if already logged in
if (is_logged_in()) {
  redirect(ADMIN_URL . '/dashboard.php');
}

$message = '';
$message_type = '';
$reset_link = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = sanitize_input($_POST['email'] ?? '');
  $csrf_token = $_POST['csrf_token'] ?? '';

  // Validate CSRF token
  if (!validate_csrf_token($csrf_token)) {
    $message = 'Invalid request. Please try again.';
    $message_type = 'danger';
  } elseif (empty($email)) {
    $message = 'Please enter your email address.';
    $message_type = 'danger';
  } elseif (!is_valid_email($email)) {
    $message = 'Please enter a valid email address.';
    $message_type = 'danger';
  } else {
    // Create password reset request (sends email automatically)
    $result = create_password_reset_request($pdo, $email);

    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'danger';
    $reset_link = $result['reset_link'] ?? '';
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password - <?php echo escape(PROJECT_NAME); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <link href="<?php echo ADMIN_URL; ?>/assets/css/admin.css" rel="stylesheet">
</head>

<body class="forgot-password-page">
  <div class="forgot-password-container">
    <div class="card forgot-password-card">
      <div class="forgot-password-header">
        <i class="bi bi-key-fill forgot-password-header-icon"></i>
        <h3 class="mb-0">Forgot Password?</h3>
        <p class="mb-0 mt-2" style="font-size: 14px; opacity: 0.9;">No worries, we'll send you reset instructions</p>
      </div>

      <div class="forgot-password-body">
        <?php if ($message): ?>
          <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <i
              class="bi bi-<?php echo $message_type === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill'; ?> me-2"></i>
            <?php echo escape($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <?php if ($message_type === 'success'): ?>
          <!-- Successfully sent -->
          <div class="info-box">
            <i class="bi bi-envelope-check-fill"></i>
            If your email is registered with us, you will receive a password reset link shortly. The link expires in 30
            minutes.
          </div>

          <?php
          $smtpHostForInfo = (string) get_admin_setting('smtp_host', defined('MAIL_SMTP_HOST') ? MAIL_SMTP_HOST : '');
          if (stripos($smtpHostForInfo, 'sandbox.smtp.mailtrap.io') !== false):
            ?>
            <div class="alert alert-info" role="alert">
              <i class="bi bi-info-circle-fill me-2"></i>
              SMTP is configured with Mailtrap Sandbox. Emails are captured in your Mailtrap inbox and will not arrive in
              Gmail directly.
            </div>
          <?php endif; ?>

          <?php if (!empty($reset_link)): ?>
            <div class="alert alert-warning" role="alert">
              <i class="bi bi-link-45deg me-2"></i>
              Localhost fallback reset link:
              <div class="mt-2">
                <a href="<?php echo escape($reset_link); ?>" style="word-break: break-all;">
                  <?php echo escape($reset_link); ?>
                </a>
              </div>
            </div>
          <?php endif; ?>

          <div class="back-to-login">
            <a href="<?php echo ADMIN_URL; ?>/login.php">
              <i class="bi bi-arrow-left me-1"></i>Back to Login
            </a>
          </div>

        <?php else: ?>
          <!-- Show the form -->
          <div class="info-box">
            <i class="bi bi-info-circle-fill"></i>
            Enter your email address and we'll send you instructions to reset your password.
          </div>

          <form method="POST" action="">
            <?php echo csrf_field(); ?>

            <div class="mb-4">
              <label for="email" class="form-label fw-bold">Email Address</label>
              <div class="input-group">
                <span class="input-group-text bg-light">
                  <i class="bi bi-envelope-fill text-muted"></i>
                </span>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required
                  autofocus value="<?php echo escape($_POST['email'] ?? ''); ?>">
              </div>
              <small class="text-muted">We'll send a password reset link to this email.</small>
            </div>

            <button type="submit" class="btn btn-gradient w-100 mb-3">
              <i class="bi bi-send-fill me-2"></i>Send Reset Link
            </button>

            <div class="back-to-login">
              <a href="<?php echo ADMIN_URL; ?>/login.php">
                <i class="bi bi-arrow-left me-1"></i>Back to Login
              </a>
            </div>
          </form>

          <!-- <div class="text-center mt-4">
            <small class="text-muted">
              <i class="bi bi-shield-check me-1"></i>
              Secure password reset powered by <?php echo escape(PROJECT_NAME); ?>
            </small>
          </div> -->
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>