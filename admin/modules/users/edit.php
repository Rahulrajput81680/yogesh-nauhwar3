<?php
/**
 * User Management – Edit Existing Admin User
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_login();
require_module('roles');
require_permission('users_edit');

$page_title = 'Edit User';
$errors     = [];
$user_id    = (int)($_GET['id'] ?? 0);

if (!$user_id) {
    set_flash('error', 'Invalid user ID.');
    redirect(ADMIN_URL . '/modules/users/index.php');
}

// Load user
try {
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
} catch (PDOException $e) { $user = null; }

if (!$user) {
    set_flash('error', 'User not found.');
    redirect(ADMIN_URL . '/modules/users/index.php');
}

// Non-superadmins cannot edit a superadmin account
if ($user['role'] === 'superadmin' && ($_SESSION['admin_role'] ?? '') !== 'superadmin') {
    set_flash('error', 'Only Super Admins can edit Super Admin accounts.');
    redirect(ADMIN_URL . '/modules/users/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $username  = sanitize_input($_POST['username']  ?? '');
        $full_name = sanitize_input($_POST['full_name'] ?? '');
        $email     = sanitize_input($_POST['email']     ?? '');
        $role      = sanitize_input($_POST['role']      ?? 'editor');
        $status    = sanitize_input($_POST['status']    ?? 'active');
        $new_pw    = $_POST['new_password']     ?? '';
        $confirm   = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($username)) $errors[] = 'Username is required.';
        if (empty($email) || !is_valid_email($email)) $errors[] = 'Valid email is required.';
        if (!array_key_exists($role, get_roles())) $errors[] = 'Invalid role.';

        // Prevent escalating to superadmin without being superadmin
        if ($role === 'superadmin' && ($_SESSION['admin_role'] ?? '') !== 'superadmin') {
            $errors[] = 'Only a Super Admin can assign the Super Admin role.';
        }

        // Can't change own status to inactive
        if ((int)$user_id === (int)$_SESSION['admin_id'] && $status === 'inactive') {
            $errors[] = 'You cannot deactivate your own account.';
        }

        // Uniqueness checks (excluding self)
        if (empty($errors)) {
            $chk = $pdo->prepare("SELECT id FROM admin_users WHERE username = ? AND id != ?");
            $chk->execute([$username, $user_id]);
            if ($chk->fetch()) $errors[] = 'Username is already taken.';

            $chk = $pdo->prepare("SELECT id FROM admin_users WHERE email = ? AND id != ?");
            $chk->execute([$email, $user_id]);
            if ($chk->fetch()) $errors[] = 'Email is already in use.';
        }

        // Password change
        $new_hash = null;
        if ($new_pw !== '') {
            if ($new_pw !== $confirm) $errors[] = 'Passwords do not match.';
            elseif (strlen($new_pw) < 6) $errors[] = 'Password must be at least 6 characters.';
            else $new_hash = hash_password($new_pw);
        }

        if (empty($errors)) {
            try {
                if ($new_hash) {
                    $pdo->prepare("UPDATE admin_users SET username=?,full_name=?,email=?,role=?,status=?,password=?,updated_at=NOW() WHERE id=?")
                        ->execute([$username, $full_name, $email, $role, $status, $new_hash, $user_id]);
                } else {
                    $pdo->prepare("UPDATE admin_users SET username=?,full_name=?,email=?,role=?,status=?,updated_at=NOW() WHERE id=?")
                        ->execute([$username, $full_name, $email, $role, $status, $user_id]);
                }

                // Refresh own session data if editing self
                if ((int)$user_id === (int)$_SESSION['admin_id']) {
                    $_SESSION['admin_username'] = $username;
                    $_SESSION['admin_email']    = $email;
                    $_SESSION['admin_role']     = $role;
                }

                log_activity('update', 'users', $user_id, "Updated user: $username ($role)");
                set_flash('success', 'User updated successfully.');
                redirect(ADMIN_URL . '/modules/users/index.php');
            } catch (PDOException $e) {
                $errors[] = 'Failed to update: ' . $e->getMessage();
            }
        }
    }
} else {
    // Pre-fill from DB
    $_POST = array_merge($_POST, $user);
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
  <h1><i class="bi bi-person-gear me-2"></i>Edit User</h1>
  <a href="<?php echo ADMIN_URL; ?>/modules/users/index.php" class="btn btn-secondary">
    <i class="bi bi-arrow-left me-2"></i>Back
  </a>
</div>

<div class="row">
  <div class="col-md-8 col-lg-6 mx-auto">
    <div class="card">
      <div class="card-body">
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?php echo escape($e); ?></li><?php endforeach; ?></ul>
          </div>
        <?php endif; ?>

        <form method="POST" action="">
          <?php echo csrf_field(); ?>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">Username <span class="text-danger">*</span></label>
              <input type="text" name="username" class="form-control"
                value="<?php echo escape($_POST['username'] ?? ''); ?>" required maxlength="50">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Full Name</label>
              <input type="text" name="full_name" class="form-control"
                value="<?php echo escape($_POST['full_name'] ?? ''); ?>" maxlength="100">
            </div>
            <div class="col-md-12">
              <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control"
                value="<?php echo escape($_POST['email'] ?? ''); ?>" required maxlength="100">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Role <span class="text-danger">*</span></label>
              <select name="role" class="form-select" required
                <?php echo ($user['role'] === 'superadmin' && ($_SESSION['admin_role'] ?? '') !== 'superadmin') ? 'disabled' : ''; ?>>
                <?php foreach (get_roles() as $slug => $label):
                  if ($slug === 'superadmin' && ($_SESSION['admin_role'] ?? '') !== 'superadmin') continue;
                ?>
                  <option value="<?php echo $slug; ?>"
                    <?php echo (($_POST['role'] ?? '') === $slug) ? 'selected' : ''; ?>>
                    <?php echo escape($label); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Status</label>
              <select name="status" class="form-select"
                <?php echo ((int)$user_id === (int)$_SESSION['admin_id']) ? 'disabled' : ''; ?>>
                <option value="active"   <?php echo (($_POST['status'] ?? '') === 'active')   ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo (($_POST['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
              </select>
              <?php if ((int)$user_id === (int)$_SESSION['admin_id']): ?>
                <input type="hidden" name="status" value="active">
                <small class="text-muted">Cannot deactivate your own account.</small>
              <?php endif; ?>
            </div>

            <div class="col-12"><hr class="my-1"><p class="mb-2 text-muted small">Leave blank to keep existing password.</p></div>

            <div class="col-md-6">
              <label class="form-label fw-bold">New Password</label>
              <input type="password" name="new_password" class="form-control"
                placeholder="Min. 6 characters" minlength="6">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Confirm New Password</label>
              <input type="password" name="confirm_password" class="form-control">
            </div>
          </div>

          <div class="mt-3">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-2"></i>Save Changes
            </button>
            <a href="<?php echo ADMIN_URL; ?>/modules/users/index.php" class="btn btn-secondary ms-2">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
