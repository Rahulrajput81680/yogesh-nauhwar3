<?php
/**
 * Events Management - Delete
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_once dirname(dirname(__DIR__)) . '/core/uploader.php';
require_login();
require_module('events');
require_permission('events_delete');

$id = (int) ($_GET['id'] ?? 0);
$csrf = $_GET['csrf'] ?? '';

if (!$id || !validate_csrf_token($csrf)) {
  set_flash('error', 'Invalid request.');
  redirect(ADMIN_URL . '/modules/events/index.php');
}

try {
  $stmt = $pdo->prepare('SELECT * FROM events WHERE id = ?');
  $stmt->execute([$id]);
  $event = $stmt->fetch();

  if (!$event) {
    set_flash('error', 'Event not found.');
    redirect(ADMIN_URL . '/modules/events/index.php');
  }

  if (!empty($event['image'])) {
    $uploader = new FileUploader();
    $uploader->delete($event['image']);
  }

  $del = $pdo->prepare('DELETE FROM events WHERE id = ?');
  $del->execute([$id]);

  log_activity('delete', 'events', $id, 'Deleted event: ' . $event['title']);
  set_flash('success', 'Event deleted successfully.');
} catch (PDOException $e) {
  set_flash('error', 'Failed to delete event: ' . $e->getMessage());
}

redirect(ADMIN_URL . '/modules/events/index.php');
