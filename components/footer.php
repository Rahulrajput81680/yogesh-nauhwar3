<!-- footer-section start -->
<?php
$footerContentText = (isset($contentText) && is_callable($contentText))
	? $contentText
	: static function (string $path): string {
		$value = frontend_content($path);
		return (is_string($value) && $value !== '') ? $value : '';
	};
?>
<footer class="footer-section footer-section-2 p-t-55 p-t-md-100 p-t-xs-80 p-b-20">
	<div class="container">
		<div class="row justify-content-between row-gap-md-5 row-gap-4 p-b-40">
			<div class="col-xl-4 col-lg-8 col-md-7">
				<div class="footer-widget">
					<div class="about-widget">
						<div class="footer-logo">
							<a href='index.html'>
								<img src="assets/img/logo/yogesh-logo-white.png" alt="logo"/>
							</a>
						</div>
						<div class="text">
							<p>
								<?php echo frontend_escape($footerContentText('footer-description.title')); ?>
							</p>
						</div>
						<!-- <div class="info">
							<p><b>We Are Available !!</b></p>
							<p>Mon-Sat: <span>10:00am to 07:30pm</span></p>
						</div> -->
						<div class="social-links">
							<a href="https://www.facebook.com/ChaudharyYN/">
								<i class="fab fa-facebook-f"></i>
							</a>
							<!-- <a href="https://twitter.com/">
								<i class="fab fa-x-twitter"></i>
							</a> -->
							<a href="https://www.instagram.com/vidhayak_yogeshnauhwar/">
								<i class="fab fa-instagram"></i>
							</a>
							<!-- <a href="https://linkedin.com/">
								<i class="fab fa-linkedin-in"></i>
							</a> -->
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-2 col-lg-4 col-md-5">
				<div class="footer-widget">
					<h3 class="w-title">
						<span><img src="assets/img/icons/wheat.png" alt="icon"/></span>
						Quick Links
					</h3>
					<ul>
						<li><a href='index.php'>Home</a></li>
						<li><a href='about.php'>About</a></li>
						<li><a href='events.php'>Events</a></li>
						<li><a href='our-work.php'>Our Work</a></li>
						<li><a href='blogs/index.php'>Blogs</a></li>
						<li><a href='gallery.php'>Gallery</a></li>
						
					</ul>
				</div>
			</div>
			<div class="col-xl-3 col-lg-4 col-md-5">
				<div class="footer-widget">
					<h3 class="w-title">
						<span><img src="assets/img/icons/wheat.png" alt="icon"/></span>
						Our Events
					</h3>
					<ul>
						<li><a href='events.php'>Hindi Bhasha Samman Protest </a></li>
						<li><a href='events.php'>Press Conference</a></li>
						<li><a href='events.php'>Electric Bus Launch</a></li>
						<li><a href='events.php'>Tree Plantation Drive</a></li>
						<li><a href='events.php'>Tablet Distribution</a></li>
						<li><a href='events.php'>Skill India Mobile Van</a></li>
					</ul>
				</div>
			</div>
			<div class="col-xl-3 col-lg-4 col-md-5">
				<div class="footer-widget">
					<h3 class="w-title">
						<span><img src="assets/img/icons/wheat.png" alt="icon"/></span>
						Get in Touch
					</h3>
					<div class="get-in-touch">
						<a href="#" class="footer-address">
							<div class="icon">
								<i class="fa-solid fa-location-dot"></i>
							</div>
							<div class="text">
								<h6>Address</h6>
								<p>Mant, Mathura, Uttar Pradesh</p>
							</div>
						</a>
						<a href="mailto:support@example.com" class="email">
							<div class="icon">
								<i class="fa-solid fa-paper-plane"></i>
							</div>
							<div class="text">
								<h6>Email</h6>
								<p>yogeshnauhwar@gmail.com</p>
							</div>
						</a>
						<a href="tel:+70264566579" class="phone">
							<div class="icon">
								<i class="fa-solid fa-phone-arrow-up-right"></i>
							</div>
							<div class="text">
								<h6>Phone</h6>
								<p>+70 264 566 579</p>
							</div>
						</a>
					</div>
				</div>
			</div>
		</div>
		<div class="footer-bottom">
		<div class="row">
			<div class="col-xl-12">
				<div class="container">
					<div class="footer-bottom-layout">
						<div class="footer-copyright">
							© <?php echo date('Y'); ?> Yogesh Nauhwar. All Rights Reserved.
						</div>
						<div class="footer-bottom-menu">
							<ul>
								<li><a href='terms-and-conditions.php'>Terms & Conditions</a></li>
								<li><a href='privacy-policy.php'>Privacy Policy</a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>
</footer>
<!-- footer-section end -->
