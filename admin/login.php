<?php
/**
 * Admin Login Page
 */

require_once __DIR__ . '/init.php';

// Redirect if already logged in
if (is_logged_in()) {
  redirect(ADMIN_URL . '/dashboard.php');
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = sanitize_input($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $csrf_token = $_POST['csrf_token'] ?? '';

  // Validate CSRF token
  if (!validate_csrf_token($csrf_token)) {
    $error = 'Invalid request. Please try again.';
  } elseif (empty($username) || empty($password)) {
    $error = 'Please enter both username and password.';
  } else {
    try {
      // Fetch user — use deleted_at guard when the column exists (post-migration)
      try {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ? AND status = 'active' AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([$username]);
      } catch (PDOException $colErr) {
        // deleted_at column doesn't exist yet — fall back to simple query
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$username]);
      }
      $user = $stmt->fetch();

      if ($user && verify_password($password, $user['password'])) {
        // Restrict login to superadmin only when role-based access is active.
        if (!is_module_enabled('users') && is_module_enabled('roles') && $user['role'] !== 'superadmin') {
          $error = 'Login is currently restricted. Only Super Administrators are allowed to access the panel.';
        } else {
          // Login successful
          $_SESSION['admin_logged_in'] = true;
          $_SESSION['admin_id'] = $user['id'];
          $_SESSION['admin_username'] = $user['username'];
          $_SESSION['admin_email'] = $user['email'];
          $_SESSION['admin_role'] = $user['role'];
          $_SESSION['last_activity'] = time();

          // Update last login
          $updateStmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
          $updateStmt->execute([$user['id']]);

          // Log activity
          log_activity('login', 'authentication', $user['id'], 'User logged in');

          redirect(ADMIN_URL . '/dashboard.php');
        } // end users-module check
      } else {
        $error = 'Invalid username or password.';
      }
    } catch (PDOException $e) {
      $error = 'An error occurred. Please try again.';
      error_log('Login error: ' . $e->getMessage());
    }
  }
}

// Check for timeout message
$timeout_message = isset($_GET['timeout']) ? 'Your session has expired. Please login again.' : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - <?php echo escape(PROJECT_NAME); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <link href="<?php echo ADMIN_URL; ?>/assets/css/admin.css" rel="stylesheet">
</head>

<body class="login-page">
  <div class="login-container">
    <div class="card login-card">
      <div class="login-header">
        <!-- <i class="bi bi-shield-lock-fill login-header-icon"></i> -->
        <h3 class="mt-3 mb-0"><?php echo escape(PROJECT_NAME); ?></h3>
        <p class="mb-0 mt-2">Admin Panel Login</p>
      </div>
      <div class="login-body">
        <?php if ($timeout_message): ?>
          <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo escape($timeout_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <?php if ($error): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i><?php echo escape($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <form method="POST" action="">
          <?php echo csrf_field(); ?>

          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
              <input type="text" class="form-control" id="username" name="username"
                value="<?php echo escape($_POST['username'] ?? ''); ?>" required autofocus>
            </div>
          </div>

          <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
              <input type="password" class="form-control" id="password" name="password" required>
              <button type="button" class="btn btn-outline-secondary" id="toggle-password" aria-label="Show password"
                aria-pressed="false">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-login w-100">
            <i class="bi bi-box-arrow-in-right me-2"></i>Login
          </button>
        </form>

        <div class="text-center mt-3">
          <a href="<?php echo ADMIN_URL; ?>/forgot-password.php" class="forgot-password-link">
            <i class="bi bi-key me-1"></i>Forgot Password?
          </a>
        </div>

        <!-- <div class="text-center mt-4 text-muted small">
          <p class="mb-0">Default credentials: admin / admin123</p>
        </div> -->
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function () {
      var passwordInput = document.getElementById('password');
      var toggleButton = document.getElementById('toggle-password');

      if (!passwordInput || !toggleButton) {
        return;
      }

      toggleButton.addEventListener('click', function () {
        var isVisible = passwordInput.type === 'text';
        passwordInput.type = isVisible ? 'password' : 'text';
        toggleButton.setAttribute('aria-pressed', isVisible ? 'false' : 'true');
        toggleButton.setAttribute('aria-label', isVisible ? 'Show password' : 'Hide password');
        toggleButton.innerHTML = isVisible
          ? '<i class="bi bi-eye"></i>'
          : '<i class="bi bi-eye-slash"></i>';
      });
    })();
  </script>
</body>

</html>