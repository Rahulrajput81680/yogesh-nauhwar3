<?php
/**
 * Blog Management - Create New Blog Post
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_once dirname(dirname(__DIR__)) . '/core/uploader.php';
require_login();
require_module('blog');
require_permission('blog_create');

$page_title = 'Create Blog Post';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrf_token = $_POST['csrf_token'] ?? '';

  // Validate CSRF token
  if (!validate_csrf_token($csrf_token)) {
    $errors[] = 'Invalid request. Please try again.';
  } else {
    // Get and sanitize form data
    $title = sanitize_input($_POST['title'] ?? '');
    $slug = sanitize_input($_POST['slug'] ?? '');
    $content = $_POST['content'] ?? ''; // Don't sanitize content (will use in DB)
    $category = sanitize_input($_POST['category'] ?? '');
    $status = sanitize_input($_POST['status'] ?? 'draft');
    $seo_title = sanitize_input($_POST['seo_title'] ?? '');
    $seo_description = sanitize_input($_POST['seo_description'] ?? '');
    $meta_keywords = sanitize_input($_POST['meta_keywords'] ?? '');
    $tags = sanitize_input($_POST['tags'] ?? '');

    // Validation
    if (empty($title)) {
      $errors[] = 'Title is required.';
    }

    if (empty($slug)) {
      $slug = generate_slug($title);
    } else {
      $slug = generate_slug($slug);
    }

    // Check if slug already exists
    if (!empty($slug)) {
      $stmt = $pdo->prepare("SELECT id FROM blogs WHERE slug = ?");
      $stmt->execute([$slug]);
      if ($stmt->fetch()) {
        $errors[] = 'Slug already exists. Please use a different slug.';
      }
    }

    if (empty($content)) {
      $errors[] = 'Content is required.';
    }

    // Handle thumbnail upload
    $thumbnail = null;
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] !== UPLOAD_ERR_NO_FILE) {
      $uploader = new FileUploader();
      $thumbnail = $uploader->upload($_FILES['thumbnail'], 'blog');

      if (!$thumbnail) {
        $errors = array_merge($errors, $uploader->getErrors());
      }
    }

    // Insert if no errors
    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare("
                    INSERT INTO blogs (title, slug, content, thumbnail, category, status, seo_title, seo_description, meta_keywords, tags, author_id, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");

        $stmt->execute([
          $title,
          $slug,
          $content,
          $thumbnail,
          $category,
          $status,
          $seo_title,
          $seo_description,
          $meta_keywords,
          $tags,
          $_SESSION['admin_id']
        ]);

        $blog_id = $pdo->lastInsertId();

        // Log activity
        log_activity('create', 'blog', $blog_id, "Created blog: $title");

        set_flash('success', 'Blog post created successfully!');
        redirect(ADMIN_URL . '/modules/blog/index.php');
      } catch (PDOException $e) {
        $errors[] = 'Failed to create blog post. ' . $e->getMessage();
      }
    }
  }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header">
  <h1><i class="bi bi-plus-lg me-2"></i>Create Blog Post</h1>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="card">
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

        <form method="POST" action="" enctype="multipart/form-data">
          <?php echo csrf_field(); ?>

          <!-- Title -->
          <div class="mb-3">
            <label for="title" class="form-label">Blog Title *</label>
            <input type="text" class="form-control" id="title" name="title"
              value="<?php echo escape($_POST['title'] ?? ''); ?>" required>
          </div>

          <!-- Slug -->
          <div class="mb-3">
            <label for="slug" class="form-label">URL Slug</label>
            <input type="text" class="form-control" id="slug" name="slug"
              value="<?php echo escape($_POST['slug'] ?? ''); ?>" placeholder="Auto-generated from title">
            <small class="text-muted">Leave empty to auto-generate from title</small>
          </div>

          <!-- Meta Title -->
          <div class="mb-3">
            <label for="seo_title" class="form-label">Meta Title</label>
            <input type="text" class="form-control" id="seo_title" name="seo_title"
              value="<?php echo escape($_POST['seo_title'] ?? ''); ?>" placeholder="Leave empty to use blog title">
            <small class="text-muted">Max 60 characters</small>
          </div>

          <!-- Meta Description -->
          <div class="mb-3">
            <label for="seo_description" class="form-label">Meta Description</label>
            <textarea class="form-control" id="seo_description" name="seo_description" rows="3"
              maxlength="160"><?php echo escape($_POST['seo_description'] ?? ''); ?></textarea>
            <small class="text-muted">Max 160 characters</small>
          </div>

          <!-- Meta Keywords -->
          <div class="mb-3">
            <label for="meta_keywords" class="form-label">Meta Keywords</label>
            <input type="text" class="form-control" id="meta_keywords" name="meta_keywords"
              value="<?php echo escape($_POST['meta_keywords'] ?? ''); ?>" placeholder="keyword1, keyword2, keyword3">
            <small class="text-muted">Comma separated</small>
          </div>

          <!-- Featured Image -->
          <div class="mb-3">
            <label for="thumbnail" class="form-label">Featured Image</label>
            <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/webp">
            <small class="text-muted">Only WebP format. Max size: <?php echo format_file_size(MAX_UPLOAD_SIZE); ?></small>
          </div>

          <!-- Content with Summernote -->
          <div class="mb-3">
            <label for="content" class="form-label">Blog Content *</label>
            <textarea id="summernote" name="content" required><?php echo $_POST['content'] ?? ''; ?></textarea>
          </div>

          <!-- Tags -->
          <div class="mb-3">
            <label for="tags" class="form-label">Tags</label>
            <input type="text" class="form-control" id="tags" name="tags"
              value="<?php echo escape($_POST['tags'] ?? ''); ?>" placeholder="technology, tutorial, guide (comma separated)">
            <small class="text-muted">Separate multiple tags with commas</small>
          </div>

          <!-- Category -->
          <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <input type="text" class="form-control" id="category" name="category"
              value="<?php echo escape($_POST['category'] ?? ''); ?>" placeholder="e.g., Technology, Lifestyle">
          </div>

          <!-- Status -->
          <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status">
              <option value="draft" <?php echo (isset($_POST['status']) && $_POST['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
              <option value="published" <?php echo (isset($_POST['status']) && $_POST['status'] === 'published') ? 'selected' : ''; ?>>Published</option>
            </select>
          </div>

          <!-- Submit Buttons -->
          <div class="d-flex gap-2">
            <button type="submit" class="btn">
              <i class="bi bi-check-lg me-2"></i>Create Blog Post
            </button>
            <a href="<?php echo ADMIN_URL; ?>/modules/blog/index.php" class="btn btn-secondary">
              <i class="bi bi-x-lg me-2"></i>Cancel
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>

<!-- Summernote CSS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-bs5.min.css" rel="stylesheet">

<!-- Summernote JS -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-bs5.min.js"></script>

<script>
  $(document).ready(function() {
    // Initialize Summernote
    $('#summernote').summernote({
      height: 600,
      width: '100%',
      placeholder: 'Write your blog content here. You can format text, add images, and more...',
      toolbar: [
        ['style', ['style']],
        ['font', ['bold', 'underline', 'italic', 'clear']],
        ['fontname', ['fontname']],
        ['fontsize', ['fontsize']],
        ['color', ['color']],
        ['para', ['ul', 'ol', 'paragraph']],
        ['table', ['table']],
        ['insert', ['link', 'picture', 'video']],
        ['view', ['fullscreen', 'codeview', 'help']]
      ]
    });

    // Auto-generate slug from title
    $('#title').on('input', function() {
      const title = $(this).val();
      const slug = title.toLowerCase()
        .replace(/[^\w\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/--+/g, '-')
        .trim();
      $('#slug').val(slug);
    });
  });
</script>