<?php
/**
 * Contact Messages - Delete
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_login();
require_module('contact');
require_permission('contact_delete');

$id   = (int)($_GET['id'] ?? 0);
$csrf = $_GET['csrf'] ?? '';

if (!$id || !validate_csrf_token($csrf)) {
    set_flash('error', 'Invalid request.');
    redirect(ADMIN_URL . '/modules/contact/index.php');
}

try {
    $stmt = $pdo->prepare("SELECT id, name FROM contact_messages WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $msg = $stmt->fetch();

    if (!$msg) {
        set_flash('error', 'Message not found.');
        redirect(ADMIN_URL . '/modules/contact/index.php');
    }

    $pdo->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$id]);
    log_activity('delete', 'contact', $id, 'Deleted contact message from ' . $msg['name']);
    set_flash('success', 'Message deleted successfully.');
} catch (PDOException $e) {
    set_flash('error', 'Failed to delete message: ' . $e->getMessage());
}

redirect(ADMIN_URL . '/modules/contact/index.php');
