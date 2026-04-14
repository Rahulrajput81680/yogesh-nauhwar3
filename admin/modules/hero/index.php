<?php
/**
 * Hero Section Management – Item list
 *
 * Supports two modes (controlled by config/modules.php → hero_slider):
 *   false  →  Only the first active item is shown on the website (single static hero).
 *   true   →  All active items are loaded and displayed as a rotating slider.
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_login();
require_module('hero');
require_permission('hero_view');

$page_title = 'Hero Section';
$slider_mode = is_module_enabled('hero_slider');

// ── Fetch all hero items ordered by slide_order ──────────────────────────────
$items = [];
$table_exists = false;
try {
  $check = $pdo->query("SHOW TABLES LIKE 'hero_items'")->fetch();
  if ($check) {
    $table_exists = true;
    $items = $pdo->query("SELECT * FROM hero_items ORDER BY slide_order ASC, id ASC")->fetchAll();
  }
} catch (PDOException $e) {
  error_log('Hero index error: ' . $e->getMessage());
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <h1><i class="bi bi-layout-text-window-reverse me-2"></i>Hero Section</h1>
    <p class="mb-0 text-muted small">
      Mode:
      <?php if ($slider_mode): ?>
        <span class="badge bg-success"><i class="bi bi-collection-play me-1"></i>Slider – all active items shown</span>
      <?php else: ?>
        <span class="badge bg-secondary"><i class="bi bi-image me-1"></i>Single – only first active item shown</span>
      <?php endif; ?>
      <!-- <span class="ms-1">Change via <code>config/modules.php</code> → <code>'hero_slider'</code></span> -->
    </p>
  </div>
  <?php if (has_permission('hero_create')): ?>
    <a href="<?php echo ADMIN_URL; ?>/modules/hero/create.php" class="btn btn-primary">
      <i class="bi bi-plus-lg me-2"></i>Add Hero Item
    </a>
  <?php endif; ?>
</div>

<?php $flash = get_flash(); ?>
<?php if ($flash): ?>
  <div class="alert alert-<?php echo escape($flash['type']); ?> alert-dismissible fade show">
    <?php echo escape($flash['message']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<?php if (!$table_exists): ?>
  <!-- hero_items table not yet created -->
  <div class="card border-warning">
    <div class="card-body text-center py-5">
      <i class="bi bi-exclamation-triangle fs-1 text-warning mb-3 d-block"></i>
      <h4>Database Table Missing</h4>
      <p class="text-muted">The <code>hero_items</code> table does not exist yet.<br>
        Run the SQL in <code>database/schema.sql</code> (look for the <em>Hero Items Table</em> section) then refresh.</p>
    </div>
  </div>

<?php elseif (empty($items)): ?>
  <div class="card">
    <div class="card-body text-center py-5 text-muted">
      <i class="bi bi-layout-text-window-reverse fs-1 mb-3 d-block"></i>
      <p>No hero items yet.</p>
      <?php if (has_permission('hero_create')): ?>
        <a href="<?php echo ADMIN_URL; ?>/modules/hero/create.php" class="btn btn-primary">
          <i class="bi bi-plus-lg me-2"></i>Add First Hero Item
        </a>
      <?php endif; ?>
    </div>
  </div>

<?php else: ?>

  <!-- Mode info banner -->
  <?php if (!$slider_mode): ?>
    <div class="alert alert-info d-flex align-items-center gap-2 mb-3">
      <i class="bi bi-info-circle-fill flex-shrink-0"></i>
      <div>
        <strong>Single mode:</strong> Only the <span class="badge bg-success">active</span> item with the
        lowest slide order will appear on your website.
        Set <code>'hero_slider' => true</code> in <code>config/modules.php</code> to enable the slider.
      </div>
    </div>
  <?php else: ?>
    <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
      <i class="bi bi-collection-play-fill flex-shrink-0"></i>
      <div>
        <strong>Slider mode:</strong> All <span class="badge bg-success">active</span> items will rotate
        as slides on your website, ordered by slide order.
      </div>
    </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th width="60">Order</th>
              <th width="80">Preview</th>
              <th>Heading</th>
              <th>Button</th>
              <th width="100">Status</th>
              <th width="130">Updated</th>
              <th width="130">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $first_active_marked = false;
            foreach ($items as $item):
              // Highlight the one item that will show in single mode
              $row_class = '';
              if (!$slider_mode && $item['status'] === 'active' && !$first_active_marked) {
                $row_class = 'table-success';
                $first_active_marked = true;
              }
              ?>
              <tr class="<?php echo $row_class; ?>">
                <td>
                  <span class="badge bg-secondary"><?php echo (int) $item['slide_order']; ?></span>
                </td>

                <td>
                  <?php if (!empty($item['background_image'])): ?>
                    <img src="<?php echo escape(UPLOAD_URL . '/' . $item['background_image']); ?>" alt=""
                      style="width:64px;height:42px;object-fit:cover;border-radius:4px;">
                  <?php else: ?>
                    <div class="bg-light d-flex align-items-center justify-content-center"
                      style="width:64px;height:42px;border-radius:4px;">
                      <i class="bi bi-image text-muted small"></i>
                    </div>
                  <?php endif; ?>
                </td>

                <td>
                  <strong><?php echo escape(truncate($item['heading'], 60)); ?></strong>
                  <?php if (!empty($item['description'])): ?>
                    <br><small class="text-muted"><?php echo escape(truncate($item['description'], 72)); ?></small>
                  <?php endif; ?>
                </td>

                <td>
                  <?php if ($item['button_text']): ?>
                    <span class="small"><?php echo escape($item['button_text']); ?></span>
                    <?php if ($item['button_link']): ?>
                      <br><code class="text-muted" style="font-size:0.72em;">
                                                <?php echo escape(truncate($item['button_link'], 32)); ?>
                                              </code>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>

                <td><?php echo get_status_badge($item['status']); ?></td>

                <td><small class="text-muted"><?php echo format_datetime($item['updated_at']); ?></small></td>

                <td style="white-space:nowrap;">
                  <?php if (has_permission('hero_edit')): ?>
                    <a href="<?php echo ADMIN_URL; ?>/modules/hero/edit.php?id=<?php echo $item['id']; ?>"
                      class="btn btn-sm btn-outline-primary" title="Edit">
                      <i class="bi bi-pencil"></i>
                    </a>
                  <?php endif; ?>
                  <?php if (has_permission('hero_delete')): ?>
                    <a href="<?php echo ADMIN_URL; ?>/modules/hero/delete.php?id=<?php echo $item['id']; ?>&csrf=<?php echo generate_csrf_token(); ?>"
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

      <?php
      $active_count = count(array_filter($items, fn($i) => $i['status'] === 'active'));
      ?>
      <div class="px-3 py-2 border-top text-muted small">
        Total: <?php echo count($items); ?> item(s) — <strong><?php echo $active_count; ?> active</strong>
      </div>
    </div>
  </div>

  <!-- Frontend integration snippet -->
  <!-- <div class="card border-0 bg-light">
    <div class="card-body">
      <h6 class="mb-2"><i class="bi bi-code-slash me-2"></i>Frontend Integration</h6>
      <p class="mb-2 small text-muted">Drop into your website template where the hero should appear:</p>
      <pre class="bg-white border rounded p-3 mb-0" style="font-size:0.78rem;">&lt;?php
    // Include your DB connection ($pdo) before this snippet.
    // Path to admin config — adjust as needed.
    $cfg = require __DIR__ . '/admin/config/modules.php';

    if (!$cfg['hero']) {
        // Module off: render your static HTML hero here instead

    } else {
        $limit = ($cfg['hero_slider'] ?? false) ? 999 : 1;
        $s = $pdo-&gt;prepare(
            "SELECT * FROM hero_items WHERE status = 'active'
             ORDER BY slide_order ASC LIMIT ?"
        );
        $s-&gt;execute([$limit]);
        $slides = $s-&gt;fetchAll(PDO::FETCH_ASSOC);

        // $slides is an array of hero items.
        // Loop through $slides to render each slide.
        // Use a JS slider library (e.g. Swiper, Slick) when slider mode is on.
    }
    ?&gt;</pre>
    </div>
  </div> -->

<?php endif; ?>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>