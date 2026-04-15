<?php
/**
 * Tour Gallery — upload / manage multiple images per tour.
 *
 * GET  ?tour_id=X               – gallery grid + upload form
 * GET  ?tour_id=X&delete_id=Y&csrf=… – delete single image
 * POST ?tour_id=X               – upload new image(s)
 * POST ?tour_id=X&update_caption=Y – update caption for a single image
 */

require_once dirname(dirname(__DIR__)) . '/init.php';
require_once dirname(dirname(__DIR__)) . '/core/uploader.php';
require_login();
require_module('tours');
require_permission('tours_edit');

$tour_id = isset($_GET['tour_id']) ? (int) $_GET['tour_id'] : 0;
if (!$tour_id) {
    set_flash('error', 'Invalid tour ID.');
    redirect(ADMIN_URL . '/modules/tours/index.php');
}

try {
    $tStmt = $pdo->prepare("SELECT id, title FROM tours WHERE id = ? AND deleted_at IS NULL");
    $tStmt->execute([$tour_id]);
    $tour = $tStmt->fetch();
} catch (PDOException $e) {
    set_flash('error', 'Database error.');
    redirect(ADMIN_URL . '/modules/tours/index.php');
}

if (!$tour) {
    set_flash('error', 'Tour not found.');
    redirect(ADMIN_URL . '/modules/tours/index.php');
}

$page_title = 'Gallery — ' . $tour['title'];
$tour_title = $tour['title'];
$active_tab = 'gallery';
$errors     = [];

// --- DELETE ---
if (isset($_GET['delete_id'])) {
    $del_id = (int) $_GET['delete_id'];
    if (!validate_csrf_token($_GET['csrf'] ?? '')) {
        set_flash('error', 'Invalid security token.');
        redirect(ADMIN_URL . '/modules/tours/gallery.php?tour_id=' . $tour_id);
    }
    try {
        $imgStmt = $pdo->prepare("SELECT image FROM tour_gallery WHERE id = ? AND tour_id = ?");
        $imgStmt->execute([$del_id, $tour_id]);
        $imgRow = $imgStmt->fetch();

        if ($imgRow) {
            $imgPath = UPLOAD_DIR . '/' . $imgRow['image'];
            if (file_exists($imgPath)) {
                @unlink($imgPath);
            }
            $pdo->prepare("DELETE FROM tour_gallery WHERE id = ? AND tour_id = ?")
                ->execute([$del_id, $tour_id]);
            log_activity('delete', 'tour_gallery', $del_id, "Deleted gallery image from tour #{$tour_id}");
            set_flash('success', 'Image deleted.');
        }
    } catch (PDOException $e) {
        set_flash('error', 'Could not delete image.');
    }
    redirect(ADMIN_URL . '/modules/tours/gallery.php?tour_id=' . $tour_id);
}

// --- POST: upload or update caption ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {

        // Update caption for existing image
        if (!empty($_POST['update_caption_id'])) {
            $cap_id      = (int) $_POST['update_caption_id'];
            $caption     = sanitize_input($_POST['caption_' . $cap_id] ?? '');
            $sort_order  = (int) ($_POST['sort_order_' . $cap_id] ?? 0);
            try {
                $pdo->prepare("UPDATE tour_gallery SET caption = ?, sort_order = ? WHERE id = ? AND tour_id = ?")
                    ->execute([$caption, $sort_order, $cap_id, $tour_id]);
                set_flash('success', 'Caption updated.');
            } catch (PDOException $e) {
                set_flash('error', 'Could not update caption.');
            }
            redirect(ADMIN_URL . '/modules/tours/gallery.php?tour_id=' . $tour_id);
        }

        // Upload new images
        if (isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
            $uploader     = new FileUploader();
            $uploadedCount = 0;
            $fileCount    = count($_FILES['gallery_images']['name']);

            // Re-index for loop
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['gallery_images']['error'][$i] === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                $singleFile = [
                    'name'     => $_FILES['gallery_images']['name'][$i],
                    'type'     => $_FILES['gallery_images']['type'][$i],
                    'tmp_name' => $_FILES['gallery_images']['tmp_name'][$i],
                    'error'    => $_FILES['gallery_images']['error'][$i],
                    'size'     => $_FILES['gallery_images']['size'][$i],
                ];
                $filename = $uploader->upload($singleFile, 'tours/gallery');
                if ($filename) {
                    $sort_order = (int) ($_POST['sort_start'] ?? 0) + $i;
                    $caption    = sanitize_input($_POST['captions'][$i] ?? '');
                    try {
                        $pdo->prepare("INSERT INTO tour_gallery (tour_id, image, caption, sort_order, created_at) VALUES (?, ?, ?, ?, NOW())")
                            ->execute([$tour_id, $filename, $caption, $sort_order]);
                        $uploadedCount++;
                    } catch (PDOException $e) {
                        $errors[] = 'DB error for ' . escape($singleFile['name']) . ': ' . $e->getMessage();
                    }
                } else {
                    foreach ($uploader->getErrors() as $ue) {
                        $errors[] = escape($singleFile['name']) . ': ' . $ue;
                    }
                }
            }

            if ($uploadedCount > 0) {
                log_activity('create', 'tour_gallery', null, "Uploaded {$uploadedCount} image(s) to tour #{$tour_id}");
                if (empty($errors)) {
                    set_flash('success', "{$uploadedCount} image(s) uploaded successfully.");
                    redirect(ADMIN_URL . '/modules/tours/gallery.php?tour_id=' . $tour_id);
                } else {
                    set_flash('warning', "{$uploadedCount} uploaded, but some errors occurred.");
                }
            } elseif (empty($errors)) {
                $errors[] = 'No images were uploaded. Please select at least one image.';
            }
        } else {
            $errors[] = 'Please select at least one image to upload.';
        }
    }
}

// --- Load gallery images ---
try {
    $galStmt = $pdo->prepare("SELECT * FROM tour_gallery WHERE tour_id = ? ORDER BY sort_order ASC, id ASC");
    $galStmt->execute([$tour_id]);
    $gallery = $galStmt->fetchAll();
} catch (PDOException $e) {
    $gallery = [];
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header">
    <h1><i class="bi bi-images me-2"></i>Tour Gallery</h1>
</div>

<?php include __DIR__ . '/_tour_nav.php'; ?>

<!-- Upload Form -->
<div class="card mb-4">
    <div class="card-header"><strong>Upload Images</strong></div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <strong>Upload errors:</strong>
                <ul class="mb-0 mt-1">
                    <?php foreach ($errors as $e): ?><li><?php echo $e; ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Select Images <span class="text-danger">*</span></label>
                    <input type="file" name="gallery_images[]" class="form-control" accept="image/webp"
                           multiple id="galleryFilePicker" data-no-generic-preview="1">
                    <small class="text-muted">
                        You can select multiple files at once.
                        WebP only. Max <?php echo format_file_size(MAX_UPLOAD_SIZE); ?> each.
                    </small>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Start Sort Order</label>
                    <input type="number" name="sort_start" class="form-control" min="0"
                           value="<?php echo count($gallery) * 10; ?>">
                    <small class="text-muted">First uploaded image gets this order value.</small>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-cloud-upload me-2"></i>Upload
                    </button>
                </div>
            </div>

            <!-- Preview container -->
            <div id="galleryPreviewContainer" class="row g-2 mt-2 d-none"></div>
        </form>
    </div>
</div>

<!-- Gallery Grid -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Gallery Images <span class="badge bg-secondary ms-1"><?php echo count($gallery); ?></span></strong>
    </div>
    <div class="card-body">
        <?php if (empty($gallery)): ?>
            <p class="text-muted text-center py-4 mb-0">
                <i class="bi bi-images d-block fs-2 mb-2"></i>No gallery images yet.
            </p>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($gallery as $gimg): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card h-100 shadow-sm">
                            <img src="<?php echo UPLOAD_URL . '/tours/gallery/' . escape($gimg['image']); ?>"
                                 alt="<?php echo escape($gimg['caption'] ?? ''); ?>"
                                 class="card-img-top"
                                 style="height:150px;object-fit:cover">
                            <div class="card-body p-2">
                                <form method="POST" action="">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="update_caption_id" value="<?php echo $gimg['id']; ?>">
                                    <input type="text"
                                           name="caption_<?php echo $gimg['id']; ?>"
                                           class="form-control form-control-sm mb-1"
                                           value="<?php echo escape($gimg['caption'] ?? ''); ?>"
                                           placeholder="Caption (optional)">
                                    <input type="number"
                                           name="sort_order_<?php echo $gimg['id']; ?>"
                                           class="form-control form-control-sm mb-2"
                                           value="<?php echo (int)$gimg['sort_order']; ?>"
                                           placeholder="Order">
                                    <div class="d-flex gap-1">
                                        <button type="submit" class="btn btn-sm btn-outline-primary flex-fill"
                                                title="Save caption & order">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <a href="gallery.php?tour_id=<?php echo $tour_id; ?>&delete_id=<?php echo $gimg['id']; ?>&csrf=<?php echo generate_csrf_token(); ?>"
                                           class="btn btn-sm btn-outline-danger" title="Delete"
                                           onclick="return confirm('Delete this image?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$extra_js = <<<JS
<script>
document.getElementById('galleryFilePicker').addEventListener('change', function () {
    var container = document.getElementById('galleryPreviewContainer');
    container.innerHTML = '';
    container.classList.add('d-none');
    if (!this.files || this.files.length === 0) return;
    container.classList.remove('d-none');
    Array.from(this.files).forEach(function (file) {
        var reader = new FileReader();
        reader.onload = function (e) {
            var col = document.createElement('div');
            col.className = 'col-4 col-md-2';
            col.innerHTML = '<img src="' + e.target.result + '" class="img-fluid rounded" style="height:80px;object-fit:cover;width:100%">';
            container.appendChild(col);
        };
        reader.readAsDataURL(file);
    });
});
</script>
JS;
?>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
