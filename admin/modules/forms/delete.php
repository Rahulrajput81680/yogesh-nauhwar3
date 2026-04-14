<?php
/**
 * Form Builder - Delete Form
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_login();
require_module('forms');
require_permission('forms_delete');

$id   = (int)($_GET['id'] ?? 0);
$csrf = $_GET['csrf'] ?? '';

if (!$id || !validate_csrf_token($csrf)) {
    set_flash('error', 'Invalid request.');
    redirect(ADMIN_URL . '/modules/forms/index.php');
}

try {
    $stmt = $pdo->prepare("SELECT name FROM forms WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $form = $stmt->fetch();

    if (!$form) {
        set_flash('error', 'Form not found.');
        redirect(ADMIN_URL . '/modules/forms/index.php');
    }

    // Delete form (fields + submissions are ON DELETE CASCADE)
    $pdo->prepare("DELETE FROM forms WHERE id = ?")->execute([$id]);

    // Also clean up any uploaded files in /uploads/forms/{form_id}/
    $dir = UPLOAD_DIR . '/forms/' . $id;
    if (is_dir($dir)) {
        foreach (glob($dir . '/*') as $file) {
            @unlink($file);
        }
        @rmdir($dir);
    }

    log_activity('delete', 'forms', $id, 'Deleted form: ' . $form['name']);
    set_flash('success', 'Form "' . $form['name'] . '" deleted successfully.');
} catch (PDOException $e) {
    set_flash('error', 'Failed to delete form: ' . $e->getMessage());
}

redirect(ADMIN_URL . '/modules/forms/index.php');
