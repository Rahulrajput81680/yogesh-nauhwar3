<?php
require_once __DIR__ . '/components/frontend-init.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

$contactMessage = '';
$contactMessageType = '';
$contactValues = [
	'name' => '',
	'phone' => '',
	'email' => '',
	'location' => '',
	'date' => '',
	'occupation' => '',
	'message' => '',
];

if (!empty($_SESSION['contact_form_flash'])) {
	$flash = $_SESSION['contact_form_flash'];
	unset($_SESSION['contact_form_flash']);
	$contactMessage = $flash['message'] ?? '';
	$contactMessageType = $flash['type'] ?? '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
	
	// SPAM PROTECTION: Honeypot Field Check
	$honeypotField = trim($_POST['website'] ?? '');
	if ($honeypotField !== '') {
		// Silently reject spam without showing error
		$_SESSION['contact_form_flash'] = [
			'type' => 'success',
			'message' => 'Your contact form is submitted successfully.'
		];
		header('Location: contact.php?submitted=1');
		exit;
	}
	
	// SPAM PROTECTION: Time-Based Check
	$formTimestamp = $_POST['form_timestamp'] ?? '';
	if ($formTimestamp) {
		$currentTime = time();
		$elapsed = $currentTime - (int)$formTimestamp;
		if ($elapsed < 3) {
			// Silently reject spam (bot threshold)
			$_SESSION['contact_form_flash'] = [
				'type' => 'success',
				'message' => 'Your contact form is submitted successfully.'
			];
			header('Location: contact.php?submitted=1');
			exit;
		}
	}
	
	// SPAM PROTECTION: Rate Limiting (3 submissions per IP per 10 minutes)
	try {
		$pdo = frontend_db();
		$tenMinutesAgo = date('Y-m-d H:i:s', strtotime('-10 minutes'));
		
		$stmt = $pdo->prepare("SELECT COUNT(*) FROM contact_messages WHERE ip_address = ? AND created_at > ?");
		$stmt->execute([$clientIp, $tenMinutesAgo]);
		$submissionCount = $stmt->fetchColumn();
		
		if ($submissionCount >= 10) {
			// Silently return success (fake 200 OK, not an error)
			$_SESSION['contact_form_flash'] = [
				'type' => 'success',
				'message' => 'Your contact form is submitted successfully.'
			];
			header('Location: contact.php?submitted=1');
			exit;
		}
	} catch (Throwable $e) {
		// Continue processing if rate limit check fails
	}
	
	$name = trim($_POST['name'] ?? '');
	$phone = trim($_POST['phone'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$location = trim($_POST['location'] ?? '');
	$dateOfBirth = trim($_POST['date'] ?? '');
	$occupation = trim($_POST['occupation'] ?? '');
	$message = trim($_POST['message'] ?? '');
	$contactValues = [
		'name' => $name,
		'phone' => $phone,
		'email' => $email,
		'location' => $location,
		'date' => $dateOfBirth,
		'occupation' => $occupation,
		'message' => $message,
	];

	if ($name === '' || $email === '' || $message === '') {
		$contactMessage = 'Please fill all required fields.';
		$contactMessageType = 'error';
	} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$contactMessage = 'Please enter a valid email address.';
		$contactMessageType = 'error';
	} else {
		$subjectParts = [];
		if ($location !== '') {
			$subjectParts[] = 'Location: ' . $location;
		}
		if ($occupation !== '') {
			$subjectParts[] = 'Occupation: ' . $occupation;
		}
		$subject = !empty($subjectParts) ? implode(' | ', $subjectParts) : 'Website Contact Form';

		try {
			$pdo = frontend_db();
			$columns = ['name', 'email', 'phone', 'subject', 'message', 'status', 'ip_address'];
			$values = [$name, $email, $phone !== '' ? $phone : null, $subject, $message, 'unread', $_SERVER['REMOTE_ADDR'] ?? null];

			if (frontend_has_column($pdo, 'contact_messages', 'location')) {
				$columns[] = 'location';
				$values[] = $location !== '' ? $location : null;
			}
			if (frontend_has_column($pdo, 'contact_messages', 'date_of_birth')) {
				$columns[] = 'date_of_birth';
				$values[] = $dateOfBirth !== '' ? $dateOfBirth : null;
			}
			if (frontend_has_column($pdo, 'contact_messages', 'occupation')) {
				$columns[] = 'occupation';
				$values[] = $occupation !== '' ? $occupation : null;
			}

			$columns[] = 'created_at';

			$columnSql = implode(', ', $columns);
			$placeholderSql = implode(', ', array_fill(0, count($values), '?')) . ', NOW()';
			$stmt = $pdo->prepare("INSERT INTO contact_messages ({$columnSql}) VALUES ({$placeholderSql})");
			$stmt->execute($values);

			// For AJAX requests, output message and exit
			if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
				echo 'Your contact form is submitted successfully.';
				exit;
			}

			$_SESSION['contact_form_flash'] = [
				'type' => 'success',
				'message' => 'Your contact form is submitted successfully.'
			];
			header('Location: contact.php?submitted=1');
			exit;
		} catch (Throwable $e) {
			$contactMessage = 'Something went wrong while submitting the form. Please try again.';
			$contactMessageType = 'error';
			$contactValues = [
				'name' => $name,
				'phone' => $phone,
				'email' => $email,
				'location' => $location,
				'date' => $dateOfBirth,
				'occupation' => $occupation,
				'message' => $message,
			];
		}
	}
} elseif (!empty($_GET['submitted'])) {
	$contactMessage = $contactMessage ?: 'Your contact form is submitted successfully.';
	$contactMessageType = $contactMessageType ?: 'success';
}

// For AJAX requests with validation errors, output message and exit
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' && $contactMessage !== '') {
	http_response_code($contactMessageType === 'error' ? 400 : 200);
	echo $contactMessage;
	exit;
}
?>
<!doctype html>
<html lang="en">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport">
	<title>Contact Yogesh Nauhwar — MLC UP | Reach RLD Office Mathura Uttar Pradesh</title>
	<meta name="description"
		content="Contact Chaudhary Yogesh Nauhwar MLC for grievances, government scheme help, or party membership. Reach our Mathura office or connect via WhatsApp, Facebook, and Instagram.">
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
									<li><a href='index.html'>Home</a></li>
									<li><a href="#">Contact</a></li>
								</ul>
							</div>
							<div class="breadcrumb-title" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="400">
								<h2>Contact Us</h2>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- breadcrumb-section end -->

		<!-- services-section start -->
		<section class="services-section p-t-120">
			<div class="container">
				<div class="row" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="600">
					<div class="col-xl-4">
						<div class="service-card">
							<div class="service-top">
								<h4>Our Location</h4>
								<i class="fa-light fa-location-dot"></i>
							</div>
							<div class="service-content">
								<p>2972 Westheimer Rd. Santa Ana,<br> Illinois 85486 </p>
							</div>
							<!-- <div class="i-shape">
							<i class="fa-light fa-location-dot"></i>
						</div> -->
						</div>
					</div>
					<div class="col-xl-4">
						<div class="service-card">
							<div class="service-top">
								<h4>Phone
									Numbers</h4>
								<i class="fa-light fa-phone-volume"></i>
							</div>
							<div class="service-content">
								<p>+02 (54) 669 - 2589 <br>
									+00 (307) 555 - 0133 </p>
							</div>
							<!-- <div class="i-shape">
							<i class="fa-light fa-phone-volume"></i>
						</div> -->
						</div>
					</div>
					<div class="col-xl-4">
						<div class="service-card">
							<div class="service-top">
								<h4>Email
									Address</h4>
								<i class="fa-light fa-envelope"></i>
							</div>
							<div class="service-content">
								<p>bizcase.info@example.com
									support@example.com</p>
							</div>
							<!-- <div class="i-shape">
							<i class="fa-light fa-envelope"></i>
						</div> -->
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- services-section end -->

		<!-- contact-section start -->
		<div class="contact-section p-t-120 p-b-120">
			<div class="container">
				<div class="row justify-content-center">
					<div class="col-xl-9">
						<div class="contact-form-wrap" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
							<h3>Get in touch with our team</h3>
							<p>Fill out the form and Feel free to say !!</p>
							<form id="contact-form" action="contact.php" method="post">
								<!-- Honeypot field - hidden from users -->
								<input type="text" name="website" style="position: absolute; left: -9999px; opacity: 0;" tabindex="-1" autocomplete="off" aria-hidden="true">
								<!-- Timestamp field for time-based spam check -->
								<input type="hidden" name="form_timestamp" id="form_timestamp" value="">
								
								<div class="row form-row">
									<div class="col-xl-6">
										<div class="input-wrap">
											<input type="text" placeholder="Full Name" name="name"
												value="<?php echo frontend_display_text($contactValues['name']); ?>">
										</div>
									</div>
									<div class="col-xl-6">
										<div class="input-wrap">
											<input type="tel" placeholder="Phone Number" name="phone"
												value="<?php echo frontend_display_text($contactValues['phone']); ?>">
										</div>
									</div>
								</div>
								<div class="row form-row">
									<div class="col-xl-6">
										<div class="input-wrap">
											<input type="email" placeholder="Email Address" name="email"
												value="<?php echo frontend_display_text($contactValues['email']); ?>">
										</div>
									</div>
									<div class="col-xl-6">
										<div class="input-wrap">
											<input type="text" placeholder="Current Location" name="location"
												value="<?php echo frontend_display_text($contactValues['location']); ?>">
										</div>
									</div>
								</div>
								<div class="row form-row">
									<div class="col-xl-6">
										<div class="input-wrap">
											<input type="text" placeholder="Date of Birth" name="date"
												value="<?php echo frontend_display_text($contactValues['date']); ?>">
										</div>
									</div>
									<div class="col-xl-6">
									<div class="input-wrap">
										<input type="text" placeholder="Occupation" name="occupation"
											value="<?php echo frontend_display_text($contactValues['occupation']); ?>">
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-xl-12">
										<div class="input-wrap">
											<textarea placeholder="Say Something..."
												name="message"><?php echo frontend_display_text($contactValues['message']); ?></textarea>
										</div>
										<div class="input-button">
											<button id="contactSubmitBtn" type="submit" class="e-primary-btn has-icon" data-default-text="Submit Now" data-submitting-text="Submitting...">
												Submit Now
												<span class="icon-wrap">
													<span class="icon"><i class="fa-regular fa-arrow-right"></i><i
															class="fa-regular fa-arrow-right"></i></span>
												</span>
											</button>
										</div>
									</div>
								</div>
							</form>
							<?php if ($contactMessage !== ''): ?>
								<div class="alert <?php echo $contactMessageType === 'success' ? 'alert-success' : 'alert-danger'; ?> mt-3" role="alert">
									<?php echo frontend_escape($contactMessage); ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
			<!-- <div class="c-shape-1">
			<img src="assets/img/shapes/shape-34.webp" alt="shape">
		</div>
		<div class="c-shape-2">
			<img src="assets/img/shapes/shape-35.webp" alt="shape">
		</div> -->
		</div>
		<!-- contact-section end -->

		<!-- map-section start -->
		<!-- <div class="map-section">
		<div class="contact-map" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
			<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3154.9740031200513!2d-122.44247972367066!3d37.74375411394556!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x808f7e739760c5d9%3A0x9ad85f5ebc3112d4!2s5214f%20Diamond%20Heights%20Blvd%20%23553%2C%20San%20Francisco%2C%20CA%2094131%2C%20USA!5e0!3m2!1sen!2sbd!4v1743169375746!5m2!1sen!2sbd" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
		</div>
	</div> -->
		<!-- map-section end -->


	</main>

	<?php include 'components/footer.php'; ?>
	<?php include 'components/script.php'; ?>
	<script>
		(function () {
			// Set form timestamp for spam protection on page load
			document.getElementById('form_timestamp').value = Math.floor(Date.now() / 1000);
			
			var form = document.getElementById('contact-form');
			var submitBtn = document.getElementById('contactSubmitBtn');
			if (!form || !submitBtn) {
				return;
			}

			form.addEventListener('submit', function () {
				submitBtn.disabled = true;
				submitBtn.classList.add('disabled');
				submitBtn.childNodes[0].nodeValue = submitBtn.getAttribute('data-submitting-text') + ' ';
			});
		})();
	</script>
</body>

</html>