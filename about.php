<?php
require_once __DIR__ . '/components/frontend-init.php';

$lang = frontend_current_lang();
$contentText = static function (string $path) {
	$value = frontend_content($path);
	return (is_string($value) && $value !== '') ? $value : '';
};
$contentArray = static function (string $path): array {
	$value = frontend_content($path);
	return is_array($value) ? $value : [];
};
$contentList = static function (string $path) use ($lang): array {
	return frontend_content_list($path, $lang);
};
?>
<!DOCTYPE html>
<html lang="<?php echo frontend_escape(frontend_current_lang()); ?>">
<head>
	<!-- Required meta tags -->
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>

	<title><?php echo frontend_escape($contentText('about.meta.title')); ?></title>
	<meta name="description" content="<?php echo frontend_escape($contentText('about.meta.description')); ?>">

	<?php include 'components/links.php'; ?>
</head>

<body class="inner-page">
<?php include 'components/loader.php'; ?>
<?php include 'components/header.php'; ?>

<main>
	<!-- breadcrumb-section start -->
	<section class="breadcrumb-section">
		<div class="container-fluid">
			<div class="row g-0">
				<div class="col-xl-12 col-lg-12">
					<div class="breadcrumb-content">
						<div class="breadcrumb-nav" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
							<ul>
								<li><a href='index.html'><?php echo frontend_escape($contentText('about.breadcrumb.home')); ?></a></li>
								<li><a href="#"><?php echo frontend_escape($contentText('about.breadcrumb.about')); ?></a></li>
							</ul>
						</div>
						<div class="breadcrumb-title" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="400">
							<h2><?php echo frontend_escape($contentText('about.breadcrumb.title')); ?></h2>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- breadcrumb-section end -->

	<!-- why-us-section start -->
	<section class="why-us-section-4 p-t-120 p-b-100 p-t-md-100 p-t-xs-80 p-b-xs-80">
		<div class="container">
			<div class="row row-gap-5 align-items-center">
				<div class="col-xl-6">
					<div class="thumb" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
						<div class="shape-wrapped-thumb-2">
							<div class="thumb-wrapper"> <img alt="thumb" src="assets/img/about/about1.jpeg"></div>
							<!-- <div class="positioned-thumb"><img alt="thumb" src="assets/img/about/about2.jpg"></div> -->
							<div class="shape-1"><img alt="shape" src="assets/img/shapes/shape-22.webp"></div>
							<div class="shape-2"><img alt="shape" src="assets/img/shapes/shape-23.webp"></div>
						</div>
					</div>
				</div>
				<div class="col-xl-6">
					<div class="why-us-content-2" data-aos="fade-up" data-aos-delay="400" data-aos-duration="1000">
						<div class="common-subtitle text-uppercase">
							<span><?php echo frontend_escape($contentText('about.whyUs.subtitle')); ?></span>
						</div>
						<div class="common-title text-start">
							<h2><?php echo frontend_escape($contentText('about.whyUs.title')); ?></h2>
						</div>
						<div class="text">
							<p><?php echo frontend_escape($contentText('about.whyUs.description')); ?></p>
						</div>
						<div class="services">
							<?php
							$whyUsPoints = $contentList('about.whyUs.points');
							$leftPoints = array_slice($whyUsPoints, 0, 2);
							$rightPoints = array_slice($whyUsPoints, 2);
							?>
							<div class="service-left">
								<?php foreach ($leftPoints as $point): ?>
									<div class="service">
										<i class="fa-solid fa-check"></i>
										<p><?php echo frontend_escape($point); ?></p>
									</div>
								<?php endforeach; ?>
							</div>
							<div class="service-right">
								<?php foreach ($rightPoints as $point): ?>
									<div class="service">
										<i class="fa-solid fa-check"></i>
										<p><?php echo frontend_escape($point); ?></p>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
						<div class="annual-donation-wrap">
							<a class='e-primary-btn has-icon' href='contact.php'><?php echo frontend_escape($contentText('about.whyUs.cta')); ?> <span class="icon-wrap"><span class="icon"><i class="fa-regular fa-arrow-right"></i> <i class="fa-regular fa-arrow-right"></i></span></span></a>
							<!-- <div class="annual-donation">
								<img alt="icon-4" src="assets/img/icons/icon-4.svg">
								<div class="annual-text">
									<p>Annual Donation</p>
									<h5>$2,000,00</h5>
								</div>
							</div> -->
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- <div class="shape"><img alt="shape" src="assets/img/shapes/shape-40.webp"></div> -->
		<!-- <div class="icon-1"><img alt="icon-1" data-aos="zoom-in" data-aos-delay="400" data-aos-duration="1000" src="assets/img/icons/icon-14.svg"></div> -->
		<!-- <div class="icon-2"><img alt="icon-2" data-aos="zoom-in" data-aos-delay="400" data-aos-duration="1000" src="assets/img/icons/icon-16.svg"></div> -->
	</section>
	<!-- why-us-section end -->
	</div>

	<!-- about-us-section-3 start -->
	<section class="about-us-section-3 p-t-120 p-b-120 p-t-md-100 p-b-md-100 p-t-xs-80 p-b-xs-80">
		<div class="container">
			<div class="row align-items-center justify-content-center">
				<div class="col-xl-6">
					<div class="about-us-content" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
						<div class="common-subtitle">
							<img src="assets/img/icons/wheat.png" alt="icon-2" class="wheat-icon"/>
							<span><?php echo frontend_escape($contentText('about.history.subtitle')); ?></span>
						</div>
						<div class="common-title text-start">
							<h2><?php echo frontend_escape($contentText('about.history.title')); ?></h2>
						</div>
						<div class="c-tabs-wrapper">
							<ul class="nav nav-tabs" id="myTab" role="tablist">
								<li class="nav-item" role="presentation">
									<button class="nav-link active" id="c-tab-1" data-bs-toggle="tab" data-bs-target="#c-tab-1-pane" type="button" role="tab" aria-controls="c-tab-1-pane" aria-selected="true">
										2007
									</button>
								</li>
								<li class="nav-item" role="presentation">
									<button class="nav-link" id="c-tab-2" data-bs-toggle="tab" data-bs-target="#c-tab-2-pane" type="button" role="tab" aria-controls="c-tab-2-pane" aria-selected="false">
										2012
									</button>
								</li>
								<li class="nav-item" role="presentation">
									<button class="nav-link" id="c-tab-3" data-bs-toggle="tab" data-bs-target="#c-tab-3-pane" type="button" role="tab" aria-controls="c-tab-3-pane" aria-selected="false">
										2017
									</button>
								</li>
								<li class="nav-item" role="presentation">
									<button class="nav-link" id="c-tab-4" data-bs-toggle="tab" data-bs-target="#c-tab-4-pane" type="button" role="tab" aria-controls="c-tab-4-pane" aria-selected="false">
										2022
									</button>
								</li>
								<li class="nav-item" role="presentation">
									<button class="nav-link" id="c-tab-5" data-bs-toggle="tab" data-bs-target="#c-tab-5-pane" type="button" role="tab" aria-controls="c-tab-5-pane" aria-selected="false">
										2024
									</button>
								</li>
							</ul>
							<div class="tab-content" id="myTabContent">
								<div class="tab-pane fade show active" id="c-tab-1-pane" role="tabpanel" aria-labelledby="c-tab-1" tabindex="0">
									<div class="tab-content">
										<div class="year">
											<h6><?php echo frontend_escape($contentArray('about.history.timeline')[0]['year'] ?? ''); ?></h6>
										</div>
										<div class="reward">
											<h5><?php echo frontend_escape($contentArray('about.history.timeline')[0]['title'][$lang] ?? ''); ?></h5>
										</div>
										<div class="text">
											<p><?php echo nl2br(frontend_escape($contentArray('about.history.timeline')[0]['description'][$lang] ?? '')); ?></p>
										</div>
										<div class="annual-donation-wrap">
											<a class='e-primary-btn has-icon' href='contact.php'>
												<?php echo frontend_escape($contentText('about.history.cta')); ?>
												<span class="icon-wrap">
                                                    <span class="icon"><i class="fa-regular fa-arrow-right"></i><i class="fa-regular fa-arrow-right"></i></span>
                                                </span>
											</a>
										</div>
									</div>
								</div>
								<div class="tab-pane fade" id="c-tab-2-pane" role="tabpanel" aria-labelledby="c-tab-2" tabindex="0">
									<div class="tab-content">
										<div class="year">
											<h6><?php echo frontend_escape($contentArray('about.history.timeline')[1]['year'] ?? ''); ?></h6>
										</div>
										<div class="reward">
											<h5><?php echo frontend_escape($contentArray('about.history.timeline')[1]['title'][$lang] ?? ''); ?></h5>
										</div>
										<div class="text">
											<p><?php echo nl2br(frontend_escape($contentArray('about.history.timeline')[1]['description'][$lang] ?? '')); ?></p>
										</div>
										<div class="annual-donation-wrap">
											<a class='e-primary-btn has-icon' href='contact.php'>
												<?php echo frontend_escape($contentText('about.history.cta')); ?>
												<span class="icon-wrap"><span class="icon"><i class="fa-regular fa-arrow-right"></i><i class="fa-regular fa-arrow-right"></i></span>
                                                </span>
											</a>
										</div>
									</div>
								</div>
								<div class="tab-pane fade" id="c-tab-3-pane" role="tabpanel" aria-labelledby="c-tab-3" tabindex="0">
									<div class="tab-content">
										<div class="year">
											<h6><?php echo frontend_escape($contentArray('about.history.timeline')[2]['year'] ?? ''); ?></h6>
										</div>
										<div class="reward">
											<h5><?php echo frontend_escape($contentArray('about.history.timeline')[2]['title'][$lang] ?? ''); ?></h5>
										</div>
										<div class="text">
											<p><?php echo nl2br(frontend_escape($contentArray('about.history.timeline')[2]['description'][$lang] ?? '')); ?></p>
										</div>
										<div class="annual-donation-wrap">
											<a class='e-primary-btn has-icon' href='contact.php'>
												<?php echo frontend_escape($contentText('about.history.cta')); ?>
												<span class="icon-wrap">
													<span class="icon"><i class="fa-regular fa-arrow-right"></i><i class="fa-regular fa-arrow-right"></i></span>
                                                </span>
											</a>
										</div>
									</div>
								</div>
								<div class="tab-pane fade" id="c-tab-4-pane" role="tabpanel" aria-labelledby="c-tab-4" tabindex="0">
									<div class="tab-content">
										<div class="year">
											<h6><?php echo frontend_escape($contentArray('about.history.timeline')[3]['year'] ?? ''); ?></h6>
										</div>
										<div class="reward">
											<h5><?php echo frontend_escape($contentArray('about.history.timeline')[3]['title'][$lang] ?? ''); ?></h5>
										</div>
										<div class="text">
											<p><?php echo nl2br(frontend_escape($contentArray('about.history.timeline')[3]['description'][$lang] ?? '')); ?></p>
										</div>
										<div class="annual-donation-wrap">
											<a class='e-primary-btn has-icon' href='contact.php'>
												<?php echo frontend_escape($contentText('about.history.cta')); ?>
												<span class="icon-wrap">
													<span class="icon"><i class="fa-regular fa-arrow-right"></i><i class="fa-regular fa-arrow-right"></i></span>
                                                </span>
											</a>
										</div>
									</div>
								</div>
								<div class="tab-pane fade" id="c-tab-5-pane" role="tabpanel" aria-labelledby="c-tab-5" tabindex="0">
									<div class="tab-content">
										<div class="year">
											<h6><?php echo frontend_escape($contentArray('about.history.timeline')[4]['year'] ?? ''); ?></h6>
										</div>
										<div class="reward">
											<h5><?php echo frontend_escape($contentArray('about.history.timeline')[4]['title'][$lang] ?? ''); ?></h5>
										</div>
										<div class="text">
											<p><?php echo nl2br(frontend_escape($contentArray('about.history.timeline')[4]['description'][$lang] ?? '')); ?></p>
										</div>
										<div class="annual-donation-wrap">
											<a class='e-primary-btn has-icon' href='contact.php'>
												<?php echo frontend_escape($contentText('about.history.cta')); ?>
												<span class="icon-wrap">
													<span class="icon"><i class="fa-regular fa-arrow-right"></i><i class="fa-regular fa-arrow-right"></i></span>
                                                </span>
											</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-6">
					<div class="history-image-wrap" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="400">
						<img src="assets/img/about/about2.jpg" alt="Our history"/>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- about-us-section-3 end -->


    
	<!-- services-section start -->
	<section class="services-section-2 p-t-100 p-t-xs-80">
		<div class="container">
			<div class="services-content">
				<div class="text-center m-b-50 m-b-xs-40">
					<div class="common-subtitle" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
						<img alt="icon" src="assets/img/icons/icon-9.svg"> <span><?php echo frontend_escape($contentText('about.joinUs.subtitle')); ?></span>
					</div>
					<div class="common-title m-b-0" data-aos="fade-up" data-aos-delay="400" data-aos-duration="1000">
						<h2><?php echo frontend_escape($contentText('about.joinUs.title')); ?></h2>
					</div>
				</div>
				<div class="row row-gap-4 m-b-135 m-b-lg-120 m-b-md-100 m-b-xs-80" data-aos="fade-up" data-aos-delay="600" data-aos-duration="1000">
					<div class="col-xl-4 col-md-6">
						<div class="service-card-2">
							<div class="service-top">
								<h4><?php echo frontend_escape($contentArray('about.joinUs.options')[0]['title'][$lang] ?? ''); ?></h4>
								<div class="join-utility join-qr">
									<img src="https://quickchart.io/qr?size=170&text=https%3A%2F%2Fchat.whatsapp.com%2F" alt="WhatsApp community QR" loading="lazy">
								</div>
							</div>
							<div class="service-content">
								<p><?php echo frontend_escape($contentArray('about.joinUs.options')[0]['description'][$lang] ?? ''); ?></p>
							</div>
						</div>
					</div>
					<div class="col-xl-4 col-md-6">
						<div class="service-card-2">
							<div class="service-top">
								<h4><?php echo frontend_escape($contentArray('about.joinUs.options')[1]['title'][$lang] ?? ''); ?></h4>
								<div class="join-utility join-social-links">
									<a href="https://www.instagram.com/vidhayak_yogeshnauhwar/" target="_blank" rel="noopener" aria-label="Instagram">
										<i class="fab fa-instagram"></i>
									</a>
									<a href="https://www.facebook.com/ChaudharyYN/" target="_blank" rel="noopener" aria-label="Facebook">
										<i class="fab fa-facebook-f"></i>
									</a>
								</div>
							</div>
							<div class="service-content">
								<p><?php echo frontend_escape($contentArray('about.joinUs.options')[1]['description'][$lang] ?? ''); ?></p>
							</div>
						</div>
					</div>
					<div class="col-xl-4 col-md-6">
						<div class="service-card-2">
							<div class="service-top">
								<h4><?php echo frontend_escape($contentArray('about.joinUs.options')[2]['title'][$lang] ?? ''); ?></h4>
								<div class="join-utility join-qr">
									<img src="https://quickchart.io/qr?size=170&text=https%3A%2F%2Fwww.rashtriyalokdal.org%2F" alt="RLD membership QR" loading="lazy">
								</div>
							</div>
							<div class="service-content">
								<p><?php echo frontend_escape($contentArray('about.joinUs.options')[2]['description'][$lang] ?? ''); ?></p>
							</div>
						</div>
					</div>
				</div>
				<div class="shape-2"></div>
			</div>
		</div>
		<div class="shape-1"><img alt="shape" src="assets/img/shapes/shape-26.webp"></div>
	</section>
	<!-- services-section end -->




	<!-- major-partners start -->
	<section class="major-partners p-t-80 p-b-140 p-b-xs-80">
		<div class="container">
			<div class="partners-title" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
				<div class="line-right">
					<img src="assets/img/shapes/shape-4.webp" alt="shape-4"/>
				</div>
				<h3><?php echo frontend_escape($contentText('about.schemes.title')); ?></h3>
				<div class="line">
					<img src="assets/img/shapes/shape-4.webp" alt="shape-4"/>
				</div>
			</div>
			<div class="row p-t-60 p-b-60" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="400">
				<div class="col-xl-12">
					<div class="partner-marquee">
						<div class="partner-marquee-layout">
							<div class="partner-1">
								<img src="assets/img/logo/fasal-bima-yojana.png" alt="partner-logo"/>
							</div>
							<div class="partner-1">
								<img src="assets/img/logo/soil-health-card.png" alt="partner-logo"/>
							</div>
							<div class="partner-1">
								<img src="assets/img/logo/pm-kisan.png" alt="partner-logo"/>
							</div>
							<div class="partner-1">
								<img src="assets/img/logo/ayushman.png" alt="partner-logo"/>
							</div>
							<div class="partner-1">
								<img src="assets/img/logo/ujjavala-yojana.png" alt="partner-logo"/>
							</div>
							<div class="partner-1">
								<img src="assets/img/logo/jal-jeevan-mission.png" alt="partner-logo"/>
							</div>
						</div>
						<div class="partner-marquee-layout">
							<div class="partner-1">
								<img src="assets/img/logo/awas-yojana.png" alt="partner-logo"/>
							</div>
							<div class="partner-1">
								<img src="assets/img/logo/pm-kisan.png" alt="partner-logo"/>
							</div>
							<div class="partner-1">
								<img src="assets/img/logo/ayushman.png" alt="partner-logo"/>
							</div>
							<div class="partner-1">
								<img src="assets/img/logo/ujjavala-yojana.png" alt="partner-logo"/>
							</div>
							<div class="partner-1">
								<img src="assets/img/logo/jal-jeevan-mission.png" alt="partner-logo"/>
							</div>
							<div class="partner-1">
								<img src="assets/img/logo/mudra-yojana.png" alt="partner-logo"/>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="partner-btn text-center">
				<a class='e-primary-btn has-icon' data-aos-delay='600' data-aos-duration='1000' data-aos='fade-up' href='contact.php'>
					<?php echo frontend_escape($contentText('about.schemes.cta')); ?>
					<span class="icon-wrap">
						<span class="icon"><i class="fa-regular fa-arrow-right"></i><i class="fa-regular fa-arrow-right"></i></span>
                    </span>
				</a>
			</div>
		</div>
	</section>
	<!-- major-partners end -->

	<!-- our-events-section-2 start -->
	<!-- <section class="our-events-section-2 p-t-120 p-b-120 p-t-xs-80 p-b-xs-80 m-b-100">
		<div class="container">
			<div class="row align-items-end m-b-60 m-b-xs-40">
				<div class="col-xl-6 col-md-8 m-b-xs-20">
					<div class="common-subtitle" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
						<img src="assets/img/icons/icon-2.svg" alt="icon-1"/>
						<span>Our Arrange</span>
					</div>
					<div class="common-title m-b-0 style-color-3 text-start" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="400">
						<h2>Econest Upcoming Events</h2>
					</div>
				</div>
				<div class="col-xl-6 col-md-4 text-md-end">
					<a class='e-primary-btn has-icon' data-aos-delay='600' data-aos-duration='1000' data-aos='fade-up' href='camping.html'>
						View All Events
						<span class="icon-wrap">
							<span class="icon"><i class="fa-regular fa-arrow-right"></i><i class="fa-regular fa-arrow-right"></i></span>
                        </span>
					</a>
				</div>
			</div>
			<div class="row" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="800">
				<div class="col-xl-12">
					<div class="event-card-2 m-b-30">
						<div class="event-thumb">
							<a href='camping-details.html'>
								<img src="assets/img/thumbs/thumb-36.webp" alt="thumb-1"/>
							</a>
							<div class="event-date">
								<h5>12 Jan-20 Jan, 2025</h5>
							</div>
						</div>
						<div class="card-content">
							<div class="event-card-title">
								<h2>
									<a href='camping-details.html'>
										The forest is our life, it is our job to keep the forest
										clean
									</a>
								</h2>
							</div>
							<div class="address">
								<div class="time">
									<i class="fa-regular fa-clock"></i>
									<span>8:30am - 4:00pm</span>
								</div>
								<div class="location">
									<i class="fa-regular fa-location-dot"></i>
									<span>Jones Street, New York</span>
								</div>
							</div>
							<div class="join-event">
								<div class="blog-btn">
									<a class='e-primary-btn has-icon' href='camping-details.html'>
										Join Event
										<span class="icon-wrap">
											<span class="icon"><i class="fa-regular fa-arrow-right"></i><i class="fa-regular fa-arrow-right"></i></span>
                                        </span>
									</a>
								</div>
								<div class="top-right">
									<img src="assets/img/authors/author-1.webp" alt="authors"/>
									<div class="people-joined">
										<h5>236</h5>
										<span>Joined People</span>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="event-card-2">
						<div class="event-thumb">
							<a href='camping-details.html'>
								<img src="assets/img/thumbs/thumb-37.webp" alt="thumb-2"/>
							</a>
							<div class="event-date">
								<h5>12 Jan-20 Jan, 2025</h5>
							</div>
						</div>
						<div class="card-content">
							<div class="event-card-title">
								<h2>
									<a href='camping-details.html'>
										The forest is our life, it is our job to keep the forest
										clean
									</a>
								</h2>
							</div>
							<div class="address">
								<div class="time">
									<i class="fa-regular fa-clock"></i>
									<span>9:00am - 6:00pm</span>
								</div>
								<div class="location">
									<i class="fa-regular fa-location-dot"></i>
									<span>85 Great Portland Street, London</span>
								</div>
							</div>
							<div class="join-event">
								<div class="blog-btn">
									<a class='e-primary-btn has-icon' href='camping-details.html'>
										Join Event
										<span class="icon-wrap">
                                            <span class="icon"><i class="fa-regular fa-arrow-right"></i><i class="fa-regular fa-arrow-right"></i></span>
                                        </span>
									</a>
								</div>
								<div class="top-right">
									<img src="assets/img/authors/author-1.webp" alt="authors"/>
									<div class="people-joined">
										<h5>162</h5>
										<span>Joined People</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section> -->
	<!-- our-events-section-2 end -->

	
</main>

<?php include 'components/footer.php';?>

<?php include 'components/script.php';?>
</body>
</html>
