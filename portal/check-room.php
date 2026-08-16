<?php
session_start();

// Check if user is logged in and session is active
if (!isset($_SESSION['user_id']) || !isset($_SESSION['timeout']) || $_SESSION['timeout'] < time()) {
	header("Location: login.html");
	exit();
}

// Extend session timeout upon activity
$_SESSION['timeout'] = time() + 1800;

include 'php/config.php';
require_once __DIR__ . '/php/academic_helper.php';

$user_id = (int)$_SESSION['user_id'];
$activeSession = pt_active_session();
$sessionId = $activeSession ? (int)$activeSession['id'] : 0;

$students_info = array();
$stmt = $conn->prepare("SELECT id, regNo, firstName, lastName, middleName, email, contactNo, userImage, department, level FROM userregistration WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 1) {
	$students_info = $result->fetch_assoc();
}
$stmt->close();

$assignment = null;
if ($sessionId > 0) {
	$stmt = $conn->prepare(
		"SELECT r.room_id, r.bed_space, r.room_category, rm.room_number, rm.full_capacity, rc.room_type, rc.rate, r.session_id
		 FROM reservations r
		 LEFT JOIN room rm ON r.room_id = rm.id
		 LEFT JOIN room_category rc ON rm.category_id = rc.id
		 WHERE r.user_id = ? AND r.session_id = ?
		 ORDER BY r.id DESC LIMIT 1"
	);
	$stmt->bind_param('ii', $user_id, $sessionId);
	$stmt->execute();
	$result = $stmt->get_result();
	if ($result->num_rows > 0) {
		$assignment = $result->fetch_assoc();
	}
	$stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

	<!-- Meta -->
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">


	<!-- Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- PAGE TITLE HERE -->
	<title>Pa-etos Hostel Accommodation </title>

	<!-- Favicon icon -->
	<link rel="shortcut icon" type="image/png" href="images/paetoa.png">
	<link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
	<link class="main-css" href="css/style.css" rel="stylesheet">
	<link href="vendor/toastr/css/toastr.min.css" rel="stylesheet">
	<link href="css/paetos.css" rel="stylesheet">
	<script>(function(){try{var t=localStorage.getItem('pt_theme');if(t!=='dark'&&t!=='light'){t=(typeof getCookie==='function'&&getCookie('version')==='dark')?'dark':'light'}document.documentElement.setAttribute('data-pt-theme',t)}catch(e){document.documentElement.setAttribute('data-pt-theme','light')}})();</script>

</head>

<body>

	<!-- Preloader with Paetos logo -->
	<div id="preloader">
		<div class="pt-pre-brand">
			<img class="pt-pre-logo" src="images/paetoa.png" alt="Pa-etos Logo" width="70" height="70">
			<div class="pt-spinner"></div>
		</div>
	</div>

	<!--**********************************
		Main wrapper start
	***********************************-->
	<div id="main-wrapper">

		<!--**********************************
			Nav header start
		***********************************-->
		<div class="nav-header">
			<a href="dashboard.php" class="brand-logo">
				<img class="logo-abbr" src="images/paetoa.svg" width="134" height="48" alt="Pa-etos Hostel Accommodation">
			</a>
			<div class="nav-control">
				<div class="hamburger">
					<span class="line"></span><span class="line"></span><span class="line"></span>
				</div>
			</div>
		</div>
		<!--**********************************
			Nav header end
		***********************************-->

		<div class="header">
			<div class="header-content">
				<nav class="navbar navbar-expand">
					<div class="collapse navbar-collapse justify-content-between">
						<div class="header-left">
							<div class="dashboard_bar">
								<?php echo htmlspecialchars($students_info['firstName'] ?? 'Guest'); ?> Check Room
							</div>

						</div>
						<ul class="navbar-nav header-right">

							<li class="nav-item dropdown notification_dropdown">
								<a class="nav-link bell dlab-theme-mode p-0" href="#;">
									<i id="icon-light" class="fas fa-sun"></i>
									<i id="icon-dark" class="fas fa-moon"></i>

								</a>
							</li>

							<li class="nav-item dropdown header-profile">
								<a class="nav-link" href="#;" role="button" data-bs-toggle="dropdown">
									<?php if (!empty($students_info['userImage'])) : ?>
										<img src="data:image/jpeg;base64,<?php echo base64_encode($students_info['userImage']); ?>" title="<?php echo htmlspecialchars($students_info['firstName'] ?? 'Guest'); ?> Image" alt="<?php echo htmlspecialchars($students_info['firstName'] ?? 'Guest'); ?> Image">
									<?php endif; ?>
								</a>
								<div class="dropdown-menu dropdown-menu-end">
									<a href="profile.php" class="dropdown-item ai-icon">
										<svg id="icon-user2" xmlns="http://www.w3.org/2000/svg" class="text-primary" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
											<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
											<circle cx="12" cy="7" r="4"></circle>
										</svg>
										<span class="ms-2">Profile </span>
									</a>

									<a href="php/logout.php" class="dropdown-item ai-icon">
										<svg xmlns="http://www.w3.org/2000/svg" class="text-danger" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
											<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
											<polyline points="16 17 21 12 16 7"></polyline>
											<line x1="21" y1="12" x2="9" y2="12"></line>
										</svg>
										<span class="ms-2">Logout </span>
									</a>
								</div>
							</li>
						</ul>
					</div>
				</nav>
			</div>
		</div>



		<!--**********************************
			Sidebar start
		***********************************-->
		<div class="dlabnav">
			<div class="dlabnav-scroll">
				<div class="dropdown header-profile2 ">
					<a class="nav-link " href="#;" role="button" data-bs-toggle="dropdown">
						<div class="header-info2 d-flex align-items-center">
							<?php if (!empty($students_info['userImage'])) : ?>
								<img src="data:image/jpeg;base64,<?php echo base64_encode($students_info['userImage']); ?>" title="<?php echo htmlspecialchars($students_info['firstName'] ?? 'Guest'); ?> Image" alt="<?php echo htmlspecialchars($students_info['firstName'] ?? 'Guest'); ?> Image">
							<?php endif; ?>
							<div class="d-flex align-items-center sidebar-info">
								<div>
									<span class="font-w400 d-block"><?php echo htmlspecialchars(($students_info['firstName'] ?? '') . ' ' . ($students_info['lastName'] ?? '')); ?></span>
									<small class="text-end font-w400"><?php echo htmlspecialchars($students_info['email'] ?? ''); ?></small>
								</div>
								<i class="fas fa-chevron-down"></i>
							</div>

						</div>
					</a>
					<div class="dropdown-menu dropdown-menu-end">
						<a href="profile.php" class="dropdown-item ai-icon ">
							<svg xmlns="http://www.w3.org/2000/svg" class="text-primary" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
								<circle cx="12" cy="7" r="4"></circle>
							</svg>
							<span class="ms-2">Profile </span>
						</a>

						<a href="php/logout.php" class="dropdown-item ai-icon">
							<svg xmlns="http://www.w3.org/2000/svg" class="text-danger" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
								<polyline points="16 17 21 12 16 7"></polyline>
								<line x1="21" y1="12" x2="9" y2="12"></line>
							</svg>
							<span class="ms-2">Logout </span>
						</a>
					</div>
				</div>
				<ul class="metismenu" id="menu">
					<li><a href="dashboard.php" data-pt-nav aria-expanded="false">
							<i class="flaticon-025-dashboard"></i>
							<span class="nav-text">Dashboard</span>
						</a>
					</li>
					<li><a href="book-hostel.php" data-pt-nav aria-expanded="false">
							<i class="flaticon-046-home"></i>
							<span class="nav-text">Book Room</span>
						</a>
					</li>
					<li class="mm-active"><a href="check-room.php" data-pt-nav aria-expanded="false">
							<i class="flaticon-046-home"></i>
							<span class="nav-text">Check Status</span>
						</a>
					</li>

				</ul>

				<div class="pt-sidebar-foot">
					<div class="pt-sidebar-item" role="button" aria-label="Toggle dark mode">
						<i class="fas fa-moon"></i>
						<span class="nav-text">Dark Mode</span>
						<label class="pt-switch">
							<input type="checkbox" id="pt-theme-switch">
							<span class="pt-switch-slider"></span>
						</label>
					</div>
					<a href="php/logout.php" class="pt-sidebar-item pt-sidebar-logout" aria-label="Logout">
						<i class="fas fa-sign-out-alt"></i>
						<span class="nav-text">Logout</span>
					</a>
				</div>

			</div>
		</div>
		<!--**********************************
			Sidebar end
		***********************************-->

		<div class="content-body" id="pt-content">
			<!-- row -->
			<div class="container-fluid">
				<div class="row">
					<div class="col-xl-8">
						<div class="card">
							<div class="card-header">
								<h4 class="card-title">Room Allocation</h4>
							</div>
							<div class="card-body">
								<?php if ($assignment) : ?>
									<div class="row">
										<div class="col-sm-6 m-b30">
											<label class="form-label">Room Number</label>
											<input type="text" class="form-control" value="<?php echo htmlspecialchars($assignment['room_number']); ?>" readonly>
										</div>
										<div class="col-sm-6 m-b30">
											<label class="form-label">Room Type</label>
											<input type="text" class="form-control" value="<?php echo htmlspecialchars($assignment['room_type'] ?? $assignment['room_category']); ?>" readonly>
										</div>
										<div class="col-sm-6 m-b30">
											<label class="form-label">Bed Space</label>
											<input type="text" class="form-control" value="<?php echo htmlspecialchars($assignment['bed_space']); ?>" readonly>
										</div>
										<div class="col-sm-6 m-b30">
											<label class="form-label">Room Capacity</label>
											<input type="text" class="form-control" value="<?php echo htmlspecialchars($assignment['full_capacity']); ?>" readonly>
										</div>
										<?php if (!empty($assignment['rate'])) : ?>
											<div class="col-sm-6 m-b30">
												<label class="form-label">Rate (NGN)</label>
												<input type="text" class="form-control" value="<?php echo number_format((float)$assignment['rate'], 2); ?>" readonly>
											</div>
										<?php endif; ?>
										<div class="col-12">
											<div class="alert alert-success mb-0">
												<i class="fas fa-check-circle me-2"></i>
												Your room has been assigned. Report to the hostel management desk for your room keys.
											</div>
										</div>
									</div>
								<?php else : ?>
									<div class="text-center py-4">
										<svg class="my-2" viewBox="0 0 24 24" width="80" height="80" stroke="currentColor" stroke-width="1" fill="none" stroke-linecap="round" stroke-linejoin="round">
											<path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
											<line x1="3" y1="6" x2="21" y2="6"></line>
										</svg>
										<h4 class="my-2">No room has been assigned to you yet</h4>
										<p class="text-muted mb-4">Once your payment is confirmed and a room is assigned, it will appear here.</p>
										<a class="btn btn-danger dlabnav-buy" href="book-hostel.php">Submit Payment Proof</a>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>

					<div class="col-xl-4">
						<div class="card">
							<div class="card-header">
								<h4 class="card-title">Your Details</h4>
							</div>
							<div class="card-body">
								<ul class="list-group">
									<li class="list-group-item d-flex justify-content-between">
										<span>Reg Number</span>
										<strong><?php echo htmlspecialchars($students_info['regNo'] ?? '-'); ?></strong>
									</li>
									<li class="list-group-item d-flex justify-content-between">
										<span>Department</span>
										<strong><?php echo htmlspecialchars($students_info['department'] ?? '-'); ?></strong>
									</li>
									<li class="list-group-item d-flex justify-content-between">
										<span>Level</span>
										<strong><?php echo htmlspecialchars($students_info['level'] ?? '-'); ?></strong>
									</li>
									<li class="list-group-item d-flex justify-content-between">
										<span>Email</span>
										<strong><?php echo htmlspecialchars($students_info['email'] ?? '-'); ?></strong>
									</li>
									<li class="list-group-item d-flex justify-content-between">
										<span>Phone</span>
										<strong><?php echo htmlspecialchars($students_info['contactNo'] ?? '-'); ?></strong>
									</li>
								</ul>
								<a href="edit-profile.php" class="btn btn-primary mt-3 w-100">Edit Profile</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>


		<!--**********************************
				Footer start
			***********************************-->
<div class="footer">
			<div class="copyright">
				<p>All rights reserved &copy; <?php echo date('Y'); ?> <a href="#" target="_blank">BMXCODERS</a></p>
			</div>
		</div>
		</div>


	</div>

	<!--**********************************
	Scripts
***********************************-->
	<!-- Required vendors -->
	<script src="vendor/global/global.min.js"></script>
	<script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
	<script src="vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.min.js"></script>

	<script src="js/custom.min.js"></script>
	<script src="js/dlabnav-init.js"></script>
	<script src="js/demo.js"></script>
	<!-- <script src="js/styleSwitcher.js"></script> -->

	<script>
		if (window.PT && !<?php echo $assignment ? 'true' : 'false'; ?>) {
			window.PT.warning('No room assigned yet', 'Room Allocation');
		}
	</script>

	<script src="vendor/toastr/js/toastr.min.js"></script>
	<script src="js/paetos.js"></script>
	<script src="js/pt-nav.js"></script>
</body>

</html>
