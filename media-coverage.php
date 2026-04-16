<?php require_once __DIR__ . '/components/frontend-init.php'; ?>
<!DOCTYPE html>
<html lang="<?php echo frontend_escape(frontend_current_lang()); ?>">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

	<title>Yogesh Nauhwar - Media Coverage</title>

	<?php include 'components/links.php'; ?>
</head>

<body class="inner-page">
	<?php include 'components/loader.php'; ?>
	<?php include 'components/header.php'; ?>

	<?php
	$galleryItems = [];

	try {
		$pdo = frontend_db();
		$galleryItems = frontend_gallery_items($pdo, 'media_coverage');
	} catch (Throwable $e) {
		$galleryItems = [];
	}
	?>

	<main>
		<!-- breadcrumb-section start -->
		<section class="breadcrumb-section">
			<div class="container-fluid">
				<div class="row g-0">
					<div class="col-xl-12 col-lg-12">
						<div class="breadcrumb-content">
							<div class="breadcrumb-nav" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
								<ul>
									<li><a href='index.php'>Home</a></li>
									<li><a href="#">Media Coverage</a></li>
								</ul>
							</div>
							<div class="breadcrumb-title" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="400">
								<h2>Media Coverage</h2>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- breadcrumb-section end -->

		<!-- services-section start -->
		<section class="services-section p-t-100 p-b-120">
			<div class="container">
				<div class="row justify-content-center text-center m-b-50 m-b-xs-40">
					<div class="col-xl-8">
						<div class="common-subtitle" data-aos="fade-up" data-aos-delay="600" data-aos-duration="1000">
							<img alt="icon-1" src="assets/img/icons/wheat.png" class="wheat-icon"> <span>Media Coverage</span>
						</div>
						<div class="common-title m-b-0" data-aos="fade-up" data-aos-delay="800" data-aos-duration="1000">
							<h2>Our Work, Covered by the Press</h2>
						</div>
					</div>
				</div>
			</div>
			<div class="container">
				<div class="row equal-height-card-row" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
					<?php if (empty($galleryItems)): ?>
						<div class="col-12 text-center">
							<p>No media coverage images available right now.</p>
						</div>
					<?php else: ?>
						<?php foreach ($galleryItems as $item): ?>
							<div class="col-xl-4 col-md-6 m-b-30">
								<div class="project-card style-2 style-service">
									<div class="thumb">
										<a href="<?php echo frontend_escape(frontend_upload_url($item['image'])); ?>"
											data-fancybox="media-coverage-gallery">
											<img alt="<?php echo frontend_escape($item['title'] ?: 'Media image'); ?>"
												src="<?php echo frontend_escape(frontend_upload_url($item['image'])); ?>">
										</a>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<!-- services-section end -->
	</main>

	<?php include 'components/footer.php'; ?>
	<?php include 'components/script.php'; ?>
</body>

</html>