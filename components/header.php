<?php
require_once __DIR__ . '/frontend-init.php';

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$currentPath = str_replace('\\', '/', strtolower($currentPath));
$currentPath = rtrim($currentPath, '/');

if ($currentPath === '') {
	$currentPath = '/';
}

$isBlogs = strpos($currentPath, '/blogs') !== false;
$isHome = ($currentPath === '/' || preg_match('#/index\.php$#', $currentPath)) && !$isBlogs;
$isAbout = preg_match('#/about\.php$#', $currentPath) === 1;
$isWork = preg_match('#/our-work\.php$#', $currentPath) === 1;
$isEvents = preg_match('#/events\.php$#', $currentPath) === 1;
$isBlogsPage = preg_match('#/blogs(?:/(index|blog-detail)\.php)?$#', $currentPath) === 1;
$isGallery = preg_match('#/gallery\.php$#', $currentPath) === 1;
$isContact = preg_match('#/contact\.php$#', $currentPath) === 1;

$homeActive = $isHome ? 'active' : '';
$aboutActive = $isAbout ? 'active' : '';
$workActive = $isWork ? 'active' : '';
$eventsActive = $isEvents ? 'active' : '';
$blogsActive = $isBlogsPage ? 'active' : '';
$galleryActive = $isGallery ? 'active' : '';
$contactActive = $isContact ? 'active' : '';
$mediaActive = preg_match('#/media-coverage\.php$#', $currentPath) === 1 ? 'active' : '';

$currentLang = frontend_current_lang();
$toggleLang = $currentLang === 'hi' ? 'en' : 'hi';
$switchLabel = $currentLang === 'hi' ? translate('lang_en', 'EN') : translate('lang_hi', 'हिंदी');
$query = $_GET;
$query['lang'] = $toggleLang;
$switchUrl = $currentPath;
if ($switchUrl === '') {
	$switchUrl = '/';
}
$switchUrl .= '?' . http_build_query($query);
?>
<!-- header-section start -->
<header class="header-section-2">
	<div class="header-bottom-2">
		<div class="container">
			<div class="container-fluid">
			<div class="row">
				<div class="col-xl-12">
					<div class="header-bottom-layout">
						<div class="header-left-3">
							<div class="logo-wrap">
								<a href='index.php'>
									<img src="assets/img/logo/yogesh-logo-white.png" alt="logo"/>
								</a>
							</div>
							<nav class="main-menu-3 d-none d-xl-block">
								<ul>
									<li class="<?php echo $homeActive; ?>">
										<a href="index.php"><?php echo frontend_escape(translate('nav_home', 'Home')); ?></a>
										
									</li>
									<li class="<?php echo $aboutActive; ?>"><a href='about.php'><?php echo frontend_escape(translate('nav_about', 'About')); ?></a></li>
									<li class="<?php echo $workActive; ?>"><a href='our-work.php'><?php echo frontend_escape(translate('nav_our_work', 'Our Work')); ?></a></li>
									<li class="<?php echo $eventsActive; ?>">
										<a href='events.php'><?php echo frontend_escape(translate('nav_events', 'Events')); ?></a>
										
									</li>
									<li class="<?php echo $blogsActive; ?>">
										<a href="blogs/index.php"><?php echo frontend_escape(translate('nav_blogs', 'Blogs')); ?></a>
										
									</li>
									<!-- <li><a href='contact.php'>Contact Us</a></li> -->
	                            <li class="<?php echo $galleryActive; ?>"><a href='gallery.php'><?php echo frontend_escape(translate('nav_gallery', 'Gallery')); ?></a></li>
								<li class="<?php echo $mediaActive; ?>"><a href='media-coverage.php'><?php echo frontend_escape(translate('nav_media', 'Media')); ?></a></li>
								</ul>
							</nav>
						</div>
						<div class="header-right">
							<div class="header-btn-wrap d-none d-xl-flex me-2">
								<a class='e-primary-btn is-hover-white' href='<?php echo frontend_escape($switchUrl); ?>'>
									<?php echo frontend_escape($switchLabel); ?>
								</a>
							</div>
							<!-- <div class="header-info-2 d-none d-xl-flex">
								<div class="header-info-icon">
									<i class="fa-regular fa-phone-volume"></i>
								</div>
								<div class="header-info-content">
									<span>Contact Us!</span>
									<p><a href="tel:+1629555-0129">+1 (629) 555-0129</a></p>
								</div>
							</div> -->
							<div class="header-btn-wrap d-none d-xl-flex">
								<a class='e-primary-btn is-hover-white has-icon' href='contact.php'>
									<?php echo frontend_escape(translate('header_contact_now', 'Contact Now')); ?>
									<span class="icon-wrap">
										<span class="icon"><i class="fa-regular fa-arrow-right"></i><i class="fa-regular fa-arrow-right"></i></span>
									</span>
								</a>
							</div>
							<div class="header-bar-2 open-mobile-menu d-xl-none" data-toggle="menubar">
								<div class="bar bar-1"></div>
								<div class="bar bar-2"></div>
								<div class="bar bar-3"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		</div>
	</div>
</header>
<!-- header-section end -->

<!-- off-canvas-menubar start -->
<div class="off-canvas-menubar">
	<div class="off-canvas-menubar-body">
		<div class="off-canvas-head">
			<div class="off-canvas-logo">
				<a href='index.php'>
					<img src="assets/img/logo/yogesh-logo-white.png" alt="logo"/>
				</a>
			</div>
			<div class="off-canvas-menubar-close" data-close="menubar">
				<i class="fa-regular fa-xmark"></i>
			</div>
		</div>
		<div class="off-canvas-menu">
			<ul>
				<li class="<?php echo $homeActive; ?>">
					<a href="index.php"><?php echo frontend_escape(translate('nav_home', 'Home')); ?></a>
				</li>
				<li class="<?php echo $aboutActive; ?>"><a href='about.php'><?php echo frontend_escape(translate('nav_about', 'About')); ?></a></li>
				<li class="<?php echo $workActive; ?>"><a href='our-work.php'><?php echo frontend_escape(translate('nav_our_work', 'Our Work')); ?></a></li>
				<li class="<?php echo $eventsActive; ?>">
					<a href='events.php'><?php echo frontend_escape(translate('nav_events', 'Events')); ?></a>
				</li>
				<li class="<?php echo $blogsActive; ?>">
					<a href="blogs/index.php"><?php echo frontend_escape(translate('nav_blogs', 'Blogs')); ?></a>
				</li>
				<li class="<?php echo $galleryActive; ?>"><a href='gallery.php'><?php echo frontend_escape(translate('nav_gallery', 'Gallery')); ?></a></li>
				<li class="<?php echo $contactActive; ?>"><a href='contact.php'><?php echo frontend_escape(translate('nav_contact', 'Contact Us')); ?></a></li>
				<li class="<?php echo $mediaActive; ?>"><a href='media-coverage.php'><?php echo frontend_escape(translate('nav_media', 'Media')); ?></a></li>
				<li><a href="<?php echo frontend_escape($switchUrl); ?>"><?php echo frontend_escape($switchLabel); ?></a></li>

			</ul>
		</div>
		<div class="off-canvas-extra">
			<!-- <div class="off-canvas-profile">
				<img src="assets/img/logo/yogesh-logo.png" alt="Yogesh Nauhwar"/>
				<h5>Chaudhary Yogesh Nauhwar</h5>
				<p>MLC | Public Service | Development Work</p>
			</div> -->
			<div class="off-canvas-contact">
				<a class="off-canvas-contact-item" href="tel:+70264566579">
					<span class="icon"><i class="fa-solid fa-phone"></i></span>
					<span class="text">
						<h6>Call</h6>
						<p>+70 264 566 579</p>
					</span>
				</a>
				<a class="off-canvas-contact-item" href="mailto:yogeshnauhwar@gmail.com">
					<span class="icon"><i class="fa-solid fa-envelope"></i></span>
					<span class="text">
						<h6>Email</h6>
						<p>yogeshnauhwar@gmail.com</p>
					</span>
				</a>
				<div class="off-canvas-contact-item">
					<span class="icon"><i class="fa-solid fa-location-dot"></i></span>
					<span class="text">
						<h6>Address</h6>
						<p>Mant, Mathura, Uttar Pradesh</p>
					</span>
				</div>
			</div>
			<div class="off-canvas-social-links">
				<a href="https://www.facebook.com/ChaudharyYN/" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
				<!-- <a href="https://twitter.com/" target="_blank" rel="noopener noreferrer" aria-label="X"><i class="fab fa-x-twitter"></i></a> -->
				<a href="https://www.instagram.com/vidhayak_yogeshnauhwar/" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
				<!-- <a href="https://www.youtube.com/" target="_blank" rel="noopener noreferrer" aria-label="YouTube"><i class="fab fa-youtube"></i></a> -->
			</div>
		</div>
	</div>
	<div class="off-canvas-menubar-overlay" data-close="menubar"></div>
</div>
<!-- off-canvas-menubar end -->

<div class="floating-social-links" aria-label="Social links">
	<a href="https://www.facebook.com/ChaudharyYN/" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
		<i class="fab fa-facebook-f"></i>
	</a>
	<!-- <a href="https://twitter.com/" target="_blank" rel="noopener noreferrer" aria-label="X">
		<i class="fab fa-x-twitter"></i>
	</a> -->
	<a href="https://www.instagram.com/vidhayak_yogeshnauhwar/" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
		<i class="fab fa-instagram"></i>
	</a>
	<!-- <a href="https://linkedin.com/" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
		<i class="fab fa-linkedin-in"></i>
	</a> -->
</div>