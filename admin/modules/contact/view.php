<?php
/**
 * Contact Messages - View Full Message
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_login();
require_module('contact');

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
  set_flash('error', 'Invalid message ID.');
  redirect(ADMIN_URL . '/modules/contact/index.php');
}

try {
  $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ? LIMIT 1");
  $stmt->execute([$id]);
  $msg = $stmt->fetch();
} catch (PDOException $e) {
  set_flash('error', 'Could not load message.');
  redirect(ADMIN_URL . '/modules/contact/index.php');
}

if (!$msg) {
  set_flash('error', 'Message not found.');
  redirect(ADMIN_URL . '/modules/contact/index.php');
}

// Mark as read automatically
if ($msg['status'] === 'unread') {
  try {
    $pdo->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?")->execute([$id]);
    $msg['status'] = 'read';
    log_activity('read', 'contact', $id, 'Viewed message from ' . $msg['email']);
  } catch (PDOException $e) {
    // Silently continue
  }
}

$page_title = 'View Message – ' . escape($msg['name']);

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
  <h1><i class="bi bi-envelope-open me-2"></i>Message Details</h1>
  <a href="<?php echo ADMIN_URL; ?>/modules/contact/index.php" class="btn btn-secondary">
    <i class="bi bi-arrow-left me-2"></i>Back to Messages
  </a>
</div>

<div class="row">
  <div class="col-lg-8 mx-auto">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><?php echo escape($msg['subject'] ?: '(No subject)'); ?></h5>
        <span class="badge bg-<?php echo $msg['status'] === 'unread' ? 'danger' : 'secondary'; ?>">
          <?php echo ucfirst($msg['status']); ?>
        </span>
      </div>
      <div class="card-body">

        <!-- Sender info -->
        <div class="row g-3 mb-4 pb-4 border-bottom">
          <div class="col-sm-6">
            <span class="text-muted small fw-bold text-uppercase d-block">Name</span>
            <span><?php echo escape($msg['name']); ?></span>
          </div>
          <div class="col-sm-6">
            <span class="text-muted small fw-bold text-uppercase d-block">Email</span>
            <a href="mailto:<?php echo escape($msg['email']); ?>">
              <?php echo escape($msg['email']); ?>
            </a>
          </div>
          <?php if ($msg['phone']): ?>
            <div class="col-sm-6">
              <span class="text-muted small fw-bold text-uppercase d-block">Phone</span>
              <a href="tel:<?php echo escape($msg['phone']); ?>">
                <?php echo escape($msg['phone']); ?>
              </a>
            </div>
          <?php endif; ?>
          <?php if (!empty($msg['location'] ?? '')): ?>
            <div class="col-sm-6">
              <span class="text-muted small fw-bold text-uppercase d-block">Current Location</span>
              <span><?php echo escape($msg['location'] ?? ''); ?></span>
            </div>
          <?php endif; ?>
          <?php if (!empty($msg['date_of_birth'] ?? '')): ?>
            <div class="col-sm-6">
              <span class="text-muted small fw-bold text-uppercase d-block">Date of Birth</span>
              <span><?php echo escape($msg['date_of_birth'] ?? ''); ?></span>
            </div>
          <?php endif; ?>
          <?php if (!empty($msg['occupation'] ?? '')): ?>
            <div class="col-sm-6">
              <span class="text-muted small fw-bold text-uppercase d-block">Occupation</span>
              <span><?php echo escape($msg['occupation'] ?? ''); ?></span>
            </div>
          <?php endif; ?>
          <div class="col-sm-6">
            <span class="text-muted small fw-bold text-uppercase d-block">Submitted</span>
            <span><?php echo format_datetime($msg['created_at']); ?></span>
          </div>
          <?php if ($msg['ip_address']): ?>
            <div class="col-sm-6">
              <span class="text-muted small fw-bold text-uppercase d-block">IP Address</span>
              <span class="text-muted"><?php echo escape($msg['ip_address']); ?></span>
            </div>
          <?php endif; ?>
        </div>

        <!-- Message body -->
        <div class="mb-4">
          <span class="text-muted small fw-bold text-uppercase d-block mb-2">Message</span>
          <div class="bg-light rounded p-3" style="white-space:pre-wrap; line-height:1.7;">
            <?php echo escape($msg['message']); ?>
          </div>
        </div>

        <!-- Actions -->
        <div class="d-flex gap-2">
          <a href="mailto:<?php echo escape($msg['email']); ?>?subject=Re: <?php echo rawurlencode($msg['subject'] ?? ''); ?>"
            class="btn">
            <i class="bi bi-reply me-2"></i>Reply via Email
          </a>
          <a href="<?php echo ADMIN_URL; ?>/modules/contact/delete.php?id=<?php echo $msg['id']; ?>&csrf=<?php echo generate_csrf_token(); ?>"
            class="btn  delete-btn">
            <i class="bi bi-trash me-2"></i>Delete Message
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>