<?php
/**
 * Admin Dashboard – Role-Aware
 */

require_once __DIR__ . '/init.php';
require_login();

$page_title = 'Dashboard';
$currentRole = $_SESSION['admin_role'] ?? 'editor';

// ── Blog stats ────────────────────────────────────────────────────────────────
if (is_module_enabled('blog') && has_permission('blog_view')) {
  $total_blogs = count_records('blogs', 'deleted_at IS NULL');
  $published_blogs = count_records('blogs', "status = ? AND deleted_at IS NULL", ['published']);
  $draft_blogs = count_records('blogs', "status = ? AND deleted_at IS NULL", ['draft']);
  $trashed_blogs = count_records('blogs', 'deleted_at IS NOT NULL');
}

// ── Gallery stats ─────────────────────────────────────────────────────────────
if (is_module_enabled('gallery') && has_permission('gallery_view')) {
  $total_gallery = count_records('gallery');
}

// ── Hero stats ────────────────────────────────────────────────────────────────
if (is_module_enabled('hero') && has_permission('hero_view')) {
  $total_hero = count_records('hero_sections');
  $active_hero = count_records('hero_sections', 'status = ?', ['active']);
}

// ── Contact stats ─────────────────────────────────────────────────────────────
if (is_module_enabled('contact') && has_permission('contact_view')) {
  $total_contact = count_records('contact_messages');
  $unread_contact = count_records('contact_messages', 'status = ?', ['unread']);
}

// ── Events stats ──────────────────────────────────────────────────────────────
if (is_module_enabled('events') && has_permission('events_view')) {
  $total_events = count_records('events');
  $upcoming_events = count_records('events', 'event_type = ? AND status = ?', ['upcoming', 'active']);
}

// ── Our Work stats ───────────────────────────────────────────────────────────
if (is_module_enabled('our_work') && has_permission('our_work_view')) {
  $total_our_work = count_records('gallery', "display_section IN ('our_work', 'our-work')");
}

// ── Media Coverage stats ─────────────────────────────────────────────────────
if (is_module_enabled('media') && has_permission('media_view')) {
  $total_media_coverage = count_records('gallery', "display_section IN ('media_coverage', 'media')");
}

// ── Forms stats ───────────────────────────────────────────────────────────────
if (is_module_enabled('forms') && has_permission('forms_view')) {
  $total_forms = count_records('forms');
  $total_submissions = count_records('form_submissions');
}

// ── Users stats (roles module only) ──────────────────────────────────────────
if (is_module_enabled('roles') && has_permission('users_view')) {
  $total_users = count_records('admin_users', 'deleted_at IS NULL');
  $active_users = count_records('admin_users', "status = 'active' AND deleted_at IS NULL");
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <h1><i class="bi bi-speedometer2 me-2"></i>Dashboard</h1>
    <p class="text-muted mb-0 small">
      Welcome back,
      <strong><?php echo escape($_SESSION['admin_username'] ?? 'Admin'); ?></strong>
      <?php if (is_module_enabled('roles') && !empty($currentRole)): ?>
        &mdash;
        <?php echo role_badge($currentRole); ?>
      <?php endif; ?>
    </p>
  </div>
</div>

<!-- ── Role Info Banner ──────────────────────────────────────────────────── -->
<?php if (is_module_enabled('roles')): ?>
  <div class="alert border-0 mb-4 py-2 px-3"
    style="background:rgba(99,102,241,.08);border-left:4px solid #6366F1 !important;border-radius:8px;">
    <i class="bi bi-shield-check me-2 text-primary"></i>
    Logged in as <strong><?php echo escape($_SESSION['admin_username']); ?></strong>
    &nbsp;<?php echo role_badge($currentRole); ?>&nbsp;
    <?php if ($currentRole === 'superadmin'): ?>
      — unrestricted access to all system functions.
    <?php elseif ($currentRole === 'admin'): ?>
      — full content management + user visibility.
    <?php elseif ($currentRole === 'editor'): ?>
      — create &amp; edit content. Delete actions are restricted.
    <?php elseif ($currentRole === 'teacher'): ?>
      — view &amp; create content in assigned sections.
    <?php endif; ?>
  </div>
<?php endif; ?>

<!-- ── Statistics Cards ──────────────────────────────────────────────────── -->
<div class="row mb-4">

  <?php if (is_module_enabled('blog') && has_permission('blog_view')): ?>
    <div class="col-6 col-md-3 mb-3">
      <div class="card stats-card">
        <div class="card-body d-flex align-items-center">
          <div class="stats-icon primary me-3"><i class="bi bi-file-text-fill"></i></div>
          <div>
            <h4 class="mb-0"><?php echo $total_blogs; ?></h4>
            <p class="text-muted mb-0 small">Blog Posts</p>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
      <div class="card stats-card">
        <div class="card-body d-flex align-items-center">
          <div class="stats-icon success me-3"><i class="bi bi-check-circle-fill"></i></div>
          <div>
            <h4 class="mb-0"><?php echo $published_blogs; ?></h4>
            <p class="text-muted mb-0 small">Published</p>
          </div>
        </div>
      </div>
    </div>
    <?php if (isset($trashed_blogs) && $trashed_blogs > 0 && has_permission('blog_restore')): ?>
      <div class="col-6 col-md-3 mb-3">
        <!-- <a href="<?php echo ADMIN_URL; ?>/modules/blog/index.php?view=trash" class="text-decoration-none"> -->
        <div class="card stats-card">
          <div class="card-body d-flex align-items-center">
            <div class="stats-icon warning me-3"><i class="bi bi-trash-fill"></i></div>
            <div>
              <h4 class="mb-0 "><?php echo $trashed_blogs; ?></h4>
              <p class="text-muted mb-0 small">In Trash</p>
            </div>
          </div>
        </div>
        <!-- </a> -->
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <?php if (is_module_enabled('gallery') && has_permission('gallery_view')): ?>
    <div class="col-6 col-md-3 mb-3">
      <div class="card stats-card">
        <div class="card-body d-flex align-items-center">
          <div class="stats-icon info me-3"><i class="bi bi-images"></i></div>
          <div>
            <h4 class="mb-0"><?php echo $total_gallery; ?></h4>
            <p class="text-muted mb-0 small">Gallery Images</p>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php if (is_module_enabled('hero') && has_permission('hero_view')): ?>
    <div class="col-6 col-md-3 mb-3">
      <div class="card stats-card">
        <div class="card-body d-flex align-items-center">
          <div class="stats-icon primary me-3"><i class="bi bi-layout-text-window-reverse"></i></div>
          <div>
            <h4 class="mb-0"><?php echo $total_hero; ?></h4>
            <p class="text-muted mb-0 small">Hero Slides</p>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php if (is_module_enabled('contact') && has_permission('contact_view')): ?>
    <div class="col-6 col-md-3 mb-3">
      <div
        class="card stats-card <?php echo (!empty($unread_contact) && $unread_contact > 0) ? 'border border-danger-subtle' : ''; ?>">
        <div class="card-body d-flex align-items-center">
          <div class="stats-icon info me-3"><i class="bi bi-envelope-fill"></i></div>
          <div>
            <h4 class="mb-0">
              <?php echo $total_contact; ?>
              <?php if (!empty($unread_contact) && $unread_contact > 0): ?>
                <span class="badge bg-danger ms-1" style="font-size:.65rem;"><?php echo $unread_contact; ?> new</span>
              <?php endif; ?>
            </h4>
            <p class="text-muted mb-0 small">Contact Messages</p>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php if (is_module_enabled('events') && has_permission('events_view')): ?>
    <div class="col-6 col-md-3 mb-3">
      <div class="card stats-card">
        <div class="card-body d-flex align-items-center">
          <div class="stats-icon primary me-3"><i class="bi bi-calendar-event-fill"></i></div>
          <div>
            <h4 class="mb-0"><?php echo $total_events; ?></h4>
            <p class="text-muted mb-0 small">Events</p>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
      <div class="card stats-card">
        <div class="card-body d-flex align-items-center">
          <div class="stats-icon success me-3"><i class="bi bi-calendar-check-fill"></i></div>
          <div>
            <h4 class="mb-0"><?php echo $upcoming_events; ?></h4>
            <p class="text-muted mb-0 small">Upcoming Events</p>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php if (is_module_enabled('our_work') && has_permission('our_work_view')): ?>
    <div class="col-6 col-md-3 mb-3">
      <div class="card stats-card">
        <div class="card-body d-flex align-items-center">
          <div class="stats-icon primary me-3"><i class="bi bi-briefcase-fill"></i></div>
          <div>
            <h4 class="mb-0"><?php echo (int) ($total_our_work ?? 0); ?></h4>
            <p class="text-muted mb-0 small">Our Work Images</p>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php if (is_module_enabled('media') && has_permission('media_view')): ?>
    <div class="col-6 col-md-3 mb-3">
      <div class="card stats-card">
        <div class="card-body d-flex align-items-center">
          <div class="stats-icon info me-3"><i class="bi bi-newspaper"></i></div>
          <div>
            <h4 class="mb-0"><?php echo (int) ($total_media_coverage ?? 0); ?></h4>
            <p class="text-muted mb-0 small">Media Coverage</p>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php if (is_module_enabled('forms') && has_permission('forms_view')): ?>
    <div class="col-6 col-md-3 mb-3">
      <div class="card stats-card">
        <div class="card-body d-flex align-items-center">
          <div class="stats-icon primary me-3"><i class="bi bi-ui-checks-grid"></i></div>
          <div>
            <h4 class="mb-0"><?php echo $total_forms; ?></h4>
            <p class="text-muted mb-0 small">Custom Forms</p>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
      <div class="card stats-card">
        <div class="card-body d-flex align-items-center">
          <div class="stats-icon success me-3"><i class="bi bi-inbox-fill"></i></div>
          <div>
            <h4 class="mb-0"><?php echo $total_submissions; ?></h4>
            <p class="text-muted mb-0 small">Form Submissions</p>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php if (is_module_enabled('roles') && has_permission('users_view')): ?>
    <div class="col-6 col-md-3 mb-3">
      <div class="card stats-card">
        <div class="card-body d-flex align-items-center">
          <div class="stats-icon primary me-3"><i class="bi bi-people-fill"></i></div>
          <div>
            <h4 class="mb-0"><?php echo $total_users; ?></h4>
            <p class="text-muted mb-0 small">Total Users</p>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
      <div class="card stats-card">
        <div class="card-body d-flex align-items-center">
          <div class="stats-icon success me-3"><i class="bi bi-person-check-fill"></i></div>
          <div>
            <h4 class="mb-0"><?php echo $active_users; ?></h4>
            <p class="text-muted mb-0 small">Active Users</p>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

</div>

<!-- ── Quick Actions ──────────────────────────────────────────────────────── -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header"><i class="bi bi-lightning-charge-fill me-2"></i>Quick Actions</div>
      <div class="card-body">

        <?php if (is_module_enabled('blog') && has_permission('blog_create')): ?>
          <a href="<?php echo ADMIN_URL; ?>/modules/blog/create.php" class="btn me-2 mb-2">
            <i class="bi bi-plus-lg me-2"></i>New Blog Post
          </a>
        <?php endif; ?>
        <?php if (is_module_enabled('blog') && has_permission('blog_view')): ?>
          <a href="<?php echo ADMIN_URL; ?>/modules/blog/index.php" class="btn me-2 mb-2">
            <i class="bi bi-list-ul me-2"></i>View Blogs
          </a>
        <?php endif; ?>

        <?php if (is_module_enabled('gallery') && has_permission('gallery_create')): ?>
          <a href="<?php echo ADMIN_URL; ?>/modules/gallery/create.php" class="btn me-2 mb-2">
            <i class="bi bi-cloud-upload me-2"></i>Upload Image
          </a>
        <?php endif; ?>
        <?php if (is_module_enabled('gallery') && has_permission('gallery_view')): ?>
          <a href="<?php echo ADMIN_URL; ?>/modules/gallery/index.php" class="btn me-2 mb-2">
            <i class="bi bi-images me-2"></i>View Gallery
          </a>
        <?php endif; ?>

        <?php if (is_module_enabled('hero') && has_permission('hero_edit')): ?>
          <a href="<?php echo ADMIN_URL; ?>/modules/hero/index.php" class="btn me-2 mb-2">
            <i class="bi bi-layout-text-window-reverse me-2"></i>Manage Hero
          </a>
        <?php endif; ?>

        <?php if (is_module_enabled('contact') && has_permission('contact_view')): ?>
          <a href="<?php echo ADMIN_URL; ?>/modules/contact/index.php" class="btn me-2 mb-2">
            <i class="bi bi-envelope me-2"></i>Contact Messages
            <?php if (!empty($unread_contact) && $unread_contact > 0): ?>
              <span class="badge bg-danger ms-1"><?php echo $unread_contact; ?></span>
            <?php endif; ?>
          </a>
        <?php endif; ?>

        <?php if (is_module_enabled('events') && has_permission('events_create')): ?>
          <a href="<?php echo ADMIN_URL; ?>/modules/events/create.php" class="btn me-2 mb-2">
            <i class="bi bi-calendar-plus me-2"></i>New Event
          </a>
        <?php endif; ?>
        <?php if (is_module_enabled('events') && has_permission('events_view')): ?>
          <a href="<?php echo ADMIN_URL; ?>/modules/events/index.php" class="btn me-2 mb-2">
            <i class="bi bi-calendar-event me-2"></i>Manage Events
          </a>
        <?php endif; ?>

        <?php if (is_module_enabled('our_work') && has_permission('our_work_create')): ?>
          <a href="<?php echo ADMIN_URL; ?>/modules/our-work/create.php" class="btn me-2 mb-2">
            <i class="bi bi-briefcase me-2"></i>Upload Our Work Image
          </a>
        <?php endif; ?>
        <?php if (is_module_enabled('our_work') && has_permission('our_work_view')): ?>
          <a href="<?php echo ADMIN_URL; ?>/modules/our-work/index.php" class="btn me-2 mb-2">
            <i class="bi bi-briefcase-fill me-2"></i>View Our Work
          </a>
        <?php endif; ?>

        <?php if (is_module_enabled('media') && has_permission('media_create')): ?>
          <a href="<?php echo ADMIN_URL; ?>/modules/media/create.php" class="btn me-2 mb-2">
            <i class="bi bi-newspaper me-2"></i>Upload Media Coverage
          </a>
        <?php endif; ?>
        <?php if (is_module_enabled('media') && has_permission('media_view')): ?>
          <a href="<?php echo ADMIN_URL; ?>/modules/media/index.php" class="btn me-2 mb-2">
            <i class="bi bi-journal-text me-2"></i>View Media Coverage
          </a>
        <?php endif; ?>

        <?php if (is_module_enabled('forms') && has_permission('forms_create')): ?>
          <a href="<?php echo ADMIN_URL; ?>/modules/forms/create.php" class="btn me-2 mb-2">
            <i class="bi bi-plus-circle me-2"></i>New Form
          </a>
        <?php endif; ?>
        <?php if (is_module_enabled('forms') && has_permission('forms_view')): ?>
          <a href="<?php echo ADMIN_URL; ?>/modules/forms/index.php" class="btn me-2 mb-2">
            <i class="bi bi-ui-checks-grid me-2"></i>View Forms
          </a>
        <?php endif; ?>

        <?php if (is_module_enabled('roles') && has_permission('users_create')): ?>
          <a href="<?php echo ADMIN_URL; ?>/modules/users/create.php" class="btn me-2 mb-2">
            <i class="bi bi-person-plus-fill me-2"></i>Add User
          </a>
        <?php endif; ?>
        <?php if (is_module_enabled('roles') && has_permission('users_view')): ?>
          <a href="<?php echo ADMIN_URL; ?>/modules/users/index.php" class="btn me-2 mb-2">
            <i class="bi bi-people-fill me-2"></i>Manage Users
          </a>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
<?php exit; // Stop execution — prevents legacy duplicate code below from running ?>
if (is_module_enabled('blog')) {
$total_blogs = count_records('blogs');
$published_blogs = count_records('blogs', 'status = ?', ['published']);
$draft_blogs = count_records('blogs', 'status = ?', ['draft']);
}

// ── Gallery stats ─────────────────────────────────────────────────────────────
if (is_module_enabled('gallery')) {
$total_gallery = count_records('gallery');
$active_gallery = count_records('gallery', 'status = ?', ['active']);
}

// ── Hero stats ────────────────────────────────────────────────────────────────
if (is_module_enabled('hero')) {
$total_hero = count_records('hero_sections');
$active_hero = count_records('hero_sections', 'status = ?', ['active']);
}

// ── Contact stats ─────────────────────────────────────────────────────────────
if (is_module_enabled('contact')) {
$total_contact = count_records('contact_messages');
$unread_contact = count_records('contact_messages', 'status = ?', ['unread']);
}

// ── Forms stats ───────────────────────────────────────────────────────────────
if (is_module_enabled('forms')) {
$total_forms = count_records('forms');
$total_submissions = count_records('form_submissions');
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
  <h1><i class="bi bi-speedometer2 me-2"></i>Dashboard</h1>
  <div>
    <span class="text-muted">Welcome back, <strong><?php echo escape($_SESSION['admin_username']); ?></strong></span>
  </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">

  <?php if (is_module_enabled('blog')): ?>
    <div class="col-md-3 mb-3">
      <div class="card stats-card">
        <div class="card-body d-flex align-items-center">
          <div class="stats-icon primary me-3">
            <i class="bi bi-file-text-fill"></i>
          </div>
          <div>
            <h4 class="mb-0"><?php echo $total_blogs; ?></h4>
            <p class="text-muted mb-0 small">Total Blog Posts</p>
          </div>
        </div>
      </div>
    </div>

    <!-- <div class="col-md-3 mb-3">
    <div class="card stats-card">
      <div class="card-body d-flex align-items-center">
        <div class="stats-icon success me-3">
          <i class="bi bi-check-circle-fill"></i>
        </div>
        <div>
          <h4 class="mb-0"><?php echo $published_blogs; ?></h4>
          <p class="text-muted mb-0 small">Published Blogs</p>
        </div>
      </div>
    </div>
  </div> -->

    <!-- <div class="col-md-3 mb-3">
      <div class="card stats-card">
        <div class="card-body d-flex align-items-center">
          <div class="stats-icon warning me-3">
            <i class="bi bi-clock-fill"></i>
          </div>
          <div>
            <h4 class="mb-0"><?php echo $draft_blogs; ?></h4>
            <p class="text-muted mb-0 small">Draft Blogs</p>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?> -->

  <?php if (is_module_enabled('gallery')): ?>
    <div class="col-md-3 mb-3">
      <div class="card stats-card">
        <div class="card-body d-flex align-items-center">
          <div class="stats-icon info me-3">
            <i class="bi bi-images"></i>
          </div>
          <div>
            <h4 class="mb-0">
              <?php echo $total_gallery; ?>
            </h4>
            <p class="text-muted mb-0 small">Gallery Images</p>
          </div>
        </div>
      </div>
    </div>

    <!-- <div class="col-md-3 mb-3">
      <div class="card stats-card">
        <div class="card-body d-flex align-items-center">
          <div class="stats-icon success me-3">
            <i class="bi bi-check2-square"></i>
          </div>
          <div>
            <h4 class="mb-0">
              <?php echo $active_gallery; ?>
            </h4>
            <p class="text-muted mb-0 small">Active Gallery Items</p>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?> -->

  <?php if (is_module_enabled('hero')): ?>
    <div class="col-md-3 mb-3">
      <div class="card stats-card">
        <div class="card-body d-flex align-items-center">
          <div class="stats-icon primary me-3">
            <i class="bi bi-layout-text-window-reverse"></i>
          </div>
          <div>
            <h4 class="mb-0">
              <?php echo $total_hero; ?>
            </h4>
            <p class="text-muted mb-0 small">Hero Slides</p>
          </div>
        </div>
      </div>
    </div>

    <!-- <div class="col-md-3 mb-3">
      <div class="card stats-card">
        <div class="card-body d-flex align-items-center">
          <div class="stats-icon success me-3">
            <i class="bi bi-play-circle-fill"></i>
          </div>
          <div>
            <h4 class="mb-0">
              <?php echo $active_hero; ?>
            </h4>
            <p class="text-muted mb-0 small">Active Hero Slides</p>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?> -->

  <?php if (is_module_enabled('contact')): ?>
    <div class="col-md-3 mb-3">
      <div class="card stats-card">
        <div class="card-body d-flex align-items-center">
          <div class="stats-icon info me-3">
            <i class="bi bi-envelope-fill"></i>
          </div>
          <div>
            <h4 class="mb-0">
              <?php echo $total_contact; ?>
            </h4>
            <p class="text-muted mb-0 small">Contact Messages</p>
          </div>
        </div>
      </div>
    </div>

    <!-- <div class="col-md-3 mb-3">
      <div class="card stats-card">
        <div class="card-body d-flex align-items-center">
          <div class="stats-icon warning me-3">
            <i class="bi bi-envelope-exclamation-fill"></i>
          </div>
          <div>
            <h4 class="mb-0">
              <?php echo $unread_contact; ?>
            </h4>
            <p class="text-muted mb-0 small">Unread Messages</p>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?> -->

  <?php if (is_module_enabled('forms')): ?>
    <div class="col-md-3 mb-3">
      <div class="card stats-card">
        <div class="card-body d-flex align-items-center">
          <div class="stats-icon primary me-3">
            <i class="bi bi-ui-checks-grid"></i>
          </div>
          <div>
            <h4 class="mb-0">
              <?php echo $total_forms; ?>
            </h4>
            <p class="text-muted mb-0 small">Custom Forms</p>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3 mb-3">
      <div class="card stats-card">
        <div class="card-body d-flex align-items-center">
          <div class="stats-icon success me-3">
            <i class="bi bi-inbox-fill"></i>
          </div>
          <div>
            <h4 class="mb-0">
              <?php echo $total_submissions; ?>
            </h4>
            <p class="text-muted mb-0 small">Form Submissions</p>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

</div>

<!-- Quick Actions -->
<?php
// Check if at least one actionable module is enabled
$has_quick_actions = is_module_enabled('blog') || is_module_enabled('gallery')
  || is_module_enabled('hero') || is_module_enabled('contact')
  || is_module_enabled('forms');
?>
<?php if ($has_quick_actions): ?>
  <div class="row mb-4">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <i class="bi bi-lightning-charge-fill me-2"></i>Quick Actions
        </div>
        <div class="card-body">

          <?php if (is_module_enabled('blog')): ?>
            <a href="<?php echo ADMIN_URL; ?>/modules/blog/create.php" class="btn me-2 mb-2">
              <i class="bi bi-plus-lg me-2"></i>New Blog Post
            </a>
            <a href="<?php echo ADMIN_URL; ?>/modules/blog/index.php" class="btn me-2 mb-2">
              <i class="bi bi-list-ul me-2"></i>View All Blogs
            </a>
          <?php endif; ?>

          <?php if (is_module_enabled('gallery')): ?>
            <a href="<?php echo ADMIN_URL; ?>/modules/gallery/create.php" class="btn me-2 mb-2">
              <i class="bi bi-cloud-upload me-2"></i>Upload Image
            </a>
            <a href="<?php echo ADMIN_URL; ?>/modules/gallery/index.php" class="btn me-2 mb-2">
              <i class="bi bi-images me-2"></i>View Gallery
            </a>
          <?php endif; ?>

          <?php if (is_module_enabled('hero')): ?>
            <a href="<?php echo ADMIN_URL; ?>/modules/hero/index.php" class="btn me-2 mb-2">
              <i class="bi bi-layout-text-window-reverse me-2"></i>Manage Hero
            </a>
          <?php endif; ?>

          <?php if (is_module_enabled('contact')): ?>
            <a href="<?php echo ADMIN_URL; ?>/modules/contact/index.php" class="btn me-2 mb-2">
              <i class="bi bi-envelope me-2"></i>Contact Messages
            </a>
          <?php endif; ?>

          <?php if (is_module_enabled('forms')): ?>
            <a href="<?php echo ADMIN_URL; ?>/modules/forms/create.php" class="btn me-2 mb-2">
              <i class="bi bi-plus-circle me-2"></i>New Form
            </a>
            <a href="<?php echo ADMIN_URL; ?>/modules/forms/index.php" class="btn mb-2">
              <i class="bi bi-ui-checks-grid me-2"></i>View Forms
            </a>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>