<?php
/**
 * User Management – Create New Admin User
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_login();
require_module('roles');
require_permission('users_create');

$page_title = 'Add User';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    $errors[] = 'Invalid request. Please try again.';
  } else {
    $username = sanitize_input($_POST['username'] ?? '');
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $role = sanitize_input($_POST['role'] ?? 'editor');
    $status = sanitize_input($_POST['status'] ?? 'active');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($username))
      $errors[] = 'Username is required.';
    if (empty($email) || !is_valid_email($email))
      $errors[] = 'Valid email is required.';
    if (empty($password))
      $errors[] = 'Password is required.';
    if ($password !== $confirm)
      $errors[] = 'Passwords do not match.';
    if (strlen($password) < 6)
      $errors[] = 'Password must be at least 6 characters.';
    if (!array_key_exists($role, get_roles()))
      $errors[] = 'Invalid role selected.';

    // Prevent non-superadmin from creating superadmin accounts
    if ($role === 'superadmin' && ($_SESSION['admin_role'] ?? '') !== 'superadmin') {
      $errors[] = 'Only a Super Admin can create Super Admin accounts.';
    }

    // Uniqueness checks
    if (empty($errors)) {
      $chk = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
      $chk->execute([$username]);
      if ($chk->fetch())
        $errors[] = 'Username is already taken.';

      $chk = $pdo->prepare("SELECT id FROM admin_users WHERE email = ?");
      $chk->execute([$email]);
      if ($chk->fetch())
        $errors[] = 'Email is already in use.';
    }

    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare("
                    INSERT INTO admin_users (username, full_name, email, password, role, status, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
        $stmt->execute([
          $username,
          $full_name,
          $email,
          hash_password($password),
          $role,
          $status,
        ]);
        $new_id = (int) $pdo->lastInsertId();
        log_activity('create', 'users', $new_id, "Created user: $username ($role)");
        set_flash('success', "User" . escape($username) . " created successfully.");
        redirect(ADMIN_URL . '/modules/users/index.php');
      } catch (PDOException $e) {
        $errors[] = 'Failed to create user: ' . $e->getMessage();
      }
    }
  }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
  <h1><i class="bi bi-person-plus-fill me-2"></i>Add User</h1>
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
            <ul class="mb-0"><?php foreach ($errors as $e): ?>
                <li><?php echo escape($e); ?></li><?php endforeach; ?>
            </ul>
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
              <input type="email" name="email" class="form-control" value="<?php echo escape($_POST['email'] ?? ''); ?>"
                required maxlength="100">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Password <span class="text-danger">*</span></label>
              <input type="password" name="password" class="form-control" required minlength="6"
                placeholder="Min. 6 characters">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Confirm Password <span class="text-danger">*</span></label>
              <input type="password" name="confirm_password" class="form-control" required minlength="6">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Role <span class="text-danger">*</span></label>
              <select name="role" class="form-select" required>
                <?php foreach (get_roles() as $slug => $label):
                  // Non-superadmins can't assign superadmin role
                  if ($slug === 'superadmin' && ($_SESSION['admin_role'] ?? '') !== 'superadmin')
                    continue;
                  ?>
                  <option value="<?php echo $slug; ?>" <?php echo (($_POST['role'] ?? 'editor') === $slug) ? 'selected' : ''; ?>>
                    <?php echo escape($label); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Status</label>
              <select name="status" class="form-select">
                <option value="active" <?php echo (($_POST['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>>
                  Active</option>
                <option value="inactive" <?php echo (($_POST['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>
                  Inactive</option>
              </select>
            </div>
          </div>

          <!-- Role info box -->
          <div class="alert alert-info mt-3 mb-0 small py-2">
            <strong>Role Guide:</strong>
            <span class="badge bg-danger">Super Admin</span> – unrestricted &nbsp;
            <span class="badge bg-primary">Admin</span> – full content + view users &nbsp;
            <span class="badge bg-info">Editor</span> – create & edit content &nbsp;
            <span class="badge bg-success">Teacher</span> – read + own content only
          </div>

          <div class="mt-3">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-person-check-fill me-2"></i>Create User
            </button>
            <a href="<?php echo ADMIN_URL; ?>/modules/users/index.php" class="btn btn-secondary ms-2">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>