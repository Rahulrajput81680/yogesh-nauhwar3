<?php
/**
 * User Management – List All Admin Users
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_login();
require_module('roles');
require_permission('users_view');

$page_title = 'Manage Users';

// ── Filters ──────────────────────────────────────────────────────────────────
$search = sanitize_input($_GET['search'] ?? '');
$role_filter = sanitize_input($_GET['role'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;

$where = '1=1';
$params = [];

if ($search !== '') {
  $where .= ' AND (username LIKE ? OR email LIKE ? OR full_name LIKE ?)';
  $s = "%$search%";
  $params[] = $s;
  $params[] = $s;
  $params[] = $s;
}
if ($role_filter !== '') {
  $where .= ' AND role = ?';
  $params[] = $role_filter;
}

try {
  $total = (int) $pdo->query("SELECT COUNT(*) FROM admin_users WHERE $where"
    . ($params ? '' : ''))->fetchColumn();
  $cntStmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE $where");
  $cntStmt->execute($params);
  $total = (int) $cntStmt->fetchColumn();
  $totalPages = max(1, ceil($total / $perPage));

  $stmt = $pdo->prepare("SELECT id, username, full_name, email, role, status, last_login, created_at
                           FROM admin_users WHERE $where ORDER BY created_at DESC
                           LIMIT $perPage OFFSET $offset");
  $stmt->execute($params);
  $users = $stmt->fetchAll();
} catch (PDOException $e) {
  $users = [];
  $total = 0;
  $totalPages = 1;
  set_flash('error', 'Could not load users: ' . $e->getMessage());
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <h1><i class="bi bi-people-fill me-2"></i>Manage Users</h1>
    <p class="text-muted mb-0 small">Total: <strong><?php echo $total; ?></strong> user(s)</p>
  </div>
  <?php if (has_permission('users_create')): ?>
    <a href="<?php echo ADMIN_URL; ?>/modules/users/create.php" class="btn btn-primary">
      <i class="bi bi-person-plus-fill me-2"></i>Add User
    </a>
  <?php endif; ?>
</div>

<?php $flash = get_flash();
if ($flash): ?>
  <div class="alert alert-<?php echo escape($flash['type']); ?> alert-dismissible fade show">
    <?php echo escape($flash['message']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<!-- Filters -->
<div class="card mb-3">
  <div class="card-body py-2">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-5">
        <input type="text" name="search" class="form-control form-control-sm"
          placeholder="Search username, email, name…" value="<?php echo escape($search); ?>">
      </div>
      <div class="col-md-3">
        <select name="role" class="form-select form-select-sm">
          <option value="">All Roles</option>
          <?php foreach (get_roles() as $slug => $label): ?>
            <option value="<?php echo $slug; ?>" <?php echo $role_filter === $slug ? 'selected' : ''; ?>>
              <?php echo escape($label); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-sm btn-primary">
          <i class="bi bi-search me-1"></i>Filter
        </button>
        <a href="<?php echo ADMIN_URL; ?>/modules/users/index.php" class="btn btn-sm btn-secondary ms-1">Clear</a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <?php if (empty($users)): ?>
      <div class="text-center py-5 text-muted">
        <i class="bi bi-people fs-1 mb-3 d-block"></i>
        <p>No users found.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Username</th>
              <th>Full Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Status</th>
              <th>Last Login</th>
              <th width="120">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u): ?>
              <tr>
                <td>
                  <i class="bi bi-person-circle me-1 text-muted"></i>
                  <strong><?php echo escape($u['username']); ?></strong>
                  <?php if ((int) $u['id'] === (int) $_SESSION['admin_id']): ?>
                    <span class="badge bg-secondary ms-1" style="font-size:.65rem;">You</span>
                  <?php endif; ?>
                </td>
                <td><?php echo escape($u['full_name'] ?? '—'); ?></td>
                <td><?php echo escape($u['email']); ?></td>
                <td><?php echo role_badge($u['role']); ?></td>
                <td><?php echo get_status_badge($u['status']); ?></td>
                <td class="text-muted small">
                  <?php echo $u['last_login'] ? format_datetime($u['last_login']) : 'Never'; ?>
                </td>
                <td style="white-space:nowrap;">
                  <?php if (has_permission('users_edit')): ?>
                    <a href="<?php echo ADMIN_URL; ?>/modules/users/edit.php?id=<?php echo $u['id']; ?>"
                      class="btn btn-sm btn-outline-primary" title="Edit">
                      <i class="bi bi-pencil"></i>
                    </a>
                  <?php endif; ?>
                  <?php if (has_permission('users_delete') && (int) $u['id'] !== (int) $_SESSION['admin_id']): ?>
                    <a href="<?php echo ADMIN_URL; ?>/modules/users/delete.php?id=<?php echo $u['id']; ?>&csrf=<?php echo generate_csrf_token(); ?>"
                      class="btn btn-sm  delete-btn" title="Delete">
                      <i class="bi bi-trash"></i>
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="px-3 py-2 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="text-muted small">Showing <?php echo count($users); ?> of <?php echo $total; ?></span>
        <?php echo create_pagination($page, $totalPages, ADMIN_URL . '/modules/users/index.php'); ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>