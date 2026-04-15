<?php
/**
 * Our Work Management - List Images
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_login();
require_module('our_work');
require_permission('our_work_view');

$page_title = 'Our Work Images';

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$perPage = ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;

$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$category_filter = isset($_GET['category']) ? sanitize_input($_GET['category']) : '';

$where = "g.display_section IN ('our_work', 'our-work')";
$params = [];

if (!empty($search)) {
  $where .= ' AND (g.title LIKE ? OR g.category LIKE ?)';
  $searchParam = '%' . $search . '%';
  $params[] = $searchParam;
  $params[] = $searchParam;
}

if (!empty($status_filter)) {
  $where .= ' AND g.status = ?';
  $params[] = $status_filter;
}

if (!empty($category_filter)) {
  $where .= ' AND g.category = ?';
  $params[] = $category_filter;
}

$total = count_records('gallery g', $where, $params);
$totalPages = ceil($total / $perPage);

try {
  $stmt = $pdo->prepare("SELECT g.*, au.username as uploader FROM gallery g LEFT JOIN admin_users au ON g.uploaded_by = au.id WHERE $where ORDER BY g.created_at DESC LIMIT $perPage OFFSET $offset");
  $stmt->execute($params);
  $images = $stmt->fetchAll();
} catch (PDOException $e) {
  error_log('Our Work fetch error: ' . $e->getMessage());
  set_flash('error', 'Failed to fetch our work images: ' . $e->getMessage());
  $images = [];
}

try {
  $categoriesStmt = $pdo->query("SELECT DISTINCT category FROM gallery WHERE display_section IN ('our_work', 'our-work') AND category IS NOT NULL AND category != '' ORDER BY category");
  $categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
  $categories = [];
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
  <h1><i class="bi bi-briefcase me-2"></i>Our Work Images</h1>
  <?php if (has_permission('our_work_create')): ?>
    <a href="<?php echo ADMIN_URL; ?>/modules/our-work/create.php" class="btn">
      <i class="bi bi-cloud-upload me-2"></i>Upload Image
    </a>
  <?php endif; ?>
</div>

<div class="card mb-4">
  <div class="card-body">
    <form method="GET" action="" class="row g-3">
      <div class="col-md-4">
        <input type="text" name="search" class="form-control" placeholder="Search images..." value="<?php echo escape($search); ?>">
      </div>
      <div class="col-md-3">
        <select name="status" class="form-select">
          <option value="">All Status</option>
          <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
          <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
        </select>
      </div>
      <div class="col-md-3">
        <select name="category" class="form-select">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?php echo escape($cat); ?>" <?php echo $category_filter === $cat ? 'selected' : ''; ?>><?php echo escape($cat); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn w-100"><i class="bi bi-search me-1"></i>Search</button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <?php if (empty($images)): ?>
      <div class="text-center py-5">
        <i class="bi bi-images empty-state-icon"></i>
        <p class="text-muted mt-3">No images found.</p>
        <a href="<?php echo ADMIN_URL; ?>/modules/our-work/create.php" class="btn btn-primary">
          <i class="bi bi-cloud-upload me-2"></i>Upload Your First Image
        </a>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Image</th>
              <th>Title</th>
              <th>Category</th>
              <th>Status</th>
              <th>Uploaded By</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($images as $image): ?>
              <tr>
                <td><img src="<?php echo UPLOAD_URL . '/' . escape($image['image']); ?>" alt="Thumbnail" class="thumbnail-image"></td>
                <td><strong><?php echo escape($image['title']); ?></strong><br><small class="text-muted"><?php echo escape($image['image']); ?></small></td>
                <td><?php echo $image['category'] ? '<span class="badge bg-info">' . escape($image['category']) . '</span>' : '<span class="text-muted">-</span>'; ?></td>
                <td><?php echo get_status_badge($image['status']); ?></td>
                <td><?php echo escape($image['uploader'] ?? 'N/A'); ?></td>
                <td><?php echo format_date($image['created_at']); ?></td>
                <td class="action-buttons">
                  <?php if (has_permission('our_work_edit')): ?>
                    <a href="<?php echo ADMIN_URL; ?>/modules/our-work/edit.php?id=<?php echo $image['id']; ?>" class="btn btn-sm" title="Edit"><i class="bi bi-pencil"></i></a>
                  <?php endif; ?>
                  <?php if (has_permission('our_work_delete')): ?>
                    <a href="<?php echo ADMIN_URL; ?>/modules/our-work/delete.php?id=<?php echo $image['id']; ?>&csrf=<?php echo generate_csrf_token(); ?>" class="btn btn-sm delete-btn" title="Delete"><i class="bi bi-trash"></i></a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php echo create_pagination($page, $totalPages, ADMIN_URL . '/modules/our-work/index.php'); ?>
    <?php endif; ?>
  </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
