<?php
/**
 * Blog Management - List All Blog Posts
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_login();
require_module('blog');
require_permission('blog_view');

$page_title = 'Blog Posts';

// Trash view toggle (superadmin / users with blog_restore only)
$view_trash = isset($_GET['view']) && $_GET['view'] === 'trash' && has_permission('blog_restore');

// Pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$perPage = ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;

// Search and filter
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';

// Build query
$where = $view_trash ? 'b.deleted_at IS NOT NULL' : 'b.deleted_at IS NULL';
$params = [];

if (!empty($search)) {
  $where .= ' AND (title LIKE ? OR content LIKE ? OR category LIKE ?)';
  $searchParam = '%' . $search . '%';
  $params[] = $searchParam;
  $params[] = $searchParam;
  $params[] = $searchParam;
}

if (!empty($status_filter)) {
  $where .= ' AND status = ?';
  $params[] = $status_filter;
}

// Get total count
$total = count_records('blogs', $where, $params);
$totalPages = ceil($total / $perPage);

// Get blogs
try {
  $stmt = $pdo->prepare("
        SELECT b.*, au.username as author
        FROM blogs b
        LEFT JOIN admin_users au ON b.author_id = au.id
        WHERE $where
        ORDER BY b.created_at DESC
        LIMIT $perPage OFFSET $offset
    ");
  $stmt->execute($params);
  $blogs = $stmt->fetchAll();
} catch (PDOException $e) {
  error_log('Blog fetch error: ' . $e->getMessage());
  set_flash('error', 'Failed to fetch blog posts: ' . $e->getMessage());
  $blogs = [];
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
  <h1>
    <i class="bi bi-file-text me-2"></i>
    <?php echo $view_trash ? 'Trash — Blog Posts' : 'Blog Posts'; ?>
  </h1>
  <div class="d-flex gap-2 flex-wrap">
    <?php if (has_permission('blog_restore')): ?>
      <?php if ($view_trash): ?>
        <a href="<?php echo ADMIN_URL; ?>/modules/blog/index.php" class="btn btn-secondary">
          <i class="bi bi-arrow-left me-2"></i>Back to Posts
        </a>
      <?php else: ?>
        <a href="<?php echo ADMIN_URL; ?>/modules/blog/index.php?view=trash" class="btn ">
          <i class="bi bi-trash me-2"></i>View Trash
        </a>
      <?php endif; ?>
    <?php endif; ?>
    <?php if (!$view_trash && has_permission('blog_create')): ?>
      <a href="<?php echo ADMIN_URL; ?>/modules/blog/create.php" class="btn">
        <i class="bi bi-plus-lg me-2"></i>New Blog Post
      </a>
    <?php endif; ?>
  </div>
</div>

<!-- Search and Filter -->
<div class="card mb-4">
  <div class="card-body">
    <form method="GET" action="" class="row g-3">
      <div class="col-md-4">
        <input type="text" name="search" class="form-control" placeholder="Search blogs..."
          value="<?php echo escape($search); ?>">
      </div>
      <div class="col-md-3">
        <select name="status" class="form-select">
          <option value="">All Status</option>
          <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>Draft</option>
          <option value="published" <?php echo $status_filter === 'published' ? 'selected' : ''; ?>>Published</option>
        </select>
      </div>
      <div class="col-md-5">
        <button type="submit" class="btn me-2">
          <i class="bi bi-search me-1"></i>Search
        </button>
        <a href="<?php echo ADMIN_URL; ?>/modules/blog/index.php" class="btn btn-secondary">
          <i class="bi bi-x-lg me-1"></i>Clear
        </a>
      </div>
    </form>
  </div>
</div>

<!-- Blog List -->
<div class="card">
  <div class="card-body">
    <?php if (empty($blogs)): ?>
      <div class="text-center py-5">
        <i class="bi bi-inbox empty-state-icon"></i>
        <p class="text-muted mt-3">No blog posts found.</p>
        <a href="<?php echo ADMIN_URL; ?>/modules/blog/create.php" class="btn">
          <i class="bi bi-plus-lg me-2"></i>Create Your First Blog Post
        </a>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Thumbnail</th>
              <th>Title</th>
              <th>Category</th>
              <th>Status</th>
              <th>Author</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($blogs as $blog): ?>
              <tr>
                <td>
                  <?php if ($blog['thumbnail']): ?>
                    <img src="<?php echo UPLOAD_URL . '/' . escape($blog['thumbnail']); ?>" alt="Thumbnail"
                      class="thumbnail-image">
                  <?php else: ?>
                    <div class="thumbnail-placeholder">
                      <i class="bi bi-image text-muted"></i>
                    </div>
                  <?php endif; ?>
                </td>
                <td>
                  <strong><?php echo escape($blog['title']); ?></strong><br>
                  <small class="text-muted"><?php echo escape($blog['slug']); ?></small>
                </td>
                <td>
                  <?php if ($blog['category']): ?>
                    <span class="badge bg-info"><?php echo escape($blog['category']); ?></span>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td><?php echo get_status_badge($blog['status']); ?></td>
                <td><?php echo escape($blog['author'] ?? 'N/A'); ?></td>
                <td><?php echo format_date($blog['created_at']); ?></td>
                <td class="action-buttons">
                  <?php if (!$view_trash): ?>
                    <?php if (has_permission('blog_edit')): ?>
                      <a href="<?php echo ADMIN_URL; ?>/modules/blog/edit.php?id=<?php echo $blog['id']; ?>"
                        class="btn btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                      </a>
                    <?php endif; ?>
                    <?php if (has_permission('blog_delete')): ?>
                      <a href="<?php echo ADMIN_URL; ?>/modules/blog/delete.php?id=<?php echo $blog['id']; ?>&csrf=<?php echo generate_csrf_token(); ?>"
                        class="btn btn-sm  delete-btn" title="Delete">
                        <i class="bi bi-trash"></i>
                      </a>
                    <?php endif; ?>
                  <?php else: ?>
                    <?php if (has_permission('blog_restore')): ?>
                      <a href="<?php echo ADMIN_URL; ?>/modules/blog/delete.php?id=<?php echo $blog['id']; ?>&action=restore&csrf=<?php echo generate_csrf_token(); ?>"
                        class="btn btn-sm btn-outline-success" title="Restore">
                        <i class="bi bi-arrow-counterclockwise"></i>
                      </a>
                      <a href="<?php echo ADMIN_URL; ?>/modules/blog/delete.php?id=<?php echo $blog['id']; ?>&action=purge&csrf=<?php echo generate_csrf_token(); ?>"
                        class="btn btn-sm  delete-btn" title="Delete Permanently">
                        <i class="bi bi-x-circle"></i>
                      </a>
                    <?php endif; ?>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php echo create_pagination($page, $totalPages, ADMIN_URL . '/modules/blog/index.php'); ?>
    <?php endif; ?>
  </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>