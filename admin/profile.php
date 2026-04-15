<?php
/**
 * Admin Profile Management
 * Change admin name, email, password, and other settings
 */

require_once __DIR__ . '/init.php';
require_login();

$page_title = 'My Profile';
$errors = [];
$success = '';

// Get current admin data
$admin_id = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrf_token = $_POST['csrf_token'] ?? '';

  // Validate CSRF token
  if (!validate_csrf_token($csrf_token)) {
    $errors[] = 'Invalid request. Please try again.';
  } else {
    // Get form data
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $username = sanitize_input($_POST['username'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($full_name)) {
      $errors[] = 'Full name is required.';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = 'Valid email is required.';
    }

    if (empty($username)) {
      $errors[] = 'Username is required.';
    }

    // Check if username is taken by another user
    if ($username !== $admin['username']) {
      $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ? AND id != ?");
      $stmt->execute([$username, $admin_id]);
      if ($stmt->fetch()) {
        $errors[] = 'Username already taken.';
      }
    }

    // Check if email is taken by another user
    if ($email !== $admin['email']) {
      $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = ? AND id != ?");
      $stmt->execute([$email, $admin_id]);
      if ($stmt->fetch()) {
        $errors[] = 'Email already in use.';
      }
    }

    // If changing password
    if (!empty($new_password) || !empty($confirm_password)) {
      if (empty($current_password)) {
        $errors[] = 'Current password is required to change password.';
      } elseif (!verify_password($current_password, $admin['password'])) {
        $errors[] = 'Current password is incorrect.';
      } elseif ($new_password !== $confirm_password) {
        $errors[] = 'New passwords do not match.';
      } elseif (strlen($new_password) < 6) {
        $errors[] = 'New password must be at least 6 characters.';
      }
    }

    // Update if no errors
    if (empty($errors)) {
      try {
        if (!empty($new_password)) {
          // Update with new password
          $hashed_password = hash_password($new_password);
          $stmt = $pdo->prepare("
                        UPDATE admin_users 
                        SET full_name = ?, email = ?, username = ?, password = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
          $stmt->execute([$full_name, $email, $username, $hashed_password, $admin_id]);
        } else {
          // Update without password change
          $stmt = $pdo->prepare("
                        UPDATE admin_users 
                        SET full_name = ?, email = ?, username = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
          $stmt->execute([$full_name, $email, $username, $admin_id]);
        }

        // Update session with new username
        $_SESSION['admin_username'] = $username;

        // Log activity
        log_activity('update', 'profile', $admin_id, "Updated profile information");

        $success = 'Profile updated successfully!';

        // Refresh admin data
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch();
      } catch (PDOException $e) {
        $errors[] = 'Failed to update profile. ' . $e->getMessage();
      }
    }
  }
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <h1><i class="bi bi-person-circle me-2"></i>My Profile</h1>
</div>

<div class="row">
  <div class="col-md-8 mx-auto">
    <div class="card">
      <div class="card-header">
        <i class="bi bi-pencil-square me-2"></i>Edit Profile Information
      </div>
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

        <?php if ($success): ?>
          <div class="alert alert-success">
            <strong>Success!</strong> <?php echo escape($success); ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="">
          <?php echo csrf_field(); ?>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="full_name" class="form-label">Full Name *</label>
                <input type="text" class="form-control" id="full_name" name="full_name"
                  value="<?php echo escape($admin['full_name']); ?>" required>
                <small class="text-muted">This is your display name</small>
              </div>
            </div>

            <div class="col-md-6">
              <div class="mb-3">
                <label for="username" class="form-label">Username *</label>
                <input type="text" class="form-control" id="username" name="username"
                  value="<?php echo escape($admin['username']); ?>" required>
                <small class="text-muted">Used for login</small>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">Email Address *</label>
            <input type="email" class="form-control" id="email" name="email"
              value="<?php echo escape($admin['email']); ?>" required>
          </div>

          <hr class="my-4">
          <h5 class="mb-3"><i class="bi bi-key me-2"></i>Change Password</h5>
          <p class="text-muted small">Leave blank if you don't want to change your password</p>

          <div class="mb-3">
            <label for="current_password" class="form-label">Current Password</label>
            <input type="password" class="form-control" id="current_password" name="current_password"
              autocomplete="current-password">
            <small class="text-muted">Required if changing password</small>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="new_password" name="new_password"
                  autocomplete="new-password">
                <small class="text-muted">Min 6 characters</small>
              </div>
            </div>

            <div class="col-md-6">
              <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                  autocomplete="new-password">
              </div>
            </div>
          </div>

          <hr class="my-4">

          <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
              <i class="bi bi-info-circle me-1"></i>
              Role: <strong><?php echo escape($admin['role']); ?></strong> •
              Status: <strong><?php echo escape($admin['status']); ?></strong>
            </div>
            <div>
              <a href="<?php echo ADMIN_URL; ?>/dashboard.php" class="btn me-2">
                <i class="bi bi-x-lg me-1"></i>Cancel
              </a>
              <button type="submit" class="btn">
                <i class="bi bi-check-lg me-2"></i>Save Changes
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Account Information -->
    <div class="card mt-3">
      <div class="card-header">
        <i class="bi bi-info-circle me-2"></i>Account Information
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <p class="mb-2"><strong>Account Created:</strong></p>
            <p class="text-muted"><?php echo format_datetime($admin['created_at']); ?></p>
          </div>
          <div class="col-md-6">
            <p class="mb-2"><strong>Last Login:</strong></p>
            <p class="text-muted">
              <?php echo $admin['last_login'] ? format_datetime($admin['last_login']) : 'Never'; ?>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
