<?php
/**
 * Tour Management - Delete / Restore
 *
 * ?action=delete  (default) – hard delete (removes record + featured image)
 * ?action=restore            – restore from trash (legacy compatibility)
 * ?action=purge              – alias of hard delete
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_once dirname(dirname(__DIR__)) . '/core/uploader.php';
require_login();
require_module('tours');

$tour_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$action  = in_array($_GET['action'] ?? '', ['delete', 'restore', 'purge'])
         ? $_GET['action'] : 'delete';

if ($action === 'restore') {
    require_permission('tours_restore');
} else {
    require_permission('tours_delete');
}

if (!validate_csrf_token($_GET['csrf'] ?? '')) {
    set_flash('error', 'Invalid security token.');
    redirect(ADMIN_URL . '/modules/tours/index.php');
}

if (!$tour_id) {
    set_flash('error', 'Invalid tour ID.');
    redirect(ADMIN_URL . '/modules/tours/index.php');
}

try {
    $stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ?");
    $stmt->execute([$tour_id]);
    $tour = $stmt->fetch();
} catch (PDOException $e) {
    set_flash('error', 'Database error.');
    redirect(ADMIN_URL . '/modules/tours/index.php');
}

if (!$tour) {
    set_flash('error', 'Tour not found.');
    redirect(ADMIN_URL . '/modules/tours/index.php');
}

try {
    if ($action === 'restore') {
        $pdo->prepare("UPDATE tours SET deleted_at = NULL WHERE id = ?")
            ->execute([$tour_id]);
        log_activity('restore', 'tours', $tour_id, "Restored tour: {$tour['title']}");
        set_flash('success', 'Tour restored successfully.');
        redirect(ADMIN_URL . '/modules/tours/index.php?view=trash');

    } elseif ($action === 'delete' || $action === 'purge') {
        // Delete featured image
        if (!empty($tour['featured_image'])) {
            $uploader = new FileUploader();
            $uploader->delete($tour['featured_image']);
        }
        // Delete tour gallery images from disk
        $galleryStmt = $pdo->prepare("SELECT image FROM tour_gallery WHERE tour_id = ?");
        $galleryStmt->execute([$tour_id]);
        $galleryImages = $galleryStmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($galleryImages as $img) {
            if (!empty($img)) {
                $imgPath = UPLOAD_DIR . '/' . $img;
                if (file_exists($imgPath)) {
                    @unlink($imgPath);
                }
            }
        }
        // Cascade deletes via foreign keys (highlights, itinerary, attributes, tabs, gallery)
        $pdo->prepare("DELETE FROM tours WHERE id = ?")
            ->execute([$tour_id]);
        log_activity('delete', 'tours', $tour_id, "Deleted tour: {$tour['title']}");
        set_flash('success', 'Tour deleted successfully.');
        redirect(ADMIN_URL . '/modules/tours/index.php');
    }
} catch (PDOException $e) {
    set_flash('error', 'Action failed: ' . $e->getMessage());
    redirect(ADMIN_URL . '/modules/tours/index.php');
}
