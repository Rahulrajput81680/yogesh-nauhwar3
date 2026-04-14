<?php
/**
 * Contact Messages - List All Submissions
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_login();
require_module('contact');
require_permission('contact_view');

$page_title = 'Contact Messages';

// Handle bulk delete via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete'])) {
  if (!has_permission('contact_delete')) {
    set_flash('error', 'You do not have permission to delete messages.');
  } elseif (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('error', 'Invalid request.');
  } else {
    $ids = array_filter(array_map('intval', $_POST['ids'] ?? []));
    if ($ids) {
      $placeholders = implode(',', array_fill(0, count($ids), '?'));
      // Fetch before deleting for count
      $pdo->prepare("DELETE FROM contact_messages WHERE id IN ($placeholders)")->execute($ids);
      set_flash('success', count($ids) . ' message(s) deleted.');
    }
  }
  redirect(ADMIN_URL . '/modules/contact/index.php');
}

// Pagination + search
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;

$search = sanitize_input($_GET['search'] ?? '');
$filter = sanitize_input($_GET['status'] ?? '');

$where = '1=1';
$params = [];

if ($search !== '') {
  $where .= ' AND (name LIKE ? OR email LIKE ? OR subject LIKE ?)';
  $params[] = "%$search%";
  $params[] = "%$search%";
  $params[] = "%$search%";
}
if (in_array($filter, ['unread', 'read'])) {
  $where .= ' AND status = ?';
  $params[] = $filter;
}

try {
  $countStmt = $pdo->prepare("SELECT COUNT(*) FROM contact_messages WHERE $where");
  $countStmt->execute($params);
  $total = (int) $countStmt->fetchColumn();
  $totalPages = max(1, ceil($total / $perPage));

  $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
  $stmt->execute($params);
  $messages = $stmt->fetchAll();

  // Unread badge count
  $unreadCount = (int) $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status='unread'")->fetchColumn();
} catch (PDOException $e) {
  $messages = [];
  $total = 0;
  $totalPages = 1;
  $unreadCount = 0;
  set_flash('error', 'Could not load messages: ' . $e->getMessage());
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <h1>
    <i class="bi bi-envelope me-2"></i>Contact Messages
    <?php if ($unreadCount > 0): ?>
      <span class="badge bg-danger ms-2"><?php echo $unreadCount; ?> Unread</span>
    <?php endif; ?>
  </h1>
</div>

<?php $flash = get_flash(); ?>
<?php if ($flash): ?>
  <div class="alert alert-<?php echo escape($flash['type']); ?> alert-dismissible fade show">
    <?php echo escape($flash['message']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<!-- Filters -->
<div class="card mb-3">
  <div class="card-body py-2">
    <form method="GET" action="" class="row g-2 align-items-end">
      <div class="col-md-5">
        <input type="text" class="form-control form-control-sm" name="search" placeholder="Search name, email, subject…"
          value="<?php echo escape($search); ?>">
      </div>
      <div class="col-md-3">
        <select name="status" class="form-select form-select-sm">
          <option value="">All Statuses</option>
          <option value="unread" <?php echo $filter === 'unread' ? 'selected' : ''; ?>>Unread</option>
          <option value="read" <?php echo $filter === 'read' ? 'selected' : ''; ?>>Read</option>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-sm">
          <i class="bi bi-search me-1"></i>Filter
        </button>
        <a href="<?php echo ADMIN_URL; ?>/modules/contact/index.php" class="btn btn-sm ms-1">Clear</a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <?php if (empty($messages)): ?>
      <div class="text-center py-5 text-muted">
        <i class="bi bi-envelope-open fs-1 mb-3 d-block"></i>
        <p>No messages found.</p>
      </div>
    <?php else: ?>
      <form method="POST" action="" id="bulkForm">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="bulk_delete" value="1">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th width="36"><input type="checkbox" class="form-check-input" id="selectAll"></th>
                <th>Name</th>
                <th>Email</th>
                <th>Subject</th>
                <th>Location</th>
                <th>Date</th>
                <th>Status</th>
                <th width="120">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($messages as $msg): ?>
                <tr class="<?php echo $msg['status'] === 'unread' ? 'fw-semibold' : ''; ?>">
                  <td>
                    <input type="checkbox" class="form-check-input item-checkbox" name="ids[]"
                      value="<?php echo $msg['id']; ?>">
                  </td>
                  <td><?php echo escape($msg['name']); ?></td>
                  <td>
                    <a href="mailto:<?php echo escape($msg['email']); ?>">
                      <?php echo escape($msg['email']); ?>
                    </a>
                  </td>
                  <td><?php echo escape(truncate($msg['subject'] ?? 'No subject', 50)); ?></td>
                  <td><?php echo escape(truncate($msg['location'] ?? '-', 30)); ?></td>
                  <td><?php echo format_datetime($msg['created_at']); ?></td>
                  <td>
                    <?php if ($msg['status'] === 'unread'): ?>
                      <span class="badge bg-danger">Unread</span>
                    <?php else: ?>
                      <span class="badge bg-secondary">Read</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="<?php echo ADMIN_URL; ?>/modules/contact/view.php?id=<?php echo $msg['id']; ?>"
                      class="btn btn-sm" title="View">
                      <i class="bi bi-eye"></i>
                    </a>
                    <?php if (has_permission('contact_delete')): ?>
                      <a href="<?php echo ADMIN_URL; ?>/modules/contact/delete.php?id=<?php echo $msg['id']; ?>&csrf=<?php echo generate_csrf_token(); ?>"
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

        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top flex-wrap gap-2">
          <div>
            <?php if (has_permission('contact_delete')): ?>
              <button type="submit" class="btn btn-sm btn-danger" id="bulkDeleteBtn" disabled
                onclick="return confirm('Delete selected messages?');">
                <i class="bi bi-trash me-1"></i>Delete Selected
              </button>
            <?php endif; ?>
            <span class="text-muted small ms-2">Total: <?php echo $total; ?> message(s)</span>
          </div>
          <?php echo create_pagination($page, $totalPages, ADMIN_URL . '/modules/contact/index.php'); ?>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>

<script>
  document.getElementById('selectAll')?.addEventListener('change', function () {
    document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = this.checked);
    updateBulkDeleteBtn();
  });
  document.querySelectorAll('.item-checkbox').forEach(cb => cb.addEventListener('change', updateBulkDeleteBtn));
  function updateBulkDeleteBtn() {
    const any = document.querySelectorAll('.item-checkbox:checked').length > 0;
    document.getElementById('bulkDeleteBtn').disabled = !any;
  }
</script>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>