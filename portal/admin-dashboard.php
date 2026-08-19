<?php
require_once __DIR__ . '/php/rbac.php';

// Admin session required (any role)
pt_require_page('dashboard');

include 'php/fetch_admin_info.php';

require_once __DIR__ . '/php/academic_helper.php';

$activeSession = pt_active_session();
$sessionId = pt_active_session_id();

// ---- Stats (computed server-side: single page render, no per-card AJAX) ----
$userCount = 0;
$hostelCount = 0;
$categoryCount = 0;
$totalTaken = 0;
$totalAvailable = 0;
$perHostel = array();

if (isset($conn)) {
	$r = $conn->query("SELECT COUNT(*) c FROM userregistration");
	if ($r) {
		$userCount = (int) $r->fetch_assoc()['c'];
	}

	$r = $conn->query("SELECT COUNT(*) c FROM hostel");
	if ($r) {
		$hostelCount = (int) $r->fetch_assoc()['c'];
	}

	$r = $conn->query("SELECT COUNT(*) c FROM room_category");
	if ($r) {
		$categoryCount = (int) $r->fetch_assoc()['c'];
	}

	// Taken (student_name NOT NULL) vs Available (student_name IS NULL) per session
	if ($sessionId > 0) {
		$st = $conn->prepare("SELECT SUM(student_name IS NOT NULL) AS taken, SUM(student_name IS NULL) AS available FROM assign_room WHERE session_id = ?");
		if ($st) {
			$st->bind_param('i', $sessionId);
			$st->execute();
			$r = $st->get_result()->fetch_assoc();
			$totalTaken = (int) $r['taken'];
			$totalAvailable = (int) $r['available'];
			$st->close();
		}
	}

	$hRes = $conn->query("SELECT id, name FROM hostel ORDER BY id ASC");
	if ($hRes) {
		while ($h = $hRes->fetch_assoc()) {
			$hid = (int) $h['id'];
			$perHostel[$hid] = array(
				'name' => $h['name'],
				'taken' => 0,
				'available' => 0,
				'rooms' => 0,
			);
		}
	}

	if ($sessionId > 0) {
		$st = $conn->prepare("SELECT hostel_id, SUM(student_name IS NOT NULL) AS taken, SUM(student_name IS NULL) AS available FROM assign_room WHERE session_id = ? GROUP BY hostel_id");
		if ($st) {
			$st->bind_param('i', $sessionId);
			$st->execute();
			$ar = $st->get_result();
			while ($row = $ar->fetch_assoc()) {
				$hid = (int) $row['hostel_id'];
				if (isset($perHostel[$hid])) {
					$perHostel[$hid]['taken'] = (int) $row['taken'];
					$perHostel[$hid]['available'] = (int) $row['available'];
				}
			}
			$st->close();
		}
	}

	// Count distinct room names per hostel (after perHostel is initialized)
	if ($sessionId > 0) {
		$st = $conn->prepare("SELECT hostel_id, COUNT(DISTINCT SUBSTRING_INDEX(room_bunk, '-', 1)) AS c FROM assign_room WHERE session_id = ? GROUP BY hostel_id");
		if ($st) {
			$st->bind_param('i', $sessionId);
			$st->execute();
			$ar = $st->get_result();
			while ($row = $ar->fetch_assoc()) {
				$hid = (int) $row['hostel_id'];
				if (isset($perHostel[$hid])) {
					$perHostel[$hid]['rooms'] = (int) $row['c'];
				}
			}
			$st->close();
		}
	}
}

$hostelChartData = array_values($perHostel);

$pageTitle = 'Dashboard';
$pageHeader = 'Dashboard';
?>
<?php include 'includes/head.php'; ?>

<body>

	<?php include 'includes/header.php'; ?>
	<?php include 'includes/sidebar.php'; ?>

	<!--**********************************
	Content body start
***********************************-->
	<div class="content-body" id="pt-content">
		<div class="container-fluid">

			<?php if ($activeSession): ?>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
									<div>
										<span class="badge badge-success rounded-pill mb-2">Active Session</span>
										<h4 class="mb-1"><?php echo htmlspecialchars($activeSession['name']); ?></h4>
										<span class="text-muted">Activated
											<?php echo htmlspecialchars($activeSession['activated_at'] ?? 'n/a'); ?></span>
									</div>
									<div class="d-flex flex-wrap gap-2">
										<?php if (pt_can('manage_session')): ?>
											<a href="manage-session.php" class="btn btn-primary btn-sm">Manage Session</a>
										<?php endif; ?>
										<?php if (pt_can('assign_room')): ?>
											<a href="assigned_room.php" class="btn btn-primary btn-sm">Assign Room</a>
										<?php endif; ?>
										<!-- <?php if (pt_can('manage_hostel')): ?>
											<a href="manage-hostel.php" class="btn btn-primary btn-sm">Manage Hostel</a>
										<?php endif; ?> -->
										<?php if (pt_can('confirm_payment')): ?>
											<a href="confirm-payments.php" class="btn btn-primary btn-sm">Confirm Payments</a>
										<?php endif; ?>
										<?php if (pt_can('list_student')): ?>
											<a href="list-student.php" class="btn btn-primary btn-sm">List Students</a>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			<?php else: ?>
				<div class="row">
					<div class="col-12">
						<div class="alert alert-warning rounded-0">
							<i class="fas fa-exclamation-triangle me-2"></i>
							No session is currently active. Student bookings and payments are <strong>closed</strong> until
							you activate a session.
							<?php if (pt_can('manage_session')): ?>
								<a href="manage-session.php" class="alert-link">Manage Session</a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<div class="row">
				<div class="col-xl-12">
					<div class="card">
						<div class="card-body">
							<div class="row shapreter-row">
								<div class="col-xl-2 col-lg-4 col-sm-6 col-6">
									<div class="static-icon">
										<span>
											<i class="fas fa-users"></i>
										</span>
										<h3 class="count mb-0"><?php echo $userCount; ?></h3>
										<p class="mb-0">Registered Users</p>
									</div>
								</div>
								<div class="col-xl-2 col-lg-4 col-sm-6 col-6">
									<div class="static-icon">
										<span>
											<i class="fas fa-building"></i>
										</span>
										<h3 class="count mb-0"><?php echo $hostelCount; ?></h3>
										<p class="mb-0">Hostels</p>
									</div>
								</div>
								<?php foreach ($perHostel as $hid => $h): ?>
								<div class="col-xl-2 col-lg-4 col-sm-6 col-6">
									<div class="static-icon">
										<span>
											<i class="fas fa-door-open"></i>
										</span>
										<h3 class="count mb-0"><?php echo $h['rooms']; ?></h3>
										<p class="mb-0"><?php echo htmlspecialchars($h['name']); ?> Rooms</p>
									</div>
								</div>
								<?php endforeach; ?>
								<!-- <div class="col-xl-2 col-lg-4 col-sm-6 col-6">
									<div class="static-icon">
										<span>
											<i class="fas fa-tags"></i>
										</span>
										<h3 class="count mb-0"><?php echo $categoryCount; ?></h3>
										<p class="mb-0">Room Categories</p>
									</div>
								</div> -->
								<?php foreach ($perHostel as $hid => $h): ?>
								<div class="col-xl-2 col-lg-4 col-sm-6 col-6">
									<div class="static-icon">
										<span>
											<i class="fas fa-bed"></i>
										</span>
										<h3 class="count mb-0"><?php echo $h['taken'] . ' / ' . $h['available']; ?></h3>
										<p class="mb-0"><?php echo htmlspecialchars($h['name']); ?> Bed </p>
									</div>
								</div>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-xl-6">
					<div class="card">
						<div class="card-header">
							<h4 class="card-title">Assigned Users per Hostel</h4>
						</div>
						<div class="card-body">
							<div id="ptHostelDonut"></div>
						</div>
					</div>
				</div>
				<div class="col-xl-6">
					<div class="card">
						<div class="card-header">
							<h4 class="card-title">Bed Capacity vs Assigned</h4>
						</div>
						<div class="card-body">
							<div id="ptCapacityChart"></div>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<?php if (pt_can('manage_session')): ?>
					<div class="col-xl-3 col-xxl-3 col-sm-6">
						<a href="manage-session.php">
							<div class="social-graph-wrapper widget-facebook">
								<span class="s-icon">Manage Session</span>
							</div>
						</a>
					</div>
				<?php endif; ?>
				<?php if (pt_can('confirm_payment')): ?>
					<div class="col-xl-3 col-xxl-3 col-sm-6">
						<a href="confirm-payments.php">
							<div class="social-graph-wrapper widget-linkedin">
								<span class="s-icon">Confirm Payments<i class="fab fa-"></i></span>
							</div>
						</a>
					</div>
				<?php endif; ?>
				<?php if (pt_can('list_student')): ?>
					<div class="col-xl-3 col-xxl-3 col-sm-6">
						<a href="list-student.php">
							<div class="social-graph-wrapper widget-googleplus">
								<span class="s-icon">List Students<i class="fab fa-"></i></span>
							</div>
						</a>
					</div>
				<?php endif; ?>
				<?php if (pt_can('assign_room')): ?>
					<div class="col-xl-3 col-xxl-3 col-sm-6">
						<a href="assigned_room.php">
							<div class="social-graph-wrapper widget-twitter">
								<span class="s-icon">Assign Room<i class="fab fa-"></i></span>
							</div>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<script>
			// Chart data computed server-side in PHP
			var ptHostelData = <?php echo json_encode($hostelChartData); ?>;
			var ptChartInstances = {};

			function ptInitCharts() {
				if (!window.ApexCharts) return;
				var labels = [];
				var taken = [];
				var available = [];
				(ptHostelData || []).forEach(function (h) {
					labels.push(h.name);
					taken.push(h.taken);
					available.push(h.available);
				});

				if (ptChartInstances.donut) { ptChartInstances.donut.destroy(); }
				if (ptChartInstances.bar) { ptChartInstances.bar.destroy(); }

				var donutEl = document.getElementById('ptHostelDonut');
				var barEl = document.getElementById('ptCapacityChart');
				if (donutEl) {
					ptChartInstances.donut = new ApexCharts(donutEl, {
						chart: { type: 'donut', height: 320 },
						labels: labels,
						series: taken,
						colors: ['#F93A0B', '#ff8a3d', '#3a86ff', '#06d6a0', '#8338ec', '#f9c74f'],
						legend: { position: 'bottom' },
						responsive: [{ breakpoint: 768, options: { legend: { position: 'bottom' } } }]
					});
					ptChartInstances.donut.render();
				}
				if (barEl) {
					ptChartInstances.bar = new ApexCharts(barEl, {
						chart: { type: 'bar', height: 320, toolbar: { show: false } },
						series: [
							{ name: 'Taken', data: taken },
							{ name: 'Available', data: available }
						],
						colors: ['#F93A0B', '#06d6a0'],
						plotOptions: { bar: { borderRadius: 3, columnWidth: '45%' } },
						dataLabels: { enabled: false },
						xaxis: { categories: labels },
						legend: { position: 'bottom' }
					});
					ptChartInstances.bar.render();
				}
			}

			document.addEventListener('DOMContentLoaded', ptInitCharts);
			window.addEventListener('load', ptInitCharts);
			document.addEventListener('pt:content-loaded', ptInitCharts);
		</script>

	</div>
	<!--**********************************
	Content body end
***********************************-->

	<?php include 'includes/footer.php'; ?>