<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($page_title) ? escape($page_title) . ' - ' : ''; ?><?php echo escape(PROJECT_NAME); ?></title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Custom Admin Styles -->
  <link href="<?php echo ADMIN_URL; ?>/assets/css/admin.css" rel="stylesheet">

  <?php if (isset($extra_css)): ?>
    <?php echo $extra_css; ?>
  <?php endif; ?>
</head>

<body>
  <div class="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar" class="sidebar">
      <div class="sidebar-header">
        <h3><i class="bi bi-speedometer2 me-2"></i><?php echo escape(PROJECT_NAME); ?></h3>
      </div>

      <ul class="list-unstyled components">
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
          <a href="<?php echo ADMIN_URL; ?>/dashboard.php">
            <i class="bi bi-house-door me-2"></i>Dashboard
          </a>
        </li>

        <li class="menu-section">
          <small class="text-muted ms-3 sidebar-info">CONTENT MANAGEMENT</small>
        </li>

        <?php if (is_module_enabled('blog') && has_permission('blog_view')): ?>
          <li class="<?php echo strpos($_SERVER['PHP_SELF'], '/modules/blog/') !== false ? 'active' : ''; ?>">
            <a href="<?php echo ADMIN_URL; ?>/modules/blog/index.php">
              <i class="bi bi-file-text me-2"></i>Blog Posts
            </a>
          </li>
        <?php endif; ?>

        <?php if (is_module_enabled('gallery') && has_permission('gallery_view')): ?>
          <li class="<?php echo strpos($_SERVER['PHP_SELF'], '/modules/gallery/') !== false ? 'active' : ''; ?>">
            <a href="<?php echo ADMIN_URL; ?>/modules/gallery/index.php">
              <i class="bi bi-images me-2"></i>Gallery
            </a>
          </li>
        <?php endif; ?>

        <?php if (is_module_enabled('hero') && has_permission('hero_view')): ?>
          <li class="<?php echo strpos($_SERVER['PHP_SELF'], '/modules/hero/') !== false ? 'active' : ''; ?>">
            <a href="<?php echo ADMIN_URL; ?>/modules/hero/index.php">
              <i class="bi bi-layout-text-window-reverse me-2"></i>Hero Section
            </a>
          </li>
        <?php endif; ?>

        <?php if (is_module_enabled('tours') && has_permission('tours_view')): ?>
          <li class="<?php echo strpos($_SERVER['PHP_SELF'], '/modules/tours/') !== false ? 'active' : ''; ?>">
            <a href="<?php echo ADMIN_URL; ?>/modules/tours/index.php">
              <i class="bi bi-compass me-2"></i>Tours
            </a>
          </li>
        <?php endif; ?>

        <!-- <?php if (is_module_enabled('pages') && has_permission('pages_view')): ?>
        <li class="<?php echo strpos($_SERVER['PHP_SELF'], '/modules/pages/') !== false ? 'active' : ''; ?>">
          <a href="<?php echo ADMIN_URL; ?>/modules/pages/index.php">
            <i class="bi bi-file-earmark-text me-2"></i>Pages
          </a>
        </li>
        <?php endif; ?> -->

        <!-- <?php if (is_module_enabled('testimonials') && has_permission('testimonials_view')): ?>
          <li class="<?php echo strpos($_SERVER['PHP_SELF'], '/modules/testimonials/') !== false ? 'active' : ''; ?>">
            <a href="<?php echo ADMIN_URL; ?>/modules/testimonials/index.php">
              <i class="bi bi-chat-quote me-2"></i>Testimonials
            </a>
          </li>
        <?php endif; ?> -->

        <?php if (is_module_enabled('forms') && has_permission('forms_view')): ?>
          <li class="<?php echo strpos($_SERVER['PHP_SELF'], '/modules/forms/') !== false ? 'active' : ''; ?>">
            <a href="<?php echo ADMIN_URL; ?>/modules/forms/index.php">
              <i class="bi bi-ui-checks-grid me-2"></i>Forms
            </a>
          </li>
        <?php endif; ?>

        <?php if (is_module_enabled('contact') && has_permission('contact_view')): ?>
          <?php
          $sidebar_unread = 0;
          try {
            $sidebar_unread = (int) $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status='unread'")->fetchColumn();
          } catch (Exception $e) {
          }
          ?>
          <li class="<?php echo strpos($_SERVER['PHP_SELF'], '/modules/contact/') !== false ? 'active' : ''; ?>">
            <a href="<?php echo ADMIN_URL; ?>/modules/contact/index.php"
              class="d-flex align-items-center justify-content-between">
              <span><i class="bi bi-envelope me-2"></i>Contact Messages</span>
              <?php if ($sidebar_unread > 0): ?>
                <span class="badge bg-danger ms-1"><?php echo $sidebar_unread; ?></span>
              <?php endif; ?>
            </a>
          </li>
        <?php endif; ?>

        <?php if (is_module_enabled('events') && has_permission('events_view')): ?>
          <li class="<?php echo strpos($_SERVER['PHP_SELF'], '/modules/events/') !== false ? 'active' : ''; ?>">
            <a href="<?php echo ADMIN_URL; ?>/modules/events/index.php">
              <i class="bi bi-calendar-event me-2"></i>Events
            </a>
          </li>
        <?php endif; ?>

        <?php if (is_module_enabled('our_work') && has_permission('our_work_view')): ?>
          <li class="<?php echo strpos($_SERVER['PHP_SELF'], '/modules/our-work/') !== false ? 'active' : ''; ?>">
            <a href="<?php echo ADMIN_URL; ?>/modules/our-work/index.php">
              <i class="bi bi-briefcase me-2"></i>Our Work
            </a>
          </li>
        <?php endif; ?>

        <?php if (is_module_enabled('media') && has_permission('media_view')): ?>
          <li class="<?php echo strpos($_SERVER['PHP_SELF'], '/modules/media/') !== false ? 'active' : ''; ?>">
            <a href="<?php echo ADMIN_URL; ?>/modules/media/index.php">
              <i class="bi bi-newspaper me-2"></i>Media Coverage
            </a>
          </li>
        <?php endif; ?>

        <?php if (is_module_enabled('roles') && has_permission('users_view')): ?>
          <li class="menu-section">
            <small class="text-muted ms-3 sidebar-info">USER MANAGEMENT</small>
          </li>
          <li
            class="<?php echo (strpos($_SERVER['PHP_SELF'], '/modules/users/') !== false && basename($_SERVER['PHP_SELF']) !== 'create.php') ? 'active' : ''; ?>">
            <a href="<?php echo ADMIN_URL; ?>/modules/users/index.php">
              <i class="bi bi-people-fill me-2"></i>Manage Users
            </a>
          </li>
          <?php if (has_permission('users_create')): ?>
            <li
              class="<?php echo basename($_SERVER['PHP_SELF']) === 'create.php' && strpos($_SERVER['PHP_SELF'], '/modules/users/') !== false ? 'active' : ''; ?>">
              <a href="<?php echo ADMIN_URL; ?>/modules/users/create.php">
                <i class="bi bi-person-plus-fill me-2"></i>Add New User
              </a>
            </li>
          <?php endif; ?>
        <?php endif; ?>

        <li class="menu-section">
          <small class="text-muted ms-3 sidebar-info">SYSTEM</small>
        </li>

        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
          <a href="<?php echo ADMIN_URL; ?>/profile.php">
            <i class="bi bi-person-circle me-2"></i>My Profile
          </a>
        </li>

        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'activity-log.php' ? 'active' : ''; ?>">
          <a href="<?php echo ADMIN_URL; ?>/activity-log.php">
            <i class="bi bi-clock-history me-2"></i>Activity Log
          </a>
        </li>

        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'smtp-settings.php' ? 'active' : ''; ?>">
          <a href="<?php echo ADMIN_URL; ?>/smtp-settings.php">
            <i class="bi bi-envelope-gear me-2"></i>SMTP Settings
          </a>
        </li>
      </ul>

      <!-- Sidebar Footer -->
      <div class="sidebar-footer">
        <div class="sidebar-footer-content">
          <!-- <p class="mb-1"><strong><?php echo escape(PROJECT_NAME); ?></strong></p> -->
          <p class="mb-0"><small>&copy; <?php echo date('Y'); ?> All Rights Reserved</small></p>
          <p class="mb-1"><small>Designed by <a href="https://digiconnunite.com/" target="_blank">Digiconn Unite</a> Pvt
              Ltd</small></p>
        </div>
      </div>
    </nav>

    <!-- Page Content -->
    <div id="content">
      <!-- Top Navigation -->
      <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
        <div class="container-fluid">
          <button type="button" id="sidebarCollapse" class="btn btn-outline-secondary">
            <i class="bi bi-list"></i>
          </button>

          <div class="ms-auto d-flex align-items-center">
            <a href="<?php echo ADMIN_URL; ?>/profile.php"
              class="text-decoration-none text-dark me-3 d-flex align-items-center gap-2">
              <i class="bi bi-person-circle"></i>
              <!-- <strong><?php echo escape($_SESSION['admin_username'] ?? 'Admin'); ?></strong> -->
              <?php if (is_module_enabled('roles') && !empty($_SESSION['admin_role'])): ?>
                <?php echo role_badge($_SESSION['admin_role']); ?>
              <?php endif; ?>
            </a>
            <a href="<?php echo ADMIN_URL; ?>/logout.php" class="btn  btn-sm"
              onclick="return confirm('Are you sure you want to logout?');">
              <i class="bi bi-box-arrow-right me-1"></i>Logout
            </a>
          </div>
        </div>
      </nav>

      <!-- Main Content Area -->
      <div class="container-fluid p-4">
        <?php display_flash(); ?>