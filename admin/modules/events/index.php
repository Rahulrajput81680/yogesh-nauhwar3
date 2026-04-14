<?php
/**
 * Events Management - List
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_login();
require_module('events');
require_permission('events_view');

$page_title = 'Events';

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;

$search = sanitize_input($_GET['search'] ?? '');
$status_filter = sanitize_input($_GET['status'] ?? '');
$type_filter = sanitize_input($_GET['event_type'] ?? '');

$where = '1=1';
$params = [];

if ($search !== '') {
  $where .= ' AND (title LIKE ? OR category LIKE ? OR description LIKE ?)';
  $searchParam = '%' . $search . '%';
  $params[] = $searchParam;
  $params[] = $searchParam;
  $params[] = $searchParam;
}

if (in_array($status_filter, ['active', 'inactive'], true)) {
  $where .= ' AND status = ?';
  $params[] = $status_filter;
}

if (in_array($type_filter, ['upcoming', 'past'], true)) {
  $where .= ' AND event_type = ?';
  $params[] = $type_filter;
}

$total = count_records('events', $where, $params);
$totalPages = max(1, (int) ceil($total / $perPage));

try {
  $stmt = $pdo->prepare("\n    SELECT e.*, au.username AS creator\n    FROM events e\n    LEFT JOIN admin_users au ON e.created_by = au.id\n    WHERE {$where}\n    ORDER BY e.event_date DESC, e.id DESC\n    LIMIT {$perPage} OFFSET {$offset}\n  ");
  $stmt->execute($params);
  $events = $stmt->fetchAll();
} catch (PDOException $e) {
  $events = [];
  set_flash('error', 'Failed to load events: ' . $e->getMessage());
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
  <h1><i class="bi bi-calendar-event me-2"></i>Events</h1>
  <?php if (has_permission('events_create')): ?>
    <a href="<?php echo ADMIN_URL; ?>/modules/events/create.php" class="btn">
      <i class="bi bi-plus-lg me-2"></i>Add Event
    </a>
  <?php endif; ?>
</div>

<div class="card mb-4">
  <div class="card-body">
    <form method="GET" class="row g-3">
      <div class="col-md-4">
        <input type="text" name="search" class="form-control" placeholder="Search events..."
          value="<?php echo escape($search); ?>">
      </div>
      <div class="col-md-3">
        <select name="event_type" class="form-select">
          <option value="">All Types</option>
          <option value="upcoming" <?php echo $type_filter === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
          <option value="past" <?php echo $type_filter === 'past' ? 'selected' : ''; ?>>Past</option>
        </select>
      </div>
      <div class="col-md-3">
        <select name="status" class="form-select">
          <option value="">All Status</option>
          <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
          <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn w-100">Filter</button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <?php if (empty($events)): ?>
      <div class="text-center py-5">
        <i class="bi bi-calendar-x empty-state-icon"></i>
        <p class="text-muted mt-3">No events found.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Image</th>
              <th>Title</th>
              <th>Category</th>
              <th>Type</th>
              <th>Date</th>
              <th>Status</th>
              <th>Created By</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($events as $event): ?>
              <tr>
                <td>
                  <?php if (!empty($event['image'])): ?>
                    <img src="<?php echo UPLOAD_URL . '/' . escape($event['image']); ?>" alt="event image"
                      class="thumbnail-image">
                  <?php else: ?>
                    <div class="thumbnail-placeholder"><i class="bi bi-image"></i></div>
                  <?php endif; ?>
                </td>
                <td>
                  <strong><?php echo escape($event['title']); ?></strong><br>
                  <small class="text-muted"><?php echo escape(truncate(strip_tags($event['description']), 80)); ?></small>
                </td>
                <td><?php echo escape($event['category'] ?: '-'); ?></td>
                <td>
                  <span class="badge bg-<?php echo $event['event_type'] === 'upcoming' ? 'primary' : 'secondary'; ?>">
                    <?php echo ucfirst($event['event_type']); ?>
                  </span>
                </td>
                <td><?php echo $event['event_date'] ? format_date($event['event_date']) : '-'; ?></td>
                <td><?php echo get_status_badge($event['status']); ?></td>
                <td><?php echo escape($event['creator'] ?? 'N/A'); ?></td>
                <td class="action-buttons">
                  <?php if (has_permission('events_edit')): ?>
                    <a href="<?php echo ADMIN_URL; ?>/modules/events/edit.php?id=<?php echo (int) $event['id']; ?>"
                      class="btn btn-sm" title="Edit">
                      <i class="bi bi-pencil"></i>
                    </a>
                  <?php endif; ?>
                  <?php if (has_permission('events_delete')): ?>
                    <a href="<?php echo ADMIN_URL; ?>/modules/events/delete.php?id=<?php echo (int) $event['id']; ?>&csrf=<?php echo generate_csrf_token(); ?>"
                      class="btn btn-sm delete-btn" title="Delete">
                      <i class="bi bi-trash"></i>
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php echo create_pagination($page, $totalPages, ADMIN_URL . '/modules/events/index.php'); ?>
    <?php endif; ?>
  </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php';
