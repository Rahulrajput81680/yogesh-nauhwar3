<?php require_once __DIR__ . '/components/frontend-init.php'; ?>
<!DOCTYPE html>
<html lang="<?php echo frontend_escape(frontend_current_lang()); ?>">

<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<title>Our Work — Yogesh Nauhwar MLC | Development Works & Achievements Mant Mathura</title>
	<meta name="description"
		content="Explore the development works and achievements of Chaudhary Yogesh Nauhwar MLC — including ₹14.41 crore road projects, farmer welfare programs, youth skill development, and rural infrastructure works in Mant Mathura.">
	<?php include 'components/links.php'; ?>
</head>

<body class="inner-page">
	<?php include 'components/loader.php'; ?>
	<?php include 'components/header.php'; ?>

	<?php
	$galleryItems = [];

	try {
		$pdo = frontend_db();
		$galleryItems = frontend_gallery_items($pdo, 'our_work');
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
									<li><a href="#">Our Work</a></li>
								</ul>
							</div>
							<div class="breadcrumb-title" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="400">
								<h2>Our Work</h2>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section class="services-section bg-cream p-t-100 p-b-70">
			<div class="container">
				<div class="row justify-content-center text-center m-b-50 m-b-xs-40">
					<div class="col-xl-6">
						<div class="common-subtitle" data-aos="fade-up" data-aos-delay="600" data-aos-duration="1000">
							<img alt="icon-1" src="assets/img/icons/wheat.png" class="wheat-icon"> <span>Our Work</span>
						</div>
						<div class="common-title m-b-0" data-aos="fade-up" data-aos-delay="800" data-aos-duration="1000">
							<h2>Real Work, Real Results</h2>
						</div>
					</div>
				</div>
			</div>
			<div class="container">
				<div class="row equal-height-card-row" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
					<?php if (empty($galleryItems)): ?>
						<div class="col-12 text-center">
							<p>No work images available right now.</p>
						</div>
					<?php else: ?>
						<?php foreach ($galleryItems as $item): ?>
							<div class="col-xl-4 col-md-6 m-b-30">
								<div class="project-card style-service">
									<div class="thumb">
										<a href="<?php echo frontend_escape(frontend_upload_url($item['image'])); ?>"
											data-fancybox="our-work-gallery">
											<img alt="<?php echo frontend_escape($item['title'] ?: 'Work image'); ?>"
												src="<?php echo frontend_escape(frontend_upload_url($item['image'])); ?>">
										</a>
									</div>
									<!-- <div class="content pt-3">
									<h5><?php echo frontend_display_text($item['title'] ?: 'Work Image'); ?></h5>
									<?php if (!empty($item['category'])): ?>
										<p><?php echo frontend_display_text($item['category']); ?></p>
									<?php endif; ?>
								</div> -->
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
			<!-- <div class="c-shape-1"><img alt="shape-30" src="assets/img/shapes/shape-30.webp"></div>
		<div class="c-shape-2"><img alt="shape-31" src="assets/img/shapes/shape-31.webp"></div> -->
		</section>
	</main>

	<?php include 'components/footer.php'; ?>
	<?php include 'components/script.php'; ?>
</body>

</html>