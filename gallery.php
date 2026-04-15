<?php require_once __DIR__ . '/components/frontend-init.php'; ?>
<!DOCTYPE html>
<html lang="<?php echo frontend_escape(frontend_current_lang()); ?>">

<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<title>Gallery — Yogesh Nauhwar MLC | Events, Public Meetings & Development Work Photos</title>
	<meta name="description"
		content="Explore the official photo gallery of Chaudhary Yogesh Nauhwar MLC — featuring public meetings, farmer events, rallies, development works, cultural celebrations, and Jan Sampark activities across Mant and Mathura.">
	<?php include 'components/links.php'; ?>
</head>

<body class="inner-page">
	<?php include 'components/loader.php'; ?>
	<?php include 'components/header.php'; ?>

	<?php
	$galleryItems = [];

	try {
		$pdo = frontend_db();
		$galleryItems = frontend_gallery_items($pdo, 'gallery');
	} catch (Throwable $e) {
		$galleryItems = [];
	}
	?>

	<main>
		<section class="breadcrumb-section">
			<div class="container-fluid">
				<div class="row g-0">
					<div class="col-xl-12 col-lg-12">
						<div class="breadcrumb-content">
							<div class="breadcrumb-nav" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
								<ul>
									<li><a href='index.php'>Home</a></li>
									<li><a href="#">Gallery</a></li>
								</ul>
							</div>
							<div class="breadcrumb-title" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="400">
								<h2>Photo Gallery</h2>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section class="gallery-collage-section p-t-100 p-b-120 p-t-xs-80 p-b-xs-80">
			<div class="container">
				<div class="row justify-content-center text-center m-b-50 m-b-xs-40">
					<div class="col-xl-8">
						<div class="common-subtitle" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
							<img src="assets/img/icons/wheat.png" alt="icon-2" class="wheat-icon" />
							<span>Visual Journey</span>
						</div>
						<div class="common-title m-b-0" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="400">
							<h2>Moments of Service and Public Connect</h2>
						</div>
					</div>
				</div>

				<div class="gallery-collage-grid" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="300">
					<?php if (empty($galleryItems)): ?>
						<p class="text-center w-100">No gallery images available right now.</p>
					<?php else: ?>
						<?php foreach ($galleryItems as $item): ?>
							<?php $img = frontend_upload_url($item['image']); ?>
							<a class="gallery-collage-item" href="<?php echo frontend_escape($img); ?>" data-fancybox="main-gallery">
								<img src="<?php echo frontend_escape($img); ?>"
									alt="<?php echo frontend_escape($item['title'] ?: 'Gallery image'); ?>" />
							</a>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>

				<!-- <div class="gallery-collage-grid gallery-collage-feature-grid" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="400">
				<a class="gallery-collage-item" href="assets/img/gallery/gallery4.jpg" data-fancybox="main-gallery">
					<img src="assets/img/gallery/gallery4.jpg" alt="Public event 11"/>
				</a>
				<a class="gallery-collage-item" href="assets/img/gallery/gallery5.jpg" data-fancybox="main-gallery">
					<img src="assets/img/gallery/gallery5.jpg" alt="Public event 12"/>
				</a>
				<a class="gallery-collage-item" href="assets/img/gallery/gallery6.jpg" data-fancybox="main-gallery">
					<img src="assets/img/gallery/gallery6.jpg" alt="Public event 13"/>
				</a>
				<a class="gallery-collage-item" href="assets/img/gallery/gallery7.jpg" data-fancybox="main-gallery">
					<img src="assets/img/gallery/gallery7.jpg" alt="Public event 14"/>
				</a>
			</div> -->
			</div>
		</section>
	</main>

	<?php include 'components/footer.php'; ?>
	<?php include 'components/script.php'; ?>
</body>

</html>