<?php
/**
 * Reset Password - Complete Password Reset with Token
 * 
 * This page validates the reset token and allows the user
 * to set a new password.
 */

require_once __DIR__ . '/init.php';

// Redirect if already logged in
if (is_logged_in()) {
  redirect(ADMIN_URL . '/dashboard.php');
}

$token = $_GET['token'] ?? '';
$error = '';
$success = false;
$validation = null;

// Validate token on page load
if (!empty($token)) {
  $validation = validate_reset_token($pdo, $token);

  if (!$validation['valid']) {
    $error = $validation['error'];
  }
} else {
  $error = 'Invalid reset link. Please request a new password reset.';
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
  $new_password = $_POST['new_password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';
  $csrf_token = $_POST['csrf_token'] ?? '';
  $submit_token = $_POST['token'] ?? '';

  // Validate CSRF token
  if (!validate_csrf_token($csrf_token)) {
    $error = 'Invalid request. Please try again.';
  } elseif (empty($submit_token)) {
    $error = 'Invalid reset token.';
  } elseif (empty($new_password) || empty($confirm_password)) {
    $error = 'Please enter and confirm your new password.';
  } elseif (strlen($new_password) < 6) {
    $error = 'Password must be at least 6 characters long.';
  } elseif ($new_password !== $confirm_password) {
    $error = 'Passwords do not match. Please try again.';
  } elseif (!preg_match('/[A-Z]/', $new_password)) {
    $error = 'Password must contain at least one uppercase letter.';
  } elseif (!preg_match('/[a-z]/', $new_password)) {
    $error = 'Password must contain at least one lowercase letter.';
  } elseif (!preg_match('/[0-9]/', $new_password)) {
    $error = 'Password must contain at least one number.';
  } else {
    // Reset the password
    $result = reset_user_password($pdo, $submit_token, $new_password);

    if ($result['success']) {
      $success = true;
      set_flash('success', 'Password reset successfully! You can now login with your new password.');

      // Redirect to login after 3 seconds
      header("refresh:3;url=" . ADMIN_URL . "/login.php");
    } else {
      $error = $result['message'];
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password - <?php echo escape(PROJECT_NAME); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <link href="<?php echo ADMIN_URL; ?>/assets/css/admin.css" rel="stylesheet">
</head>

<body class="reset-password-page">
  <div class="reset-password-container">
    <div class="card reset-password-card">
      <div class="reset-password-header">
        <i class="bi bi-shield-lock-fill reset-password-header-icon"></i>
        <h3 class="mb-0">Reset Your Password</h3>
        <p class="mb-0 mt-2" style="font-size: 14px; opacity: 0.9;">Enter your new password below</p>
      </div>

      <div class="reset-password-body">
        <?php if ($success): ?>
          <!-- Success State -->
          <div class="success-animation">
            <i class="bi bi-check-circle-fill success-icon"></i>
            <h4 class="text-success mb-3">Password Reset Successful!</h4>
            <p class="text-muted">Your password has been reset successfully.</p>

            <div class="alert alert-info mt-3" role="alert">
              <i class="bi bi-info-circle-fill me-2"></i>
              Redirecting to login page in 3 seconds...
            </div>

            <a href="<?php echo ADMIN_URL; ?>/login.php" class="btn btn-gradient w-100 mt-3">
              <i class="bi bi-box-arrow-in-right me-2"></i>Go to Login Now
            </a>
          </div>

        <?php elseif ($error): ?>
          <!-- Error State -->
          <div class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Error:</strong> <?php echo escape($error); ?>
          </div>

          <div class="text-center mt-4">
            <?php if (strpos($error, 'expired') !== false || strpos($error, 'used') !== false): ?>
              <a href="<?php echo ADMIN_URL; ?>/forgot-password.php" class="btn btn-gradient w-100 mb-2">
                <i class="bi bi-arrow-clockwise me-2"></i>Request New Reset Link
              </a>
            <?php endif; ?>

            <a href="<?php echo ADMIN_URL; ?>/login.php" class="btn btn-outline-secondary w-100">
              <i class="bi bi-arrow-left me-2"></i>Back to Login
            </a>
          </div>

        <?php elseif ($validation && $validation['valid']): ?>
          <!-- Password Reset Form -->
          <div class="alert alert-info" role="alert">
            <i class="bi bi-person-fill me-2"></i>
            Resetting password for: <strong><?php echo escape($validation['email']); ?></strong>
          </div>

          <form method="POST" action="" id="resetPasswordForm">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="token" value="<?php echo escape($token); ?>">

            <div class="mb-3">
              <label for="new_password" class="form-label fw-bold">New Password</label>
              <div class="input-group">
                <span class="input-group-text bg-light">
                  <i class="bi bi-lock-fill text-muted"></i>
                </span>
                <input type="password" class="form-control" id="new_password" name="new_password"
                  placeholder="Enter new password" minlength="6" required autofocus>
                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                  <i class="bi bi-eye" id="toggleIcon"></i>
                </button>
              </div>
              <div class="password-strength">
                <div class="password-strength-bar" id="strengthBar"></div>
              </div>
              <small class="text-muted" id="strengthText">Enter a password to see strength</small>
            </div>

            <div class="mb-3">
              <label for="confirm_password" class="form-label fw-bold">Confirm Password</label>
              <div class="input-group">
                <span class="input-group-text bg-light">
                  <i class="bi bi-lock-fill text-muted"></i>
                </span>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                  placeholder="Confirm new password" minlength="6" required>
              </div>
              <small class="text-muted" id="matchText"></small>
            </div>

            <div class="password-requirements">
              <strong><i class="bi bi-shield-check me-2"></i>Password Requirements:</strong>
              <ul>
                <li>At least 6 characters long</li>
                <li>Contains at least one uppercase letter (A-Z)</li>
                <li>Contains at least one lowercase letter (a-z)</li>
                <li>Contains at least one number (0-9)</li>
              </ul>
            </div>

            <button type="submit" class="btn btn-gradient w-100 mt-4" id="submitBtn">
              <i class="bi bi-check-circle me-2"></i>Reset Password
            </button>
          </form>

          <div class="text-center mt-3">
            <a href="<?php echo ADMIN_URL; ?>/login.php" class="text-decoration-none">
              <i class="bi bi-arrow-left me-1"></i>Back to Login
            </a>
          </div>

        <?php endif; ?>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Password strength checker
    document.getElementById('new_password')?.addEventListener('input', function (e) {
      const password = e.target.value;
      const strengthBar = document.getElementById('strengthBar');
      const strengthText = document.getElementById('strengthText');
      const submitBtn = document.getElementById('submitBtn');

      let strength = 0;
      const checks = {
        length: password.length >= 6,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password)
      };

      if (checks.length) strength++;
      if (checks.uppercase) strength++;
      if (checks.lowercase) strength++;
      if (checks.number) strength++;

      strengthBar.className = 'password-strength-bar';
      if (strength === 0) {
        strengthBar.style.width = '0';
        strengthText.textContent = 'Enter a password to see strength';
        strengthText.className = 'text-muted';
      } else if (strength <= 2) {
        strengthBar.classList.add('password-strength-weak');
        strengthText.textContent = 'Weak password';
        strengthText.className = 'text-danger';
      } else if (strength === 3) {
        strengthBar.classList.add('password-strength-medium');
        strengthText.textContent = 'Medium password';
        strengthText.className = 'text-warning';
      } else {
        strengthBar.classList.add('password-strength-strong');
        strengthText.textContent = 'Strong password!';
        strengthText.className = 'text-success';
      }

      // Check password match
      checkPasswordMatch();
    });

    // Password match checker
    document.getElementById('confirm_password')?.addEventListener('input', checkPasswordMatch);

    function checkPasswordMatch() {
      const password = document.getElementById('new_password').value;
      const confirm = document.getElementById('confirm_password').value;
      const matchText = document.getElementById('matchText');

      if (confirm.length === 0) {
        matchText.textContent = '';
        return;
      }

      if (password === confirm) {
        matchText.textContent = '✓ Passwords match';
        matchText.className = 'text-success';
      } else {
        matchText.textContent = '✗ Passwords do not match';
        matchText.className = 'text-danger';
      }
    }

    // Toggle password visibility
    document.getElementById('togglePassword')?.addEventListener('click', function () {
      const passwordInput = document.getElementById('new_password');
      const icon = document.getElementById('toggleIcon');

      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.className = 'bi bi-eye-slash';
      } else {
        passwordInput.type = 'password';
        icon.className = 'bi bi-eye';
      }
    });

    // Form validation
    document.getElementById('resetPasswordForm')?.addEventListener('submit', function (e) {
      const password = document.getElementById('new_password').value;
      const confirm = document.getElementById('confirm_password').value;

      if (password !== confirm) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
      }

      if (password.length < 6) {
        e.preventDefault();
        alert('Password must be at least 6 characters long!');
        return false;
      }

      if (!/[A-Z]/.test(password)) {
        e.preventDefault();
        alert('Password must contain at least one uppercase letter!');
        return false;
      }

      if (!/[a-z]/.test(password)) {
        e.preventDefault();
        alert('Password must contain at least one lowercase letter!');
        return false;
      }

      if (!/[0-9]/.test(password)) {
        e.preventDefault();
        alert('Password must contain at least one number!');
        return false;
      }
    });
  </script>
</body>

</html>