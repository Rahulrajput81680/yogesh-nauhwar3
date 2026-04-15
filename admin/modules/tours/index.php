<?php
/**
 * Tour Management - List All Tours
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_login();
require_module('tours');
require_permission('tours_view');

$page_title = 'Tours';

$view_trash = isset($_GET['view']) && $_GET['view'] === 'trash' && has_permission('tours_restore');

// Pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$perPage = ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;

// Filters
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$cat_filter = isset($_GET['category']) ? sanitize_input($_GET['category']) : '';

// --- Build WHERE clauses ---
// count_where: no table aliases (used with count_records)
// query_where: uses 't.' alias (used in the main SELECT with JOIN)
$count_where = $view_trash ? 'deleted_at IS NOT NULL' : 'deleted_at IS NULL';
$query_where = $view_trash ? 't.deleted_at IS NOT NULL' : 't.deleted_at IS NULL';
$params = [];

if (!empty($search)) {
  $sp = '%' . $search . '%';
  $count_where .= ' AND (title LIKE ? OR destination LIKE ? OR category LIKE ?)';
  $query_where .= ' AND (t.title LIKE ? OR t.destination LIKE ? OR t.category LIKE ?)';
  $params[] = $sp;
  $params[] = $sp;
  $params[] = $sp;
}

if (!empty($status_filter)) {
  $count_where .= ' AND status = ?';
  $query_where .= ' AND t.status = ?';
  $params[] = $status_filter;
}

if (!empty($cat_filter)) {
  $count_where .= ' AND category = ?';
  $query_where .= ' AND t.category = ?';
  $params[] = $cat_filter;
}

$total = count_records('tours', $count_where, $params);
$totalPages = ceil($total / $perPage);

// Fetch tours
try {
  $stmt = $pdo->prepare("
        SELECT t.*, au.username AS author
        FROM tours t
        LEFT JOIN admin_users au ON t.author_id = au.id
        WHERE {$query_where}
        ORDER BY t.created_at DESC
        LIMIT {$perPage} OFFSET {$offset}
    ");
  $stmt->execute($params);
  $tours = $stmt->fetchAll();
} catch (PDOException $e) {
  error_log('Tour fetch error: ' . $e->getMessage());
  set_flash('error', 'Failed to fetch tours.');
  $tours = [];
}

// Distinct categories for the filter dropdown
try {
  $categories = $pdo->query(
    "SELECT DISTINCT category FROM tours WHERE category IS NOT NULL AND deleted_at IS NULL ORDER BY category"
  )->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
  $categories = [];
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
  <h1>
    <i class="bi bi-compass me-2"></i>
    <?php echo $view_trash ? 'Trash — Tours' : 'Tours'; ?>
  </h1>
  <div class="d-flex gap-2 flex-wrap">
    <?php if (has_permission('tours_restore')): ?>
      <?php if ($view_trash): ?>
        <a href="<?php echo ADMIN_URL; ?>/modules/tours/index.php" class="btn btn-secondary">
          <i class="bi bi-arrow-left me-2"></i>Back to Tours
        </a>
      <?php else: ?>
        <a href="?view=trash" class="btn">
          <i class="bi bi-trash me-2"></i>View Trash
        </a>
      <?php endif; ?>
    <?php endif; ?>
    <?php if (!$view_trash && has_permission('tours_create')): ?>
      <a href="<?php echo ADMIN_URL; ?>/modules/tours/create.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>New Tour
      </a>
    <?php endif; ?>
  </div>
</div>

<!-- Search & Filter -->
<div class="card mb-4">
  <div class="card-body">
    <form method="GET" class="row g-3">
      <?php if ($view_trash): ?>
        <input type="hidden" name="view" value="trash">
      <?php endif; ?>
      <div class="col-md-4">
        <input type="text" name="search" class="form-control" placeholder="Search title, destination, category..."
          value="<?php echo escape($search); ?>">
      </div>
      <div class="col-md-2">
        <select name="status" class="form-select">
          <option value="">All Status</option>
          <option value="published" <?php echo $status_filter === 'published' ? 'selected' : ''; ?>>Published</option>
          <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>Draft</option>
        </select>
      </div>
      <div class="col-md-3">
        <select name="category" class="form-select">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?php echo escape($cat); ?>" <?php echo $cat_filter === $cat ? 'selected' : ''; ?>>
              <?php echo escape($cat); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-fill">
          <i class="bi bi-search me-1"></i>Filter
        </button>
        <a href="<?php echo ADMIN_URL; ?>/modules/tours/index.php" class="btn btn-secondary">Reset</a>
      </div>
    </form>
  </div>
</div>

<!-- Tours Table -->
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Tour</th>
            <th>Destination</th>
            <th>Duration</th>
            <th>Price</th>
            <th>Rating</th>
            <th>Status</th>
            <th>Author</th>
            <th>Date</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($tours)): ?>
            <tr>
              <td colspan="10" class="text-center py-5 text-muted">
                <?php if ($view_trash): ?>
                  <i class="bi bi-trash fs-4 d-block mb-2"></i>No tours in trash.
                <?php else: ?>
                  <i class="bi bi-compass fs-4 d-block mb-2"></i>
                  No tours found.
                  <?php if (has_permission('tours_create')): ?>
                    <a href="create.php">Create your first tour</a>.
                  <?php endif; ?>
                <?php endif; ?>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($tours as $tour): ?>
              <tr>
                <td class="text-muted small"><?php echo $tour['id']; ?></td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <?php if (!empty($tour['featured_image'])): ?>
                      <img src="<?php echo UPLOAD_URL . '/' . escape($tour['featured_image']); ?>" alt=""
                        class="rounded" style="width:52px;height:38px;object-fit:cover;flex-shrink:0">
                    <?php else: ?>
                      <div class="rounded bg-light border d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width:52px;height:38px">
                        <i class="bi bi-image text-muted"></i>
                      </div>
                    <?php endif; ?>
                    <div>
                      <strong><?php echo escape($tour['title']); ?></strong>
                      <?php if (!empty($tour['category'])): ?>
                        <br><span class="badge bg-light text-secondary border small">
                          <?php echo escape($tour['category']); ?>
                        </span>
                      <?php endif; ?>
                    </div>
                  </div>
                </td>
                <td><?php echo escape($tour['destination'] ?? '—'); ?></td>
                <td class="text-nowrap">
                  <?php if ($tour['duration_days']): ?>
                    <?php echo (int) $tour['duration_days']; ?>D&nbsp;/&nbsp;<?php echo (int) $tour['duration_nights']; ?>N
                  <?php else: ?>—<?php endif; ?>
                </td>
                <td class="text-nowrap">
                  <?php echo $tour['price'] !== null ? '₹' . number_format((float) $tour['price'], 2) : '—'; ?>
                </td>
                <td>
                  <?php if ($tour['rating'] !== null): ?>
                    <span class="text-warning">&#9733;</span>
                    <?php echo number_format((float) $tour['rating'], 1); ?>
                  <?php else: ?>—<?php endif; ?>
                </td>
                <td><?php echo get_status_badge($tour['status']); ?></td>
                <td><?php echo escape($tour['author'] ?? '—'); ?></td>
                <td><small class="text-muted"><?php echo format_date($tour['created_at']); ?></small></td>
                <td class="text-end text-nowrap">
                  <?php if ($view_trash): ?>
                    <?php if (has_permission('tours_restore')): ?>
                      <a href="delete.php?id=<?php echo $tour['id']; ?>&action=restore&csrf=<?php echo generate_csrf_token(); ?>"
                        class="btn btn-sm btn-success" title="Restore">
                        <i class="bi bi-arrow-counterclockwise"></i>
                      </a>
                      <a href="delete.php?id=<?php echo $tour['id']; ?>&action=purge&csrf=<?php echo generate_csrf_token(); ?>"
                        class="btn btn-sm btn-danger" title="Delete Permanently"
                        onclick="return confirm('Permanently delete this tour and ALL its data? This cannot be undone.')">
                        <i class="bi bi-trash3"></i>
                      </a>
                    <?php endif; ?>
                  <?php else: ?>
                    <?php if (has_permission('tours_edit')): ?>
                      <a href="edit.php?id=<?php echo $tour['id']; ?>" class="btn btn-sm btn-primary" title="Edit tour">
                        <i class="bi bi-pencil"></i>
                      </a>
                    <?php endif; ?>
                    <?php if (has_permission('tours_delete')): ?>
                      <a href="delete.php?id=<?php echo $tour['id']; ?>&action=delete&csrf=<?php echo generate_csrf_token(); ?>"
                        class="btn btn-sm" title="Delete" onclick="return confirm('Delete this tour permanently? This action cannot be undone.')">
                        <i class="bi bi-trash"></i>
                      </a>
                    <?php endif; ?>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
  <div class="mt-4">
    <?php
    $pUrl = '?';
    if (!empty($search))
      $pUrl .= 'search=' . urlencode($search) . '&';
    if (!empty($status_filter))
      $pUrl .= 'status=' . urlencode($status_filter) . '&';
    if (!empty($cat_filter))
      $pUrl .= 'category=' . urlencode($cat_filter) . '&';
    if ($view_trash)
      $pUrl .= 'view=trash&';
    echo create_pagination($page, $totalPages, rtrim($pUrl, '&'));
    ?>
  </div>
<?php endif; ?>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>