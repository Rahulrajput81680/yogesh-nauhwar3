<?php require_once __DIR__ . '/components/frontend-init.php'; ?>
<!DOCTYPE html>
<html lang="<?php echo frontend_escape(frontend_current_lang()); ?>">

<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<title>Events — Yogesh Nauhwar MLC | Jan Sampark, Rallies & Public Programs Mathura</title>
	<meta name="description"
		content="View all events, public meetings, rallies, cultural programs, and Jan Sampark activities of Chaudhary Yogesh Nauhwar MLC — RLD leader serving the people of Mant and Mathura, Uttar Pradesh.">
	<?php include 'components/links.php'; ?>
</head>

<body class="inner-page">
	<?php include 'components/loader.php'; ?>
	<?php include 'components/header.php'; ?>

	<?php
	$events = [];

	try {
		$pdo = frontend_db();
		// Show all published events, not just active status
		$stmt = $pdo->query("SELECT * FROM events ORDER BY COALESCE(event_date, created_at) DESC, id DESC");
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		foreach ($rows as $row) {
			// Skip if status is explicitly inactive
			if (($row['status'] ?? 'active') === 'inactive') {
				continue;
			}

			$events[] = [
				'image' => !empty($row['image']) ? frontend_upload_url($row['image']) : 'assets/img/events/event1.jpg',
				'category' => $row['category'] ?: 'General',
				'title' => $row['title'] ?? '',
				'description' => $row['description'] ?? '',
				'status' => $row['event_type'] ?? 'upcoming',
			];
		}
	} catch (Throwable $e) {
		echo '<!-- Events Error: ' . htmlspecialchars($e->getMessage()) . ' -->';
		$events = [];
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
									<li><a href="#">Events</a></li>
								</ul>
							</div>
							<div class="breadcrumb-title" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="400">
								<h2>Events</h2>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section class="services-section p-t-100 p-b-120 p-t-xs-80 p-b-xs-80">
			<div class="container">
				<div class="row justify-content-center text-center m-b-40">
					<div class="col-xl-8">
						<div class="common-subtitle" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
							<img alt="icon-1" src="assets/img/icons/wheat.png" class="wheat-icon"> <span>Events and Activities</span>
						</div>
						<div class="common-title m-b-0" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="400">
							<h2>From Villages to Vidhan Parishad</h2>
						</div>
					</div>
				</div>

				<div class="event-filter-wrap text-center m-b-40" data-aos="fade-up" data-aos-duration="1000"
					data-aos-delay="500">
					<button class="event-filter-btn active" type="button" data-filter="all">All Events</button>
					<button class="event-filter-btn" type="button" data-filter="upcoming">Upcoming Events</button>
					<button class="event-filter-btn" type="button" data-filter="past">Past Events</button>
				</div>

				<div class="row" id="eventsGrid" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="600">
					<?php if (empty($events)): ?>
						<div class="col-12 text-center">
							<p>No events available right now.</p>
						</div>
					<?php else: ?>
						<?php foreach ($events as $event): ?>
							<div class="col-xl-4 col-md-6 m-b-30 event-card-item"
								data-event-type="<?php echo frontend_escape($event['status']); ?>">
								<div class="camping-card">
									<div class="thumb">
										<img alt="event-thumb" src="<?php echo frontend_escape($event['image']); ?>">
										<div class="category">
											<a><?php echo frontend_escape($event['category']); ?></a>
										</div>
									</div>
									<div class="content">
										<div class="content-top">
											<div class="title">
												<h3><a><?php echo frontend_escape($event['title']); ?></a></h3>
											</div>
											<div class="text">
												<p><?php echo frontend_escape($event['description']); ?></p>
											</div>
										</div>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>

				<div class="row justify-content-center text-center m-t-20">
					<div class="col-xl-6">
						<div class="project-pagination" id="eventsPagination"></div>
					</div>
				</div>
			</div>
		</section>
	</main>

	<?php include 'components/footer.php'; ?>
	<?php include 'components/script.php'; ?>

	<script>
		(function () {
			var cards = Array.prototype.slice.call(document.querySelectorAll('.event-card-item'));
			var filterButtons = Array.prototype.slice.call(document.querySelectorAll('.event-filter-btn'));
			var paginationWrap = document.getElementById('eventsPagination');
			var cardsPerPage = 9;
			var currentFilter = 'all';
			var currentPage = 1;

			function getFilteredCards() {
				if (currentFilter === 'all') {
					return cards;
				}
				return cards.filter(function (card) {
					return card.getAttribute('data-event-type') === currentFilter;
				});
			}

			function renderPagination(totalPages) {
				if (!paginationWrap) {
					return;
				}
				if (totalPages <= 1) {
					paginationWrap.innerHTML = '';
					return;
				}

				var html = '<ul>';
				for (var i = 1; i <= totalPages; i++) {
					html += '<li class="' + (i === currentPage ? 'active' : '') + '"><a href="#" data-page="' + i + '">' + (i < 10 ? '0' + i : i) + '</a></li>';
				}
				html += '</ul>';
				paginationWrap.innerHTML = html;
			}

			function renderCards() {
				var filtered = getFilteredCards();
				var totalPages = Math.max(1, Math.ceil(filtered.length / cardsPerPage));

				if (currentPage > totalPages) {
					currentPage = 1;
				}

				cards.forEach(function (card) {
					card.style.display = 'none';
				});

				var start = (currentPage - 1) * cardsPerPage;
				var end = start + cardsPerPage;
				filtered.slice(start, end).forEach(function (card) {
					card.style.display = '';
				});

				renderPagination(totalPages);
			}

			filterButtons.forEach(function (button) {
				button.addEventListener('click', function () {
					filterButtons.forEach(function (btn) {
						btn.classList.remove('active');
					});
					button.classList.add('active');
					currentFilter = button.getAttribute('data-filter') || 'all';
					currentPage = 1;
					renderCards();
				});
			});

			if (paginationWrap) {
				paginationWrap.addEventListener('click', function (event) {
					var pageLink = event.target.closest('a[data-page]');
					if (!pageLink) {
						return;
					}
					event.preventDefault();
					currentPage = parseInt(pageLink.getAttribute('data-page'), 10) || 1;
					renderCards();
				});
			}

			renderCards();
		})();
	</script>
</body>

</html>