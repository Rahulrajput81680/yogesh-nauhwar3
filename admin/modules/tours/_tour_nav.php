<?php
/**
 * Tour Sub-Navigation Partial
 *
 * Requires (set before including this file):
 *   $tour_id    int    — the tour's primary key
 *   $tour_title string — the tour title for the breadcrumb label
 *   $active_tab string — one of: basic | highlights | itinerary | attributes | gallery | tabs
 */
if (!defined('ADMIN_INIT')) {
    die('Direct access not permitted');
}

$_tour_nav_items = [
    ['key' => 'basic',      'icon' => 'bi-info-circle',   'label' => 'Basic Info',   'file' => 'edit.php',       'param' => "id={$tour_id}"],
    ['key' => 'highlights', 'icon' => 'bi-star',          'label' => 'Highlights',   'file' => 'highlights.php', 'param' => "tour_id={$tour_id}"],
    ['key' => 'itinerary',  'icon' => 'bi-calendar3',     'label' => 'Itinerary',    'file' => 'itinerary.php',  'param' => "tour_id={$tour_id}"],
    ['key' => 'attributes', 'icon' => 'bi-tags',          'label' => 'Attributes',   'file' => 'attributes.php', 'param' => "tour_id={$tour_id}"],
    ['key' => 'gallery',    'icon' => 'bi-images',        'label' => 'Gallery',      'file' => 'gallery.php',    'param' => "tour_id={$tour_id}"],
    ['key' => 'tabs',       'icon' => 'bi-layout-tabs',   'label' => 'Content Tabs', 'file' => 'tabs.php',       'param' => "tour_id={$tour_id}"],
];
?>
<div class="card mb-4 bg-light border-0">
    <div class="card-body py-2 px-3">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <span class="fw-semibold text-dark me-1" style="max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                  title="<?php echo escape($tour_title); ?>">
                <i class="bi bi-compass text-primary me-1"></i><?php echo escape($tour_title); ?>
            </span>
            <ul class="nav nav-pills flex-wrap mb-0">
                <?php foreach ($_tour_nav_items as $_tn): ?>
                    <li class="nav-item">
                        <a href="<?php echo ADMIN_URL . '/modules/tours/' . $_tn['file'] . '?' . $_tn['param']; ?>"
                           class="nav-link py-1 px-2 <?php echo ($active_tab === $_tn['key']) ? 'active' : ''; ?>">
                            <i class="bi <?php echo $_tn['icon']; ?> me-1"></i><?php echo $_tn['label']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <a href="<?php echo ADMIN_URL; ?>/modules/tours/index.php"
               class="btn btn-sm btn-outline-secondary ms-auto">
                <i class="bi bi-arrow-left me-1"></i>All Tours
            </a>
        </div>
    </div>
</div>
