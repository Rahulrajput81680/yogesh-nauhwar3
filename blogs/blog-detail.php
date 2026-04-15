<?php
require_once __DIR__ . '/../components/frontend-init.php';

$blog = null;
$slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

try {
	$pdo = frontend_db();
	$hasDeletedAt = frontend_has_column($pdo, 'blogs', 'deleted_at');
	$where = "status = 'published'";
	if ($hasDeletedAt) {
		$where .= ' AND deleted_at IS NULL';
	}

	if ($slug !== '') {
		$stmt = $pdo->prepare("SELECT * FROM blogs WHERE {$where} AND slug = ? LIMIT 1");
		$stmt->execute([$slug]);
		$blog = $stmt->fetch(PDO::FETCH_ASSOC);
	} elseif ($id > 0) {
		$stmt = $pdo->prepare("SELECT * FROM blogs WHERE {$where} AND id = ? LIMIT 1");
		$stmt->execute([$id]);
		$blog = $stmt->fetch(PDO::FETCH_ASSOC);
	}

	if (!$blog) {
		$stmt = $pdo->query("SELECT * FROM blogs WHERE {$where} ORDER BY created_at DESC LIMIT 1");
		$blog = $stmt->fetch(PDO::FETCH_ASSOC);
	}
} catch (Throwable $e) {
	$blog = null;
}

$blogTitle = $blog['title'] ?? 'Blog Details';
$blogCategory = $blog['category'] ?? 'General';
$blogImage = !empty($blog['thumbnail']) ? frontend_upload_url($blog['thumbnail']) : 'assets/img/thumbs/thumb-148.webp';
$blogContent = $blog['content'] ?? '<p>Blog content is not available.</p>';
$metaTitle = $blog['seo_title'] ?? $blogTitle;
$metaDescription = trim((string) ($blog['seo_description'] ?? '')) !== '' ? $blog['seo_description'] : ($blogContent);
$metaKeywords = $blog['meta_keywords'] ?? '';
?>
<!DOCTYPE html>
<html lang="<?php echo frontend_escape(frontend_current_lang()); ?>">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo frontend_display_text($metaTitle); ?></title>
	<meta name="description" content="<?php echo frontend_escape(($metaDescription)); ?>">
	<?php if (trim((string) $metaKeywords) !== ''): ?>
		<meta name="keywords" content="<?php echo ($metaKeywords); ?>">
	<?php endif; ?>
	<base href="../">
	<?php include '../components/links.php'; ?>
</head>

<body class="inner-page">
	<?php include '../components/loader.php'; ?>
	<?php include '../components/header.php'; ?>

	<main>
		<section class="breadcrumb-section">
			<div class="container-fluid">
				<div class="row g-0">
					<div class="col-xl-12 col-lg-12">
						<div class="breadcrumb-content">
							<div class="breadcrumb-nav" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
								<ul>
									<li><a href='index.php'>Home</a></li>
									<li><a href="#">Blog-detail</a></li>
								</ul>
							</div>
							<div class="breadcrumb-title" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="400">
								<h2>Blog Details</h2>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section class="services-details-section p-t-120 p-b-250 p-t-lg-80 p-t-md-80 p-t-xs-60">
			<div class="container">
				<div class="row">
					<div class="col-xl-12">
						<div class="details-layout-wrap">
							<div class="details-content" data-aos="fade-up" data-aos-delay="600" data-aos-duration="1000">
								<div class="blog-card-5 m-b-40">
									<div class="thumb">
										<a href='blogs/index.php'><img alt="thumb-1" src="<?php echo ($blogImage); ?>"></a>
									</div>
									<div class="content">
										<div class="blog-info">
											<!-- <div class="comment">
												<i class="fa-light fa-bookmark"></i>
												<p><?php echo ($blogCategory); ?></p>
											</div> -->
										</div>
										<div class="title">
											<h3><a href='blogs/index.php'><?php echo ($blogTitle); ?></a></h3>
										</div>
										<div class="text">
											<?php echo html_entity_decode($blogContent, ENT_QUOTES, 'UTF-8'); ?>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</main>

	<?php include '../components/footer.php'; ?>
	<?php include '../components/script.php'; ?>
</body>

</html>