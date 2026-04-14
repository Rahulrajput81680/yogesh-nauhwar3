<?php
/**
 * User Management – Soft-Delete / Deactivate User
 *
 * Rather than permanently removing an account we mark it deleted_at
 * so the record can be recovered by a superadmin later.
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_login();
require_module('roles');
require_permission('users_delete');

$user_id = (int)($_GET['id']   ?? 0);
$csrf    = $_GET['csrf'] ?? '';

if (!$user_id || !validate_csrf_token($csrf)) {
    set_flash('error', 'Invalid request.');
    redirect(ADMIN_URL . '/modules/users/index.php');
}

// Cannot delete yourself
if ((int)$user_id === (int)$_SESSION['admin_id']) {
    set_flash('error', 'You cannot delete your own account.');
    redirect(ADMIN_URL . '/modules/users/index.php');
}

try {
    $stmt = $pdo->prepare("SELECT id, username, role FROM admin_users WHERE id = ? AND deleted_at IS NULL LIMIT 1");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        set_flash('error', 'User not found.');
        redirect(ADMIN_URL . '/modules/users/index.php');
    }

    // Prevent deleting the last superadmin
    if ($user['role'] === 'superadmin') {
        $superadminCount = (int)$pdo->query(
            "SELECT COUNT(*) FROM admin_users WHERE role = 'superadmin' AND deleted_at IS NULL"
        )->fetchColumn();
        if ($superadminCount <= 1) {
            set_flash('error', 'Cannot delete the only Super Admin account.');
            redirect(ADMIN_URL . '/modules/users/index.php');
        }
    }

    // Soft delete: set deleted_at timestamp, deactivate account
    $pdo->prepare("UPDATE admin_users SET deleted_at = NOW(), status = 'inactive', updated_at = NOW() WHERE id = ?")
        ->execute([$user_id]);

    log_activity('delete', 'users', $user_id, "Soft-deleted user: {$user['username']}");
    set_flash('success', "User <strong>" . escape($user['username']) . "</strong> has been deleted.");

} catch (PDOException $e) {
    set_flash('error', 'Failed to delete user: ' . $e->getMessage());
}

redirect(ADMIN_URL . '/modules/users/index.php');
