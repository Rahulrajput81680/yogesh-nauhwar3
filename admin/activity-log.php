<?php
/**
 * Activity Log - View All Admin Activities
 */

require_once __DIR__ . '/init.php';
require_login();

$page_title = 'Activity Log (Last 15)';

// Show only last 10 activities
$limit = 15;

// Filter
$module_filter = isset($_GET['module']) ? sanitize_input($_GET['module']) : '';

// Build query
$where = '1=1';
$params = [];

if (!empty($module_filter)) {
  $where .= ' AND module = ?';
  $params[] = $module_filter;
}

// No pagination - just show last 10

// Get activities
try {
  $stmt = $pdo->prepare("
        SELECT al.*, au.username
        FROM activity_log al
        LEFT JOIN admin_users au ON al.admin_id = au.id
        WHERE $where
        ORDER BY al.created_at DESC
        LIMIT $limit
    ");
  $stmt->execute($params);
  $activities = $stmt->fetchAll();
} catch (PDOException $e) {
  set_flash('error', 'Failed to fetch activity log.');
  $activities = [];
}

// Get modules for filter
try {
  $modulesStmt = $pdo->query("SELECT DISTINCT module FROM activity_log ORDER BY module");
  $modules = $modulesStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
  $modules = [];
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <h1><i class="bi bi-clock-history me-2"></i>Activity Log</h1>
</div>

<!-- Filter -->
<div class="card mb-4">
  <div class="card-body">
    <form method="GET" action="" class="row g-3">
      <div class="col-md-4">
        <select name="module" class="form-select">
          <option value="">All Modules</option>
          <?php foreach ($modules as $module): ?>
            <option value="<?php echo escape($module); ?>" <?php echo $module_filter === $module ? 'selected' : ''; ?>>
              <?php echo ucfirst(escape($module)); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-8">
        <button type="submit" class="btn btn-primary me-2">
          <i class="bi bi-filter me-1"></i>Filter
        </button>
        <a href="<?php echo ADMIN_URL; ?>/activity-log.php" class="btn btn-secondary">
          <i class="bi bi-x-lg me-1"></i>Clear
        </a>
      </div>
    </form>
  </div>
</div>

<!-- Activity Log -->
<div class="card">
  <div class="card-body">
    <?php if (empty($activities)): ?>
      <div class="text-center py-5">
        <i class="bi bi-inbox empty-state-icon"></i>
        <p class="text-muted mt-3">No activities logged yet.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Date & Time</th>
              <th>User</th>
              <th>Module</th>
              <th>Action</th>
              <th>Details</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($activities as $activity): ?>
              <tr>
                <td><?php echo format_datetime($activity['created_at']); ?></td>
                <td>
                  <strong><?php echo escape($activity['username'] ?? 'System'); ?></strong>
                </td>
                <td>
                  <span class="badge bg-secondary">
                    <?php echo ucfirst(escape($activity['module'])); ?>
                  </span>
                </td>
                <td>
                  <?php
                  $action_class = 'info';
                  if ($activity['action'] === 'delete')
                    $action_class = 'danger';
                  elseif ($activity['action'] === 'create')
                    $action_class = 'success';
                  elseif ($activity['action'] === 'update')
                    $action_class = 'warning';
                  ?>
                  <span class="badge bg-<?php echo $action_class; ?>">
                    <?php echo ucfirst(escape($activity['action'])); ?>
                  </span>
                </td>
                <td>
                  <?php if ($activity['details']): ?>
                    <?php echo escape($activity['details']); ?>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>


    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>