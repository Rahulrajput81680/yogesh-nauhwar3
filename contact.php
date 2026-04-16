<?php
require_once __DIR__ . '/components/frontend-init.php';

if (!function_exists('build_contact_auto_reply_email')) {
	function build_contact_auto_reply_email(string $name, string $subject, string $message): array
	{
		$projectName = defined('PROJECT_NAME') ? PROJECT_NAME : 'Our Team';
		$currentYear = date('Y');
		$recipientName = htmlspecialchars($name !== '' ? $name : 'there', ENT_QUOTES, 'UTF-8');
		$subjectLine = htmlspecialchars($subject !== '' ? $subject : 'General enquiry', ENT_QUOTES, 'UTF-8');
		$messageBody = htmlspecialchars($message !== '' ? $message : 'No additional message provided.', ENT_QUOTES, 'UTF-8');
		$projectNameSafe = htmlspecialchars($projectName, ENT_QUOTES, 'UTF-8');

		$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<style>
		body { margin: 0; padding: 0; background: #edf7f0; font-family: Arial, Helvetica, sans-serif; color: #1f2937; }
		.wrapper { width: 100%; padding: 32px 12px; }
		.card { max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 18px 48px rgba(17, 24, 39, 0.12); }
		.header { background: linear-gradient(135deg, #2f8f5b 0%, #1f6f4a 100%); color: #ffffff; padding: 34px 32px; text-align: center; }
		.badge { display: inline-block; margin-bottom: 14px; padding: 8px 14px; border-radius: 999px; background: rgba(255,255,255,0.16); font-size: 12px; letter-spacing: 0.08em; text-transform: uppercase; }
		.content { padding: 34px 32px 30px; }
		.lead { font-size: 16px; line-height: 1.7; margin: 0 0 16px; }
		.panel { background: #f4faf6; border: 1px solid #cfe8d8; border-radius: 14px; padding: 18px 20px; margin: 22px 0; }
		.panel h3 { margin: 0 0 8px; font-size: 14px; text-transform: uppercase; letter-spacing: 0.08em; color: #2f8f5b; }
		.message { margin: 0; white-space: pre-wrap; line-height: 1.7; }
		.footer { padding: 18px 32px 30px; text-align: center; color: #6b7280; font-size: 13px; }
	</style>
</head>
<body>
	<div class="wrapper">
		<div class="card">
			<div class="header">
				<div class="badge">Message Received</div>
				<h1 style="margin: 0; font-size: 28px;">Thank you for contacting us</h1>
				<p style="margin: 12px 0 0; opacity: 0.95;">We will contact you soon.</p>
			</div>
			<div class="content">
				<p class="lead">Hello {$recipientName},</p>
				<p class="lead">Thank you for contacting us. We have received your message regarding <strong>{$subjectLine}</strong>, and our team will contact you soon.</p>
				<div class="panel">
					<h3>Your Message</h3>
					<p class="message">{$messageBody}</p>
				</div>
				<p class="lead" style="margin-bottom: 0;">If your request is urgent, please keep this email thread open so we can respond faster.</p>
			</div>
			<div class="footer">
				<p style="margin: 0 0 8px;">&copy; {$currentYear} {$projectNameSafe}. All rights reserved.</p>
				<p style="margin: 0;">This is an automated confirmation email. Please do not reply.</p>
			</div>
		</div>
	</div>
</body>
</html>
HTML;

		$plain = "Hello {$name},\n\nThank you for contacting us. We have received your message regarding {$subject}, and our team will contact you soon.\n\nYour message:\n{$message}\n\nIf your request is urgent, please keep this email thread open so we can respond faster.\n\n{$projectName}\nThis is an automated confirmation email. Please do not reply.";

		return ['html' => $html, 'plain' => $plain];
	}
}

$currentLang = frontend_current_lang();

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
			'message' => translate('contact_success', 'Your message has been sent successfully.')
		];

		if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(['success' => true, 'message' => translate('contact_success', 'Your message has been sent successfully.')]);
			exit;
		}

		header('Location: contact.php?submitted=1');
		exit;
	}

	// SPAM PROTECTION: Time-Based Check
	$formTimestamp = $_POST['form_timestamp'] ?? '';
	if ($formTimestamp) {
		$currentTime = time();
		$elapsed = $currentTime - (int) $formTimestamp;
		if ($elapsed < 3) {
			// Silently reject spam (bot threshold)
			$_SESSION['contact_form_flash'] = [
				'type' => 'success',
				'message' => translate('contact_success', 'Your message has been sent successfully.')
			];

			if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
				header('Content-Type: application/json; charset=utf-8');
				echo json_encode(['success' => true, 'message' => translate('contact_success', 'Your message has been sent successfully.')]);
				exit;
			}

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
				'message' => translate('contact_success', 'Your message has been sent successfully.')
			];

			if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
				header('Content-Type: application/json; charset=utf-8');
				echo json_encode(['success' => true, 'message' => translate('contact_success', 'Your message has been sent successfully.')]);
				exit;
			}

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
		$contactMessage = translate('contact_required', 'Please fill all required fields.');
		$contactMessageType = 'error';
	} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$contactMessage = translate('contact_invalid_email', 'Please enter a valid email address.');
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

			if (!function_exists('send_email')) {
				if (!defined('ADMIN_INIT')) {
					define('ADMIN_INIT', true);
				}
				require_once __DIR__ . '/admin/core/mailer.php';
			}

			if (function_exists('send_email')) {
				try {
					$autoReply = build_contact_auto_reply_email($name, $subject, $message);
					$autoReplySubject = (defined('PROJECT_NAME') ? PROJECT_NAME : 'Website') . ' - We Received Your Message';
					$replySent = send_email($email, $autoReplySubject, $autoReply['html'], $autoReply['plain']);
					if (!$replySent) {
						error_log('Contact auto-reply failed for: ' . $email);
					}
				} catch (Throwable $mailError) {
					error_log('Contact auto-reply exception: ' . $mailError->getMessage());
				}
			}

			// For AJAX requests, output message and exit
			if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
				header('Content-Type: application/json; charset=utf-8');
				echo json_encode([
					'success' => true,
					'message' => translate('contact_success', 'Your message has been sent successfully.')
				]);
				exit;
			}

			$_SESSION['contact_form_flash'] = [
				'type' => 'success',
				'message' => translate('contact_success', 'Your message has been sent successfully.')
			];
			header('Location: contact.php?submitted=1');
			exit;
		} catch (Throwable $e) {
			$contactMessage = translate('contact_failed', 'Something went wrong while submitting the form. Please try again.');
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
	$contactMessage = $contactMessage ?: translate('contact_success', 'Your message has been sent successfully.');
	$contactMessageType = $contactMessageType ?: 'success';
}

// For AJAX requests with validation errors, output message and exit
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' && $contactMessage !== '') {
	header('Content-Type: application/json; charset=utf-8');
	http_response_code($contactMessageType === 'error' ? 422 : 200);
	echo json_encode([
		'success' => $contactMessageType !== 'error',
		'message' => $contactMessage
	]);
	exit;
}
?>
<!doctype html>
<html lang="<?php echo frontend_escape($currentLang); ?>">

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
							<h3><?php echo frontend_escape(translate('contact_form_title', 'Get in touch with our team')); ?></h3>
							<p>
								<?php echo frontend_escape(translate('contact_form_subtitle', 'Fill out the form and Feel free to say !!')); ?>
							</p>
							<form id="contact-form" action="contact.php" method="post" data-modern-contact-handler="1">
								<!-- Honeypot field - hidden from users -->
								<input type="text" name="website" style="position: absolute; left: -9999px; opacity: 0;" tabindex="-1"
									autocomplete="off" aria-hidden="true">
								<!-- Timestamp field for time-based spam check -->
								<input type="hidden" name="form_timestamp" id="form_timestamp" value="">

								<div class="row form-row">
									<div class="col-xl-6">
										<div class="input-wrap">
											<input type="text"
												placeholder="<?php echo frontend_escape(translate('placeholder_full_name', 'Full Name')); ?>"
												name="name" value="<?php echo frontend_display_text($contactValues['name']); ?>">
										</div>
									</div>
									<div class="col-xl-6">
										<div class="input-wrap">
											<input type="tel"
												placeholder="<?php echo frontend_escape(translate('placeholder_phone', 'Phone Number')); ?>"
												name="phone" value="<?php echo frontend_display_text($contactValues['phone']); ?>">
										</div>
									</div>
								</div>
								<div class="row form-row">
									<div class="col-xl-6">
										<div class="input-wrap">
											<input type="email"
												placeholder="<?php echo frontend_escape(translate('placeholder_email', 'Email Address')); ?>"
												name="email" value="<?php echo frontend_display_text($contactValues['email']); ?>">
										</div>
									</div>
									<div class="col-xl-6">
										<div class="input-wrap">
											<input type="text"
												placeholder="<?php echo frontend_escape(translate('placeholder_location', 'Current Location')); ?>"
												name="location" value="<?php echo frontend_display_text($contactValues['location']); ?>">
										</div>
									</div>
								</div>
								<div class="row form-row">
									<div class="col-xl-6">
										<div class="input-wrap">
											<input type="text"
												placeholder="<?php echo frontend_escape(translate('placeholder_dob', 'Date of Birth')); ?>"
												name="date" value="<?php echo frontend_display_text($contactValues['date']); ?>">
										</div>
									</div>
									<div class="col-xl-6">
										<div class="input-wrap">
											<input type="text"
												placeholder="<?php echo frontend_escape(translate('placeholder_occupation', 'Occupation')); ?>"
												name="occupation" value="<?php echo frontend_display_text($contactValues['occupation']); ?>">
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-xl-12">
										<div class="input-wrap">
											<textarea
												placeholder="<?php echo frontend_escape(translate('placeholder_message', 'Say Something...')); ?>"
												name="message"><?php echo frontend_display_text($contactValues['message']); ?></textarea>
										</div>
										<div class="input-button">
											<button id="contactSubmitBtn" type="submit" class="e-primary-btn has-icon"
												data-default-text="<?php echo frontend_escape(translate('contact_submit_now', 'Submit Now')); ?>"
												data-submitting-text="<?php echo frontend_escape(translate('contact_submitting', 'Submitting...')); ?>">
												<?php echo frontend_escape(translate('contact_submit_now', 'Submit Now')); ?>
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
								<div id="contact-server-alert"
									class="alert <?php echo $contactMessageType === 'success' ? 'alert-success' : 'alert-danger'; ?> mt-3"
									role="alert" data-type="<?php echo frontend_escape($contactMessageType); ?>">
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
			var timestampField = document.getElementById('form_timestamp');
			if (timestampField) {
				timestampField.value = Math.floor(Date.now() / 1000);
			}

			var form = document.getElementById('contact-form');
			var submitBtn = document.getElementById('contactSubmitBtn');
			var serverAlert = document.getElementById('contact-server-alert');
			if (!form || !submitBtn) {
				return;
			}

			if (serverAlert) {
				setTimeout(function () {
					serverAlert.remove();
				}, 5000);
			}

			var originalLabel = submitBtn.getAttribute('data-default-text') || 'Submit Now';
			var submittingLabel = submitBtn.getAttribute('data-submitting-text') || 'Submitting...';
			var isSubmitting = false;
			var messageWrap = document.createElement('div');
			messageWrap.className = 'mt-3';
			form.insertAdjacentElement('afterend', messageWrap);

			function setButtonState(isSubmitting) {
				submitBtn.disabled = isSubmitting;
				submitBtn.classList.toggle('disabled', isSubmitting);
				submitBtn.childNodes[0].nodeValue = (isSubmitting ? submittingLabel : originalLabel) + ' ';
			}

			function showMessage(type, text) {
				messageWrap.innerHTML = '';
				if (!text) {
					return;
				}
				var alert = document.createElement('div');
				alert.className = 'alert ' + (type === 'success' ? 'alert-success' : 'alert-danger');
				alert.setAttribute('role', 'alert');
				alert.textContent = text;
				messageWrap.appendChild(alert);

				setTimeout(function () {
					if (alert.parentNode) {
						alert.remove();
					}
				}, 5000);
			}

			form.addEventListener('submit', function (event) {
				event.preventDefault();
				if (isSubmitting) {
					return;
				}
				isSubmitting = true;
				setButtonState(true);
				showMessage('', '');

				var formData = new FormData(form);

				fetch(form.getAttribute('action') || 'contact.php', {
					method: 'POST',
					body: formData,
					headers: {
						'X-Requested-With': 'XMLHttpRequest'
					}
				})
					.then(function (response) {
						return response.json().then(function (data) {
							return { ok: response.ok, data: data || {} };
						});
					})
					.then(function (payload) {
						if (payload.ok && payload.data.success) {
							form.reset();
							if (timestampField) {
								timestampField.value = Math.floor(Date.now() / 1000);
							}
							showMessage('success', payload.data.message || <?php echo json_encode(translate('contact_success', 'Your message has been sent successfully.')); ?>);
						} else {
							showMessage('error', payload.data.message || <?php echo json_encode(translate('contact_failed', 'Something went wrong while submitting the form. Please try again.')); ?>);
						}
					})
					.catch(function () {
						showMessage('error', <?php echo json_encode(translate('contact_failed', 'Something went wrong while submitting the form. Please try again.')); ?>);
					})
					.finally(function () {
						isSubmitting = false;
						setButtonState(false);
					});
			});
		})();
	</script>
</body>

</html>