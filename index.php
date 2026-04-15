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

	<title><?php echo frontend_escape($contentText('home.meta.title')); ?></title>
	<meta name="description" content="<?php echo frontend_escape($contentText('home.meta.description')); ?>">
    <?php include 'components/links.php';?>
</head>

<body>
<?php include 'components/loader.php';?>
<?php include 'components/header.php';?>


<main>
	<!-- hero-section start -->
	<section class="hero-slider-active-1">
		<div class="swiper">
			<div class="swiper-wrapper">
				<div class="swiper-slide">
					<div class="hero-side" style="background-image: url(assets/img/home/hero/hero10.jpg)">
						<div class="container">
							<div class="row">
								<div class="col-xl-12">
									<div class="hero-content-1">
										<div class="subtitle" data-animation="animate__fadeInUp" data-delay="0.3s">
											<img src="assets/img/icons/wheat.png" alt="icon-1" class="wheat-icon"/>
											<span><?php echo frontend_escape($contentText('home.hero.slide1.subtitle')); ?></span>
										</div>
										<div class="title" data-animation="animate__fadeInUp" data-delay="0.4s">
											<h1><?php echo $contentText('home.hero.slide1.title'); ?></h1>
										</div>
										<div class="text" data-animation="animate__fadeInUp" data-delay="0.5s">
											<p><?php echo frontend_escape($contentText('home.hero.slide1.text')); ?></p>
										</div>
										<div class="join-us" data-animation="animate__fadeInUp" data-delay="0.6s">
											<a class='e-primary-btn has-icon' href='about.php'>
												<?php echo frontend_escape($contentText('home.hero.slide1.cta')); ?>
												<span class="icon-wrap"><span class="icon"><i class="fa-regular fa-arrow-right"></i><i class="fa-regular fa-arrow-right"></i></span></span>
											</a>
											<div class="author-wrap">
												<img src="assets/img/authors/author-1.webp" alt="authors"/>
												<div class="author-info">
													<h5>50k+</h5>
													<p><?php echo frontend_escape($contentText('home.hero.supporters.label')); ?></p>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="s-shape-1">
							<!-- <img src="assets/img/shapes/shape-2.webp" alt="s-shape-1"/> -->
						</div>
					</div>
				</div>
				<div class="swiper-slide">
					<div class="hero-side" style="background-image: url(assets/img/home/hero/hero6.jpg)">
						<div class="container">
							<div class="row">
								<div class="col-xl-12">
									<div class="hero-content-1">
										<div class="subtitle" data-animation="animate__fadeInUp" data-delay="0.3s">
											<img src="assets/img/icons/wheat.png" alt="icon-1" class="wheat-icon"/>
											<span><?php echo frontend_escape($contentText('home.hero.slide2.subtitle')); ?></span>
										</div>
										<div class="title" data-animation="animate__fadeInUp" data-delay="0.4s">
											<h1><?php echo $contentText('home.hero.slide2.title'); ?></h1>
										</div>
										<div class="text" data-animation="animate__fadeInUp" data-delay="0.5s">
											<p><?php echo frontend_escape($contentText('home.hero.slide2.text')); ?></p>
										</div>
										<div class="join-us" data-animation="animate__fadeInUp" data-delay="0.6s">
											<a class='e-primary-btn has-icon' href='about.php'>
												<?php echo frontend_escape($contentText('home.hero.slide2.cta')); ?>
												<span class="icon-wrap"><span class="icon"><i class="fa-regular fa-arrow-right"></i><i class="fa-regular fa-arrow-right"></i></span></span>
											</a>
											<div class="author-wrap">
												<img src="assets/img/authors/author-1.webp" alt="authors"/>
												<div class="author-info">
													<h5>50k+</h5>
													<p><?php echo frontend_escape($contentText('home.hero.supporters.label')); ?></p>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="s-shape-1">
							<!-- <img src="assets/img/shapes/shape-2.webp" alt="s-shape-1"/> -->
						</div>
					</div>
				</div>
				<div class="swiper-slide">
					<div class="hero-side" style="background-image: url(assets/img/home/hero/hero7.jpg)">
						<div class="container">
							<div class="row">
								<div class="col-xl-12">
									<div class="hero-content-1">
										<div class="subtitle" data-animation="animate__fadeInUp" data-delay="0.3s">
											<img src="assets/img/icons/wheat.png" alt="icon-1" class="wheat-icon"/>
											<span><?php echo frontend_escape($contentText('home.hero.slide3.subtitle')); ?></span>
										</div>
										<div class="title" data-animation="animate__fadeInUp" data-delay="0.4s">
											<h1><?php echo $contentText('home.hero.slide3.title'); ?></h1>
										</div>
										<div class="text" data-animation="animate__fadeInUp" data-delay="0.5s">
											<p><?php echo frontend_escape($contentText('home.hero.slide3.text')); ?></p>
										</div>
										<div class="join-us" data-animation="animate__fadeInUp" data-delay="0.6s">
											<a class='e-primary-btn has-icon' href='donations.html'>
												<?php echo frontend_escape($contentText('home.hero.slide3.cta')); ?>
												<span class="icon-wrap"><span class="icon"><i class="fa-regular fa-arrow-right"></i><i class="fa-regular fa-arrow-right"></i></span></span>
											</a>
											<div class="author-wrap">
												<img src="assets/img/authors/author-1.webp" alt="authors"/>
												<div class="author-info">
													<h5>50k+</h5>
													<p><?php echo frontend_escape($contentText('home.hero.supporters.label')); ?></p>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="s-shape-1">
							<!-- <img src="assets/img/shapes/shape-2.webp" alt="s-shape-1"/> -->
						</div>
					</div>
				</div>
				<div class="swiper-slide">
					<div class="hero-side" style="background-image: url(assets/img/home/hero/hero11.jpg)">
						<div class="container">
							<div class="row">
								<div class="col-xl-12">
									<div class="hero-content-1">
										<div class="subtitle" data-animation="animate__fadeInUp" data-delay="0.3s">
											<img src="assets/img/icons/wheat.png" alt="icon-1" class="wheat-icon"/>
											<span><?php echo frontend_escape($contentText('home.hero.slide4.subtitle')); ?></span>
										</div>
										<div class="title" data-animation="animate__fadeInUp" data-delay="0.4s">
											<h1><?php echo $contentText('home.hero.slide4.title'); ?></h1>
										</div>
										<div class="text" data-animation="animate__fadeInUp" data-delay="0.5s">
											<p><?php echo frontend_escape($contentText('home.hero.slide4.text')); ?></p>
										</div>
										<div class="join-us" data-animation="animate__fadeInUp" data-delay="0.6s">
											<a class='e-primary-btn has-icon' href='about.php'>
												<?php echo frontend_escape($contentText('home.hero.slide4.cta')); ?>
												<span class="icon-wrap"><span class="icon"><i class="fa-regular fa-arrow-right"></i><i class="fa-regular fa-arrow-right"></i></span></span>
											</a>
											<div class="author-wrap">
												<img src="assets/img/authors/author-1.webp" alt="authors"/>
												<div class="author-info">
													<h5>50k+</h5>
													<p><?php echo frontend_escape($contentText('home.hero.supporters.label')); ?></p>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="s-shape-1">
							<!-- <img src="assets/img/shapes/shape-2.webp" alt="s-shape-1"/> -->
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="hero-slider-pagination-1"></div>
		<!-- <div class="hero-slider-social">
			<div class="social-links">
				<a href="https://facebook.com/">
					<i class="fab fa-facebook-f"></i>
				</a>
				<a href="https://twitter.com/">
					<i class="fab fa-x-twitter"></i>
				</a>
				<a href="https://www.instagram.com/">
					<i class="fab fa-instagram"></i>
				</a>
				<a href="https://linkedin.com/">
					<i class="fab fa-linkedin-in"></i>
				</a>
			</div>
			
		</div> -->
	</section>
	<!-- hero-section end -->

	<!-- about-us-section start -->
	<section class="about-us-section m-t-100 m-t-md-100 m-t-xs-80">
		<div class="container">
			<div class="row align-items-center">
				<div class="col-xl-6">
					<div class="shape-wrapped-thumb-1" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
						<img src="assets/img/home/about/about2.jpg" alt="thumb"/>
						<!-- <div class="experience-shape-2">
							<h3>
								<span class="purecounter" data-purecounter-duration="2" data-purecounter-end="29">0</span>+
							</h3>
							<p>Years of experience</p>
						</div> -->
						<!-- <div class="award-shape" style="background-image: url(assets/img/shapes/shape-18.webp)">
							<img src="assets/img/icons/icon-6.svg" alt="icon"/>
							<p>2024-We are the best award winner</p>
						</div> -->
						<div class="box-shape">
							<img src="assets/img/shapes/shape-14.webp" alt="box-shape"/>
						</div>
						<div class="positioned-shape">
							<div class="shape-wrapped-thumb-2">
								<div class="video-thumb">
									<img src="assets/img/home/about/about4.jpg" alt="thumb"/>
									<!-- <a href="https://www.youtube.com/watch?v=fLeJJPxua3E&amp;ab_channel=Motiversity" data-fancybox="" class="video-play-btn">
										<i class="fa-solid fa-play"></i>
									</a> -->
								</div>
								<!-- <div class="vector-shape">
									<img src="assets/img/shapes/shape-19.webp" alt="vector-shape"/>
								</div> -->
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-6">
					<div class="about-us-content px-xxl-5 px-3" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="400">
						<div class="common-subtitle">
							<img src="assets/img/icons/wheat.png" alt="icon-2"/ class="wheat-icon">
							<span><?php echo frontend_escape($contentText('home.about.sectionSubtitle')); ?></span>
						</div>
						<div class="common-title text-start">
							<h2><?php echo $contentText('home.about.sectionTitle'); ?></h2>
						</div>
						<div class="c-tabs-wrapper">
							<ul class="nav nav-tabs" id="myTab" role="tablist">
								<li class="nav-item" role="presentation">
									<button class="nav-link active" id="c-tab-1" data-bs-toggle="tab" data-bs-target="#c-tab-1-pane" type="button" role="tab" aria-controls="c-tab-1-pane" aria-selected="true">
										<?php echo frontend_escape($contentText('home.about.tabs.tab1Label')); ?>
									</button>
								</li>
								<li class="nav-item" role="presentation">
									<button class="nav-link" id="c-tab-2" data-bs-toggle="tab" data-bs-target="#c-tab-2-pane" type="button" role="tab" aria-controls="c-tab-2-pane" aria-selected="false">
										<?php echo frontend_escape($contentText('home.about.tabs.tab2Label')); ?>
									</button>
								</li>
								<li class="nav-item" role="presentation">
									<button class="nav-link" id="c-tab-3" data-bs-toggle="tab" data-bs-target="#c-tab-3-pane" type="button" role="tab" aria-controls="c-tab-3-pane" aria-selected="false">
										<?php echo frontend_escape($contentText('home.about.tabs.tab3Label')); ?>
									</button>
								</li>
							</ul>
							<div class="tab-content" id="myTabContent">
								<div class="tab-pane fade show active" id="c-tab-1-pane" role="tabpanel" aria-labelledby="c-tab-1" tabindex="0">
									<div class="text">
											<p><?php echo frontend_escape($contentText('home.about.tabs.tab1.text')); ?></p>
									</div>
									<div class="benefits">
										<ul>
											<?php foreach ($contentList('home.about.tabs.tab1.bullets') as $bullet): ?>
												<li><?php echo frontend_escape($bullet); ?></li>
											<?php endforeach; ?>
										</ul>
									</div>
								</div>
								<div class="tab-pane fade" id="c-tab-2-pane" role="tabpanel" aria-labelledby="c-tab-2" tabindex="0">
									<div class="text">
											<p><?php echo frontend_escape($contentText('home.about.tabs.tab2.text')); ?></p>
									</div>
									<div class="benefits">
										<ul>
											<?php foreach ($contentList('home.about.tabs.tab2.bullets') as $bullet): ?>
												<li><?php echo frontend_escape($bullet); ?></li>
											<?php endforeach; ?>
										</ul>
									</div>
								</div>
								<div class="tab-pane fade" id="c-tab-3-pane" role="tabpanel" aria-labelledby="c-tab-3" tabindex="0">
									<div class="text">
											<p><?php echo frontend_escape($contentText('home.about.tabs.tab3.text')); ?></p>
									</div>
                                    <div class="benefits">
										<ul>
											<?php foreach ($contentList('home.about.tabs.tab3.bullets') as $bullet): ?>
												<li><?php echo frontend_escape($bullet); ?></li>
											<?php endforeach; ?>
										</ul>
									</div>
								</div>
							</div>
						</div>
						<div class="annual-donation-wrap">
							<a class='e-primary-btn has-icon' href='about.php'>
								<?php echo frontend_escape($contentText('home.about.cta')); ?>
								<span class="icon-wrap"><span class="icon"><i class="fa-regular fa-arrow-right"></i><i class="fa-regular fa-arrow-right"></i></span></span>
							</a>
							<!-- <div class="rating-wrap">
								<div class="star-rating">
									<img src="assets/img/logo/logo-8.svg" alt="logo"/>
									<div class="stars">
										<i class="fa-solid fa-star-sharp"></i>
										<i class="fa-solid fa-star-sharp"></i>
										<i class="fa-solid fa-star-sharp"></i>
										<i class="fa-solid fa-star-sharp"></i>
										<i class="fa-solid fa-star-sharp"></i>
									</div>
								</div>
								<p>Excellent 4.9 out of 5</p>
							</div> -->
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- about-us-section end -->

	<!-- company-achievements-section start -->
	<section class="company-achievements-section m-t-60 m-b-50">
		<div class="container">
			<div class="company-achievements" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
				<div class="achievement">
					<i class="fa-light fa-chart-mixed"></i>
					<h2>
						<span class="purecounter" data-purecounter-duration="2" data-purecounter-end="50">50</span>k+
					</h2>
					<p><?php echo frontend_escape($contentText('home.achievements.item1.label')); ?></p>
				</div>
				<div class="achievement">
					<i class="fa-light fa-lightbulb-exclamation-on"></i>
					<h2>
						<span class="purecounter" data-purecounter-duration="2" data-purecounter-end="500">500</span>+
					</h2>
					<p><?php echo frontend_escape($contentText('home.achievements.item2.label')); ?></p>
				</div>
				<div class="achievement">
					<i class="fa-light fa-thumbs-up"></i>
					<h2>
						<span class="purecounter" data-purecounter-duration="2" data-purecounter-end="17">17</span>+
					</h2>
					<p><?php echo frontend_escape($contentText('home.achievements.item3.label')); ?></p>
				</div>
				<div class="achievement">
					<i class="fa-light fa-users-medical"></i>
					<h2>
						<span class="purecounter" data-purecounter-duration="2" data-purecounter-end="3">3</span>+
					</h2>
					<p><?php echo frontend_escape($contentText('home.achievements.item4.label')); ?></p>
				</div>
			</div>
		</div>
	</section>

	<!-- company-achievements-section end -->

    <!-- what-we-do-section start -->
	<section class="what-we-do-section-2">
		<div class="container">
			<div class="row">
				<div class="col-xl-12">
					<div class="section-top-3">
						<div class="left">
							<div class="common-subtitle" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
								<img alt="icon-2" src="assets/img/icons/wheat.png" class="wheat-icon"> <span><?php echo frontend_escape($contentText('home.grassroots.sectionSubtitle')); ?></span>
							</div>
							<div class="common-title text-start" data-aos="fade-up" data-aos-delay="400" data-aos-duration="1000">
								<h2><?php echo frontend_escape($contentText('home.grassroots.sectionTitle')); ?></h2>
							</div>
						</div>
						<div class="right" data-aos="fade-up" data-aos-delay="600" data-aos-duration="1000">
							<p><?php echo frontend_escape($contentText('home.grassroots.quote')); ?></p><a class='service-btn e-primary-btn has-icon' href='contact.php'><?php echo frontend_escape($contentText('home.grassroots.cta')); ?> <span class="icon-wrap"><span class="icon"><i class="fa-regular fa-arrow-right"></i> <i class="fa-regular fa-arrow-right"></i></span></span></a>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-xl-12">
					<div class="whatwedo-slider-active" data-aos="fade-up" data-aos-delay="800" data-aos-duration="1000">
						<div class="swiper">
							<div class="swiper-wrapper">
								<?php foreach ($contentList('home.grassroots.cards') as $index => $cardTitle): ?>
									<div class="swiper-slide">
										<div class="we-do-card">
											<div class="card-content">
												<i class="fa-solid fa-sun"><span><?php echo frontend_escape((string) ($index + 1)); ?></span></i>
												<h5><?php echo frontend_escape($cardTitle); ?></h5>
											</div>
											<div class="card-thumb">
												<img alt="thumb" src="assets/img/home/public-meetings/meeting<?php echo frontend_escape((string) ($index + 1)); ?>.jpg">
											</div>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
						<div class="whatwedo-slider-bottom">
							<div class="whatwedo-navigation">
								<div class="whatwedo-button-prev">
									<i class="fa-regular fa-arrow-left"></i>
								</div>
								<div class="whatwedo-button-next">
									<i class="fa-regular fa-arrow-right"></i>
								</div>
							</div>
							<div class="whatwedo-pagination-wrap">
								<div class="whatwedo-pagination"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="shape"><img alt="shape" src="assets/img/shapes/shape-29.webp"></div>
	</section>
	<!-- what-we-do-section end -->

	<!-- we-are-friends-section start -->
	<section class="we-are-friends-section m-t-160 m-t-md-120 m-t-xs-100 m-b-120 m-b-md-100 m-b-xs-80">
		<div class="container">
			<div class="row row-gap-5">
				<div class="col-xl-6" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
					<div class="friends-left">
						<div class="common-subtitle text-uppercase">
							<span><?php echo frontend_escape($contentText('home.leadership.sectionSubtitle')); ?></span>
						</div>
						<div class="common-title text-start">
							<h2><?php echo frontend_escape($contentText('home.leadership.sectionTitle')); ?></h2>
						</div>
						<div class="text">
							<p><?php echo frontend_escape($contentText('home.leadership.text')); ?></p>
						</div>
						<div class="blog-btn">
							<a class='e-primary-btn has-icon' href='contact.php'><?php echo frontend_escape($contentText('home.leadership.cta')); ?> <span class="icon-wrap"><span class="icon"><i class="fa-regular fa-arrow-right"></i> <i class="fa-regular fa-arrow-right"></i></span></span></a>
						</div>
						<div class="top-right">
							<img alt="authors" src="assets/img/authors/author-1.webp">
							<div class="people-joined">
								<h5>50k+</h5><span><?php echo frontend_escape($contentText('home.leadership.supporters.label')); ?></span>
							</div>
						</div>
						<!-- <div class="shape"><img alt="icon" src="assets/img/icons/icon-15.svg"></div> -->
					</div>
				</div>
				<div class="col-xl-6" data-aos="fade-up" data-aos-delay="400" data-aos-duration="1000">
					<div class="friends-card m-b-20">
						<div class="row">
							<div class="col-md-12">
								<div class="thumb">
									<img alt="thumb" src="assets/img/home/senior-citizens/leader1.jpg">
								</div>
							</div>
						</div>
					</div>
					<div class="friends-card m-b-20">
						<div class="row">
							<div class="col-md-12">
								<div class="thumb">
									<img alt="thumb" src="assets/img/home/senior-citizens/leader2.jpg">
								</div>
							</div>
						</div>
					</div>
					<div class="friends-card m-b-20">
						<div class="row">
							<div class="col-md-12">
								<div class="thumb">
									<img alt="thumb" src="assets/img/home/senior-citizens/leader4.jpg">
								</div>
							</div>
						</div>
					</div>
					<div class="friends-card">
						<div class="row">
							<div class="col-md-12">
								<div class="thumb">
									<img alt="thumb" src="assets/img/home/senior-citizens/leader5.jpg">
								</div>
							</div>
						</div>
					</div>
					<div class="friends-card">
						<div class="row">
							<div class="col-md-12">
								<div class="thumb">
									<img alt="thumb" src="assets/img/home/senior-citizens/leader9.jpg">
								</div>
							</div>
						</div>
					</div>
					<div class="friends-card">
						<div class="row">
							<div class="col-md-12">
								<div class="thumb">
									<img alt="thumb" src="assets/img/home/senior-citizens/leader10.jpg">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- we-are-friends-section end -->

		       	<!-- services-we-offer-section start -->
	<section class="services-we-offer-section p-t-100 p-b-100 p-t-xs-80 p-b-xs-80" style="background-image: url('assets/img/bg/services-we-offer-bg.webp')">
		<div class="container">
			<div class="text-center m-b-50 m-b-xs-40">
				<div class="common-subtitle" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
					<img alt="icon-2" src="assets/img/icons/wheat.png" class="wheat-icon"> <span><?php echo frontend_escape($contentText('home.workSection.sectionSubtitle')); ?></span>
				</div>
				<div class="common-title m-b-0" data-aos="fade-up" data-aos-delay="400" data-aos-duration="1000">
					<h2><?php echo frontend_escape($contentText('home.workSection.sectionTitle')); ?></h2>
				</div>
			</div>
			<div class="row">
				<div class="col-xl-12">
					<div class="service-slider-active" data-aos="fade-up" data-aos-delay="600" data-aos-duration="1000">
						<div class="swiper">
							<div class="swiper-wrapper">
								<div class="swiper-slide">
									<div class="service-card-3">
										<div class="thumb">
											<a><img alt="thumb" src="assets/img/home/work/work1.jpg"></a>
										</div>
									</div>
								</div>
								<div class="swiper-slide">
									<div class="service-card-3">
										<div class="thumb">
											<a><img alt="thumb" src="assets/img/home/work/work2.jpg"></a>
										</div>
									</div>
								</div>
								<div class="swiper-slide">
									<div class="service-card-3">
										<div class="thumb">
											<a><img alt="thumb" src="assets/img/home/work/work3.jpg"></a>
										</div>
									</div>
								</div>
								<div class="swiper-slide">
									<div class="service-card-3">
										<div class="thumb">
											<a><img alt="thumb" src="assets/img/home/work/work4.jpg"></a>
										</div>
									</div>
								</div>
								<div class="swiper-slide">
									<div class="service-card-3">
										<div class="thumb">
											<a><img alt="thumb" src="assets/img/home/work/work5.jpg"></a>
										</div>
									</div>
								</div>
								<div class="swiper-slide">
									<div class="service-card-3">
										<div class="thumb">
											<a><img alt="thumb" src="assets/img/home/work/work6.jpg"></a>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="service-pagination-wrap">
							<div class="service-pagination"></div>
						</div>
						<div class="service-button-prev">
							<i class="fa-regular fa-arrow-left"></i>
						</div>
						<div class="service-button-next">
							<i class="fa-regular fa-arrow-right"></i>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- services-we-offer-section end -->
    
	<!-- services-section start -->
	<section class="services-section-2 p-t-100 p-t-xs-80">
		<div class="container">
			<div class="services-content">
				<div class="text-center m-b-50 m-b-xs-40">
					<div class="common-subtitle" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
						<img alt="icon" src="assets/img/icons/icon-9.svg"> <span><?php echo frontend_escape($contentText('home.joinUs.sectionSubtitle')); ?></span>
					</div>
					<div class="common-title m-b-0" data-aos="fade-up" data-aos-delay="400" data-aos-duration="1000">
						<h2><?php echo frontend_escape($contentText('home.joinUs.sectionTitle')); ?></h2>
					</div>
				</div>
				<div class="row row-gap-4 m-b-135 m-b-lg-120 m-b-md-100 m-b-xs-80" data-aos="fade-up" data-aos-delay="600" data-aos-duration="1000">
					<div class="col-xl-4 col-md-6">
						<div class="service-card-2">
							<div class="service-top">
								<h4><?php echo frontend_escape($contentText('home.joinUs.card1.title')); ?></h4>
								<div class="join-utility join-qr">
									<img src="https://quickchart.io/qr?size=170&text=https%3A%2F%2Fchat.whatsapp.com%2F" alt="WhatsApp community QR" loading="lazy">
								</div>
							</div>
							<div class="service-content">
								<p><?php echo frontend_escape($contentText('home.joinUs.card1.text')); ?></p>
							</div>
						</div>
					</div>
					<div class="col-xl-4 col-md-6">
						<div class="service-card-2">
							<div class="service-top">
								<h4><?php echo frontend_escape($contentText('home.joinUs.card2.title')); ?></h4>
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
								<p><?php echo frontend_escape($contentText('home.joinUs.card2.text')); ?></p>
							</div>
						</div>
					</div>
					<div class="col-xl-4 col-md-6">
						<div class="service-card-2">
							<div class="service-top">
								<h4><?php echo frontend_escape($contentText('home.joinUs.card3.title')); ?></h4>
								<div class="join-utility join-qr">
									<img src="https://quickchart.io/qr?size=170&text=https%3A%2F%2Fwww.rashtriyalokdal.org%2F" alt="RLD membership QR" loading="lazy">
								</div>
							</div>
							<div class="service-content">
								<p><?php echo frontend_escape($contentText('home.joinUs.card3.text')); ?></p>
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


	<!-- latest-work-section start -->
	<section class="latest-work-section mt-0 p-t-100 p-b-100 p-t-xs-80 p-b-xs-80">
		<div class="container">
			<div class="text-center m-b-60 m-b-xs-50">
				<div class="common-subtitle" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
					<img alt="icon" src="assets/img/icons/icon-9.svg"> <span><?php echo frontend_escape($contentText('home.events.sectionSubtitle')); ?></span>
				</div>
				<div class="common-title m-b-0" data-aos="fade-up" data-aos-delay="400" data-aos-duration="1000">
					<h2><?php echo frontend_escape($contentText('home.events.sectionTitle')); ?></h2>
				</div>
			</div>
			<div class="row" data-aos="fade-up" data-aos-delay="600" data-aos-duration="1000">
				<div class="col-xl-12">
					<div class="work-card">
						<div class="work-card-thumb"><svg class="clippy">
							<defs>
								<clippath id="w-mask-image">
									<path d="M94.208 5.66961L19.4714 50.3601C7.39412 57.582 0 70.6187 0 84.6905V542.872H499.787V138.759C499.787 124.827 492.537 111.898 480.65 104.631L319.1 5.87191C312.818 2.0319 305.599 0 298.236 0H114.737C107.507 0 100.413 1.95932 94.208 5.66961Z" fill="#FFE175"></path>
								</clippath>
								<clippath id="w-mask-shape">
									<path d="M94.208 5.66961L19.4714 50.3601C7.39412 57.582 0 70.6187 0 84.6905V542.872H499.787V138.759C499.787 124.827 492.537 111.898 480.65 104.631L319.1 5.87191C312.818 2.0319 305.599 0 298.236 0H114.737C107.507 0 100.413 1.95932 94.208 5.66961Z" fill="#FFE175"></path>
								</clippath>
							</defs></svg> <img alt="thumb" src="assets/img/home/events/event1.jpg"></div>
						<div class="work-card-content">
							<p><?php echo frontend_escape($contentText('home.events.items.0.tag')); ?></p>
							<h3><?php echo frontend_escape($contentText('home.events.items.0.title')); ?></h3>
							<div class="annual-donation-wrap">
								<div class="blog-btn">
									<a class='e-primary-btn has-icon is-hover-white' href='events.php'><?php echo frontend_escape($contentText('home.events.cta')); ?> <span class="icon-wrap"><span class="icon"><i class="fa-regular fa-arrow-right"></i> <i class="fa-regular fa-arrow-right"></i></span></span></a>
								</div>
							</div>
						</div>
						<div class="card-number">
							<h1>01</h1>
						</div>
					</div>
					<div class="work-card">
						<div class="work-card-thumb"><img alt="thumb" src="assets/img/home/events/event2.jpg"></div>
						<div class="work-card-content">
							<p><?php echo frontend_escape($contentText('home.events.items.1.tag')); ?></p>
							<h3><?php echo frontend_escape($contentText('home.events.items.1.title')); ?></h3>
							<div class="annual-donation-wrap">
								<div class="blog-btn">
									<a class='e-primary-btn has-icon is-hover-white' href='events.php'><?php echo frontend_escape($contentText('home.events.cta')); ?> <span class="icon-wrap"><span class="icon"><i class="fa-regular fa-arrow-right"></i> <i class="fa-regular fa-arrow-right"></i></span></span></a>
								</div>
							</div>
						</div>
						<div class="card-number">
							<h1>02</h1>
						</div>
					</div>
					<div class="work-card">
						<div class="work-card-thumb"><img alt="thumb" src="assets/img/home/events/event8.jpg"></div>
						<div class="work-card-content">
							<p><?php echo frontend_escape($contentText('home.events.items.2.tag')); ?></p>
							<h3><?php echo frontend_escape($contentText('home.events.items.2.title')); ?></h3>
							<div class="annual-donation-wrap">
								<div class="blog-btn">
									<a class='e-primary-btn has-icon is-hover-white' href='events.php'><?php echo frontend_escape($contentText('home.events.cta')); ?> <span class="icon-wrap"><span class="icon"><i class="fa-regular fa-arrow-right"></i> <i class="fa-regular fa-arrow-right"></i></span></span></a>
								</div>
							</div>
						</div>
						<div class="card-number">
							<h1>03</h1>
						</div>
					</div>
					<div class="work-card">
						<div class="work-card-thumb"><img alt="thumb" src="assets/img/home/events/event4.jpg"></div>
						<div class="work-card-content">
							<p><?php echo frontend_escape($contentText('home.events.items.3.tag')); ?></p>
							<h3><?php echo frontend_escape($contentText('home.events.items.3.title')); ?></h3>
							<div class="annual-donation-wrap">
								<div class="blog-btn">
									<a class='e-primary-btn has-icon is-hover-white' href='events.php'><?php echo frontend_escape($contentText('home.events.cta')); ?> <span class="icon-wrap"><span class="icon"><i class="fa-regular fa-arrow-right"></i> <i class="fa-regular fa-arrow-right"></i></span></span></a>
								</div>
							</div>
						</div>
						<div class="card-number">
							<h1>04</h1>
						</div>
					</div>
					<div class="work-card">
						<div class="work-card-thumb"><img alt="thumb" src="assets/img/home/events/event7.jpg"></div>
						<div class="work-card-content">
							<p><?php echo frontend_escape($contentText('home.events.items.4.tag')); ?></p>
							<h3><?php echo frontend_escape($contentText('home.events.items.4.title')); ?></h3>
							<div class="annual-donation-wrap">
								<div class="blog-btn">
									<a class='e-primary-btn has-icon is-hover-white' href='events.php'><?php echo frontend_escape($contentText('home.events.cta')); ?> <span class="icon-wrap"><span class="icon"><i class="fa-regular fa-arrow-right"></i> <i class="fa-regular fa-arrow-right"></i></span></span></a>
								</div>
							</div>
						</div>
						<div class="card-number">
							<h1>05</h1>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- latest-work-section end -->
	<!-- testimonial start -->
	<section class="testimonial p-t-120 p-t-md-100 p-t-xs-80 p-b-100 p-b-md-80 p-b-xs-60">
		<div class="container">
			<div class="row align-items-center justify-content-between">
				<div class="col-xl-4 m-b-lg-60 m-b-md-60 m-b-xs-60">
					<div class="testimonial-content" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
						<div class="common-subtitle">
							<img alt="icon-2" src="assets/img/icons/wheat.png" class="wheat-icon"> <span><?php echo frontend_escape($contentText('home.testimonials.sectionSubtitle')); ?></span>
						</div>
						<div class="common-title text-start">
							<h2><?php echo $contentText('home.testimonials.sectionTitle'); ?></h2>
						</div>
						<div class="text">
							<p><?php echo frontend_escape($contentText('home.testimonials.text')); ?></p>
						</div>
						<div class="reviews">
							<h3>
								<span class="purecounter" data-purecounter-duration="1" data-purecounter-end="99">0</span>%
							<!-- </h3><img alt="favicon" src="assets/img/logo/favicon.webp"> -->
							<h5><?php echo frontend_escape($contentText('home.testimonials.positiveReviews.label')); ?></h5>
						</div>
						<!-- <a class='review-btn' href='contact.html'><img alt="icon" src="assets/img/icons/icon-3.svg">
							<span><span>Write your honest review</span> <i class="fa-solid fa-arrow-right-long"></i></span></a> -->
					</div>
				</div>
				<div class="col-xl-8">
					<div class="testimonial-slider-active" data-aos="fade-up" data-aos-delay="400" data-aos-duration="1000">
						<div class="swiper">
							<div class="swiper-wrapper">
								<div class="swiper-slide">
									<div class="testimonial-card">
										<div class="thumb">
											<img alt="thumb-10" src="assets/img/home/testimonials/yugal-lawaniya.jpg">
											<!-- <a class="video-play-btn" data-fancybox="" href="https://www.youtube.com/watch?v=fLeJJPxua3E&amp;ab_channel=Motiversity">Play</a> -->
										</div>
										<div class="card-content">
											<div class="rating">
												<p><?php echo frontend_escape($contentText('home.testimonials.ratingLabel')); ?></p><i class="fa-solid fa-star-sharp"></i> <span>5.0</span>
											</div>
											<div class="review">
												<p><?php echo frontend_escape($contentText('home.testimonials.items.0.review')); ?></p>
											</div>
											<div class="author-details">
												<h5><?php echo frontend_escape($contentArray('home.testimonials.items')[0]['name'] ?? ''); ?></h5>
												<!-- <h6>Village elder</h6> -->
											</div>
										</div>
									</div>
								</div>
								<div class="swiper-slide">
									<div class="testimonial-card">
										<div class="thumb">
											<img alt="thumb-10" src="assets/img/home/testimonials/vikas-chaudhary.jpg">
											<!-- <a class="video-play-btn" data-fancybox="" href="https://www.youtube.com/watch?v=fLeJJPxua3E&amp;ab_channel=Motiversity">Play</a> -->
										</div>
										<div class="card-content">
											<div class="rating">
												<p><?php echo frontend_escape($contentText('home.testimonials.ratingLabel')); ?></p><i class="fa-solid fa-star-sharp"></i> <span>5.0</span>
											</div>
											<div class="review">
												<p><?php echo frontend_escape($contentText('home.testimonials.items.1.review')); ?></p>
											</div>
											<div class="author-details">
												<h5><?php echo frontend_escape($contentArray('home.testimonials.items')[1]['name'] ?? ''); ?></h5>
												<!-- <h6>Sr. Volunteer</h6> -->
											</div>
										</div>
									</div>
								</div>
								<div class="swiper-slide">
									<div class="testimonial-card">
										<div class="thumb">
											<img alt="thumb-10" src="assets/img/home/testimonials/kush-chaudhary.jpg">
											<!-- <a class="video-play-btn" data-fancybox="" href="https://www.youtube.com/watch?v=fLeJJPxua3E&amp;ab_channel=Motiversity">Play</a> -->
										</div>
										<div class="card-content">
											<div class="rating">
												<p><?php echo frontend_escape($contentText('home.testimonials.ratingLabel')); ?></p><i class="fa-solid fa-star-sharp"></i> <span>5.0</span>
											</div>
											<div class="review">
												<p><?php echo frontend_escape($contentText('home.testimonials.items.2.review')); ?></p>
											</div>
											<div class="author-details">
												<h5><?php echo frontend_escape($contentArray('home.testimonials.items')[2]['name'] ?? ''); ?></h5>
												<!-- <h6>Sr. Volunteer</h6> -->
											</div>
										</div>
									</div>
								</div>
								<div class="swiper-slide">
									<div class="testimonial-card">
										<div class="thumb">
											<img alt="thumb-10" src="assets/img/home/testimonials/sachin-chaudhary.webp">
											<!-- <a class="video-play-btn" data-fancybox="" href="https://www.youtube.com/watch?v=fLeJJPxua3E&amp;ab_channel=Motiversity">Play</a> -->
										</div>
										<div class="card-content">
											<div class="rating">
												<p><?php echo frontend_escape($contentText('home.testimonials.ratingLabel')); ?></p><i class="fa-solid fa-star-sharp"></i> <span>5.0</span>
											</div>
											<div class="review">
												<p><?php echo frontend_escape($contentText('home.testimonials.items.3.review')); ?></p>
											</div>
											<div class="author-details">
												<h5><?php echo frontend_escape($contentArray('home.testimonials.items')[3]['name'] ?? ''); ?></h5>
												<!-- <h6>Sr. Volunteer</h6> -->
											</div>
										</div>
									</div>
								</div>
								<div class="swiper-slide">
									<div class="testimonial-card">
										<div class="thumb">
											<img alt="thumb-10" src="assets/img/home/testimonials/aman-upadhyay.jpg">
											<!-- <a class="video-play-btn" data-fancybox="" href="https://www.youtube.com/watch?v=fLeJJPxua3E&amp;ab_channel=Motiversity">Play</a> -->
										</div>
										<div class="card-content">
											<div class="rating">
												<p><?php echo frontend_escape($contentText('home.testimonials.ratingLabel')); ?></p><i class="fa-solid fa-star-sharp"></i> <span>5.0</span>
											</div>
											<div class="review">
												<p><?php echo frontend_escape($contentText('home.testimonials.items.4.review')); ?></p>
											</div>
											<div class="author-details">
												<h5><?php echo frontend_escape($contentArray('home.testimonials.items')[4]['name'] ?? ''); ?></h5>
												<!-- <h6>Village elder</h6> -->
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- testimonial end -->

    	<!-- completed-project start -->
	<section class="completed-project-section">
		<div class="completed-project-top" style="background-image: url(assets/img/bg/completed-project-bg.webp)">
			<div class="container">
				<div class="row align-items-end m-b-60 m-b-xs-40">
					<div class="col-xl-6 col-lg-6 col-md-7">
						<div class="common-subtitle style-color-2" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
							<img alt="icon-2" src="assets/img/icons/wheat.png" class="wheat-icon"> <span><?php echo frontend_escape($contentText('home.mediaCoverage.sectionSubtitle')); ?></span>
						</div>
						<div class="common-title style-color-light text-start m-b-0" data-aos="fade-up" data-aos-delay="400" data-aos-duration="1000">
							<h2><?php echo $contentText('home.mediaCoverage.sectionTitle'); ?></h2>
						</div>
					</div>
					<div class="col-xl-6 col-lg-6 col-md-5 text-md-end">
						<a class='e-primary-btn is-hover-white has-icon' data-aos-delay='600' data-aos-duration='1000' data-aos='fade-up' href='media-coverage.php'><?php echo frontend_escape($contentText('home.mediaCoverage.cta')); ?>
							<span class="icon-wrap"><span class="icon"><i class="fa-regular fa-arrow-right"></i> <i class="fa-regular fa-arrow-right"></i></span></span></a>
					</div>
				</div>
			</div>
			<!-- <div class="shape-8"><img alt="shape-8" src="assets/img/shapes/shape-8.webp"></div> -->
			<!-- <div class="shape-9"><img alt="shape-9" src="assets/img/shapes/shape-9.webp"></div> -->
		</div>
		<div class="completed-project-bottom">
			<div class="completed-project-slider-active" data-aos="fade-up" data-aos-delay="800" data-aos-duration="1000">
				<div class="swiper">
					<div class="swiper-wrapper">
						<div class="swiper-slide">
							<div class="project-card">
								<div class="thumb">
									<a href='media-coverage.php'><img alt="thumb-11" src="assets/img/home/media-coverage/news7.jpeg"></a>
									<div class="number">
										<a href='media-coverage.php'>No - 01</a>
									</div>
								</div>
							</div>
						</div>
						<div class="swiper-slide">
							<div class="project-card">
								<div class="thumb">
									<a href='media-coverage.php'><img alt="thumb-12" src="assets/img/home/media-coverage/news4.jpeg"></a>
									<div class="number">
										<a href='media-coverage.php'>No - 02</a>
									</div>
								</div>
							</div>
						</div>
						<div class="swiper-slide">
							<div class="project-card">
								<div class="thumb">
									<a href='media-coverage.php'><img alt="thumb-13" src="assets/img/home/media-coverage/news5.jpeg"></a>
									<div class="number">
										<a href='media-coverage.php'>No - 03</a>
									</div>
								</div>
							</div>
						</div>
						<div class="swiper-slide">
							<div class="project-card">
								<div class="thumb">
									<a href='media-coverage.php'><img alt="thumb-12" src="assets/img/home/media-coverage/news6.jpeg"></a>
									<div class="number">
										<a href='media-coverage.php'>No - 04</a>
									</div>
								</div>
							</div>
						</div>
						<div class="swiper-slide">
							<div class="project-card">
								<div class="thumb">
									<a href='media-coverage.php'><img alt="thumb-12" src="assets/img/home/media-coverage/news10.jpeg"></a>
									<div class="number">
										<a href='media-coverage.php'>No - 05</a>
									</div>
								</div>
							</div>
						</div>
						<div class="swiper-slide">
							<div class="project-card">
								<div class="thumb">
									<a href='media-coverage.php'><img alt="thumb-12" src="assets/img/home/media-coverage/news12.jpeg"></a>
									<div class="number">
										<a href='media-coverage.php'>No - 06</a>
									</div>
								</div>
							</div>
						</div>
						<div class="swiper-slide">
							<div class="project-card">
								<div class="thumb">
									<a href='media-coverage.php'><img alt="thumb-12" src="assets/img/home/media-coverage/news11.jpeg"></a>
									<div class="number">
										<a href='media-coverage.php'>No - 06</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="container">
					<div class="row">
						<div class="col-xl-12">
							<div class="completed-project-pagination"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- completed-project end -->
    <!-- faq-section start -->
	<section class="faq-section p-t-180 p-t-md-160 p-t-xs-140 p-b-120 p-b-md-100 p-b-xs-80">
		<div class="container">
			<div class="row faq">
				<div class="col-xl-6" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
					<div class="accordion faq-accordion" id="accordionFlushExample">
						<div class="accordion-item">
							<h2 class="accordion-header"><button aria-controls="flush-collapseOne" aria-expanded="false" class="accordion-button collapsed" data-bs-target="#flush-collapseOne" data-bs-toggle="collapse" type="button"><span class="accordion-title">1.</span><?php echo frontend_escape($contentText('home.faq.items.0.question')); ?> <span class="icon"><span class="icon-plus"></span> <span class="icon-minus"></span></span></button></h2>
							<div class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample" id="flush-collapseOne">
								<div class="accordion-body">
									<?php echo frontend_escape($contentText('home.faq.items.0.answer')); ?>
								</div>
							</div>
						</div>
						<div class="accordion-item">
							<h2 class="accordion-header"><button aria-controls="flush-collapseTwo" aria-expanded="false" class="accordion-button collapsed" data-bs-target="#flush-collapseTwo" data-bs-toggle="collapse" type="button"><span class="accordion-title">2.</span><?php echo frontend_escape($contentText('home.faq.items.1.question')); ?> <span class="icon"><span class="icon-plus"></span> <span class="icon-minus"></span></span></button></h2>
							<div class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample" id="flush-collapseTwo">
								<div class="accordion-body">
									<?php echo frontend_escape($contentText('home.faq.items.1.answer')); ?>
								</div>
							</div>
						</div>
						<div class="accordion-item">
							<h2 class="accordion-header"><button aria-controls="flush-collapseThree" aria-expanded="false" class="accordion-button" data-bs-target="#flush-collapseThree" data-bs-toggle="collapse" type="button"><span class="accordion-title">3.</span> <?php echo frontend_escape($contentText('home.faq.items.2.question')); ?> <span class="icon"><span class="icon-plus"></span> <span class="icon-minus"></span></span></button></h2>
							<div class="accordion-collapse collapse show" data-bs-parent="#accordionFlushExample" id="flush-collapseThree">
								<div class="accordion-body">
									<?php echo frontend_escape($contentText('home.faq.items.2.answer')); ?>
								</div>
							</div>
						</div>
						<div class="accordion-item">
							<h2 class="accordion-header"><button aria-controls="flush-collapseFour" aria-expanded="false" class="accordion-button collapsed" data-bs-target="#flush-collapseFour" data-bs-toggle="collapse" type="button"><span class="accordion-title">4.</span><?php echo frontend_escape($contentText('home.faq.items.3.question')); ?> <span class="icon"><span class="icon-plus"></span> <span class="icon-minus"></span></span></button></h2>
							<div class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample" id="flush-collapseFour">
								<div class="accordion-body">
									<?php echo frontend_escape($contentText('home.faq.items.3.answer')); ?>
								</div>
							</div>
						</div>
						<div class="accordion-item">
							<h2 class="accordion-header"><button aria-controls="flush-collapseFive" aria-expanded="false" class="accordion-button collapsed" data-bs-target="#flush-collapseFive" data-bs-toggle="collapse" type="button"><span class="accordion-title">5.</span><?php echo frontend_escape($contentText('home.faq.items.4.question')); ?> <span class="icon"><span class="icon-plus"></span> <span class="icon-minus"></span></span></button></h2>
							<div class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample" id="flush-collapseFive">
								<div class="accordion-body">
									<?php echo frontend_escape($contentText('home.faq.items.4.answer')); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-5" data-aos="fade-up" data-aos-delay="400" data-aos-duration="1000">
					<div class="common-subtitle">
						<img alt="icon-2" src="assets/img/icons/wheat.png" class="wheat-icon"> <span><?php echo frontend_escape($contentText('home.faq.sectionSubtitle')); ?></span>
					</div>
					<div class="common-title text-start">
						<h2><?php echo $contentText('home.faq.sectionTitle'); ?></h2>
					</div>
					<div class="text">
						<p><?php echo frontend_escape($contentText('home.faq.text')); ?></p>
					</div>
					<div class="blog-btn">
						<a class='e-primary-btn has-icon' href='contact.php'><?php echo frontend_escape($contentText('home.faq.cta')); ?> <span class="icon-wrap"><span class="icon"><i class="fa-regular fa-arrow-right"></i> <i class="fa-regular fa-arrow-right"></i></span></span></a>
					</div>
					<div class="top-right">
						<img alt="authors" src="assets/img/authors/author-1.webp">
						<div class="people-joined">
							<h5>50k+</h5><span><?php echo frontend_escape($contentText('home.hero.supporters.label')); ?></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- faq-section end -->
</main>

<?php include 'components/footer.php';?>
<?php include 'components/script.php';?>
</body>

</html>
